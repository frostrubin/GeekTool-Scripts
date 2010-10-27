#!/bin/bash
# This Script outputs the different temperatures from the Mac's sensors.
# For this script to work, you NEED to download the free
# Temperature Monitor from http://www.bresink.com/osx/0TemperatureMonitor/download.php5

# Open the dmg, and right click -> Show Package Contents the application Temperature Monitor.
# Navigate into the Contents -> MacOS folder and copy the file named tempmonitor
# Past it into the same folder, where this script resides
# Change the following path accordingly:
temperatures=`/Users/bernhard/.NerdTool/tempmonitor -l -a`


########## CPU Cores ##########
core1=`echo "$temperatures" | grep "CPU A" | sed -e s/^[^:]*:// | sed -e 's/^[ \t]*//' | sed s/C//g | sed s/" "//g`

echo -e "CPU ""$core1"$'\xB0'

########## HDD Temp ##########
hdd=`echo "$temperatures" | grep "SMART" | sed -e s/^[^:]*:// | sed s/C//g | sed -e 's/^[ \t]*//' | sed s/" "//g`
echo -e "HDD ""$hdd"$'\xB0'

########## Bottom Temp ##########
bottomside=`echo "$temperatures" | grep "ENCLOSURE BOTTOMSIDE" | sed -e s/^[^:]*:// | sed -e 's/^[ \t]*//' | sed s/C//g | sed s/" "//g | sort -nr | head -n 1`
echo -e "Bottom ""$bottomside"$'\xB0'

########## Heat Sink ##########
#heatsink=`echo "$temperatures" | grep "HEAT SINK" | sed -e s/^[^:]*:// | sed -e 's/^[ \t]*//' | sed s/C//g | sed s/" "//g | sort -nr | head -n 1`
#echo -e "Heat Sink: ""$heatsink"$'\xB0'
