#!/usr/bin/env php
<?php
//Setup
$clock_hands="/Users/bernhard/.NerdTool/files/clock_hands.png";
$hour_lines="/Users/bernhard/.NerdTool/files/hour_lines.png";
$minute_lines="/Users/bernhard/.NerdTool/files/minute_lines.png";
$second_lines="/Users/bernhard/.NerdTool/files/second_lines.png";
$hourmin_lines="/Users/bernhard/.NerdTool/files/hourmin_lines.png";
$size=1000;

function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1) {
   /* this way it works well only for orthogonal lines
    imagesetthickness($image, $thick);
    return imageline($image, $x1, $y1, $x2, $y2, $color);
    */
   if ($thick == 1) {
      return imageline($image, $x1, $y1, $x2, $y2, $color);
   }
   $t = $thick / 2 - 0.5;
   if ($x1 == $x2 || $y1 == $y2) {
      return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
   }
   $k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
   $a = $t / sqrt(1 + pow($k, 2));
   $points = array(
                   round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
                   round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
                   round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
                   round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
                   );
   imagefilledpolygon($image, $points, 4, $color);
   return imagepolygon($image, $points, 4, $color);
}
   
function clock_hands() {
   // set up array of points for polygon
   $hourhand = array(
                     500,  550, // Point 1 (x, y)
                     420,  400, // Point 2 (x, y)
                     500,  120, // Point 4 (x, y)
                     580,  400  // Point 3 (x, y)
                     );
   $minutehand = array(
                       500,  550,
                       450,  400,
                       500,  0,
                       550,  400   
                       );
   
   $hour = @date('g');
   $minute = @date('i');
   
   $minute_position = 360-($minute*6);
   $hour_position_basic = 360-($hour*30);
   $hour_position_refinement = $minute/10*5;
   $hour_position = $hour_position_basic - $hour_position_refinement;
   
   $minute_rotation = $minute_position;
   $hour_rotation = $hour_position - $minute_position;
   
   // create image
   $image = imagecreatetruecolor(1000, 1000);
   imagealphablending( $image, false );
   imagesavealpha( $image, true );
   
   // Define hand colors
   $hour_hand_color = imagecolorallocate($image, 255, 255, 255);
   $minute_hand_color = imagecolorallocate($image, 255, 255, 255);
   
   // allocate colors
   $pseudo_trans = imagecolorallocate($image,200,200,200);
   
   // fill the background
   imagefilledrectangle($image, 0, 0, 999, 999, $pseudo_trans);
   
   // draw the hour hand
   imagefilledpolygon($image, $hourhand, 4, $hour_hand_color);
   
   //rotate for hours image   
   $image = imagerotate($image, $hour_rotation, $pseudo_trans);
   ImageColorTransparent($image, $pseudo_trans); 
   
   // Resample
   $width = imagesy($image);
   $height = imagesx($image);
   $image_dest = imagecreatetruecolor(1000,1000);
   imagecopyresampled($image_dest, $image, 0, 0, ($width-1000)/2, ($height-1000)/2, 1000, 1000, 1000, 1000);
   $image = $image_dest;
   
   // draw the minute hand
   imagefilledpolygon($image, $minutehand, 4, $minute_hand_color);   
   
   //rotate for minutes image
   $image = imagerotate($image, $minute_rotation, $pseudo_trans);
   
   // Resample
   $width = imagesy($image);
   $height = imagesx($image);
   $image_dest = imagecreatetruecolor(1000,1000);
   imagecopyresampled($image_dest, $image, 0, 0, ($width-1000)/2, ($height-1000)/2, 1000, 1000, 1000, 1000);
   $image = $image_dest;
   
   // flush image   
   ImageColorTransparent($image, $pseudo_trans);
   return $image;
}
   
function hour_lines() {
   $radius = floor($GLOBALS['size'] / 2);
   
   $img = ImageCreate($GLOBALS['size'], $GLOBALS['size']);
   $color_alpha = ImageColorAllocate($img, 254, 254, 254);
   $color_white = ImageColorAllocate($img, 255, 255, 255);
   $color_black = ImageColorAllocate($img, 0, 0, 0);
   $color_gray  = ImageColorAllocate($img, 192, 192, 192);
   $color_red   = ImageColorAllocate($img, 255, 0, 0);
   $color_blue  = ImageColorAllocate($img, 115, 192, 255);
   ImageColorTransparent($img, $color_alpha);
   
   
   $hour = @date('g');
   $hour = $hour * 5;
   $radiusbak = $radius;
   if ($hour < 60) {
      
      $min = 0;
      while($min++ < $hour) {
         $color = $color_blue;
         $radius = $radiusbak;
         if ($min % 15 == 0) {
            $len = $radius / 5;
         } elseif ($min % 5 == 0) {
            $len = $radius / 8;
         } else {
            $len = 0; 
            $radius = 0;//$radius / 25;
            $color = $color_alpha;
         }
         
         $ang = (2 * M_PI * $min) / 60 ;
         $ang = $ang - (M_PI/2);
         //echo $ang;
         $x1 = cos($ang) * ($radius - $len) + $radius;
         $y1 = sin($ang) * ($radius - $len) + $radius;
         $x2 = (1 + cos($ang)) * $radius;
         $y2 = (1 + sin($ang)) * $radius;
         
         
         ImageLinethick($img, $x1, $y1, $x2, $y2, $color, 3);
         
      }
   } else {
      ImageLinethick($img, $radius, $radius / 10, $radius, 0, $color_blue, 5);
   } 
   return $img;
}
   
