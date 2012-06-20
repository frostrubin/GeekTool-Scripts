#!/bin/bash

fburl="fritz.box"  # Fritz!Box IP (default: fritz.box)
pass="passw0rd"  # Fritz!Box password
storage="/tmp/anrufliste.xml"

if [ -n "$2" ] || [ -z "$1" ]; then
  echo 'usage: anrufliste.sh {get|type|date|time|datetime|name|number|duration|html}'
  exit 1
fi

if [ "$1" == "get" ] || [ "$1" == "html" ]; then
  mac=`arp "$fburl"|cut -d ' ' -f 4`
  if [ "$mac" != "c0:25:6:98:90:77" ]; then # Restrict this whole script to my home router
    exit 0
  fi

  # Get the Fritzbox Password
  pass=$(security 2>&1 >/dev/null find-generic-password -gs Fritz.BoxPassword | cut -d '"' -f 2|sed s/\\\\012/\\\\n/g)

  webcm="$fburl/cgi-bin/webcm"

  challenge=`curl -s "$webcm?getpage=../html/login_sid.xml" | grep "Challenge" | awk -F '>' '{print $2}' | awk -F '<' '{print $1}'`

  encpass=`echo -n "$challenge-$pass" | perl -pe 's/(.)/\1\0/gs'  | md5 | awk '{print $1}'`
  logintoken="$challenge-$encpass"
  sid=`curl -s -d "login:command/response=$logintoken&getpage=../html/login_sid.xml" "$webcm" | grep SID | awk -F '>' '{print $2}' | awk -F '<' '{print $1}'`

  if [ $sid = "0000000000000000" ]; then
    exit 1
  fi
  
  if [ "$1" == "html" ];then
    time=$(date "+%a. %d %B %H:%M")    
    curl -s -d "sid=$sid&getpage=../html/de/home/foncallsdaten.xml" "$webcm" | xsltproc fritzbox_anrufliste_filter.xslt - |sed s,SETDATEANDTIME,"$time",
    curl -s -d "security:command/logout=1&sid=$sid" "$webcm" > /dev/null
    exit 0
  fi
    curl -s -d "sid=$sid&getpage=../html/de/home/foncallsdaten.xml" "$webcm" > "$storage"
    chmod 600 "$storage"
    curl -s -d "security:command/logout=1&sid=$sid" "$webcm" > /dev/null
    exit 0  
fi

if [ "$1" == "type" ];then
  cat "$storage" | grep "<Type>" | sed -e 's/^[ \t]*//' |cut -c7
fi
if [ "$1" == "date" ];then
  cat "$storage" | grep "<Date>" | sed -e 's/^[ \t]*//' |cut -c7-14
fi
if [ "$1" == "time" ];then
  cat "$storage" | grep "<Date>" | sed -e 's/^[ \t]*//' |cut -c16-20
fi
if [ "$1" == "datetime" ];then
  cat "$storage" | grep "<Date>" | sed -e 's/^[ \t]*//' |cut -c7-20
fi
if [ "$1" == "name" ];then
  cat "$storage" | grep "<Name>"| sed -e 's/^[ \t]*//'|sed -e 's/[CDATA[]]/oUnbekannt/g'  |cut -c16-400|sed -e 's,]]></Name>,,g;s,]></Name>,,g'
fi
if [ "$1" == "number" ];then
  cat "$storage" | grep "<Number>" | sed -e 's/^[ \t]*//' |cut -c9-400| sed -e 's,</Number>,,g'
fi
if [ "$1" == "duration" ];then
  cat "$storage" | grep "<Duration>" | sed -e 's/^[ \t]*//' |cut -c11-400| sed -e 's,</Duration>,,g'
fi
