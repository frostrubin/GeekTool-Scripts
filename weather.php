#!/usr/bin/env php
<?php
//   This script tries to get your location by
//   WLAN Name or the MAC address of your router.
//   This way, weather can be displayed 
//   according to your location!
   
//   1. Go to http://weather.yahoo.com and search for the city you want to monitor.
//   2. Copy the URL; it should look similar to this:
//      http://weather.yahoo.com/united-states/arkansas/washington-2514857/
//      or http://weather.yahoo.com/germany/baden-wurttemberg/mannheim-673711/
//   3. Append an ?unit=c or ?unit=f to the URL, so it looks similar to:
//      http://weather.yahoo.com/united-states/arkansas/washington-2514857/?unit=f
//      or http://weather.yahoo.com/germany/baden-wurttemberg/mannheim-673711/?unit=c
//   4. Look at the SSIDs array. It contains the
//      WLAN names, a short location name like "Frankfurt, Germany" and the 
//      URL to the yahoo weather page of that location.
//      Keep the pre-defined values to copy their syntax (this needs to be copied exactly!)
//      and add your own WLAN name, city name and yahoo URL.
//      "WUENSCH" is an example WLAN Name.
//      "Mannheim, Germany" is an example City Location
//      "http://weather.yahoo.com/germany/baden-wurttemberg/mannheim-673711/?unit=c" 
//      is the corresponding weather url from yahoo (with temperatures in Celsius).   
//   5. Change the paths of the image files (it's right below this text).
//      Under these paths, the images of clouds and suns and stuff will be stored.
//      Also, in the "weather.txt" file, the according texts will be stored as well.
//   6. Add this Script to GeekTool multiple times!
//      Once with the "get" parameter, once with the "info", once with "forecast1", etc.
//   7. Also, add the generated image files to GeekTool
   
   
//   Oh, by the way: if you happen to have no WLAN connection (at certain locations, or in general)
//   Simply run the "mac" parameter to get the MAC Address of your current router. 
//   In the SSIDs array, add it instead (or additionally) of the WLAN SSID.
//   So if you move your laptop around, if the location can be found via SSID or used router
//   The according weather information can be displayed.
     
//Setup
$long_iconmap = '/Users/bernhard/.NerdTool/files/longIconMap.png';
$current_icon = '/Users/bernhard/.NerdTool/files/current.png';
$forecast_icon1 = '/Users/bernhard/.NerdTool/files/forecast1.png';
$forecast_icon2 = '/Users/bernhard/.NerdTool/files/forecast2.png';
$forecast_icon3 = '/Users/bernhard/.NerdTool/files/forecast3.png';
$forecast_icon4 = '/Users/bernhard/.NerdTool/files/forecast4.png';
$forecast_icon5 = '/Users/bernhard/.NerdTool/files/forecast5.png';
$weather_file = '/Users/bernhard/.NerdTool/files/weather.txt';
   
   
$SSIDs = array(
               "WUENSCH" => array(1 => 'Reinheim, Germany', 2 => 'http://weather.yahoo.com/germany/hesse/reinheim-687697/?unit=c'),
               "Lecker Pizza" => array(1 => 'Darmstadt, Germany', 2 => 'http://weather.yahoo.com/germany/hesse/darmstadt-643787/?unit=c'),
               "0:23:8:cd:c6:76" => array(1 => 'Darmstadt, Germany', 2 => 'http://weather.yahoo.com/germany/hesse/darmstadt-643787/?unit=c'),
               "BaWebAuth" => array(1 => 'Mannheim, Germany', 2 => 'http://weather.yahoo.com/germany/baden-wurttemberg/mannheim-673711/?unit=c'),
               "unser netz" => array(1 => 'Reinheim, Germany', 2 => 'http://weather.yahoo.com/germany/hesse/reinheim-687697/?unit=c'),
               "0:15:c:6a:e1:b3" => array(1 => 'Reinheim, Germany', 2 => 'http://weather.yahoo.com/germany/hesse/reinheim-687697/?unit=c'),
               "0:11:9:af:c7:f2" => array(1 => 'Washington DC, USA', 2 => 'http://weather.yahoo.com/united-states/district-of-columbia/washington-2514815/?unit=f')
               );  