function minute_lines() {
   $radius = floor($GLOBALS['size'] / 2);
   
   $img = ImageCreate($GLOBALS['size'], $GLOBALS['size']);
   $color_alpha = ImageColorAllocate($img, 254, 254, 254);
   $color_white = ImageColorAllocate($img, 255, 255, 255);
   $color_black = ImageColorAllocate($img, 0, 0, 0);
   $color_gray  = ImageColorAllocate($img, 192, 192, 192);
   $color_red   = ImageColorAllocate($img, 255, 0, 0);
   $color_blue  = ImageColorAllocate($img, 0, 0, 255);
   ImageColorTransparent($img, $color_alpha);
   
   $minute = @date('i');
   if($minute == '00') {$minute = '60';}
   
   $min = 0;
   while($min++ < $minute) {
      if ($min % 15 == 0)
         $len = $radius / 5;
      elseif ($min % 5 == 0)
		$len = $radius / 10;
      else
         $len = $radius / 25;
      
      $ang = (2 * M_PI * $min) / 60 ;
      $ang = $ang - (M_PI/2);
      //echo $ang;
      $x1 = cos($ang) * ($radius - $len) + $radius;
      $y1 = sin($ang) * ($radius - $len) + $radius;
      $x2 = (1 + cos($ang)) * $radius;
      $y2 = (1 + sin($ang)) * $radius;
      
      ImageLinethick($img, $x1, $y1, $x2, $y2, $color_white);
   }
   return $img;
}
   
function second_lines() {   
   $radius = floor($GLOBALS['size'] / 2);
   
   $img = ImageCreate($GLOBALS['size'], $GLOBALS['size']);
   $color_alpha = ImageColorAllocate($img, 254, 254, 254);
   $color_white = ImageColorAllocate($img, 255, 255, 255);
   $color_black = ImageColorAllocate($img, 0, 0, 0);
   $color_gray  = ImageColorAllocate($img, 192, 192, 192);
   $color_red   = ImageColorAllocate($img, 255, 0, 0);
   $color_blue  = ImageColorAllocate($img, 115, 192, 255);
   ImageColorTransparent($img, $color_alpha);
   
   
   $second = @date('s');
   if($second == '00') {$second = '60';}
   
   $min = 0;
   while($min++ < $second) {
      if ($min % 15 == 0)
         $len = $radius / 5;
      elseif ($min % 5 == 0)
		$len = $radius / 10;
      else
         $len = $radius / 25;
      
      $ang = (2 * M_PI * $min) / 60 ;
      $ang = $ang - (M_PI/2);
      $x1 = cos($ang) * ($radius - $len) + $radius;
      $y1 = sin($ang) * ($radius - $len) + $radius;
      $x2 = (1 + cos($ang)) * $radius;
      $y2 = (1 + sin($ang)) * $radius;
      
      ImageLinethick($img, $x1, $y1, $x2, $y2, $color_white);
   }
   return $img;
}
      
   
   

function parameters(){
   echo 'Valid parameters are: "hours", "minutes", "hourmin", "seconds" and "hands"'."\n";   
}
   
if ($argc > 2) {
   echo 'Only one parameter is allowed.'."\n";   
   parameters();
} elseif ($argc == 2) {
   switch ($argv[1]) {
      case 'hours':
         Imagepng(hour_lines(), $hour_lines);
         break;
      case 'minutes':
         Imagepng(minute_lines(), $minute_lines);
         break;
      case 'seconds':
         Imagepng(second_lines(), $second_lines);
         break;
      case 'hands':
         Imagepng(clock_hands(), $clock_hands);
         break;
      case 'hourmin':
         $output = minute_lines();
         imagecopymerge($output,hour_lines(),0,0,0,0,1000,1000,1000);
         Imagepng($output, $hourmin_lines);
         break;
      default:
         echo 'The parameter '.$argv[1].' does not exist.'."\n";
         parameters();
   }
} else {
   echo 'Sorry, but you have to supply a parameter.'."\n";
   parameters();
}
?>