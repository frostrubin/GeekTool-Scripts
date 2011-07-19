<?php
  function get_string_between($string, $start, $end){
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
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
  
  function download_url($url) {
    $url = str_replace(' ','%20',$url);
    try {
      $error = 'Data could not be retreived';
      $string = @file_get_contents($url);
      return $string;
      
    }
    catch (Exception $e)
    {
      echo 'An Error occured: ',  $e->getMessage(), "\n";
    } 
  }
  
  function get_google_reverse_geolocation($lat,$long) {
    # Prepare Google Reverse Geo Lookup Coordinates
    $GoogleReverseGeoLatitude = str_replace('+','',$lat);
    $GoogleReverseGeoLongitude = str_replace('+','',$long);
    
    # Download Google Reverse Geo Lookup Address  
    $GoogleReverseGeoURL = 'http://maps.google.com/maps/api/geocode/xml?latlng='.$GoogleReverseGeoLatitude.','.$GoogleReverseGeoLongitude.'&sensor=true';
    $GoogleReverseGeo = download_url($GoogleReverseGeoURL);
    $GoogleReverseGeoLocation = get_string_between($GoogleReverseGeo, '<formatted_address>','</formatted_address>');
    echo $GoogleReverseGeoLocation;
    return $GoogleReverseGeoLocation;
  }
  
  function parse_google_weather_xml($xml) {
    if(!$xml = @simplexml_load_string(utf8_encode($xml))) {
      die('Google Weather XML-Document could not be parsed!');
    }
    
    // Allgemeine Informationen
    $weather['city'] = (string)@$xml->weather->forecast_information->city->attributes()->data;
    $weather['date'] = (string)@$xml->weather->forecast_information->forecast_date->attributes()->data;
    $weather['time'] = (string)@$xml->weather->forecast_information->current_date_time->attributes()->data;
    
    // Aktuelles weather
    $weather[0]['condition']   = (string)@$xml->weather->current_conditions->condition->attributes()->data;
    $weather[0]['temperature'] = (string)@$xml->weather->current_conditions->temp_c->attributes()->data;
    $weather[0]['humidity']    = (string)@$xml->weather->current_conditions->humidity->attributes()->data;
    $weather[0]['wind']        = (string)@$xml->weather->current_conditions->wind_condition->attributes()->data;
    $weather[0]['icon']        = 'http://www.google.com'.(string)@$xml->weather->current_conditions->icon->attributes()->data;
    
    $i = 1;
    foreach($xml->weather->forecast_conditions as $forecast) {
      $weather[$i]['weekday']   = (string)@$forecast->day_of_week->attributes()->data;
      $weather[$i]['condition'] = (string)@$forecast->condition->attributes()->data;
      $weather[$i]['low']       = (string)@$forecast->low->attributes()->data;
      $weather[$i]['high']      = (string)@$forecast->high->attributes()->data;
      $weather[$i]['icon']      = 'http://www.google.com'.(string)@$forecast->icon->attributes()->data;
      
      $i++;
    }
    return $weather;
  }
  
  function parse_wunderground_weather_xml($xml) {
    if(!$xml = @simplexml_load_string($xml)) {
      die('Wunderground Weather XML-Document could not be parsed!');
    }
    
    // Allgemeine Informationen
    $weather['city'] = '';
    $weather['date'] = $xml->simpleforecast->forecastday[0]->date[0]->year.'-'.
    $xml->simpleforecast->forecastday[0]->date[0]->month.'-'.
    $xml->simpleforecast->forecastday[0]->date[0]->day;
    $weather['time'] = $weather['date'].' '.
    $xml->simpleforecast->forecastday[0]->date[0]->hour.':'.
    $xml->simpleforecast->forecastday[0]->date[0]->min.':'.
    $xml->simpleforecast->forecastday[0]->date[0]->sec;
    
    
    $i = 0;
    foreach($xml->simpleforecast->forecastday as $forecast) {
      $weather[$i]['weekday']   = (string)@$forecast->date->weekday[0];
      $weather[$i]['condition'] = (string)@$forecast->conditions[0];
      $weather[$i]['low']       = (string)@$forecast->low->celsius[0];
      $weather[$i]['high']      = (string)@$forecast->high->celsius[0];
      
      $icon_sets = @$forecast->icons->icon_set;
      foreach ($icon_sets as $icon_set) {
        if ( $icon_set['name'] == 'Incredible' ) {
          $weather[$i]['icon'] = (string)$icon_set->icon_url;
        }
      }
      
      $i++;
    }
    return $weather;
  }
  
  function parse_yahoo_woeid_xml($xml) {
    if(!$xml = @simplexml_load_string($xml)) {
      die('Yahoo WOEID XML-Document could not be parsed!');
    }
    return @$xml->Result->woeid[0];
  }
  
  function google_weather_by_address($address) {
    
    # Get Google Weather by Address
    $address = str_replace(' ','%20',$address);
    $GoogleWeatherByAddressURL = 'http://www.google.com/ig/api?weather='.$address;
    $GoogleWeatherByAddress = download_url($GoogleWeatherByAddressURL);
    return parse_google_weather_xml($GoogleWeatherByAddress);
  }
  
  function parse_yahoo_weather_rss($rss) {
    return get_string_between($rss,'<link>','</link>');
  }
  
  function google_weather_by_latlong($lat,$long) {
    # Remove unwanted characters
    $lat  = str_replace('.','',$lat);
    $long = str_replace('.','',$long);
    $lat  = str_replace('+','',$lat);
    $long = str_replace('+','',$long);
    
    # Shorten coordinates
    $lat  = substr($lat,0,7);
    $long = substr($long,0,7); 
    
    # Get Google Weather by Latitude and Longitude
    $GoogleWeatherByLatLongURL = 'http://www.google.com/ig/api?weather=,,,'.$lat.','.$long;
    $GoogleWeatherByLatLong = download_url($GoogleWeatherByLatLongURL);
    echo $GoogleWeatherByLatLongURL;
    return parse_google_weather_xml($GoogleWeatherByLatLong);    
  }
  
  function wunderground_weather_by_latlong($lat,$long) {
    # Remove unwanted characters
    $lat  = str_replace('+','',$lat);
    $long = str_replace('+','',$long);
    
    # Get Wunderground Weather by Latitude and Longitude
    $WundergroundWeatherByLatLongURL = 'http://api.wunderground.com/auto/wui/geo/ForecastXML/index.xml?query='.$lat.','.$long;
    $WundergroundWeatherByLatLong = download_url($WundergroundWeatherByLatLongURL);
    echo $WundergroundWeatherByLatLongURL;
    return parse_wunderground_weather_xml($WundergroundWeatherByLatLong);
  }
  
  
  function yahoo_weather_by_latlong($lat,$long) {
    # Remove unwanted characters
    $lat  = str_replace('+','',$lat);
    $long = str_replace('+','',$long);
    
    # Get Yahoo Weather WOEID via XML
    $YahooWOEIDURL = 'http://where.yahooapis.com/geocode?location='.$lat.','.$long.'&gflags=R';
    $YahooWOEID    = download_url($YahooWOEIDURL);
    echo $YahooWOEIDURL;
    
    $YahooWOEID = parse_yahoo_woeid_xml($YahooWOEID);
    
    # Get Yahoo Weather RSS Stream with WOEID
    $YahooWeatherRSSURL = 'http://weather.yahooapis.com/forecastrss?w='.$YahooWOEID;
    $YahooWeatherRSS = download_url($YahooWeatherRSSURL);
    
    echo $YahooWeatherRSSURL;
    
    # Get Yahoo Weather URL
    $YahooWeatherURL = parse_yahoo_weather_rss($YahooWeatherRSS);
    $YahooWeather = download_url($YahooWeatherURL);
    
    echo $YahooWeatherURL;
    
    $pos = strpos($value['icon'],'unit=');
    if($pos === false) {
      //No Unit is specified in the URL. So we assume that celsius is wanted.
      //If you don't, comment the next two lines...
      
      # Get Yahoo Weather URL with Celsius. If you want fahrenheit, skip this step.
      $YahooWeatherURL = 'http://weather.yahoo.com'.get_string_between($YahooWeather,'|  <a href="','">C&#176;</a>');
      $YahooWeather = download_url($YahooWeatherURL);
    }
    return parse_yahoo_weather_html($YahooWeather);  
  }
  
  function parse_yahoo_weather_html($html) {
    $string = ereg_replace("\n", " ", $html); //remove line breaks
    $string = ereg_replace("\r", " ", $html); //remove line breaks
    
    $feelslike = get_string_between($string, "<dt>Feels", "</dd>");
    $feelslike = str_replace('Like:</dt><dd>','',$feelslike);
    
    $currentcond = get_string_between($string, '<div id="yw-cond">','</div>');
    
    $currenttemp = get_string_between($string, '<div id="yw-temp">','</div>'); 
    
    $currentloc = get_string_between($string, '<h1>','</h1>');
    $currentloc = str_replace('Weather','',$currentloc);
    $currentloc = str_replace('Forecasts','',$currentloc);
    
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
    
    $first_icon_width = get_string_between($string, '<img id="wiff" style="background-position:', 'px');
    $first_icon_width = str_replace(' ','',$first_icon_width);
    $first_icon_width = str_replace('-','',$first_icon_width);
    
    $forecasts = get_string_between($string, '<tr class="fiveday-icons">','<td rowspan="2" class="extended">');
    
    
    $forecasts = ereg_replace("\n", " ", $forecasts);
    $forecasts = ereg_replace("\r", " ", $forecasts);
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
    
    $weather['city'] = trim($currentloc);
    $weather['time'] = trim($asof);
    
    $weather[0]['condition'] = trim($currentcond);
    $weather[0]['temperature'] = trim($currenttemp);
    $weather[0]['humidity'] = trim($humidity);
    $weather[0]['wind'] = trim($wind);
    $weather[0]['icon'] = $currenticon;
    $weather[0]['feelslike'] = trim($feelslike);
    $weather[0]['barometer'] = trim($barometer);
    $weather[0]['visibility'] = trim($visibility);
    $weather[0]['dewpoint'] = trim($dewpoint);
    $weather[0]['sunrise'] = trim($sunrise);
    $weather[0]['sunset'] = trim($sunset);
    $weather[0]['high'] = trim($high);
    $weather[0]['low'] = trim($low);
    
    $weather[1]['condition'] = trim($first_icon_condition);
    $weather[1]['weekday'] = trim($data[0]);
    $weather[1]['high'] = trim($first_icon_high);
    $weather[1]['low'] = trim($first_icon_low);
    $weather[1]['icon'] = $first_icon_position;
    
    $weather[2]['condition'] = trim($second_icon_condition);
    $weather[2]['weekday'] = trim($data[1]);
    $weather[2]['high'] = trim($second_icon_high);
    $weather[2]['low'] = trim($second_icon_low);
    $weather[2]['icon'] = $second_icon_position;    
    
    $weather[3]['condition'] = trim($third_icon_condition);
    $weather[3]['weekday'] = trim($data[2]);
    $weather[3]['high'] = trim($third_icon_high);
    $weather[3]['low'] = trim($third_icon_low);
    $weather[3]['icon'] = $third_icon_position;  
    
    $weather[4]['condition'] = trim($fourth_icon_condition);
    $weather[4]['weekday'] = trim($data[3]);
    $weather[4]['high'] = trim($fourth_icon_high);
    $weather[4]['low'] = trim($fourth_icon_low);
    $weather[4]['icon'] = $fourth_icon_position;   
    
    $weather[5]['condition'] = trim($fifth_icon_condition);
    $weather[5]['weekday'] = trim($data[4]);
    $weather[5]['high'] = trim($fifth_icon_high);
    $weather[5]['low'] = trim($fifth_icon_low);
    $weather[5]['icon'] = $fifth_icon_position;  
    
    # print_r($weather);
    return $weather;
  }
  
  function get_weather_icons($weather) {
    
    $index = 0;
    foreach ($weather as $key => $value) {
      if (is_array($value)) {
        //Only do something if it is not the "header" information, date, time, city.    
        #echo $value['icon']."\n";
        $pos = strpos($value['icon'],'http');
        if($pos === false) {
          // No "normal" http image. Then it is a yahoo image
          if (!file_exists($GLOBALS['long_iconmap'])) {
            // Get The Big Yahoo Weather Image PNG
            $img = download_url('http://l.yimg.com/a/lib/ywc/img/wicons.png');
            $myFile = $GLOBALS['long_iconmap'];
            $fh = fopen($myFile, 'w') or die("can't open file");
            fwrite($fh, $img);
            fclose($fh);
          }
          
          // Resample
          $filename = $GLOBALS['long_iconmap'];
          $image_p = imagecreatetruecolor(61, 34);
          imagealphablending( $image_p, false );
          imagesavealpha( $image_p, true );
          $image = imagecreatefrompng($filename);
          
          $position = trim($value['icon']);
          
          #echo 'a'.$value['icon'].'a'."\n";
          //Create forecast image
          imagecopyResampled($image_p, $image, 0, 0, $position , 0, 61, 34, 61, 34);
          imagepng($image_p,$GLOBALS['forecast'.$index]);
        }
        else {
          // It is an image reachable via http
          if ($value['icon'] == 'http://www.google.com') {
            //It is only the google Address but no image behind it
            //because the XML was empty and google.com
            //was added hard in the coding
            unlink($GLOBALS['forecast'.$index]);
          } else {
            $img = download_url($value['icon']);
            $myFile = $GLOBALS['forecast'.$index];
            $fh = fopen($myFile, 'w') or die("can't open file");
            fwrite($fh, $img);
            fclose($fh);
          }
        }
        $index++;
      }
    }
  }
  
  function write_array_to_file($arr,$filename) {
    $fh = fopen($filename, 'w') or die("can't open file");
    fwrite($fh, base64_encode(serialize($arr)));
    fclose($fh);
  }
  
  function read_array_from_file($filename) {
    $arr = unserialize(base64_decode(download_url($filename)));
    if (!is_array($arr)) {
      echo "Weather File not found or not Readable. Run this script with parameter get first.\n";
      parameters();
      exit;
    } else {
      return $arr;
    }
  }
  
  function short_to_long_weekday($dayname) {
    if (strlen($dayname) <= 4) {
      return str_replace('.','',
             str_replace('Mon','Monday',
             str_replace('Tue','Tuesday',
             str_replace('Wed','Wednesday',
             str_replace('Thu','Thursday',
             str_replace('Fri','Friday',
             str_replace('Sat','Saturday',
             str_replace('Sun','Sunday',
             $dayname)
             )))))));
    } else {
      return $dayname;
    }
  }
  
  function show_weather_for_day($arr, $day) {
    if ($day != 0) {
      // Array position 0 is always Today. So let's start with a real forecast.
      echo short_to_long_weekday($arr[$day]['weekday'])."\n";
      echo str_replace('/',"/\n",$arr[$day]['condition'])."\n";
      if (strlen($arr[$day]['low']) < 4) {
        echo 'Low: '.$arr[$day]['low']."\n";
      } else {
        echo $arr[$day]['low']."\n";
      }
      if (strlen($arr[$day]['high']) < 4) {
        echo 'High: '.$arr[$day]['high']."\n";
      } else {
        echo $arr[$day]['high']."\n";
      }
    } else {
      // Todays data
      if (strlen($arr['city']) > 0) {
        echo $arr['city'].' ';
      }
      echo $arr['date'];
      echo ' '.$arr['time']."\n";
      if (strlen($arr[$day]['wind']) > 0) {
        echo 'Wind: '.str_replace('Wind: ','',$arr[$day]['wind'])."\n";    
      }
      if (strlen($arr[$day]['humidity']) > 0) {
        echo 'H: '.trim(str_replace(':','',str_replace('Humidity','',str_replace('%','',$arr[$day]['humidity'])))).'%';
      }
      if (strlen($arr[$day]['barometer']) > 0) {
        echo ' B: '.$arr[$day]['barometer']."\n";
      }
      echo ' '.$arr[$day]['condition']."\n";
      if (strlen($arr[$day]['visibility']) > 0) {
        echo 'Visibility '.$arr[$day]['visibility']."\n";
      }
      if (strlen($arr[$day]['sunrise']) > 0 ) {
        echo 'Sunrise '.$arr[$day]['sunrise']."\n";
      }
      if (strlen($arr[$day]['sunset']) > 0 ) {
        echo 'Sunset '.$arr[$day]['sunset']."\n";
      }
      if (strlen(str_replace('&#176;C','',$arr[$day]['dewpoint'])) > 0 ) {
        echo 'Dewpoint '.str_replace('&#176;C','',$arr[$day]['dewpoint'])."\n";
      }
      if (strlen($arr[$day]['low']) > 0 ) {
        echo 'Low '.$arr[$day]['low']."\n";
      }
      if (strlen(str_replace('&#176;','',$arr[$day]['temperature'])) > 0 ) {
        echo 'Currently '.str_replace('&#176;','',$arr[$day]['temperature'])."\n";
      }
      if (strlen(str_replace('&deg;C','',$arr[$day]['feelslike'])) > 0 ) {
        echo 'Feels like '.str_replace('&deg;C','',$arr[$day]['feelslike'])."\n";
      }
      if (strlen($arr[$day]['high']) > 0 ) {
        echo 'High '.$arr[$day]['high']."\n";
      }
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
  
  function get_mac_address() {
    // Find routing information from this computer to google.com
    $eth_route = shell_exec('route get google.com 2>&1');
    
    $contains = strpos($eth_route,'bad address:');
    
    if($contains != '') {
      echo "You don't seem to have an internet connection...";
      exit;
    }
    
    $gateway = get_string_between($eth_route,'gateway: ','interface: ');
    $arp = shell_exec('arp '.$gateway);
    $mac = get_string_between($arp,'at ',' on');  
    echo $mac;
    return $mac;
  }
  
  function get_wlan_name() {
    // Get List of Available WLANS
    $network_name = shell_exec('system_profiler SPAirPortDataType');
    
    $contains = strpos($network_name,'Status: Connected');
    if($contains === false) {
      echo "Airport is not connected";
    }
    
    $network_name = ereg_replace("\n", " ", $network_name); //remove line breaks
    $network_name = ereg_replace("\r", " ", $network_name); //remove line breaks
    
    $network_name = trim(get_string_between($network_name,'Current Network Information:','PHY'));
    $network_name = substr_replace($network_name ,"",-1);
    echo $network_name;
    return $network_name;
  }
  
  function get_location_by_ssid_or_mac() {
    $arr = array();
    $mac = get_mac_address();    
    $arr['URL'] = $GLOBALS['SSIDs'][$mac][2];
    $arr['ADR'] = $GLOBALS['SSIDs'][$mac][1];
    
    if (strlen($arr['URL']) > 0 or strlen($arr['ADR']) > 0)  {
      echo "Location found via MAC. ";
      return $arr;
    } else {
      // The MAC Address was not in the SSIDs List.
      // But maybe the WLAN Name is?
      $wlan = get_wlan_name();
      $arr['URL'] = $GLOBALS['SSIDs'][$wlan][2];
      $arr['ADR'] = $GLOBALS['SSIDs'][$wlan][1];
      if (strlen($arr['URL']) > 0 or strlen($arr['ADR']) > 0)  {
        echo "Location found via WLAN Name. ";
        return $arr;
      } else {
        echo "Location could not be found via SSIDs Array";
        return '';
      }
    }  
  }
  
  function get_location_by_corelocation() {
    $arr = array();
    # Get Geolocation with CoreLocation
    $CoreLocation = shell_exec('./CoreLocationCLI -once');
    echo $CoreLocation;
    $pos = strpos($CoreLocation,'ERROR');
    if ($pos === true) { 
      echo "Error whilst getting Lat Long with CoreLocation";
      return '';
    }
    $arr['lat']  = get_string_between($CoreLocation, '<', ',');
    $arr['long'] = get_string_between($CoreLocation, ',', '>');
    # Remove unwanted characters
    $arr['lat'] = str_replace(' ','',$arr['lat']);
    $arr['long'] = str_replace(' ','',$arr['long']);
    if (strlen($arr['lat']) > 0) {
      echo "Location found via CoreLocation ";
      return $arr;
    }
  }
  
  function parameters(){
    echo 'Valid parameters are: "mac", "get", "forecast0", "forecast1", "forecast2", "forecast3", "forecast4" and "forecast5"'."\n";   
  }
  
  function get_weather() {
    $location = get_location_by_ssid_or_mac();
    if (is_array($location)) {
      // We have an URL or an Address or both
      if (strlen($location['URL']) > 0) {
        //We have a Yahoo URL
        echo "Weather Gets Downloaded via Yahoo URL from SSIDs Array";
        $weather = parse_yahoo_weather_html(download_url($location['URL']));
        write_array_to_file($weather,$GLOBALS['weather_file']);
        get_weather_icons($weather);
      } else {
        echo "Weather Gets Downloaded from Google via Address from SSIDs Array";
        //We have an Address
        $weather = google_weather_by_address($location['ADR']);
        write_array_to_file($weather,$GLOBALS['weather_file']);
        get_weather_icons($weather);
      }
    } else {
      // No Location was found via SSID or MAC
      // Let's try CoreLocation
      $location = get_location_by_corelocation();
      if (!is_array($location)) {
        echo "Exiting...";
        exit;
      }
      $weather = yahoo_weather_by_latlong($location['lat'],$location['long']);
      if (is_array($weather)) {
        echo "Yahoo HTML Weather by Lat Long found";
        write_array_to_file($weather,$GLOBALS['weather_file']);
        get_weather_icons($weather);
      } else {
        $weather = wunderground_weather_by_latlong($location['lat'],$location['long']);
        if (is_array($weather)) {
          echo "Wunderground Weather by Lat Long found.";
          write_array_to_file($weather,$GLOBALS['weather_file']);
          get_weather_icons($weather);
        } else {
          // Reverse Geo Lookup
          $address = get_google_reverse_geolocation($location['lat'],$location['long']);
          $weather = google_weather_by_address($address);
          if (is_array($weather)) {
            echo "Google Weather by Address via Reverse Geo Location found.";
            write_array_to_file($weather,$GLOBALS['weather_file']);
            get_weather_icons($weather);
          } else {
            $weather = google_weather_by_latlong($location['lat'],$location['long']);
            if (is_array($weather)) {
              echo "Google Weather by Lat Long found.";
              write_array_to_file($weather,$GLOBALS['weather_file']);
              get_weather_icons($weather);
            } else {
              $weather = wunderground_weather_by_latlong($location['lat'],$location['long']);
              if (is_array($weather)) {
                echo "Wunderground Weather by Lat Long found.";
                write_array_to_file($weather,$GLOBALS['weather_file']);
                get_weather_icons($weather);
              } else {
                echo "No Weatherdata could be found. Sorry";
              }
            }  
          }
        }
      }
    }
  }  
?>