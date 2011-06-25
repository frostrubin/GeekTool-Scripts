#!/usr/bin/env php
<?php
  # This Script displays the time in a "fuzzy" way
  # eg only in 5 minute intervals.
  # It was taken from http://iceyboard.no-ip.org/projects/code/php/fuzzy_clock/
  # and has only been adapted in minor ways.
  // fuzzy clock v1.0
  
  // if no time is sent, this will default to the current server time
  function fuzzy_clock($seconds = 0)
  {
    // check if no time was sent
    if (!$seconds)
      // use the server time
      $seconds = time();
    
    // get the hour in 24 hour format    
    $hour = @date(G, $seconds);
    
    // get the nearest 5 minute interval
    // so 16 minutes past would be 3, 34 would be 7 and so on
    $minute = round(@date(i, $seconds) / 5);
    
    // the different hours that make up the day,
    // includes special names like noon and midnight
    $hour_intervals = array(
                            'midnight', 'one', 'two', 'three', 'four', 'five',
                            'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
                            'noon', 'one', 'two', 'three', 'four', 'five',
                            'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
                            'midnight'
                            );
    // the five minute intervals that make up an hour
    $minute_intervals = array(
                              'five', 'ten', 'quarter', 'twenty',
                              'twenty five', 'half', 'twenty five',
                              'twenty', 'quarter', 'ten', 'five'
                              );
    
    // check if this is exactly the hour
    if (!$minute)
      // just show the hour, like seven/midnight
      return $hour_intervals[$hour];
    if ($minute == 12)
      // if it's nearly the next hour, display that, like noon/six
      return $hour_intervals[$hour+1];
    else
    {
      // it must be at some point during the hour
      // work out if this is the second half of the hour
      if ($minute > 6)
        // display time leading to the next hour, like 'twenty to six'
        return $minute_intervals[$minute-1] . ' to ' . $hour_intervals[$hour+1];
      else
        // display time from the current hour, like 'quarter past two'
        return $minute_intervals[$minute-1] . ' past ' . $hour_intervals[$hour];
    }
  }
  
  // show the fuzzy time of the current time
  echo fuzzy_clock()."\n";
  
?>