#!/usr/bin/php
<?php

$output_file = "ATWIII.kml";

$placemarks = "";

#$colors = ["AE2914", "19AE14", ];
$colors = ["AE2914", "831C8C"];


$header = <<<HTML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">
<Document>
	<name>Around the World III</name>
	<open>1</open>
	<Snippet maxLines="2"><![CDATA[ATWIII]]></Snippet>
	<Folder id="Tracks">
		<name>Tracks</name>
HTML;

$footer = <<<HTML
        </Folder>
    </Document>
</kml>
HTML;

$directories = glob("*_*-*");
foreach ($directories as $data_dir) {
    $files = glob($data_dir."/Data.txt");
    if (!empty($files)) {
        $data_file = $files[0];

        $splited = explode("_", $data_file);
        $splited2 = explode(".", $splited[1]);
        $route = $splited2[0];
        $name = $route;

        $coordinates = "";
        $cadence = 5;
        if (isset($argv[2])) { 
            $cadence = $argv[2];
        }

	$nula = -1;

	print "Processing $data_file\n";

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
 

        // flip colors
        $temp = $colors[0];
        $colors[0] = $colors[1];
        $colors[1] = $temp;

        $coloridx = 0;
        $color1 = $colors[$coloridx]."ff";
        $color2 = $colors[$coloridx]."e6";

        $placemarks .= placemarkHTML($coordinates, $name, $color1, $color2);

    }

}

// Create trip KML
$result = "$header $placemarks $footer";
file_put_contents($output_file, $result);

function placemarkHTML($coordinates, $name, $color1, $color2) {

$placemark_start = <<<HTML
        <Placemark>
			<name>$name</name>
			<Snippet maxLines="2">$name</Snippet>
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
				<altitudeMode>clampToGround</altitudeMode>
				<coordinates>
HTML;

$placemark_end = <<<HTML
                </coordinates>
			</LineString>
		</Placemark>
HTML;

    $result = $placemark_start.$coordinates.$placemark_end;
    return $result;
}