function get_string_between($string, $start, $end){
   $string = " ".$string;
   $ini = strpos($string,$start);
   if ($ini == 0) return "";
   $ini += strlen($start);
   $len = strpos($string,$end,$ini) - $ini;
   return substr($string,$ini,$len);
}
   
function get_weatherdata() {
   function delete_files(){      
      @unlink($GLOBALS['long_iconmap']);
      @unlink($GLOBALS['current_icon']);
      @unlink($GLOBALS['forecast_icon1']);
      @unlink($GLOBALS['forecast_icon2']);
      @unlink($GLOBALS['forecast_icon3']);
      @unlink($GLOBALS['forecast_icon4']);
      @unlink($GLOBALS['forecast_icon5']);
      @unlink($GLOBALS['weather_file']);
   }

   function str_replace_once($needle, $replace, $haystack) { 
      // Looks for the first occurence of $needle in $haystack 
      // and replaces it with $replace. 
      $pos = strpos($haystack, $needle); 
      if ($pos === false) { 
         // Nothing found 
         return $haystack; 
      } 
      return substr_replace($haystack, $replace, $pos, strlen($needle)); 
   }


   sleep(15); // This is neccessary after wakeup, network connections need 2 seconds to re-establish.

   // First part: try to get ethernet mac address of gateway   
   $eth_route = shell_exec('route get google.com 2>&1');

   $contains = strpos($eth_route,'bad address:');

   if($contains != '') {
      echo "You don't seem to have an internet connection...";
      delete_files();
      exit;
   }

   $gateway = get_string_between($eth_route,'gateway: ','interface: ');
   $arp = shell_exec('arp '.$gateway);
   $mac = get_string_between($arp,'at ',' on');  
   // We now have the mac address of the gateway


   $yahoo_url = $GLOBALS['SSIDs'][$mac][2];
   if($yahoo_url == '') {
      //Nothing has to happen, since we will try to identify the location via WLAN name...
      //echo "Ethernet MAC address was not in list";
      //delete_files();
      //exit;
      
      $network_name = shell_exec('system_profiler SPAirPortDataType');
      
      $contains = strpos($network_name,'Status: Connected');
      
      if($contains === false) {
         echo "Ethernet Address was not in list and Airport is not connected";
         delete_files();
         exit;
      }
      
      $network_name = ereg_replace("\n", " ", $network_name); //remove line breaks
      $network_name = ereg_replace("\r", " ", $network_name); //remove line breaks
      
      $network_name = trim(get_string_between($network_name,'Current Network Information:','PHY'));
      $network_name = substr_replace($network_name ,"",-1);
      
      $yahoo_url = $GLOBALS['SSIDs'][$network_name][2];
      if($yahoo_url == '') {
         //OK, here we're screwed. No MAC location, no WLAN location
         echo "Airport Network not in Location List";
         delete_files();
         exit;
      }else {
         echo "Yahoo URL found via WLAN. ";
         echo "Location is: ".$GLOBALS['SSIDs'][$network_name][1];
      }
      
   } else {
      echo "Yahoo URL found via MAC. ";
      echo "Location is: ".$GLOBALS['SSIDs'][$mac][1];
   }


   //Download Weather Homepage   
   try {
      $error = 'Data could not be retreived';
      $string = file_get_contents($yahoo_url);    
   }
   catch (Exception $e)
   {
      echo 'An Error occured: ',  $e->getMessage(), "\n";
      delete_files();
      exit;
   } 

   $string = ereg_replace("\n", " ", $string); //remove line breaks
   $string = ereg_replace("\r", " ", $string); //remove line breaks

   $contains = strpos($string,'Cisco Systems');

   if($contains != '') {
      echo "Cisco Login Page/No Internet";
      delete_files();
      exit;
   }


   $feelslike = get_string_between($string, "<dt>Feels", "</dd>");
   $feelslike = str_replace('Like:</dt><dd>','',$feelslike);

   $currentcond = get_string_between($string, '<div id="yw-cond">','</div>');

   $currenttemp = get_string_between($string, '<div id="yw-temp">','</div>'); 

   $currentloc = get_string_between($string, '<h1>','</h1>');
   $currentloc = str_replace('Weather','',$currentloc);

   $high = get_string_between($string, "<p>High:", "&#176;");
   $low = get_string_between($string, "Low: ", "&#176;");

   $currenticon = get_string_between($string, '<div class="forecast-icon"','png'); 
   $currenticon = str_replace('style="background:url(\'','',$currenticon).png;
   $currenticon = str_replace(' ','',$currenticon);

   $asof = get_string_between($string, '<em>Current conditions as of','</em>');  


   $barometer = get_string_between($string, "<dt>Barometer:", "</dd>");
   $barometer = str_replace('</dt>','',$barometer);
   $barometer = preg_replace ('/<dd(.*?)>/','',$barometer);

   $humidity = get_string_between($string, "<dt>Humidity:", "%</dd>");
   $humidity = str_replace('</dt><dd>','',$humidity);   

   $visibility = get_string_between($string, "<dt>Visibility:", "</dd>");
   $visibility = str_replace('</dt><dd>','',$visibility);  

   $dewpoint = get_string_between($string, "<dt>Dewpoint:", "</dd>");
   $dewpoint = str_replace('</dt><dd>','',$dewpoint);  

   $wind = get_string_between($string, "<dt>Wind:", "</dd>");
   $wind = str_replace('</dt><dd>','',$wind);


   $sunrise = get_string_between($string, "<dt>Sunrise:", "AM</dd>");
   $sunrise = str_replace('</dt><dd>','',$sunrise);

   $sunset = get_string_between($string, "<dt>Sunset:", "PM</dd>");
   $sunset = str_replace('</dt><dd>','',$sunset);

   //Download forecast icons
   if (file_exists($GLOBALS['long_iconmap'])) {
      //No need to download it again
   } else {
      $img = file_get_contents('http://l.yimg.com/a/lib/ywc/img/wicons.png');
      $myFile = $GLOBALS['long_iconmap'];
      $fh = fopen($myFile, 'w') or die("can't open file");
      fwrite($fh, $img);
      fclose($fh);
   }

   //Download current condition icon
   $img = file_get_contents($currenticon);
   $myFile = $GLOBALS['current_icon'];
   $fh = fopen($myFile, 'w') or die("can't open file");
   fwrite($fh, $img);
   fclose($fh);


   $first_icon_width = get_string_between($string, '<img id="wiff" style="background-position:', 'px');
   $first_icon_width = str_replace(' ','',$first_icon_width);
   $first_icon_width = str_replace('-','',$first_icon_width);

   $forecasts = get_string_between($string, '<tr class="fiveday-icons">','<td rowspan="2" class="extended">');

   $forecasts = str_replace('- ','',$forecasts);
   $forecasts = str_replace('<img id="wiff" style="background-position: ',"\n",$forecasts);
   $forecasts = str_replace('T-storms','Gewitter',$forecasts); //Only to remove the "-"


   $first_icon_position = strpos($forecasts,'-');
   $first_icon_position = substr($forecasts,$first_icon_position,8);
   $first_icon_position = get_string_between($first_icon_position,'-','px');

   $forecasts = substr_replace($forecasts,'',strpos($forecasts,'-'),20);

   $second_icon_position = strpos($forecasts,'-');
   $second_icon_position = substr($forecasts,$second_icon_position,8);
   $second_icon_position = get_string_between($second_icon_position,'-','px');

   $forecasts = substr_replace($forecasts,'',strpos($forecasts,'-'),20);

   $third_icon_position = strpos($forecasts,'-');
   $third_icon_position = substr($forecasts,$third_icon_position,8);
   $third_icon_position = get_string_between($third_icon_position,'-','px');

   $forecasts = substr_replace($forecasts,'',strpos($forecasts,'-'),20);

   $fourth_icon_position = strpos($forecasts,'-');
   $fourth_icon_position = substr($forecasts,$fourth_icon_position,8);
   $fourth_icon_position = get_string_between($fourth_icon_position,'-','px');

   $forecasts = substr_replace($forecasts,'',strpos($forecasts,'-'),20);

   $fifth_icon_position = strpos($forecasts,'-');
   $fifth_icon_position = substr($forecasts,$fifth_icon_position,8);
   $fifth_icon_position = get_string_between($fifth_icon_position,'-','px');

   $forecasts = substr_replace($forecasts,'',strpos($forecasts,'-'),20);


   // Resample
   $filename = $GLOBALS['long_iconmap'];
   $image_p = imagecreatetruecolor(61, 34);
   imagealphablending( $image_p, false );
   imagesavealpha( $image_p, true );
   $image = imagecreatefrompng($filename);

   //Create first forecast image
   imagecopyResampled($image_p, $image, 0, 0, $first_icon_position, 0, 61, 34, 61, 34);
   imagepng($image_p,$GLOBALS['forecast_icon1']);
   //Create second forecast image
   imagecopyResampled($image_p, $image, 0, 0, $second_icon_position, 0, 61, 34, 61, 34);
   imagepng($image_p,$GLOBALS['forecast_icon2']);
   //Create third forecast image
   imagecopyResampled($image_p, $image, 0, 0, $third_icon_position, 0, 61, 34, 61, 34);
   imagepng($image_p,$GLOBALS['forecast_icon3']);
   //Create fourth forecast image
   imagecopyResampled($image_p, $image, 0, 0, $fourth_icon_position, 0, 61, 34, 61, 34);
   imagepng($image_p,$GLOBALS['forecast_icon4']);
   //Create fifth forecast image
   imagecopyResampled($image_p, $image, 0, 0, $fifth_icon_position, 0, 61, 34, 61, 34);
   imagepng($image_p,$GLOBALS['forecast_icon5']);

   $forecasts = str_replace('Gewitter','T-storms',$forecasts);
   $first_icon_condition = strpos($forecasts,'<br/>');
   $first_icon_condition = substr($forecasts,$first_icon_condition,50);
   $first_icon_condition = get_string_between($first_icon_condition,'<br/>','</div>');

   $forecasts = substr_replace($forecasts,'',strpos($forecasts,'<br/>'),20);

   $second_icon_condition = strpos($forecasts,'<br/>');
   $second_icon_condition = substr($forecasts,$second_icon_condition,50);
   $second_icon_condition = get_string_between($second_icon_condition,'<br/>','</div>');

   $forecasts = substr_replace($forecasts,'',strpos($forecasts,'<br/>'),20);

   $third_icon_condition = strpos($forecasts,'<br/>');
   $third_icon_condition = substr($forecasts,$third_icon_condition,50);
   $third_icon_condition = get_string_between($third_icon_condition,'<br/>','</div>');

   $forecasts = substr_replace($forecasts,'',strpos($forecasts,'<br/>'),20);

   $fourth_icon_condition = strpos($forecasts,'<br/>');
   $fourth_icon_condition = substr($forecasts,$fourth_icon_condition,50);
   $fourth_icon_condition = get_string_between($fourth_icon_condition,'<br/>','</div>');

   $forecasts = substr_replace($forecasts,'',strpos($forecasts,'<br/>'),20);

   $fifth_icon_condition = strpos($forecasts,'<br/>');
   $fifth_icon_condition = substr($forecasts,$fifth_icon_condition,50);
   $fifth_icon_condition = get_string_between($fifth_icon_condition,'<br/>','</div>');

   $forecasts = substr_replace($forecasts,'',strpos($forecasts,'<br/>'),20);

   $days= get_string_between($string,'<div id="yw-fivedayforecast"','</tr>');
   $days= str_replace('class="night">','',$days);
   $days = str_replace('</th><th>',' ',$days);
   $days = str_replace('<table>','',$days);
   $days = str_replace('<tr>','',$days);
   $days = str_replace('<th>','',$days);
   $days = str_replace('</th>','',$days);
   $days = preg_replace('/<th(.*?)Day/','',$days);
   $days = str_replace('>','',$days);
   $days = trim($days);
   $data = explode(" ", $days); 

   $temps = get_string_between($string,'<tr class="fiveday-temps">','</tr>');

   $first_icon_temp = get_string_between($temps,'<td>','</td>');
   $temps = str_replace_once($first_icon_temp,'',$temps);
   $temps = str_replace_once('<td></td>','',$temps);

   $second_icon_temp = get_string_between($temps,'<td>','</td>');
   $temps = str_replace_once($second_icon_temp,'',$temps);
   $temps = str_replace_once('<td></td>','',$temps);

   $third_icon_temp = get_string_between($temps,'<td>','</td>');
   $temps = str_replace_once($third_icon_temp,'',$temps);
   $temps = str_replace_once('<td></td>','',$temps);

   $fourth_icon_temp = get_string_between($temps,'<td>','</td>');
   $temps = str_replace_once($fourth_icon_temp,'',$temps);
   $temps = str_replace_once('<td></td>','',$temps);   

   $fifth_icon_temp = get_string_between($temps,'<td>','</td>');
   $temps = str_replace_once($fifth_icon_temp,'',$temps);
   $temps = str_replace_once('<td></td>','',$temps);   


   $first_icon_temp = str_replace('&#176;','',$first_icon_temp);
   $first_icon_high = trim(preg_replace('/<div>(.*?)<\/div>/','',$first_icon_temp));
   $first_icon_low = get_string_between($first_icon_temp,'<div>','</div>');

   $second_icon_temp = str_replace('&#176;','',$second_icon_temp);
   $second_icon_high = trim(preg_replace('/<div>(.*?)<\/div>/','',$second_icon_temp));
   $second_icon_low = get_string_between($second_icon_temp,'<div>','</div>'); 

   $third_icon_temp = str_replace('&#176;','',$third_icon_temp);
   $third_icon_high = trim(preg_replace('/<div>(.*?)<\/div>/','',$third_icon_temp));
   $third_icon_low = get_string_between($third_icon_temp,'<div>','</div>');    

   $fourth_icon_temp = str_replace('&#176;','',$fourth_icon_temp);
   $fourth_icon_high = trim(preg_replace('/<div>(.*?)<\/div>/','',$fourth_icon_temp));
   $fourth_icon_low = get_string_between($fourth_icon_temp,'<div>','</div>');   


   $fifth_icon_temp = str_replace('&#176;','',$fifth_icon_temp);
   $fifth_icon_high = trim(preg_replace('/<div>(.*?)<\/div>/','',$fifth_icon_temp));
   $fifth_icon_low = get_string_between($fifth_icon_temp,'<div>','</div>');

   $myFile = $GLOBALS['weather_file'];
   $fh = fopen($myFile, 'w') or die("can't open file");
   fwrite($fh, '<?php'."\n".
          '$feelslike="'.trim($feelslike).'";'."\n".
          '$currentcond="'.trim($currentcond).'";'."\n".
          '$asof="'.trim($asof).'";'."\n".
          '$currenttemp="'.trim($currenttemp).'";'."\n".
          '$barometer="'.trim($barometer).'";'."\n".
          '$humidity="'.trim($humidity).'";'."\n".
          '$visibility="'.trim($visibility).'";'."\n".
          '$dewpoint="'.trim($dewpoint).'";'."\n".
          '$wind="'.trim($wind).'";'."\n".
          '$sunrise="'.trim($sunrise).'";'."\n".
          '$sunset="'.trim($sunset).'";'."\n".
          '$high="'.trim($high).'";'."\n".
          '$low="'.trim($low).'";'."\n".
          '$first_icon_condition="'.trim($first_icon_condition).'";'."\n".
          '$second_icon_condition="'.trim($second_icon_condition).'";'."\n".
          '$third_icon_condition="'.trim($third_icon_condition).'";'."\n".
          '$fourth_icon_condition="'.trim($fourth_icon_condition).'";'."\n".
          '$fifth_icon_condition="'.trim($fifth_icon_condition).'";'."\n".
          '$currentloc="'.trim($currentloc).'";'."\n".
          '$first_icon_day="'.$data[0].'";'."\n".
          '$second_icon_day="'.$data[1].'";'."\n".
          '$third_icon_day="'.$data[2].'";'."\n".
          '$fourth_icon_day="'.$data[3].'";'."\n".
          '$fifth_icon_day="'.$data[4].'";'."\n".
          '$first_icon_high="'.trim($first_icon_high).'";'."\n".
          '$second_icon_high="'.trim($second_icon_high).'";'."\n".
          '$third_icon_high="'.trim($third_icon_high).'";'."\n".
          '$fourth_icon_high="'.trim($fourth_icon_high).'";'."\n".
          '$fifth_icon_high="'.trim($fifth_icon_high).'";'."\n".
          '$first_icon_low="'.trim($first_icon_low).'";'."\n".
          '$second_icon_low="'.trim($second_icon_low).'";'."\n".
          '$third_icon_low="'.trim($third_icon_low).'";'."\n".
          '$fourth_icon_low="'.trim($fourth_icon_low).'";'."\n".
          '$fifth_icon_low="'.trim($fifth_icon_low).'";'."\n".
          '?'.'>'
          );
   fclose($fh);
}
   
