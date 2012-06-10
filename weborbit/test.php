<?php

// Include the phpPredict library so we can use the classes
include 'phpPredict.php';
//
// // Create a new phpPredict object
$phpPredict = new phpPredict();
//
// // Test SGP4

$line0 = "GPS BIIA-11 (PRN 24)    ";
$line1 = "1 21552U 91047A   11125.07302275  .00000045  00000-0  10000-3 0   952";
$line2 = "2 21552  54.3295 193.3624 0060668 341.2287  18.6366  2.00558058145267";
//
// // Create a new tle object based on the tle data
$tle = new tle($line0, $line1, $line2);
//
// // Create a new geodetic object for the observers lat, lon and alt
$obs_geodetic = new geodetic(40, 116, 0);
//
// // Get the current daynum from microtime
// // This function is only available on operating systems that support the gettimeofday() system call.
$daynum = $phpPredict->current_daynum();
$daynum = $phpPredict->DayNum(5, 2, 11);
//
// // Track a single satellites position using the specified tle, observers geodetics and daynum
$sat_data = new sat();
$pos = new vector();
$pos = $phpPredict->track($tle, $obs_geodetic, $sat_data, $daynum);
//
// // Print the satellite location and velocity
echo "lat=".$sat_data->lat."<br>";
echo "lon=".$sat_data->lon."<br>";
echo "vel=".$sat_data->alt."<br>";
echo "a=".$sat_data->azi."<br>";
echo "e=".$sat_data->ele."<br>";
echo "range=".$sat_data->range."<br>";
//
echo $pos->x."<br>".$pos->y."<br>".$pos->z;
//$phpPredict->
//
//
	$sat = array(
		'name'=>'GPS BIIA-10 (PRN 32)',	
		'point'=>array(
			"x"=>-6032927.72,
			"y"=>-25610106.37,
			"z"=>1240959.68
		)
	);
	$json_string = json_encode($sat);
	$json = json_decode($json_string);
	echo '<br>'.$json_string.'<br>';
	echo $json->name;

?>
