#!/bin/bash

# This Script gives you the seconds counting up from the time of your birth.
# I am using it to watch closely, when exactly I will get 1 Gigasecond old.

# My time of Birth: Aug 13 1988 at 14:28 and 44 seconds
# So the syntax is mmddHHMMCCYY.ss

birthseconds=`date -j 081309281988.44 +%s`
nowseconds=`date +%s`

echo $(($nowseconds-$birthseconds))
