#!/usr/bin/php
<?php

$directories = glob("*_*", GLOB_ONLYDIR);

$TOTAL_KML_DISTANCE = 0;
$COUNT_FLIGHTS = 0;

foreach ($directories as $data_dir) {

$COUNT_FLIGHTS++;

$coordinates = "";
$cadence = 5;

$nula = -1;

$distance = 0;
$seconds = 0;
$start_seconds = 0;
$total_dist = 0;
$data_file = "$data_dir/Data.txt";

$handle = fopen($data_file, "r");
if ($handle) {
    $previous = "";
    $c = 0;
    while (($line = fgets($handle)) !== false) {
        $c++;
        if ($c >= $cadence) {
            $c = 0;
            $splited = explode("|", str_replace(" ", "", $line));  
            if (count($splited) > 10) {     
		    $distance_ft = $splited[21];  
		    if (is_numeric($distance_ft)) {
		       $distance_km = round(($distance_ft * 0.3048)/1000, 0);  
		    }
		    if (($distance_ft > 50) && ($start_seconds == 0)) {
		       $start_seconds = $splited[2];
		    }	
		    $seconds = $splited[2];
		    $feet = $splited[9];
		    if (is_numeric($feet)) {
		       $meters = round($feet * 0.3048, 0);
		    }
		    if ($nula < 0) {
		    	$nula = $meters;
		    }
		    $meters = $meters - $nula;
		    if ($meters < 0) { $meters = 1; }
		    $rec = $splited[8].",".$splited[7].",".$meters;
		    $loc = $splited[8].",".$splited[7];
		                
		    if ( ($rec != $previous) && (is_numeric($splited[1])) ) {
		        if ($previous != "") {
		           $coordinates .= $rec." ";
		           $splited = explode(",", $previous);
		           $lat1 = $splited[0];
		           $lon1 = $splited[1];
		           $alt1 = $splited[2];
		           $splited = explode(",", $rec);
		           $lat2 = $splited[0];
		           $lon2 = $splited[1];
		           $alt2 = $splited[2];
		           $total_dist += dist($lat1, $lon1, $alt1, $lat2, $lon2, $alt2);
		        }
		        $previous = $rec;
		    }
            }
        }
    }

    fclose($handle);
} else {
    // error opening the file.
} 

/*
print "Distance: $distance_km km\n";
print "KML distance: $total_dist\n";
$t = gmdate("H:i:s", round($seconds-$start_seconds,0));
$mins = round(($seconds-$start_seconds)/60,0);
print "Flight time: $t ($mins mins)\n\n\n";
*/

$TOTAL_KML_DISTANCE += $total_dist;

}

print "TOTAL FLIGHTS: $COUNT_FLIGHTS\n";
print "TOTAL KML DISTANCE: $TOTAL_KML_DISTANCE\n";

function dist($lat1, $lon1, $alt1, $lat2, $lon2, $alt2) {

   $R = 6371; // km
   $pi = 3.1415926535897932384626433832795;


   $dLat = (Radians($lat2)-Radians($lat1)); # Make sure it's in radians, not degrees
   $dLon = (Radians($lon2)-Radians($lon1)); # Idem 

   $a = sin($dLat/2) * sin($dLat/2) +
        cos($lat1) * cos($lat2) * 
        sin($dLon/2) * sin($dLon/2); 
   $c = 2 * atan2(sqrt($a), sqrt(1-$a)); 
   $b = $R * $c;
      
   $d = sqrt( abs(($alt2-$alt1))/1000**2 + $b**2 );
   //print "$alt1, $alt2, $d\n";

   return $d;
}

function Radians($val) {
   $pi = 3.1415926535897932384626433832795;
   return ($val*$pi) / 180;
}


