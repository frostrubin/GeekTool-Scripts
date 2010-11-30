#!/usr/bin/env php
<?php
   $myFile = "/Users/bernhard/.NerdTool/files/agenda.txt";
   date_default_timezone_set('Europe/Berlin');

   @include('gcal_agenda_urls.private.php');
/*Uncomment this and enter your own calendar urls (private or public)
   $calendars=array(
                    "Work" => "http://www.google.com/calendar/feeds/kgh746ght0kptej9v4mj47acag@group.calendar.google.com/private-f369ab079d6267a063da0ecb0476b4b7",
                    "Private" => "http://www.google.com/calendar/feeds/frankenstein3000@gmail.com/private-85058999gha5e87a6389a29ahabgl1bc",
                    "My Wifes Calendar" => "http://www.google.com/calendar/feeds/f5uz5zdb207uhvkjp5etsh6mi4@group.calendar.google.com/private-4f57d1a6b1734ngt598aclkhb3c1e3a09"
                    );
 
*/
   $startMin=$_GET['from'];
   $startMax=$_GET['to'];
   $startMin=date("Y-m-d");
   $monthMax=date(m)+2;
   if (strlen($monthMax) == 1)
   {
      $monthMax='0'.$monthMax;
   }
   $startMax=date(Y).'-'.$monthMax.'-'.date(d);
   //echo $startMin;
   //echo $startMax;
   //exit;
   $params='/full?orderby=starttime&sortorder=ascending&singleevents=true&start-min='.$startMin.'&start-max='.$startMax;
   $events=array();
   
   
   # Y-m-d
   
   
   foreach ($calendars as $name => $url)
   {
      $xmlfile=$url.$params;
      
      if(!$xml = @simplexml_load_file($xmlfile))
      {
         die('XML-Document '.$name.' could not be parsed!');
      }
      
      foreach ($xml->entry as $entry)
      {
         //$gd = $entry->children('http://schemas.google.com/g/2005');
         $namespaces = $entry->getNameSpaces(true);
         //Now we don't have the URL hard-coded
         $gd = $entry->children($namespaces['gd']);
         
         $startDay=substr($gd->when->attributes()->startTime,0,10);
         $startTime=substr($gd->when->attributes()->startTime,11,5);
         $endDay=substr($gd->when->attributes()->endTime,0,10);
         $endTime=substr($gd->when->attributes()->endTime,11,5);
         
         if ($startTime =="")
         {
            $startTime="     ";
         }
         if ($endTime == "")
         {
            $endTime="     ";
         }
         $events[]=$startDay."|".
         $startTime."|".
         $endDay."|".
         $endTime."|".
         utf8_decode($entry->title);
         
         
      }
      
      
   }
   
   asort($events);
   //print_r($events);
   
   $prev_date="";
   
   $fh = fopen($myFile, 'w+') or die("can't open file");

   foreach($events as $event)
   {

      if ($prev_date != substr($event,0,10)) 
      {
         $stringData = substr(date("l", mktime(0,0,0,substr($event,5,2),substr($event,8,2),substr($event,0,4))),0,3).'.';
      }
      else 
      {
         $stringData = '    ';
      }
      
      $event = str_replace('amp;','',$event);
      
      echo $stringData;
      fwrite($fh, $stringData);
      
      echo ' ';fwrite($fh, ' ');
      echo substr($event,11,5); //starts
      fwrite($fh, substr($event,11,5));
      echo ' ';fwrite($fh, ' ');
      echo substr($event,28,5); //ends
      fwrite($fh, substr($event,28,5));
      echo ' ';fwrite($fh, ' ');
      echo substr($event,34,140); //what
      fwrite($fh, substr($event,34,140));
      
      
      $prev_date=substr($event,0,10);
      
      echo "\n";  
      fwrite($fh, "\n");
   }  
   
   
   fclose($fh);
?>