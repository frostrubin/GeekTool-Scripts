#!/bin/bash

function define_colors {
   ESC=`printf "\e"`
   startcolor="$ESC[31m"
   endcolor="$ESC[0m"
}

function mondaycal {
   mondaycal=`cal $mcal_parameter | awk '{ print " "$0; getline; print " Mo Tu We Th Fr Sa Su"; \
getline; if (substr($0,1,2) == " 1") print "                    1 "; \
do { prevline=$0; if (getline == 0) exit; print " " \
substr(prevline,4,17) " " substr($0,1,2) " "; } while (1) }'`
}

function setcal {
   if [ "$first_parameter" = "-m" ]; then
      # Week starts on monday
      mondaycal
      calendar="$mondaycal"
   else
      calendar=`cal`
   fi
}

function justcal {
   echo "$calendar" | sed "s/^/ /;s/$/ /;s/ $(date +%e) / "$startcolor"$(date +%e)"$endcolor" /"
}

function basic {
   echo "$calendar" | sed "s/^/ /;s/$/ /;s/ $(date +%e) / "$startcolor"$(date +%e | sed 's/./#/g')"$endcolor" /"
}

function next_month {
   mcal_parameter="-m $[`date +%m` + 1]"
   mondaycal
   echo "$mondaycal"
}

function previous_month {
   mcal_parameter="-m $[`date +%m` - 1]"
   mondaycal
   echo "$mondaycal"
}

function today {
   heute=`date +%e`
   if [ "$heute" -lt 10 ]; then
	   heute=${heute:1:1}
   fi
}

function horizontal_cal_with_braces {
   today
   cal | awk '{ getline; while (getline) {  print " " $0;} }'|sed "s/^/ /;s/$/ /;s/ $heute / "$startcolor"\"[$heute]\""$endcolor" /" | xargs echo
}

function horizontal_cal_with_number_sign {
   cal | awk '{ getline; while (getline) {  print " " $0;} }'|sed "s/ $(date +%e) / "$startcolor"$(date +%e | sed 's/./#/g')"$endcolor" /" | xargs echo
}

function horizontal_cal_date_only {
   today
   calendar=`cal`
   monat=`date +%m`
   jahr=`date +%Y`
   rest='0405'
   for (( i=1; i<10; i++ )); do
      if [ "$i" -ne "$heute" ]; then
         echo -ne "    "
      else
         echo -ne "$startcolor"`date -j $monat'0'$i'0405'$jahr +%a`"$endcolor"
         echo -ne " "
      fi
   done
   for (( i=10; i<=${calendar:(-2)}; i++ )); do
      if [ "$i" -ne "$heute" ]; then
         echo -ne "    "
      else 
         echo -ne "$startcolor"`date -j $monat$i'0405'$jahr +%a`"$endcolor"
         echo -ne " "
      fi
   done
   echo -ne "\n"
   for (( i=1; i<9; i++ )); do
      if [ "$i" -ne "$heute" ]; then
         echo -ne "    "
      else
         echo -ne " "
         echo -ne "$startcolor"$i"$endcolor"
      exit
      fi
   done
      if [ "$i" -ne "$heute" ]; then
         echo -ne "    "
      else
         echo -ne " "
         echo -ne "$startcolor"$i"$endcolor"
      exit
      fi

      if [ "10" -ne "$heute" ]; then
         echo -ne "    "
      else
         echo -ne "$startcolor"" 10""$endcolor"
      exit
      fi

   for (( i=11; i<=${calendar:(-2)}; i++ )); do
      if [ "$i" -ne "$heute" ]; then
         echo -ne "    "
      else
         echo -ne " "
         echo -ne "$startcolor"$i"$endcolor"
      exit
      fi
   done
}

function horizontal_cal_with_gap {
   today
   calendar=`cal`
   monat=`date +%m`
   jahr=`date +%Y`
   rest='0405'
   for (( i=1; i<10; i++ )); do
      if [ "$i" -eq "$heute" ]; then
         echo -ne "    "
      else
         echo -ne `date -j $monat'0'$i'0405'$jahr +%a`
         echo -ne " "
      fi
   done
   for (( i=10; i<=${calendar:(-2)}; i++ )); do
      if [ "$i" -eq "$heute" ]; then
         echo -ne "    "
      else 
         echo -ne `date -j $monat$i'0405'$jahr +%a`
         echo -ne " "
      fi
   done
   echo -ne "\n"
   echo -ne " "
   for (( i=1; i<9; i++ )); do
      if [ "$i" -eq "$heute" ]; then
         echo -ne "    "
      else
         echo -ne $i 
         echo -ne "   "
      fi
   done
      if [ "$i" -eq "$heute" ]; then
         echo -ne "    "
      else
         echo -ne $i
         echo -ne "   "
      fi
   for (( i=10; i<=${calendar:(-2)}; i++ )); do
      if [ "$i" -eq "$heute" ]; then
         echo -ne "    "
      else
         echo -ne $i
         echo -ne "  "
      fi
   done
}

function normal_cal_date_only {
   today
   echo " "
   echo "$calendar" |tail -n $[ $(echo "$calendar" | wc -l ) - 1 ] | sed -e "1s/$heute/%/;t" -e 1,/$heute/s//%/ | sed s/[^%]/" "/g | sed s/%/"$startcolor"$heute"$endcolor"/g
   # The cal and tail combination cuts the cal output so no year and month are shown.
   # The first two sed's replace today's date with '%'.
   # The next sed finds everythin that is NOT '%' and replaces it with ' '
   # The last sed replaces '%' with today's date
}

