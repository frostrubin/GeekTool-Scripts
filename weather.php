#!/usr/bin/env php
<?php
  //   This script tries to get your location by
  //   WLAN Name or the MAC address of your router.
  //   This way, weather can be displayed 
  //   according to your location!
  
  //§1 First you have to change the paths where on your computer   
  //   the forecast images should be stored.
  
  //§2. Second: Maintain a list of your most used locations
  
  //   For this, maintain the $SSIDs array below.
  //   Just copy an already existing entry
  //   And enter your own values.
  //
  //   The part in the first brackets can be either the name 
  //   of your locations WLAN (for instance "T-Online Hotspot")
  //   enter it just like you see it in your computers WLAN list.
  //   OR it can be the mac address of the connecting router.
  //   This comes in handy, if many WLANS have the same name
  //   (think McDonalds).
  //   Run this script with the parameter "mac" and you will
  //   Get the cryptic number for your current wlan that you then
  //   can enter into the SSIDs array.
  //
  //   The second part in brackets is the Address of the place
  //   If no URL (third part in brackets) is present, weather
  //   is searched via this address.
  //
  //   The third part in brackets is a Yahoo (it has to be Yahoo!)
  //   Weather URL.
  //   Just go to Yahoos site weather.yahoo.com once,
  //   Search for your location manually
  //   and copy the resulting URL into the bracket part.
  //   It should look something like this: 
  //   http://weather.yahoo.com/united-states/arkansas/washington-2514857/
  //   or http://weather.yahoo.com/germany/baden-wurttemberg/mannheim-673711/
  //   Hint: If you want to have fahrenheit,
  //   append a ?unit=f to the url
  //   If unit=c is used or no unit=xyz is present,
  //   Celsius is used as standard!
  
  //§3. Third: Just forget about maintaining anything,
  //    just use CoreLocation
  //
  //   If this is too technical for you,
  //   or you travel a lot and maintaining this list (see §2.) would be too much effort,
  //   This script can also use an Apple GPS Service called "CoreLocation"
  //   Same as on your iPhone or iPad...
  //   Just run this script with parameter "get" and see what happens.
  
  
  //§4. Display weather data!
  //
  //   To actually display weather data,
  //   you have to run this script with parameter "get" once
  //   so current weather information can be downloaded
  //   and icons are downloaded and weather information
  //   is written to the weather.txt file.
  //   (There, it looks just like gibberish. Numbers with Characters and stuff. Dont bother.)
  //
  //   Then run this script with parameter "forecast0" for the current weather data.
  //   Run it with "forecast1" for todays forecast
  //   Run it with "forecast2" for tomorrows forecast
  //   Run it wi..... you get the point.
  
  //§5 Add the script calls with these parameters to geektool
  //   add the downloaded images as well.
  
  
  //   Oh, by the way: if you happen to have no WLAN connection at all (at certain locations, or in general)
  //   Simply run the "mac" parameter to get the MAC Address of your current router. 
  //   In the SSIDs array, add it instead (or additionally) of the WLAN SSID.
  //   So if you move your laptop around, if the location can be found via SSID or used Router or Apple CoreLocation
  //   The according weather information can be displayed.
  
  // References just for myself
  // CoreLocationCLI has been downloaded from
  // http://code.google.com/p/corelocationcli/      A N D   I T   I S   A W E S O M E   ! ! ! 
  // Thread with nice api tricks:
  // http://stackoverflow.com/questions/2093358/receive-a-woeid-by-lat-long-with-yahoos-api
  // Alternative to getting the Yahoo WOEID:
  // http://query.yahooapis.com/v1/public/yql?q=select%20place.woeid%20from%20flickr.places%20where%20lat%3D43%20and%20lon%3D-94&format=xml
  
  
  // Defining some global variables. YOU NEED TO CHANGE THESE !!!!           <===========  R E A D    T H I S !
  $long_iconmap = '/Users/bernhard/.NerdTool/files/longIconMap.png';
  $forecast0 = '/Users/bernhard/.NerdTool/files/forecast0.png';
  $forecast1 = '/Users/bernhard/.NerdTool/files/forecast1.png';
  $forecast2 = '/Users/bernhard/.NerdTool/files/forecast2.png';
  $forecast3 = '/Users/bernhard/.NerdTool/files/forecast3.png';
  $forecast4 = '/Users/bernhard/.NerdTool/files/forecast4.png';
  $forecast5 = '/Users/bernhard/.NerdTool/files/forecast5.png';
  $weather_file = '/Users/bernhard/.NerdTool/files/weather.txt';
  
  
  // Defining an Array of WLAN Names or Router MAC Addresses
  // For best results IT IS HEAVILY RECOMMENDED THAT YOU CHANGE THESE !!!!   <=========== R E A D    T H I S   T O O   !
  $SSIDs = array();
  $SSIDS['WUENSCH'][1] = 'Reinheim, Germany';
  $SSIDs['WUENSCH'][2] = 'http://weather.yahoo.com/germany/hesse/reinheim-687697/?unit=c';
  $SSIDS['Lecker Pizza'][1] = 'Darmstadt, Germany';
  $SSIDs['Lecker Pizza'][2] = 'http://weather.yahoo.com/germany/hesse/darmstadt-643787/?unit=c';
  $SSIDS['0:23:8:cd:c6:76'][1] = 'Darmstadt, Germany';
  $SSIDs['0:23:8:cd:c6:76'][2] = 'http://weather.yahoo.com/germany/hesse/darmstadt-643787/?unit=c';
  $SSIDS['BaWebAuth'][1] = 'Mannheim, Germany';
  $SSIDs['BaWebAuth'][2] = 'http://weather.yahoo.com/germany/baden-wurttemberg/mannheim-673711/?unit=c'; 
  $SSIDS['unser netz'][1] = 'Reinheim, Germany';
  $SSIDs['unser netz'][2] = 'http://weather.yahoo.com/germany/hesse/reinheim-687697/?unit=c';
  $SSIDS['0:15:c:6a:e1:b3'][1] = 'Reinheim, Germany';
  $SSIDs['0:15:c:6a:e1:b3'][2] = 'http://weather.yahoo.com/germany/hesse/reinheim-687697/?unit=c';  
  $SSIDS['0:11:9:af:c7:f2'][1] = 'Washington DC, USA';
  $SSIDs['0:11:9:af:c7:f2'][2] = 'http://weather.yahoo.com/united-states/district-of-columbia/washington-2514815/?unit=f';  
  
  
  
  
  // Start of the Main Script
  // Dont change anything, unless below here, unless you know, what you are doing!
  $weather = array();
  @include('weather.inc.php');
  
  
  if ($argc > 2) {
    echo 'Only one parameter is allowed.'."\n";   
    parameters();
  } elseif ($argc == 2) {
    switch ($argv[1]) {
      case 'get':
        get_weather();
        break;
      case 'forecast0':
        $weather = read_array_from_file($GLOBALS['weather_file']);
        show_weather_for_day($weather,0);
        break;
      case 'forecast1':
        $weather = read_array_from_file($GLOBALS['weather_file']);
        show_weather_for_day($weather,1);
        break;
      case 'forecast2':
        $weather = read_array_from_file($GLOBALS['weather_file']);
        show_weather_for_day($weather,2);
        break;
      case 'forecast3':
        $weather = read_array_from_file($GLOBALS['weather_file']);
        show_weather_for_day($weather,3);
        break;
      case 'forecast4':
        $weather = read_array_from_file($GLOBALS['weather_file']);
        show_weather_for_day($weather,4);
        break;
      case 'forecast5':
        $weather = read_array_from_file($GLOBALS['weather_file']);
        show_weather_for_day($weather,5);
        break;
      case 'mac';
        MAChelper();
        break;
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