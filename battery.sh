#!/bin/bash

function graphical_battery {
   ## Variables you want to set
   indicator="|"           # The Char to use
   indicator_count=20      # Number of above chars per row
   indicator_rows=3        # Number of rows

   # Define escape character
   ESC=`printf "\e"`

   # Get current capacity
   variable=`system_profiler SPPowerDataType | egrep -e "Charge remaining|Full charge capacity" | sed -e 's/[A-Za-z]//g;s/()://g' | sed -e 's/^[ \t]*//'`
   remaining=`echo $variable | awk '{print $1}'`
   full=`echo $variable | awk '{print $2}'`

   # Get percentage of battery load
   percentage=`echo $remaining \* 100 / $full \* 100 / 100 | bc`
   # How many indicator characters result of this percentage
   chars_to_display=`echo $indicator_count \* $percentage / 100| bc`

   # Set color values for different load levels
   if [ "$percentage" -ge "87" ];then
      echo -ne "$ESC[30m"
   elif [ "$percentage" -ge "75" ]; then
      echo -ne "$ESC[31m"
   elif [ "$percentage" -ge "62" ]; then
      echo -ne "$ESC[32m"
   elif [ "$percentage" -ge "50" ]; then
      echo -ne "$ESC[33m"
   elif [ "$percentage" -ge "37" ]; then
      echo -ne "$ESC[34m"
   elif [ "$percentage" -ge "25" ]; then
      echo -ne "$ESC[35m"
   elif [ "$percentage" -ge "12" ]; then
      echo -ne "$ESC[36m"
   else 
      echo -ne "$ESC[37m"
   fi

   # Determine the middle of the battery (Only works, if indicator_rows is uneven)
   is_uneven=`echo $indicator_rows % 2 | bc`
   middle=`echo $indicator_rows / 2 | bc`

   for (( k=0; k<$indicator_rows; k++ )); do
      for (( i=0; i<$chars_to_display; i++ )); do
         echo -ne $indicator
      done
      # Only display the battery head, if:
      if [ "$is_uneven" -eq "1" ] &&               # Number of rows is uneven
         [ "$k" -eq "$middle" ] &&                 # The row to display is the middle row
         [ "$i" -eq "$indicator_count" ]; then     # The battery is full
            echo -ne $indicator
      fi
      echo -ne "\n"
   done

   # Echo closing Escape Character
   echo -ne "$ESC[0m"
}

function info {
   system_profiler SPPowerDataType | egrep -e "Connected|Charge remaining|Full charge capacity|Condition|Cycle count" | sed -e 's/^[ \t]*//'
}

function connected {
   system_profiler SPPowerDataType | grep "Connected"|sed -e 's/^[ \t]*//;s/Connected: //g'
}

function remaining {
   system_profiler SPPowerDataType | grep "Charge remaining"|sed 's/[^0-9]*//g'
}

function full_capacity {
   system_profiler SPPowerDataType | grep "Full charge capacity"|sed 's/[^0-9]*//g'
}

function condition {
   system_profiler SPPowerDataType | grep "Condition"|sed -e 's/^[ \t]*//;s/Condition: //g'
}

function cycle_count {
   system_profiler SPPowerDataType | grep "Cycle count"|sed 's/[^0-9]*//g'
}

function percent {
   pmset -g ps  |  sed -n 's/.*[[:blank:]]+*\(.*%\).*/\1/p'
}




function parameters {
   echo "Valid parameters are: \"graphical\", \"info\", \"connected\", \"charge\", \"capacity\", \"condition\", \"cycles\" and \"percent\""
}

if [ $# -gt 1 ]; then
   echo "Only one parameter is allowed."
   parameters
elif [ $# -eq 1 ]; then
   case "$1" in
      "graphical")
      graphical_battery;
      ;;
      "info")
      info;
      ;;
      "connected")
      connected;
      ;;
      "charge")
      remaining;
      ;;
      "capacity")
      full_capacity;
      ;;
      "condition")
      condition;
      ;;
      "cycles")
      cycle_count;
      ;;
      "percent")
      percent;
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