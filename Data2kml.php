#!/usr/bin/php
<?php

$data_dir = $argv[1];
$files = glob($data_dir."/Data.txt");
$data_file = $files[0];

$splited = explode("_", $data_dir);
$splited2 = explode("/", $splited[1]);
$route = $splited2[0];
$name = $route;

$output_file = $data_dir."map_".$route.".kml";

$coordinates = "";
$cadence = 5;
if (isset($argv[2])) { 
    $cadence = $argv[2];
}

$nula = -1;

$distance = 0;
$seconds = 0;
$start_seconds = 0;

$handle = fopen($data_file, "r");
if ($handle) {
    $previous = "";
    $c = 0;
    while (($line = fgets($handle)) !== false) {
        $c++;
        if ($c >= $cadence) {
            $c = 0;
            $splited = explode("|", str_replace(" ", "", $line));       
            $distance_ft = $splited[21];  
            $distance_km = round(($distance_ft * 0.3048)/1000, 0);  
            if (($distance_ft > 50) && ($start_seconds == 0)) {
               $start_seconds = $splited[2];
            }	
            $seconds = $splited[2];
            $feet = $splited[9];
            $meters = round($feet * 0.3048, 0);
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
                }
                $previous = $rec;
            }
        }
    }

    fclose($handle);
} else {
    // error opening the file.
} 

$colors = ["F7230A"];
$coloridx = array_rand($colors);
$color1 = $colors[$coloridx]."ff";
$color2 = $colors[$coloridx]."E6";

$header = <<<HTML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
  <Document>
    <name><![CDATA[$name]]></name>
    <visibility>1</visibility>
    <open>1</open>
    <Snippet><![CDATA[ATWIII]]></Snippet>
    <Folder id="Tracks">
      <name>Tracks</name>
      <visibility>1</visibility>
      <open>0</open>
      <Placemark>
        <name><![CDATA[$name]]></name>
        <Snippet></Snippet>
        <description><![CDATA[&nbsp;]]></description>
        <Style>
          <LineStyle>
            <color>$color1</color>
            <width>5</width>
          </LineStyle>
          <PolyStyle>
            <color>$color2</color>
          </PolyStyle>
        </Style>
        <LineString>
          <extrude>1</extrude>
          <tessellate>0</tessellate>
          <altitudeMode>relativeToGround</altitudeMode>
          <coordinates>
HTML;

$footer = <<<HTML

          </coordinates>
        </LineString>
      </Placemark>
    </Folder>
  </Document>
</kml>
HTML;

$result = "$header $coordinates $footer";
file_put_contents($output_file, $result);


print "Distance: $distance_km km\n";
$t = gmdate("H:i:s", round($seconds-$start_seconds,0));
$mins = round(($seconds-$start_seconds)/60,0);
print "Flight time: $t ($mins mins)\n";

function dist($lat1, $lon1, $alt1, $lat2, $lon2, $alt2) {

   $R = 6371000; // km
   $pi = 3.1415926535897932384626433832795;


   $dLat = (Radians($lat2)-Radians($lat1)); # Make sure it's in radians, not degrees
   $dLon = (Radians($lon2)-Radians($lon1)); # Idem 

   $a = sin($dLat/2) * sin($dLat/2) +
        cos($lat1) * cos($lat2) * 
        sin($dLon/2) * sin($dLon/2); 
   $c = 2 * atan2(sqrt($a), sqrt(1-$a)); 
   $b = $R * $c;
   
   $d = sqrt( abs($alt2-$alt1)**2 + $b**2 );

   return $d;
}

function Radians($val) {
   $pi = 3.145;
   return ($val*$pi) / 180;
}

