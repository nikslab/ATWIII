#!/usr/bin/php
<?php

$directories = glob("*_*", GLOB_ONLYDIR);

foreach ($directories as $data_dir) {
	$cmd = './Data2kml.php '.$data_dir."/";
	print "$cmd\n";
	exec($cmd);
}
