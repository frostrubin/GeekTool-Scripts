#!/usr/bin/env php
<?php
   //Add the path to the different status images.
   //Their actual name is: status1.png, status2.png, etc.
   //The "1.png" and "2.png" parts are added at the end of this script.
   //So the path to add here is _without_ 1.png ...
   $statusimage='/Users/bernhard/.NerdTool/status';
   $path_to_tempmonitor='~/.NerdTool/tempmonitor -l -a';
   
   
   $status = '1'; //from green to red, 1 to 4
   $batterystatus = '1';
   $ramstatus = '1';
   $corestatus = '1';
   $hddtempstatus = '1';
   $hddspacestatus = '1';
   
   $battery = shell_exec('pmset -g ps');
   $battery = substr($battery,strpos($battery,'%')-3,3);
   
   switch ($battery) {
       
      case ($battery <= 15):
         $batterystatus = '4';
         break;
 
      case ($battery <= 25):
         $batterystatus = '3';
         break;
         
      case ($battery <= 40):
         $batterystatus = '2';
         break;
   }
   
   $ram = shell_exec('top -l 1 | grep PhysMem');
   $raminactive = shell_exec('echo "'.$ram.'" |awk \'{print "" $6 ""}\' |sed \'s/[^0-9]*//g\'');
   $ramused = shell_exec('echo "'.$ram.'" |awk \'{print "" $8 ""}\' |sed \'s/[^0-9]*//g\'');
   $ram = $ramused - $raminactive;
   
   switch ($ram) {
         
      case ($ram >= 3700):
         $ramstatus = '4';
         break;
         
      case ($ram >= 3500):
         $ramstatus = '3';
         break;
         
      case ($ram >= 3000):
         $ramstatus = '2';
         break;
   }
   
   $temperatures = shell_exec($path_to_tempmonitor);
   $core1 = shell_exec('echo "'.$temperatures.'" | grep "CPU A" | sed -e s/^[^:]*:// | sed -e \'s/^[ \t]*//\' | sed s/C//g | sed s/" "//g');
   
   switch ($core1) {
         
      case ($core1 >= 75):
         $corestatus = '4';
         break;
         
      case ($core1 >= 70):
         $corestatus = '3';
         break;
         
      case ($core1 >= 65):
         $corestatus = '2';
         break;
   }
   
   $hddtemp = shell_exec('echo "'.$temperatures.'" | grep "SMART" | sed -e s/^[^:]*:// | sed s/C//g | sed -e \'s/^[ \t]*//\' | sed s/" "//g');
   
   switch ($hddtemp) {
         
      case ($hddtemp >= 60):
         $hddtempstatus = '4';
         break;
         
      case ($hddtemp >= 50):
         $hddtempstatus = '3';
         break;
         
      case ($hddtemp >= 40):
         $hddtempstatus = '2';
         break;
   }

   $hddspace = shell_exec('df -h /|awk \'{ print $4 }\'|sed \'s/[^0-9]*//g\'|tail -n 1');
   
   switch ($hddspace) {
         
      case ($hddspace <= 10):
         $hddspacestatus = '4';
         break;
         
      case ($hddspace <= 15):
         $hddspacestatus = '3';
         break;
         
      case ($hddspace <= 20):
         $hddspacestatus = '2';
         break;
   }

   $arr = array($batterystatus,$ramstatus,$corestatus,$hddtempstatus,$hddspacestatus);
   sort($arr);
   
   echo "  ";
   
   #echo $status;
   shell_exec('cp -f '.$statusimage.end($arr).'.png /Users/bernhard/.NerdTool/files/status.png');
?>