function normal_cal_with_gap {
   echo "$calendar" |sed "s/ $(date +%e) / $(date +%e | sed 's/./ /g') /"
}

function vertical_cal_date_only {
   calendar=`cal`
   today
   monat=`date +%m`
   jahr=`date +%Y`
   for (( i=1; i<10; i++ )); do
      if [ "$i" -eq "$heute" ]; then
         echo -ne "$startcolor"`date -j $monat'0'$i'0405'$jahr +%a`
         echo -ne "  "
         echo $i"$endcolor"
         exit
      else
         echo " "
      fi
   done
   for (( i=10; i<=${calendar:(-2)}; i++ )); do
      if [ "$i" -eq "$heute" ]; then
         echo -ne "$startcolor"`date -j $monat$i'0405'$jahr +%a`
         echo -ne " "
         echo $i"$startcolor"
         exit
      else 
         echo " "
      fi
   done
}

function vertical_cal_with_gap {
   calendar=`cal`
   today
   monat=`date +%m`
   jahr=`date +%Y`
   for (( i=1; i<10; i++ )); do
      if [ "$i" -eq "$heute" ]; then
         echo " "
      else
         echo -ne `date -j $monat'0'$i'0405'$jahr +%a`
         echo -ne "  "
         echo $i
      fi
   done
   for (( i=10; i<=${calendar:(-2)}; i++ )); do
      if [ "$i" -eq "$heute" ]; then
         echo " "
      else 
         echo -ne `date -j $monat$i'0405'$jahr +%a`
         echo -ne " "
         echo $i
      fi
   done
}




function parameters {
   echo "Valid parameters are: \"-m\" or \"-c\" or \"-mc\" (only as first parameters), \"justcal\", \"basic\", \"next\", \"previous\", \"basic_gap\", \"basic_date\", "
   echo "                      \"h_braces\", \"h_number\", \"h_gap\", \"h_date\", \"v_gap\" and \"v_date\"."
   echo "So a valid command can look like: calendar.sh -m basic"
   echo "                              or  calendar.sh basic"
   echo "                              or  calendar.sh -mc h_braces"
   echo "                                  etc."    
}

if [ $# -gt 2 ]; then
   echo "A maximum of 2 parameters is allowed."
   parameters
elif [ $# -eq 1 ]; then
   case "$1" in
      "-m")
      echo "You chose \"-m\". So weeks start on monday."
      echo "You still need to supply a second parameter."
      parameters
      ;;
      "-c")
      echo "You chose \"-c\". So the current date will be colored."
      echo "You still need to supply a second parameter."
      parameters
      ;;
      "-mc")
      echo "You chose \"-mc\". So weeks start on monday and the current date will be colored."
      echo "You still need to supply a second parameter."
      parameters
      ;;
      "justcal")
      setcal;
      justcal;
      ;;
      "basic")
      setcal;
      basic;
      ;;
      "next")
      setcal;
      next_month;
      ;;
      "previous")
      setcal;
      previous_month;
      ;;
      "basic_gap")
      setcal;
      normal_cal_with_gap;
      ;;
      "basic_date")
      setcal;
      normal_cal_date_only;
      ;;
      "h_braces")
      setcal;
      horizontal_cal_with_braces;
      ;;
      "h_number")
      setcal;
      horizontal_cal_with_number_sign;
      ;;
      "h_gap")
      setcal;
      horizontal_cal_with_gap;
      ;;
      "h_date")
      setcal;
      horizontal_cal_date_only;
      ;;
      "v_gap")
      setcal;
      vertical_cal_with_gap;
      ;;
      "v_date")
      setcal;
      vertical_cal_date_only;
      ;;
      *)
      echo "The parameter" $1 "does not exist."
      parameters;
      ;;
   esac
elif [ $# -eq 2 ]; then
   case "$1" in
      "-m")
      first_parameter="$1";
      ;;
      "-c")
      first_parameter="$1";
      define_colors;
      ;;
      "-mc")
      first_parameter="-m";
      define_colors;
      ;;
      "-cm")
      first_parameter="-m";
      define_colors;
      ;;
      *)
      echo "The first parameter" $1 "does not exist.";
      parameters;
      exit
      ;;
   esac
   
   case "$2" in
      "justcal")
      setcal;
      justcal;
      ;;
      "basic")
      setcal;
      basic;
      ;;
      "next")
      setcal;
      next_month;
      ;;
      "previous")
      setcal;
      previous_month;
      ;;
      "basic_gap")
      setcal;
      normal_cal_with_gap;
      ;;
      "basic_date")
      setcal;
      normal_cal_date_only;
      ;;
      "h_braces")
      setcal;
      horizontal_cal_with_braces;
      ;;
      "h_number")
      setcal;
      horizontal_cal_with_number_sign;
      ;;
      "h_gap")
      setcal;
      horizontal_cal_with_gap;
      ;;
      "h_date")
      setcal;
      horizontal_cal_date_only;
      ;;
      "v_gap")
      setcal;
      vertical_cal_with_gap;
      ;;
      "v_date")
      setcal;
      vertical_cal_date_only;
      ;;
      *)
      echo "The second parameter" $2 "does not exist."
      parameters;
      exit
      ;;
   esac
else
   echo "Sorry, but you have to supply at least one parameter."
   parameters
fi