<?php

include 'phpPredict.php';
include 'TleOrbit.php';

$code = $_GET['code'];
if($code == null)
	exit ;
$phpPredict = new phpPredict();
$calcStep = 1;
$curtime = $phpPredict -> current_daynum();

switch ($code) {
	case 1 :

	//返回所有星历文件名
		$tleFiles = array();
		$dir = opendir('./Tle');
		while($file = readdir($dir)) {
			if(substr($file, 0, 1) != '.') {
				$atle = new stdClass();
				$atle -> name = $file;
				array_push($tleFiles, $atle);
			}
		}
		$result = array('result' => $tleFiles);
		echo json_encode($result);
		break;

	case 2 :

	//输出指定星历文件的轨迹坐标，和所有卫星的名字、坐标
		$filename = $_GET['name'];
		if($filename == null)
			exit ;
		$points = array();
		$sats = array();
		$tle;
		$path = './Tle/' . $filename;
		$tles = TlesFromTxt($path);

		//每1分钟计算一次坐标
		for($i = 0; $i < count($tles); $i++) {
			$tle = $tles[$i];
			//卫星运行周期，分钟
			$periodMin = floor($tle -> period) + 1;
			if($i == 0) { //输出第一个卫星的轨道坐标
				for($j = 0; $j <= $periodMin * 2; $j += $calcStep) {
					$temptime = $curtime + $j / 1440.0;
					$obs_geodetic = new geodetic(40, 116, 0);
					$sat_data = new sat();
					$phpPredict -> track($tle, $obs_geodetic, $sat_data, $temptime);

					$point = new stdClass();
					$point -> b = number_format($sat_data -> lat, 2, '.', '');
					$point -> l = number_format($sat_data -> lon, 2, '.', '');
					array_push($points, $point);
				}
			}

			$phpPredict -> track($tle, $obs_geodetic, $sat_data, $curtime);

			$sat = new stdClass();
			$sat -> name = $tle -> sat_name;
			$sat -> b = number_format($sat_data -> lat, 2, '.', '');
			$sat -> l = number_format($sat_data -> lon, 2, '.', '');
			array_push($sats, $sat);

		}
		$result = new stdClass();
		$result -> points = $points;
		$result -> sats = $sats;
		echo json_encode($result);
		break;

	case 3 :

	//输出指定星历文件，指定卫星的xyz坐标
		$filename = trim($_GET['file']);
		$name = urldecode(trim($_GET['name']));
		$names = split(',', $name);
		$tles = TlesFromTxt('./Tle/' . $filename);
		$jul_utc = $curtime + 2444238.5;
		$sats = array();
		for($i = 0; $i < count($tles); $i++) {
			$tle = $tles[$i];
			if(in_array(trim($tle -> sat_name), $names, true)) {
				$jul_epoch = $phpPredict -> Julian_Date_of_Epoch($tle -> epoch);
				$tsince = ($jul_utc - $jul_epoch) * Globals::xmnpda;
				$periodMin = floor($tle -> period) + 1;
				$points = array();
				for($j = 0; $j <= $periodMin * 2; $j += $calcStep) {
					$tsince = $tsince + $j / 1440.0;
					$vel = new vector();
					$pos = new vector();
					if($tle -> deep)
						$phpPredict -> SDP4($tsince, $tle, $pos, $vel);
					else
						$phpPredict -> SGP4($tsince, $tle, $pos, $vel);
					$phpPredict -> Convert_Sat_State($pos, $vel);
					$point = array();
					$point['x'] = number_format($pos -> x * 1000, 2, '.', '');
					$point['y'] = number_format($pos -> y * 1000, 2, '.', '');
					$point['z'] = number_format($pos -> z * 1000, 2, '.', '');
					array_push($points, $point);
				}
				$sat = new stdClass();
				$sat -> name = $tle -> sat_name;
				$sat -> points = $points;
				array_push($sats, $sat);
			}
		}
		$result = new stdClass();
		$result -> result = $sats;
		echo json_encode($result);
		break;
	case 4 :

	//输出卫星可见数情况
		$almfiles = array('gps-ops.txt', 'glo-ops.txt', 'galileo.txt');
		$numarr = array();
		$lon = $_GET['lon'];
		$lat = $_GET['lat'];
		$alt = $_GET['alt'];
		$datestr = $_GET['date'];
		$datearr = explode('-', $datestr);
		$datearr[0] = substr($datearr[0], 2, 2);
		//年份只用后两位，如11
		$curtime = $phpPredict -> DayNum($datearr[1], $datearr[2], $datearr[0]);
		$obs_geodetic = new geodetic($lat, $lon, $alt);
		for($i = 0; $i < count($almfiles); $i++) {
			$tles = TlesFromTxt('./Tle/' . $almfiles[$i]);
			$tarr = array();
			for($j = 0; $j < 24; $j++) {
				$num = 0;
				for($k = 0; $k < count($tles); $k++) {
					$sat_data = new sat();
					$time = $curtime + $j / 24.0;
					$phpPredict -> track($tles[$k], $obs_geodetic, $sat_data, $time);
					if($sat_data -> ele > 15)
						$num++;
				}
				array_push($tarr, $num);
			}
			array_push($numarr, $tarr);
		}

		$jsonfile = './json/numChart.json';
		$file = fopen($jsonfile, 'r');
		$content = fread($file, filesize($jsonfile));
		$json = json_decode($content);
		for($i = 0; $i < count($json -> graphset[0] -> series); $i++) {
			$json -> graphset[0] -> series[$i] -> values = $numarr[$i];
		}
		fclose($file);
		$json->graphset[0]->title->text = '卫星可见数分布图('.$datestr.')';
		$jsonstr = json_encode($json);		
		echo $jsonstr;
		break;
	case 5 :

	//输入卫星高度角列表
		$almfile = 'gps-ops.txt';
		if($_GET['file']!=null)
			$almfile = trim($_GET['file']);
		$lon = $_GET['lon'];
		$lat = $_GET['lat'];
		$alt = $_GET['alt'];
		$datestr = $_GET['date'];
		$datearr = explode('-', $datestr);
		$datearr[0] = substr($datearr[0], 2, 2);
		//年份只用后两位，如11
		$curtime = $phpPredict -> DayNum($datearr[1], $datearr[2], $datearr[0]);
		$obs_geodetic = new geodetic($lat, $lon, $alt);

		$tles = TlesFromTxt('./Tle/' . $almfile);
		$timearr = array();
		$valuearr = array();
		for($k = 0; $k < count($tles); $k++) {
			$eles = array();
			$avalue = new stdClass();
			for($j = 0; $j < 24 * 60; $j += 20) { //20分钟计算一次
				if($k == 0) {
					$tempstr = floor($j / 60) . ':' . ($j % 60);
					$timearr[] = $tempstr;
					//或用array_push($timearr, $tempstr)
				}
				$sat_data = new sat();
				$time = $curtime + $j / 24.0 / 60.0;
				$phpPredict -> track($tles[$k], $obs_geodetic, $sat_data, $time);
				if($sat_data -> ele < 10)
					$sat_data -> ele = 10;
				//高度角小于10度则不可见
				$eles[] = number_format($sat_data -> ele, 2, '.', '');
			}
			$avalue -> text = 'G'.substr($tles[$k] -> sat_name, -3,2);
			$avalue -> values = $eles;
			$avalue -> line_width = '2px';
			array_push($valuearr, $avalue);
		}

		$jsonfile = './json/eleChart.json';
		$file = fopen($jsonfile, 'r');
		$content = fread($file, filesize($jsonfile));
		fclose($file);
		$json = json_decode($content);
		$json -> graphset[0] -> series = $valuearr;
		$jsonstr = json_encode($json);
		$jsonstr = str_replace('scale-x', 'scale_x', $jsonstr);
		$json = json_decode($jsonstr);
		$json -> graphset[0] -> scale_x -> values = $timearr;
		$json->graphset[0]->title->text = '高度角分布图('.$almfile.','.$datestr.')';
		$jsonstr = json_encode($json);
		$jsonstr = str_replace('scale_x', 'scale-x', $jsonstr);
		$jsonstr = str_replace('line_width', 'line-width', $jsonstr);		
		echo $jsonstr;
		break;
	case 6 :
		
	//返回DOP值列表
		$almfile = 'gps-ops.txt';
		if($_GET['file']!=null)
			$almfile = trim($_GET['file']);
		$lon = $_GET['lon'];
		$lat = $_GET['lat'];
		$alt = $_GET['alt'];
		$datestr = $_GET['date'];
		$datearr = explode('-', $datestr);
		$datearr[0] = substr($datearr[0], 2, 2);
		//年份只用后两位，如11
		$curtime = $phpPredict -> DayNum($datearr[1], $datearr[2], $datearr[0]);
		$obs_geodetic = new geodetic($lat, $lon, $alt);
		$cor_limit = 10;		
		
		$tles = TlesFromTxt('./Tle/' . $almfile);		
		$GDOParr = array();
		$PDOParr = array();
		$HDOParr = array();
		$VDOParr = array();
		$TDOParr = array();
		$timearr = array();
		for ($j = 0; $j < 24 * 60; $j += 20){
			$timearr[] = floor($j / 60) . ':' . ($j % 60);
			$GDP = 0; $PDOP = 0; $HDOP = 0; $VDOP = 0; $VDOP = 0;
			CalcDops($tles, $curtime+$j/ 24.0 / 60.0, $lat, $lon, $alt, $cor_limit, $GDOP, $PDOP, $HDOP, $VDOP, $TDOP);
			$GDOParr[] = number_format($GDOP, 2, '.', '');
			$PDOParr[] = number_format($PDOP, 2, '.', '');
			$HDOParr[] = number_format($HDOP, 2, '.', '');
			$VDOParr[] = number_format($VDOP, 2, '.', '');
			$TDOParr[] = number_format($TDOP, 2, '.', '');
		}
		
		$jsonfile = './json/dopChart.json';
		$file = fopen($jsonfile, 'r');
		$content = fread($file, filesize($jsonfile));
		fclose($file);
		$json = json_decode($content);
		$json -> graphset[0] -> series[0]->values = $GDOParr;
		$json -> graphset[0] -> series[1]->values = $PDOParr;
		$json -> graphset[0] -> series[2]->values = $HDOParr;
		$json -> graphset[0] -> series[3]->values = $VDOParr;
		$json -> graphset[0] -> series[4]->values = $TDOParr;
		$jsonstr = json_encode($json);
		
		$jsonstr = str_replace('scale-x', 'scale_x', $jsonstr);
		$json = json_decode($jsonstr);
		$json -> graphset[0] -> scale_x -> values = $timearr;
		$json->graphset[0]->title->text = 'DOP值分布图('.$almfile.','.$datestr.')';
		$jsonstr = json_encode($json);
		$jsonstr = str_replace('scale_x', 'scale-x', $jsonstr);			
		echo $jsonstr;
		break;
}
?>