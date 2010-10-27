#!/bin/bash

function ethernet_ip {
   myen0=`ifconfig en0 | grep "inet " | grep -v 127.0.0.1 | awk '{print $2}'`
   if [ "$myen0" != "" ]; then
      address=$myen0
   else
      address="INACTIVE"
   fi
}

function airport_ip {
   myen0=`ifconfig en1 | grep "inet " | grep -v 127.0.0.1 | awk '{print $2}'`
   if [ -n "$myen0" ]; then
      address=$myen0
   else
      address="INACTIVE"
   fi
}

function external_ip {
   address=`curl -s --connect-timeout 5 http://checkip.dyndns.org/ | \
   sed 's/[a-zA-Z<>/ :]//g'`
   if [ "$address" == "" ] || \
   [[ $address == *Cisco*Systems*Inc.*Web*Authentication*Redirect* ]]; then
      address="INACTIVE"
   fi
}

function vertical_ethernet_ip {
   ethernet_ip
   for offset in 0 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15;do 
     echo ${address:$offset:1}
   done
}

function vertical_airport_ip {
   airport_ip
   for offset in 0 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15;do 
     echo ${address:$offset:1}
   done
} 

function vertical_external_ip {
   external_ip
   for offset in 0 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15;do 
     echo ${address:$offset:1}
   done
} 

function all_in_one {
   ethernet_ip
   echo "Ethernet:" $address
   airport_ip
   echo "Airport: " $address
   external_ip
   echo "External:" $address
}

function simple_monitor {
   lsof -P -i -n | cut -f 1 -d " " | uniq | sed 1d
}

function airport_networks {
   echo '<?php
      $airport = shell_exec("/System/Library/PrivateFrameworks/Apple80211.framework/Versions/Current/Resources/airport -s");
      
      $airport = preg_replace("/ 00:(.*?):(.*?):(.*?):(.*?):(.*?) -/", " -", $airport);
      $airport = str_replace("BSSID             ","",$airport);
      $airport = str_replace("   Y"," ",$airport); //new
      $airport = str_replace("   N"," ",$airport); //new
     /* $airport = str_replace("    Y","  ",$airport);
      $airport = str_replace("    N","  ",$airport);
      $airport = str_replace("      Y","  ",$airport);
      $airport = str_replace("      N","  ",$airport);
      $airport = str_replace("       Y","  ",$airport);
      $airport = str_replace("       N","  ",$airport);*/
      $airport = str_replace("EL HT","EL",$airport);

      
      $airport = str_replace("(auth/unicast/group)","",$airport);
      $airport = preg_replace ("/\((.*?)\/(.*?)\/(.*?)\)/", ",", $airport);


      $airport = str_replace("WPA,","WPA",$airport);
      $airport = str_replace("WPA WPA2,","WPA, WPA2",$airport);
      $airport = str_replace("WPA2,","WPA2",$airport);
      $airport = str_replace("WEP,","WEP",$airport);

      echo $airport;?>' | /usr/bin/env php
}




function parameters {
   echo "Valid parameters are: \"eth\", \"air\", \"ext\", \"v_eth\", \"v_air\", \"v_ext\", \"all_ip\", \"monitor\" and \"air_list\""
}

if [ $# -gt 1 ]; then
   echo "Only one parameter is allowed."
   parameters
elif [ $# -eq 1 ]; then
   case "$1" in
      "eth")
      ethernet_ip;
      echo $address;
      ;;
      "air")
      airport_ip;
      echo $address;
      ;;
      "ext")
      external_ip;
      echo $address
      ;;
      "v_eth")
      vertical_ethernet_ip;
      ;;
      "v_air")
      vertical_airport_ip;
      ;;
      "v_ext")
      vertical_external_ip;
      ;;
      "all_ip")
      all_in_one;
      ;;
      "monitor")
      simple_monitor;
      ;;
      "air_list")
      airport_networks;
      ;;
      *)
      echo "The parameter" $1 "does not exist."
      parameters;
      ;;
   esac
else
   echo "Sorry, but you have to supply a parameter."
   parameters
fi