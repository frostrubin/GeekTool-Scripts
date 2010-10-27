#!/usr/bin/perl

#### ASCII CLOCK ####
## Original source came from http://perlmonks.org/?node_id=440771 ##
## Credits to Flavio Poletti ##
## Only small changes were made for the usage with GeekTool ##

use strict;
use warnings;

#This will let me handle the output as a simple matrix.
# A simple screen abstraction, actually a long array
package Screen;
sub new { # Get a Screen of given sizes
   my ($class, $wx, $wy) = @_;
   my $self = {_wx => $wx,
      _wy => $wy,
      _data => [ ((' ') x $wx , "\n") x $wy ]};
   bless $self, $class;
}

sub _index { # Find index of given coordinate into vector
   my ($self, $x, $y) = @_;
   return $x + ($y * (1 + $self->{_wx}));
}

# Public methods
sub inbound { # Test if coordinates are within Screen bounds
   my ($self, $x, $y) = @_;
   return ($x >= 0 && $y >= 0 && $x < $self->{_wx} && $y < $self->{_wy});
}

sub set { # Set value $v at given coordinates, with clipping
   my ($self, $x, $y, $v) = @_;
   return undef unless $self->inbound($x, $y);
   $self->{_data}->[$self->_index($x, $y)] = $v;
}

sub to_string { # Dump Screen onto string
   return join('', @{$_[0]->{_data}});
}

#Back to reality. The main cycle gets a new ASCII clock representation every second and prints it.

package main;
use POSIX 'ceil';
my $pi2 = 2 * atan2(0, -1); # Useful to have around

my ($wx, $wy) = @ARGV; # Accept sizes from command line

# Make parameters mandatory. So that you HAVE to supply 2 of them.
if ($#ARGV + 1 < 2) {
   print "Please supply 2 parameters: width and height.\n";
   print "Example: ascii_clock.pl 45 23.\n";
   exit();
}

# No cycling. It's for GeekTool, which controls script-updating
#while (1) { # Cycle forever, updating each second
#print "\e[2J", anaclock($wx, $wy);
print  anaclock($wx, $wy);
#   sleep(1);
#}

#Accessor functions. The adjust function rounds numbers to have integer matrix indexes;
# in the meanwhile, it also performs a translation by 1 cell - please don't ask me why,
# it works :) 
#The two drawing functions do what they declare.

# Round and translate to better fit inside the screen
sub adjust {return ceil($_[0] - 1.5);}

sub draw_circle {
my ($screen, $cx, $cy, $rx, $ry, $c) = @_;
my $radius = ($rx > $ry) ? $rx : $ry;
my $step = abs(1 / ($radius * $pi2));
for (my $a = 0.0; $a < 2 * $pi2; $a += $step) {
$screen->set(adjust($cx + $rx * cos($a)), 
adjust($cy + $ry * sin($a)), $c);
}
}

sub draw_line {
my ($screen, $ax, $ay, $bx, $by, $c) = @_;
my ($dx, $dy) = ($bx - $ax, $by - $ay);
my ($adx, $ady) = (abs($dx), abs($dy));
my $delta = ($adx > $ady) ? $adx : $ady;
$dx /= $delta;
$dy /= $delta;
for (; $delta > 0; $ax += $dx, $ay += $dy, --$delta) {
$screen->set(adjust($ax), adjust($ay), $c);
}
}

#The actual ASCII clock building function. It accepts width and heigth even 
# if it increases them by 1 when building the screen- I hope you're really not
# going to be annoyed because of this.

sub anaclock {
   my $width  = shift || 45;
   my $height = shift || 23;
   
   --$width; --$height; # Too lazy to change code after...
   
   my ($rx, $ry) = ($width / 2, $height / 2);
   my ($cx, $cy) = (1 + $rx, 1 + $ry);
   
   # Get a virtual screen to write onto; get it a little larger
   # to cope with roundups
   my $screen = Screen->new($width + 1, $height + 1);
   
   # Draw the surronding circle
   draw_circle($screen, $cx, $cy, $rx, $ry, '.');
   
   # Decrease radius to be strictly inside
   $rx *= 6/8;
   $ry *= 6/8;
   
   # What time is it?
   my ($sec, $min, $hour) = (localtime(time))[0 .. 2];
   
   # Scale values to get angles. Note that $min is shifted by the
   # seconds, and $hour is shifted by the minutes.
   $sec  *= $pi2 / 60;
   $min  = ($min * $pi2 + $sec) / 60;
   $hour = (($hour % 12) * $pi2 + $min) / 12;
   
   # Draw lines. According to most clocks, hours are in background and
   # seconds in foreground.
   draw_line($screen, $cx, $cy, $cx + $rx * sin($hour) * 2 / 3,
   $cy - $ry * cos($hour) * 2 / 3, '#');
   draw_line($screen, $cx, $cy, $cx + $rx * sin($min), 
   $cy - $ry * cos($min), '+');
   draw_line($screen, $cx, $cy, $cx + $rx * sin($sec), 
   $cy - $ry * cos($sec), '.');
   
   # Return a string representation
   return $screen->to_string();
}