function longday($dayname) {
   return str_replace('Mon','Monday',str_replace('Tue','Tuesday',
          str_replace('Wed','Wednesday',
          str_replace('Thu','Thursday',
          str_replace('Fri','Friday',
          str_replace('Sat','Saturday',
          str_replace('Sun','Sunday',
                      $dayname)
                      ))))));
   
}
   
function curr() {
   @include($GLOBALS['weather_file']);
   echo $low."\n".str_replace('&#176;','',$currenttemp)."\n".$high;
}

function info() {
   $success = @include($GLOBALS['weather_file']);
   
   if($success === false) {
      echo "   ";
      exit;
   }
   
   $out=preg_split('/\s+/',trim($barometer)); 
   $lastword = $out[count($out)-1];  
   
   if($lastword == 'rapidly') {
      $lastword = $out[count($out)-2].' '.$lastword;
   }
   
   echo str_replace('  Forecasts','',$currentloc).', '.str_replace('CEST','',$asof)."\n";
   echo 'Wind: '.$wind."\n"; 
   echo 'H: '.$humidity.'%, '.'B: '.$lastword."\n";
   echo $currentcond."\n"; 
   echo 'Feels like: '.str_replace(' &deg;',shell_exec("echo $'\xB0'"),str_replace('C','',str_replace('F','',$feelslike)));
}
  
