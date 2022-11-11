#!/usr/bin/php
<?php

$output_file = "Tour_ATW2021.kml";

$placemarks = "";

#$colors = ["AE2914", "19AE14", ];
$colors = ["AE2914", "831C8C"];


$header = <<<HTML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2"
  xmlns:gx="http://www.google.com/kml/ext/2.2">
  
  <gx:Tour>
    <name>Around the World 2020</name>
    
    <gx:Playlist>
      
HTML;

$footer = <<<HTML
    </gx:Playlist>
  </gx:Tour>
</kml>
HTML;

$directories = glob("*");
foreach ($directories as $data_dir) {
    print "Processing $data_dir...\n";
    $files = glob($data_dir."/data_*.txt");
    if (!empty($files)) {
        $data_file = $files[0];

        $splited = explode("_", $data_file);
        $splited2 = explode(".", $splited[1]);
        $route = $splited2[0];
        $name = $route;

        $coordinates = "";
        $cadence = 1;
        if (isset($argv[2])) { 
            $cadence = $argv[2];
        }

        $lat = false;
        $long = false;
        $alt = false;
        $handle = fopen($data_file, "r");

        if ($handle) {
            $previous = "";
            $c = 0;
            while ((($line = fgets($handle)) !== false) && ($c < 50)) {
                $c++;
                if ($c == 50) {
                    $splited = explode("|", str_replace(" ", "", $line));
                    $feet = $splited[5];
                    $meters = round($feet * 0.3048, 0);
                    if ($meters < 0) { $meters = 1; }
                    $rec = $splited[1].",".$splited[0].",".$meters;
                    $loc = $splited[1].",".$splited[0];
                    $long = $splited[0];
                    $lat = $splited[1];
                    $alt = $meters;
                }
            }

            fclose($handle);
        } else {
            // error opening the file.
        } 

        $placemarks .= FlyTo($lat, $long, $alt);

    }

}

// Create trip KML
$result = "$header $placemarks $footer";
file_put_contents($output_file, $result);

function FlyTo($long, $lat, $alt) {

    $heading = rand(0, 180)-90;

$placemark_start = <<<HTML

        <gx:FlyTo>
        <gx:duration>5.0</gx:duration>
        <LookAt>
            <longitude>$long</longitude>
            <latitude>$lat</latitude>
            <altitude>$alt</altitude>
            <heading>$heading</heading>
            <tilt>71.600075</tilt>
            <range>22570.546801</range>
            <altitudeMode>relativeToGround</altitudeMode>
        </LookAt>
        </gx:FlyTo>

        <gx:Wait>
            <gx:duration>5.0</gx:duration>   <!-- wait time in seconds -->
        </gx:Wait>

HTML;

    $result = $placemark_start;
    return $result;
}
