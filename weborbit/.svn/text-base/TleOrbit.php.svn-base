<?php
include_once 'phpPredict.php';
include 'matrix.php';

function TlesFromTxt($path) {
	$i = 1;
	$line1 = '';
	$line2 = '';
	$line3 = '';
	$tles = array();
	$file = fopen($path, 'r') or exit("Unable to open file!"); ;
	while(!feof($file)) {
		switch ($i) {
			case 1 :
				$line1 = fgets($file);
				$i++;
				break;
			case 2 :
				$line2 = fgets($file);
				$i++;
				break;
			case 3 :
				$line3 = fgets($file);
				$tle0 = new tle($line1, $line2, $line3);
				array_push($tles, $tle0);
				$i = 1;
				break;
		}
	}
	fclose($file);
	return $tles;
}

//$time为curtime类型的
function CalcDops($tles, $time, $B, $L, $H, $cor_limit, &$GDOP, &$PDOP, &$HDOP, &$VDOP, &$TDOP) {
	$x2 = 0;
	$y2 = 0;
	$z2 = 0;
	BLToXYZ($B / 180 * Globals::pi, $L / 180 * Globals::pi, $H, $x2, $y2, $z2);
	$obs_geodetic = new geodetic($B, $L, $H);
	$phpPredict = new phpPredict();
	//$jul_utc = $time + 2444238.5;
	$temp = array();
	$sum = 0;
	for($i = 0; $i < count($tles); $i++) {
		$tle = $tles[$i];
		$sat_data = new sat();		
		$pos = new vector();
		$pos = $phpPredict -> track($tle, $obs_geodetic, $sat_data, $time); //返回卫星坐标 
		$x = $pos -> x * 1000;
		$y = $pos -> y * 1000;
		$z = $pos -> z * 1000;		
		if($sat_data -> ele > $cor_limit) {
			$sum++;
			$d_x = $x - $x2;
			$d_y = $y - $y2;
			$d_z = $z - $z2;
			$r2 = sqrt($d_x * $d_x + $d_y * $d_y + $d_z * $d_z);
			$temp[] = $d_x / $r2;
			$temp[] = $d_y / $r2;
			$temp[] = $d_z / $r2;
		}
	}
	$Q = array();
	$Q_X0 = array();
	$Q_x = array();
	for($j = 0; $j < count($temp); $j++) {
		$div = floor($j / 3);
		if(($j + 1) % 3 == 1) {
			$Q[$div][0] = $temp[$j];
		} else if(($j + 1) % 3 == 2) {
			$Q[$div][1] = $temp[$j];
		} else if(($j + 1) % 3 == 0) {
			$Q[$div][2] = $temp[$j];
		}
		$Q[$div][3] = 1;
	}
	$Q_t = matrix_trans($Q);
	$Q_x0 = matrix_multi($Q_t, $Q);
	$Q_x = matrix_inverse($Q_x0);
	$GDOP = sqrt($Q_x[0][0] + $Q_x[1][1] + $Q_x[2][2] + $Q_x[3][3]);
	$PDOP = sqrt($Q_x[0][0] + $Q_x[1][1] + $Q_x[2][2]);
	$HDOP = sqrt($Q_x[0][0] + $Q_x[1][1]);
	$VDOP = sqrt($Q_x[2][2]);
	$TDOP = sqrt($Q_x[3][3]);
	return $sum;
}

function BLToXYZ($B, $L, $H, &$X, &$Y, &$Z) {
	$a2 = 6378245.0;
	$e2 = 0.00669342162297;
	$N = $a2 / (sqrt(1 - $e2 * pow(sin($B), 2)));
	$X = ($N + $H) * cos($B) * cos($L);
	$Y = ($N + $H) * cos($B) * sin($L);
	$Z = ($N * (1 - $e2) + $H) * sin($B);
}
?>