function forecast1() {
   @include($GLOBALS['weather_file']);
   #echo $first_icon_day."\n";
   #echo $first_icon_condition."\n";
   #echo $first_icon_high."\n";
   #echo $first_icon_low;
   if (strpos($first_icon_low,'ow:') != false) {
      echo $first_icon_day."\n".str_replace('Low: ','',$first_icon_low).' - '.str_replace('&#176;','',$currenttemp).' - '.str_replace('High: ','',$first_icon_high);
   } else {
      echo " ";
   }
}
   
function forecast2() {
   @include($GLOBALS['weather_file']);
   #echo $second_icon_day."\n";
   #echo $second_icon_condition."\n";
   #echo $second_icon_high."\n";
   #echo $second_icon_low;
   if (strpos($second_icon_low,'ow:') != false) {
      echo $second_icon_day."\n".str_replace('Low: ','',$second_icon_low).' - '.str_replace('High: ','',$second_icon_high);
   } else {
      echo " ";
   }
}

function forecast3() {
   @include($GLOBALS['weather_file']);
   #echo $third_icon_day."\n";
   #echo $third_icon_condition."\n";
   #echo $third_icon_high."\n";
   #echo $third_icon_low;
   $third_icon_day = longday($third_icon_day);
   
   if (strpos($third_icon_low,'ow:') != false) {
      echo $third_icon_day."\n".str_replace('Low: ','',$third_icon_low).' - '.str_replace('High: ','',$third_icon_high);
   } else {
      echo " ";
   }
}
   
