#!/bin/bash

osxversion=`/usr/bin/sw_vers|grep 'ProductVersion'|sed 's/[^0-9]*//g'`
osxversion=`echo ${osxversion:2:1}`

if [ $osxversion -ge 6 ]; then
   space="df -H"
else
   space="df -h"
fi

function all_in_one {
   proto_table=$(echo "|"Volume"|"Free"\n"
   ls /Volumes/ | while read FILE; do
      free=`$space /Volumes/"$FILE"`
      free=`echo $free | awk '{print $11}'` 
      echo "|""$FILE""|"$free"\n"
   done)

   echo -e $proto_table | sed 's/ |/|/g'| column -c 2 -s "|" -t
}

function size {
   free=`$space /Volumes/*| grep -v used | awk '{ print $4 }' | sed 's/[^A-MG-Za-mg-z0-9]\.//g;s/i//g'`
   for (( i=2; i<=$(echo "$free"|wc -l); i++ ));do
      echo "$free"|sed -n $i"$n{p;}"
   done
}

function volume_name {
   ls /Volumes/ | while read FILE; do echo "$FILE"; done
}




function parameters {
   echo "Valid parameters are: \"one\", \"size\" and \"volume\""
}

if [ $# -gt 1 ]; then
   echo "Only one parameter is allowed."
   parameters
elif [ $# -eq 1 ]; then
   case "$1" in
      "one")
      all_in_one;
      ;;
      "size")
      size;
      ;;
      "volume")
      volume_name;
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