function forecast4() {
   @include($GLOBALS['weather_file']);
   #echo $fourth_icon_day."\n";
   #echo $fourth_icon_condition."\n";
   #echo $fourth_icon_high."\n";
   #echo $fourth_icon_low;
   $fourth_icon_day = longday($fourth_icon_day);
   
   if (strpos($fourth_icon_low,'ow:') != false) {
      echo $fourth_icon_day."\n".str_replace('Low: ','',$fourth_icon_low).' - '.str_replace('High: ','',$fourth_icon_high);
   } else {
      echo " ";
   }
}
   
function forecast5() {
   @include($GLOBALS['weather_file']);
   #echo $fifth_icon_day."\n";
   #echo $fifth_icon_condition."\n";
   #echo $fifth_icon_high."\n";
   #echo $fifth_icon_low;
   $fifth_icon_day = longday($fifth_icon_day);
   
   if (strpos($fifth_icon_low,'ow:') != false) {
      echo $fifth_icon_day."\n".str_replace('Low: ','',$fifth_icon_low).' - '.str_replace('High: ','',$fifth_icon_high);
   } else {
      echo " ";
   }
}
   
function MAChelper(){
   $eth_route = shell_exec('route get google.com 2>&1');
   $contains = strpos($eth_route,'bad address:');
   
   if($contains != '') {
      echo "You don't seem to have an internet connection..."."\n";
      echo "An Internet Connection is needed to have weather info.... ;-)\n";
      exit;
   }
   
   $gateway = get_string_between($eth_route,'gateway: ','interface: ');
   $arp = shell_exec('arp '.$gateway);
   $mac = get_string_between($arp,'at ',' on');
   
   if($mac != '') {
      echo "The MAC Address you have to enter is: ".$mac."\n";
      echo "This is the MAC Address of your Router. Since (normally) the router is stationary, the Address can be used to set your location.\n";
      echo "You have to enter it into the SSIDs array at the beginning of this script and add the according yahoo url.\n";
   } else {
      echo "There was an Error. Sorry. Your MAC Address could not be determined.\n";
      echo "However, you can still try to use your WLAN Name to determine the location.\n";
   }
}
   
   

function parameters(){
   echo 'Valid parameters are: "mac", "get", "curr", "info", "forecast1", "forecast2", "forecast3", "forecast4" and "forecast5"'."\n";   
}
   
if ($argc > 2) {
   echo 'Only one parameter is allowed.'."\n";   
   parameters();
} elseif ($argc == 2) {
   switch ($argv[1]) {
      case 'get':
         get_weatherdata();
         break;
      case 'curr':
         curr();
         break;
      case 'info':
         info();
         break;
      case 'forecast1':
         forecast1();
         break;
      case 'forecast2':
         forecast2();
         break;
      case 'forecast3':
         forecast3();
         break;
      case 'forecast4':
         forecast4();
         break;
      case 'forecast5':
         forecast5();
         break;
      case 'mac';
         MAChelper();
      default:
         echo 'The parameter '.$argv[1].' does not exist.'."\n";
         parameters();
   }
} else {
   echo 'Sorry, but you have to supply a parameter.'."\n";
   parameters();
   echo 'Since you did not specify a parameter, I am assuming, that you wanted to get your MAC Address in order to determine your location'."\n";
   MAChelper();
}
?>