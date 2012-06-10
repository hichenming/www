<?php

// phpPredict 1.0.4 by Carl Fretwell
// Last Updated: 16 February 2011

// Implementation of NORAD SGP4/SDP4 algorithms in PHP for determining a satellites location and velocity in Earth orbit.
// Features now include sun tracking, sun predictions, moon tracking and moon predictions.

// This program is free software. You can redistribute it and/or modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2 of the License or any later version.

// Please feel free to send me any comments, suggestions or bug reports at http://carlfretwell.com/contact

// Constants used throughout SGP4/SDP4 code

class Globals {
	const km2mi = 0.621371;
	const deg2rad = 1.745329251994330E-2;
	const pi = 3.14159265358979323846;
	const pio2 = 1.57079632679489656;
	const x3pio2 = 4.71238898038468967;
	const twopi = 6.28318530717958623;
	const e6a = 1.0E-6;
	const tothrd = 6.6666666666666666E-1;
	const xj2 = 1.0826158E-3;
	const xj3 = -2.53881E-6;
	const xj4 = -1.65597E-6;
	const xke = 7.43669161E-2;
	const xkmper = 6.378137E3;
	const xmnpda = 1.44E3;
	const ae = 1.0;
	const ck2 = 5.413079E-4;
	const ck4 = 6.209887E-7;
	const f = 3.35281066474748E-3;
	const ge = 3.986008E5;
	const s = 1.012229;
	const qoms2t = 1.880279E-09;
	const secday = 8.6400E4;
	const omega_E = 1.00273790934;
	const omega_ER = 6.3003879;
	const zns = 1.19459E-5;
	const c1ss = 2.9864797E-6;
	const zes = 1.675E-2;
	const znl = 1.5835218E-4;
	const c1l = 4.7968065E-7;
	const zel = 5.490E-2;
	const zcosis = 9.1744867E-1;
	const zsinis = 3.9785416E-1;
	const zsings = -9.8088458E-1;
	const zcosgs = 1.945905E-1;
	const zcoshs = 1;
	const zsinhs = 0;
	const q22 = 1.7891679E-6;
	const q31 = 2.1460748E-6;
	const q33 = 2.2123015E-7;
	const g22 = 5.7686396;
	const g32 = 9.5240898E-1;
	const g44 = 1.8014998;
	const g52 = 1.0508330;
	const g54 = 4.4108898;
	const root22 = 1.7891679E-6;
	const root32 = 3.7393792E-7;
	const root44 = 7.3636953E-9;
	const root52 = 1.1428639E-7;
	const root54 = 2.1765803E-9;
	const thdt = 4.3752691E-3;
	const rho = 1.5696615E-1;
	const mfactor = 7.292115E-5;
	const sr = 6.96000E5;
	const AU = 1.49597870691E8;
}

// Classes

class vector {
	public $x;
	public $y;
	public $z;
	public $w;
}

// Two-line element satellite orbital data class used directly by the SGP4/SDP4 code.

class tle {
	public $sat_name;
	public $line1;
	public $line2;

	// SGP4 if false and SDP4 if true
	public $deep;
	public $epoch;
	public $xndt2o;
	public $xndd6o;
	public $bstar;
	public $xincl;
	public $xnodeo;
	public $eo;
	public $omegao;
	public $xmo;
	public $xno;
	public $catnum;
	public $elset;
	public $orbitnum;
	public $idesg;
	public $period;
	public $nodal_period;
	public $year;
	public $refepoch;
	public $drag;
	public $eccn;
	public $incl;
	public $meanan;
	public $meanmo;

	function __construct($name, $line1, $line2) {
		$this -> sat_name = trim($name);
		$this -> line1 = $line1;
		$this -> line2 = $line2;
		$this -> idesg = substr($line1, 9, 8);
		$this -> catnum = (int)substr($line1, 2, 5);
		$this -> year = substr($line1, 18, 2);
		$this -> refepoch = substr($line1, 20, 12);
		$this -> epoch = (1000.0 * $this -> year) + $this -> refepoch;
		$this -> xndt2o = substr($line1, 33, 10);
		$this -> drag = $this -> xndt2o;
		$this -> xndd6o = (1.0e-5 * substr($line1, 44, 6)) / pow(10.0, (substr($line1, 51, 1)));
		$this -> bstar = (1.0e-5 * substr($line1, 53, 6)) / pow(10.0, ($line1[60]));
		$this -> xincl = substr($line2, 8, 8);
		$this -> incl = $this -> xincl;
		$this -> xnodeo = substr($line2, 17, 8);
		$this -> eo = 1.0e-07 * substr($line2, 26, 7);
		$this -> eccn = $this -> eo;
		$this -> omegao = substr($line2, 34, 8);
		$this -> xmo = substr($line2, 43, 8);
		$this -> meanan = $this -> xmo;
		$this -> xno = substr($line2, 52, 11);
		$this -> meanmo = $this -> xno;
		$this -> orbitnum = substr($line2, 63, 5);

		// Preprocess the values in the tle set so that they are
		// appropriate for the SGP4/SDP4 routines

		$this -> xnodeo *= Globals::deg2rad;
		$this -> omegao *= Globals::deg2rad;
		$this -> xmo *= Globals::deg2rad;
		$this -> xincl *= Globals::deg2rad;
		$temp = ((Globals::twopi / Globals::xmnpda) / Globals::xmnpda);
		$this -> xno = $this -> xno * $temp * Globals::xmnpda;
		$this -> xndt2o = $this -> xndt2o * $temp;
		$this -> xndd6o = $this -> xndd6o * $temp / Globals::xmnpda;
		$this -> bstar = $this -> bstar / Globals::ae;
		$this -> period = 1440.0 / $this -> meanmo;
		$sma = 331.25 * exp(log(1440.0 / $this -> meanmo) * (2.0 / 3.0));
		$c1 = cos($this -> incl * Globals::deg2rad);
		$e2 = 1.0 - ($this -> eccn * $this -> eccn);
		$this -> nodal_period = ($this -> period * 360.0) / (360.0 + (4.97 * pow((Globals::xkmper / $sma), 3.5) * ((5.0 * $c1 * $c1) - 1.0) / ($e2 * $e2)) / $this -> meanmo);

		// A period > 225 minutes is a deep space orbit satellite

		$dd1 = (Globals::xke / $this -> xno);
		$dd2 = Globals::tothrd;
		$a1 = pow($dd1, $dd2);
		$r1 = cos($this -> xincl);
		$dd1 = (1.0 - $this -> eo * $this -> eo);
		$temp = Globals::ck2 * 1.5 * ($r1 * $r1 * 3.0 - 1.0) / pow($dd1, 1.5);
		$del1 = $temp / ($a1 * $a1);
		$ao = $a1 * (1.0 - $del1 * (Globals::tothrd * .5 + $del1 * ($del1 * 1.654320987654321 + 1.0)));
		$delo = $temp / ($ao * $ao);
		$xnodp = $this -> xno / ($delo + 1.0);

		// Select a deep-space or near-earth ephemeris

		if(Globals::twopi / $xnodp / Globals::xmnpda >= 0.15625)
			$this -> deep = 1;
		else
			$this -> deep = 0;
	}

}

// Satellite position class used by phpPredict

class sat {
	public $lat;
	public $lon;
	public $alt;
	public $footprint;
	public $range;
	public $range_rate;
	public $vel;
	public $azi;
	public $ele;
	public $eclipsed;
	public $eclipse_depth;
	public $visible;
	public $age;
}

// Sun position class used by phpPredict

class sun {
	public $lat;
	public $lon;
	public $azi;
	public $ele;
	public $range;
	public $range_rate;
	public $ra;
	public $dec;
}

// Moon position class used by phpPredict

class moon {
	public $lat;
	public $lon;
	public $azi;
	public $ele;
	public $dv;
	public $ra;
	public $dec;
	public $gha;
}

// Predict class used by phpPredict

class predict {
	public $aos_daynum;
	public $aos_ele;
	public $aos_azi;
	public $aos_lat;
	public $aos_lon;
	public $aos_range;
	public $aos_ts;
	public $aos_orbitnum;
	public $los_daynum;
	public $los_azi;
	public $los_ele;
	public $los_lat;
	public $los_lon;
	public $los_range;
	public $los_ts;
	public $los_orbitnum;
	public $duration;
}

// Geodetic position class used by SGP4/SDP4 code.
// The lat and lon needs to be passed in decimal degrees

class geodetic {
	public $lat;
	public $lon;
	public $alt;
	public $theta;

	function __construct($lat, $lon, $alt) {
		$this -> lat = $lat;
		$this -> lon = $lon;
		$this -> alt = $alt;
		$this -> theta = 0;
	}

}

class phpPredict {
	// Flags

	private $SGP4_INITIALIZED_FLAG = 0;
	private $SDP4_INITIALIZED_FLAG = 0;
	private $SIMPLE_FLAG = 0;
	private $LUNAR_TERMS_DONE_FLAG = 0;
	private $RESONANCE_FLAG = 0;
	private $SYNCHRONOUS_FLAG = 0;
	private $DO_LOOP_FLAG = 0;
	private $EPOCH_RESTART_FLAG = 0;

	// Private variables

	private $io_lat = 'N';
	private $io_lon = 'W';
	private $rv;
	private $irk;
	private $isplat;
	private $isplong;
	private $iaz;
	private $iel;

	// Public variables

	public $sun_azi;
	public $sun_ele;

	private function reset_flags() {
		// Resets all flags

		$this -> SGP4_INITIALIZED_FLAG = 0;
		$this -> SDP4_INITIALIZED_FLAG = 0;
		$this -> SIMPLE_FLAG = 0;
		$this -> LUNAR_TERMS_DONE_FLAG = 0;
		$this -> RESONANCE_FLAG = 0;
		$this -> SYNCHRONOUS_FLAG = 0;
		$this -> DO_LOOP_FLAG = 0;
		$this -> EPOCH_RESTART_FLAG = 0;
	}

	private function AosHappens(&$tle, &$obs_geodetic) {
		// This function returns a 1 if the satellite pointed to by
		// tle can ever rise above the horizon of the ground station.

		if($tle -> meanmo == 0.0)
			return 0;
		else {
			$lin = $tle -> incl;

			if($lin >= 90.0)
				$lin = 180.0 - $lin;

			$sma = 331.25 * exp(log(1440.0 / $tle -> meanmo) * (2.0 / 3.0));
			$apogee = $sma * (1.0 + $tle -> eccn) - Globals::xkmper;

			if((acos(Globals::xkmper / ($apogee + Globals::xkmper)) + ($lin * Globals::deg2rad)) > abs($obs_geodetic -> lat))
				return 1;
			else
				return 0;
		}
	}

	private function Geostationary(&$tle) {
		// This function returns a 1 if the satellite
		// appears to be in a geostationary orbit

		if(abs($tle -> meanmo - 1.0027) < 0.0002)
			return 1;
		else
			return 0;
	}

	private function Decayed(&$tle, $time) {
		// This function returns a 1 if it appears that the
		// satellite pointed to by 'x' has decayed at the
		// time of 'time'.  If 'time' is 0.0, then the
		// current date/time is used.

		if($time == 0.0)
			$time = $this -> current_daynum();

		$satepoch = $this -> DayNum(1, 0, $tle -> year) + $tle -> refepoch;

		if($satepoch + ((16.666666 - $tle -> meanmo) / (10.0 * abs($tle -> drag))) < $time)
			return 1;

		return 0;
	}

	private function FindAOS(&$tle, &$obs_geodetic, &$sat_data, &$daynum) {
		// This function finds and returns the time of AOS (aostime).

		if($this -> AosHappens($tle, $obs_geodetic) && $this -> Geostationary($tle) == 0 && $this -> Decayed($tle, $daynum) == 0) {
			$this -> calc($tle, $obs_geodetic, $sat_data, $daynum);

			// Get the satellite in range

			while($sat_data -> ele < -1.0) {
				$daynum -= 0.00035 * ($sat_data -> ele * (($sat_data -> alt / 8400.0) + 0.46) - 2.0);
				$this -> calc($tle, $obs_geodetic, $sat_data, $daynum);
			}

			// Find AOS

			while(abs($sat_data -> ele) > 0.03) {
				$daynum -= $sat_data -> ele * sqrt($sat_data -> alt) / 530000.0;
				$this -> calc($tle, $obs_geodetic, $sat_data, $daynum);
			}
		}
	}

	private function NextAOS(&$tle, &$obs_geodetic, &$sat_data, &$daynum) {
		/* This function finds and returns the time of the next
		 AOS for a satellite that is currently in range. */

		if($this -> AosHappens($tle, $obs_geodetic) && $this -> Geostationary($tle) == 0 && $this -> Decayed($tle, $daynum) == 0)
			$this -> FindLOS2($tle, $obs_geodetic, $sat_data, $daynum);
		/* Move to LOS + 20 minutes */

		$daynum += 0.014;
		$this -> FindAOS($tle, $obs_geodetic, $sat_data, $daynum);
	}

	private function FindLOS(&$tle, &$obs_geodetic, &$sat_data, &$daynum) {
		if($this -> AosHappens($tle, $obs_geodetic) && $this -> Geostationary($tle) == 0 && $this -> Decayed($tle, $daynum) == 0) {
			$this -> calc($tle, $obs_geodetic, $sat_data, $daynum);

			do {
				$daynum += $sat_data -> ele * sqrt($sat_data -> alt) / 502500.0;
				$this -> calc($tle, $obs_geodetic, $sat_data, $daynum);

			} while (abs($sat_data->ele) > 0.03);
		}
	}

	private function FindLOS2(&$tle, &$obs_geodetic, &$sat_data, &$daynum) {
		/* This function steps through the pass to find LOS.
		 FindLOS() is called to "fine tune" and return the result. */

		do {
			$daynum += cos(($sat_data -> ele - 1.0) * Globals::deg2rad) * sqrt($sat_data -> alt) / 25000.0;
			$this -> calc($tle, $obs_geodetic, $sat_data, $daynum);

		} while ($sat_data->ele>=0.0);

		$this -> FindLOS($tle, $obs_geodetic, $sat_data, $daynum);
	}

	private function modf($x, &$ipart) {
		// Equivalent to the C modf function

		$ipart = (int)$x;
		return ($x - $ipart);
	}

	public function current_daynum() {
		// Gets the current decimal day number from microtime

		list($usec, $sec) = explode(' ', microtime());
		return ((($sec + 0.0000001 * $usec) / 86400.0) - 3651.0);
	}

	public function unix2daynum($ts, $usec=0) {
		// Converts a UNIX timestamp to a decimal day number

		return ((($ts + 0.0000001 * $usec) / 86400.0) - 3651.0);
	}

	public function daynum2unix($dn) {
		// Converts a daynum to a UNIX timestamp

		return (86400.0 * ($dn + 3651.0));
	}

	public function DayNum($m, $d, $y) {
		// This function calculates the day number from m/d/y.

		// long dn;
		// double mm, yy;

		if($m < 3) {
			$y--;
			$m += 12;
		}

		if($y < 57)
			$y += 100;

		$yy = $y;
		$mm = $m;
		$dn = (int)(floor(365.25 * ($yy - 80.0)) - floor(19.0 + $yy / 100.0) + floor(4.75 + $yy / 400.0) - 16.0);
		$dn += (int)$d + 30 * $m + floor(0.6 * $mm - 0.3);
		return $dn;
	}

	private function Julian_Date_of_Year($year) {
		// The function Julian_Date_of_Year calculates the Julian Date
		// of Day 0.0 of {year}. This function is used to calculate the
		// Julian Date of any date by using Julian_Date_of_Year, DOY, and Fraction_of_Day.

		// Astronomical Formulae for Calculators, Jean Meeus,
		// pages 23-25. Calculate Julian Date of 0.0 Jan year

		// long A, B, i;
		// double jdoy;

		$year = $year - 1;
		$i = ($year / 100);
		$A = $i;
		$i = ($A / 4);
		$B = (2 - $A) + $i;
		$i = (365.25 * $year);
		$i = $i + (30.6001 * 14);
		$jdoy = ((int)$i) + 1720994.5 + ((int)$B);

		return $jdoy;
	}

	public function Julian_Date_of_Epoch($epoch) {
		// The function Julian_Date_of_Epoch returns the Julian Date of
		// an epoch specified in the format used in the NORAD two-line
		// element sets. It has been modified to support dates beyond
		// the year 1999 assuming that two-digit years in the range 00-56
		// correspond to 2000-2056. Until the two-line element set format
		// is changed, it is only valid for dates through 2056 December 31.

		// Modification to support Y2K (Valid 1957 through 2056)

		$year = 0;
		$day = $this -> modf($epoch * 1E-3, $year) * 1E3;

		if($year < 57)
			$year = $year + 2000;
		else
			$year = $year + 1900;

		return ($this -> Julian_Date_of_Year($year) + $day);
	}

	private function AcTan($sinx, $cosx) {
		// Four-quadrant arctan function

		if($cosx == 0.0) {
			if($sinx > 0.0)
				return (Globals::pio2);
			else
				return (Globals::x3pio2);
		} else {
			if($cosx > 0.0) {
				if($sinx > 0.0)
					return (atan($sinx / $cosx));
				else
					return (Globals::twopi + atan($sinx / $cosx));
			} else
				return (Globals::pi + atan($sinx / $cosx));
		}
	}

	private function ArcSin($arg) {
		// Returns the arcsine of the argument

		if(abs($arg) >= 1.0)
			return ($this -> Sign($arg) * Globals::pio2);
		else
			return (atan($arg / sqrt(1.0 - $arg * $arg)));
	}

	private function Sign($arg) {
		// Returns sign of a double

		if($arg > 0)
			return 1;
		else if($arg < 0)
			return -1;
		else
			return 0;
	}

	private function Dot(&$v1, &$v2) {
		// Returns the dot product of two vectors

		return ($v1 -> x * $v2 -> x + $v1 -> y * $v2 -> y + $v1 -> z * $v2 -> z);
	}

	private function Vec_Sub(&$v1, &$v2, &$v3) {
		// Subtracts vector v2 from v1 to produce v3

		$v3 -> x = $v1 -> x - $v2 -> x;
		$v3 -> y = $v1 -> y - $v2 -> y;
		$v3 -> z = $v1 -> z - $v2 -> z;
		$this -> Magnitude($v3);
	}

	private function Scalar_Multiply($k, &$v1, &$v2) {
		// Multiplies the vector v1 by the scalar k to produce the vector v2

		$v2 -> x = $k * $v1 -> x;
		$v2 -> y = $k * $v1 -> y;
		$v2 -> z = $k * $v1 -> z;
		$v2 -> w = abs($k) * $v1 -> w;
	}

	private function Angle(&$v1, &$v2) {
		// Calculates the angle between vectors v1 and v2

		$this -> Magnitude($v1);
		$this -> Magnitude($v2);
		return ($this -> ArcCos($this -> Dot($v1, $v2) / ($v1 -> w * $v2 -> w)));
	}

	private function ArcCos($arg) {
		// Returns arccosine of argument

		return (Globals::pio2 - $this -> ArcSin($arg));
	}

	private function FMod2p($x) {
		// Returns mod 2PI of argument

		$ret_val = $x;
		$i = (int)$ret_val / Globals::twopi;
		$ret_val -= (int)$i * Globals::twopi;

		if($ret_val < 0.0)
			$ret_val += Globals::twopi;

		return $ret_val;
	}

	public function Convert_Sat_State(&$pos, &$vel) {
		// Converts the satellite's position and velocity
		// vectors from normalized values to km and km/sec

		$this -> Scale_Vector(Globals::xkmper, $pos);
		$this -> Scale_Vector(Globals::xkmper * Globals::xmnpda / Globals::secday, $vel);
	}

	private function Scale_Vector($k, &$v) {
		// Multiplies the vector v1 by the scalar k

		$v -> x *= $k;
		$v -> y *= $k;
		$v -> z *= $k;
		$this -> Magnitude($v);
	}

	private function Magnitude(&$v) {
		// Calculates scalar magnitude of a vector argument

		$v -> w = sqrt($this -> Sqr($v -> x) + $this -> Sqr($v -> y) + $this -> Sqr($v -> z));
	}

	private function Sqr($arg) {
		// Returns square of a double

		return ($arg * $arg);
	}

	private function Frac($arg) {
		// Returns fractional part of double argument

		return ($arg - (int)$arg);
	}

	private function Modulus($arg1, $arg2) {
		// Returns arg1 mod arg2

		$ret_val = $arg1;
		$i = (int)($arg1 / $arg2);
		$ret_val -= (int)($i * $arg2);

		if($ret_val < 0.0)
			$ret_val += $arg2;

		return $ret_val;
	}

	private function FixAngle($x) {
		// This function reduces angles greater than
		// two pi by subtracting two pi from the angle

		while($x > Globals::twopi)
			$x -= Globals::twopi;

		return $x;
	}

	private function PrimeAngle($x) {
		// This function is used in the FindMoon() function.

		$x = $x - 360.0 * floor($x / 360.0);
		return $x;
	}

	private function Sat_Eclipsed(&$pos, &$sol, &$depth) {
		// Calculates stellite's eclipse status and depth

		$Rho = new vector();
		$earth = new vector();

		// Determine partial eclipse

		$sd_earth = $this -> ArcSin(Globals::xkmper / $pos -> w);
		$this -> Vec_Sub($sol, $pos, $Rho);
		$sd_sun = $this -> ArcSin(Globals::sr / $Rho -> w);
		$this -> Scalar_Multiply(-1, $pos, $earth);
		$delta = $this -> Angle($sol, $earth);
		$depth = $sd_earth - $sd_sun - $delta;

		if($sd_earth < $sd_sun)
			return 0;
		else if($depth >= 0)
			return 1;
		else
			return 0;
	}

	private function ThetaG($epoch, &$deep_arg_ds50) {
		// The function ThetaG calculates the Greenwich Mean Sidereal Time
		// for an epoch specified in the format used in the NORAD two-line
		// element sets. It has now been adapted for dates beyond the year
		// 1999, as described above. The function ThetaG_JD provides the
		// same calculation except that it is based on an input in the
		// form of a Julian Date.

		// Reference: The 1992 Astronomical Almanac, page B6.

		// Modification to support Y2K (valid 1957 through 2056)

		$year = 0;
		$day = $this -> modf($epoch * 1E-3, $year) * 1E3;

		if($year < 57)
			$year += 2000;
		else
			$year += 1900;

		$UT = $this -> modf($day, $day);
		$jd = $this -> Julian_Date_of_Year($year) + $day;
		$TU = ($jd - 2451545.0) / 36525;
		$GMST = 24110.54841 + $TU * (8640184.812866 + $TU * (0.093104 - $TU * 6.2E-6));
		$GMST = $this -> Modulus($GMST + Globals::secday * Globals::omega_E * $UT, Globals::secday);
		$deep_arg_ds50 = $jd - 2433281.5 + $UT;
		$ThetaG = $this -> FMod2p(6.3003880987 * $deep_arg_ds50 + 1.72944494);

		return $ThetaG;
	}

	private function ThetaG_JD($jd) {
		// Reference: The 1992 Astronomical Almanac, page B6.

		$UT = $this -> Frac($jd + 0.5);
		$jd = $jd - $UT;
		$TU = ($jd - 2451545.0) / 36525;
		$GMST = 24110.54841 + $TU * (8640184.812866 + $TU * (0.093104 - $TU * 6.2E-6));
		$GMST = $this -> Modulus($GMST + Globals::secday * Globals::omega_E * $UT, Globals::secday);

		return (Globals::twopi * $GMST / Globals::secday);
	}

	private function Delta_ET($year) {
		// The function Delta_ET has been added to allow calculations on
		// the position of the sun.  It provides the difference between UT
		// (approximately the same as UTC) and ET (now referred to as TDT).
		// This function is based on a least squares fit of data from 1950
		// to 1991 and will need to be updated periodically.

		// Values determined using data from 1950-1991 in the 1990
		// Astronomical Almanac. See DELTA_ET.WQ1 for details.

		return (26.465 + 0.747622 * ($year - 1950) + 1.886913 * sin(Globals::twopi * ($year - 1975) / 33));
	}

	private function Calculate_Solar_Position($time, &$solar_vector) {
		// Calculates solar position vector

		$mjd = $time - 2415020.0;
		$year = 1900 + $mjd / 365.25;
		$T = ($mjd + $this -> Delta_ET($year) / Globals::secday) / 36525.0;
		$M = (($this -> Modulus(358.47583 + $this -> Modulus(35999.04975 * $T, 360.0) - (0.000150 + 0.0000033 * $T) * $this -> Sqr($T), 360.0)) * Globals::deg2rad);
		$L = (($this -> Modulus(279.69668 + $this -> Modulus(36000.76892 * $T, 360.0) + 0.0003025 * $this -> Sqr($T), 360.0)) * Globals::deg2rad);
		$e = 0.01675104 - (0.0000418 + 0.000000126 * $T) * $T;
		$C = (((1.919460 - (0.004789 + 0.000014 * $T) * $T) * sin($M) + (0.020094 - 0.000100 * $T) * sin(2 * $M) + 0.000293 * sin(3 * $M)) * Globals::deg2rad);
		$O = (($this -> Modulus(259.18 - 1934.142 * $T, 360.0)) * Globals::deg2rad);
		$Lsa = $this -> Modulus($L + $C - ((0.00569 - 0.00479 * sin($O)) * Globals::deg2rad), Globals::twopi);
		$nu = $this -> Modulus($M + $C, Globals::twopi);
		$R = 1.0000002 * (1.0 - $this -> Sqr($e)) / (1.0 + $e * cos($nu));
		$eps = ((23.452294 - (0.0130125 + (0.00000164 - 0.000000503 * $T) * $T) * $T + 0.00256 * cos($O)) * Globals::deg2rad);
		$R = Globals::AU * $R;

		$solar_vector -> x = $R * cos($Lsa);
		$solar_vector -> y = $R * sin($Lsa) * cos($eps);
		$solar_vector -> z = $R * sin($Lsa) * sin($eps);
		$solar_vector -> w = $R;
	}

	private function Calculate_User_PosVel($time, &$geodetic, &$obs_pos, &$obs_vel) {
		// Calculate_User_PosVel() passes the user's geodetic position
		// and the time of interest and returns the ECI position and
		// velocity of the observer.  The velocity calculation assumes
		// the geodetic position is stationary relative to the earth's surface.

		// Reference: The 1992 Astronomical Almanac, page K11.
		$geodetic->lon = -$geodetic->lon;
		$geodetic -> theta = $this -> FMod2p($this -> ThetaG_JD($time) + $geodetic -> lon);
		// LMST
		$c = 1 / sqrt(1 + Globals::f * (Globals::f - 2) * $this -> Sqr(sin($geodetic -> lat)));
		$sq = $this -> Sqr(1 - Globals::f) * $c;
		$achcp = (Globals::xkmper * $c + $geodetic -> alt) * cos($geodetic -> lat);

		$obs_pos -> x = $achcp * cos($geodetic -> theta);
		// kilometers
		$obs_pos -> y = $achcp * sin($geodetic -> theta);
		$obs_pos -> z = (Globals::xkmper * $sq + $geodetic -> alt) * sin($geodetic -> lat);

		$obs_vel -> x = -Globals::mfactor * $obs_pos -> y;
		// kilometers/second
		$obs_vel -> y = Globals::mfactor * $obs_pos -> x;
		$obs_vel -> z = 0;

		$this -> Magnitude($obs_pos);
		$this -> Magnitude($obs_vel);
		$geodetic->lon = -$geodetic->lon;
	}

	private function Calculate_LatLonAlt($time, &$pos, &$geodetic) {
		// Procedure Calculate_LatLonAlt will calculate the geodetic
		// position of an object given its ECI position pos and time.
		// It is intended to be used to determine the ground track of
		// a satellite.  The calculations  assume the earth to be an
		// oblate spheroid as defined in WGS '72.

		// Reference: The 1992 Astronomical Almanac, page K12.

		$geodetic -> theta = $this -> AcTan($pos -> y, $pos -> x);
		// radians
		$geodetic -> lon = $this -> FMod2p($geodetic -> theta - $this -> ThetaG_JD($time));
		// radians
		$r = sqrt($this -> Sqr($pos -> x) + $this -> Sqr($pos -> y));
		$e2 = Globals::f * (2 - Globals::f);
		$geodetic -> lat = $this -> AcTan($pos -> z, $r);
		// radians

		do {
			$c = 1 / sqrt(1 - $e2 * $this -> Sqr(sin($geodetic -> lat)));
			$geodetic -> lat = $this -> AcTan($pos -> z + Globals::xkmper * $c * $e2 * sin($geodetic -> lat), $r);

		} while (abs($geodetic->lat-$geodetic->lat)>=1E-10);

		$geodetic -> alt = $r / cos($geodetic -> lat) - Globals::xkmper * $c;
		// kilometers

		if($geodetic -> lat > Globals::pio2)
			$geodetic -> lat -= Globals::twopi;
	}

	public function Calculate_Obs($time, &$pos, &$vel, &$geodetic, &$obs_set) {
		// The procedures Calculate_Obs and Calculate_RADec calculate
		// the *topocentric* coordinates of the object with ECI position,
		// {pos}, and velocity, {vel}, from location {geodetic} at {time}.
		// The {obs_set} returned for Calculate_Obs consists of azimuth,
		// elevation, range, and range rate (in that order) with units of
		// radians, radians, kilometers, and kilometers/second, respectively.
		// The WGS '72 geoid is used and the effect of atmospheric refraction
		// (under standard temperature and pressure) is incorporated into the
		// elevation calculation; the effect of atmospheric refraction on
		// range and range rate has not yet been quantified.

		// The {obs_set} for Calculate_RADec consists of right ascension and
		// declination (in that order) in radians.  Again, calculations are
		// based on *topocentric* position using the WGS '72 geoid and
		// incorporating atmospheric refraction.

		$obs_pos = new vector();
		$obs_vel = new vector();
		$range = new vector();
		$rgvel = new vector();

		$this -> Calculate_User_PosVel($time, $geodetic, $obs_pos, $obs_vel);

		$range -> x = $pos -> x - $obs_pos -> x;
		$range -> y = $pos -> y - $obs_pos -> y;
		$range -> z = $pos -> z - $obs_pos -> z;

		$rgvel -> x = $vel -> x - $obs_vel -> x;
		$rgvel -> y = $vel -> y - $obs_vel -> y;
		$rgvel -> z = $vel -> z - $obs_vel -> z;

		$this -> Magnitude($range);

		$sin_lat = sin($geodetic -> lat);
		$cos_lat = cos($geodetic -> lat);
		$sin_theta = sin($geodetic -> theta);
		$cos_theta = cos($geodetic -> theta);
		$top_s = $sin_lat * $cos_theta * $range -> x + $sin_lat * $sin_theta * $range -> y - $cos_lat * $range -> z;
		$top_e = -$sin_theta * $range -> x + $cos_theta * $range -> y;
		$top_z = $cos_lat * $cos_theta * $range -> x + $cos_lat * $sin_theta * $range -> y + $sin_lat * $range -> z;
		$azim = atan(-($top_e / $top_s));
		// Azimuth

		if($top_s > 0.0)
			$azim = $azim + Globals::pi;

		if($azim < 0.0)
			$azim = $azim + Globals::twopi;

		$el = $this -> ArcSin($top_z / $range -> w);
		$obs_set -> x = $azim;
		// Azimuth (radians)
		$obs_set -> y = $el;
		// Elevation (radians)
		$obs_set -> z = $range -> w;
		// Range (kilometers)

		// Range Rate (kilometers/second)

		$obs_set -> w = ($this -> Dot($range, $rgvel) / $range -> w);

		// Corrections for atmospheric refraction
		// Reference:  Astronomical Algorithms by Jean Meeus, pp. 101-104
		// Correction is meaningless when apparent elevation is below horizon

		$obs_set -> y = $el;

		if($obs_set -> y >= 0.0) {

		} else {
			$obs_set -> y = $el;
			/* Reset to true elevation */
		}
	}

	public function Calculate_RADec($time, &$pos, &$vel, &$geodetic, &$obs_set) {
		// Reference:  Methods of Orbit Determination by
		// Pedro Ramon Escobal, pp. 401-402

		$this -> Calculate_Obs($time, $pos, $vel, $geodetic, $obs_set);

		$az = $obs_set -> x;
		$el = $obs_set -> y;
		$phi = $geodetic -> lat;
		$theta = $this -> FMod2p($this -> ThetaG_JD($time) + $geodetic -> lon);
		$sin_theta = sin($theta);
		$cos_theta = cos($theta);
		$sin_phi = sin($phi);
		$cos_phi = cos($phi);
		$Lxh = -cos($az) * cos($el);
		$Lyh = sin($az) * cos($el);
		$Lzh = sin($el);
		$Sx = $sin_phi * $cos_theta;
		$Ex = -$sin_theta;
		$Zx = $cos_theta * $cos_phi;
		$Sy = $sin_phi * $sin_theta;
		$Ey = $cos_theta;
		$Zy = $sin_theta * $cos_phi;
		$Sz = -$cos_phi;
		$Ez = 0.0;
		$Zz = $sin_phi;
		$Lx = $Sx * $Lxh + $Ex * $Lyh + $Zx * $Lzh;
		$Ly = $Sy * $Lxh + $Ey * $Lyh + $Zy * $Lzh;
		$Lz = $Sz * $Lxh + $Ez * $Lyh + $Zz * $Lzh;
		$obs_set -> y = $this -> ArcSin($Lz);
		// Declination (radians)
		$cos_delta = sqrt(1.0 - $this -> Sqr($Lz));
		$sin_alpha = $Ly / $cos_delta;
		$cos_alpha = $Lx / $cos_delta;
		$obs_set -> x = $this -> AcTan($sin_alpha, $cos_alpha);
		// Right Ascension (radians)
		$obs_set -> x = $this -> FMod2p($obs_set -> x);
	}

	public function FindSun($daynum, &$obs_geodetic, &$sun_pos) {
		// This function finds the position of the Sun

		// Convert the observers geodetic position data into radians

		$obs_geodetic -> lat *= Globals::deg2rad;
		$obs_geodetic -> lon *= -Globals::deg2rad;
		$obs_geodetic -> alt /= 1000.0;

		// Zero vector for initializations

		$zero_vector = new vector();

		// Solar ECI position vector

		$solar_vector = new vector();

		// Solar observed azi and ele vectors

		$solar_set = new vector();
		$solar_rad = new vector();

		// Satellite's predicted geodetic position

		$solar_latlonalt = new geodetic(0, 0, 0);

		$jul_utc = $daynum + 2444238.5;

		$this -> Calculate_Solar_Position($jul_utc, $solar_vector);
		$this -> Calculate_Obs($jul_utc, $solar_vector, $zero_vector, $obs_geodetic, $solar_set);

		$sun_azi = $solar_set -> x / Globals::deg2rad;
		$sun_ele = $solar_set -> y / Globals::deg2rad;
		$sun_range = 1.0 + (($solar_set -> z - Globals::AU) / Globals::AU);
		$sun_range_rate = 1000.0 * $solar_set -> w;

		$this -> Calculate_LatLonAlt($jul_utc, $solar_vector, $solar_latlonalt);

		$sun_lat = $solar_latlonalt -> lat / Globals::deg2rad;
		$sun_lon = 360.0 - ($solar_latlonalt -> lon / Globals::deg2rad);

		$this -> Calculate_RADec($jul_utc, $solar_vector, $zero_vector, $obs_geodetic, $solar_rad);

		$sun_ra = $solar_rad -> x / Globals::deg2rad;
		$sun_dec = $solar_rad -> y / Globals::deg2rad;

		$sun_pos = new sun();
		$sun_pos -> azi = $sun_azi;
		$sun_pos -> ele = $sun_ele;
		$sun_pos -> range = $sun_range;
		$sun_pos -> range_rate = $sun_range_rate;
		$sun_pos -> lat = $sun_lat;
		$sun_pos -> lon = $sun_lon;
		$sun_pos -> ra = $sun_ra;
		$sun_pos -> dec = $sun_dec;

		// Convert the observers geodetic position data back into degrees

		$obs_geodetic -> lat /= Globals::deg2rad;
		$obs_geodetic -> lon /= -Globals::deg2rad;
		$obs_geodetic -> alt *= 1000.0;
	}

	public function FindMoon($daynum, &$obs_geodetic, &$moon_pos) {
		// This function determines the position of the moon, including
		// the azimuth and elevation headings, relative to the latitude
		// and longitude of the tracking station.  This code was derived
		// from a Javascript implementation of the Meeus method for
		// determining the exact position of the Moon found at:
		// http://www.geocities.com/s_perona/ingles/poslun.htm.

		$jd = $daynum + 2444238.5;

		$t = ($jd - 2415020.0) / 36525.0;
		$t2 = $t * $t;
		$t3 = $t2 * $t;
		$l1 = 270.434164 + 481267.8831 * $t - 0.001133 * $t2 + 0.0000019 * $t3;
		$m = 358.475833 + 35999.0498 * $t - 0.00015 * $t2 - 0.0000033 * $t3;
		$m1 = 296.104608 + 477198.8491 * $t + 0.009192 * $t2 + 0.0000144 * $t3;
		$d = 350.737486 + 445267.1142 * $t - 0.001436 * $t2 + 0.0000019 * $t3;
		$ff = 11.250889 + 483202.0251 * $t - 0.003211 * $t2 - 0.0000003 * $t3;
		$om = 259.183275 - 1934.142 * $t + 0.002078 * $t2 + 0.0000022 * $t3;
		$om = $om * Globals::deg2rad;

		// Additive terms

		$l1 = $l1 + 0.000233 * sin((51.2 + 20.2 * $t) * Globals::deg2rad);
		$ss = 0.003964 * sin((346.56 + 132.87 * $t - 0.0091731 * $t2) * Globals::deg2rad);
		$l1 = $l1 + $ss + 0.001964 * sin($om);
		$m = $m - 0.001778 * sin((51.2 + 20.2 * $t) * Globals::deg2rad);
		$m1 = $m1 + 0.000817 * sin((51.2 + 20.2 * $t) * Globals::deg2rad);
		$m1 = $m1 + $ss + 0.002541 * sin($om);
		$d = $d + 0.002011 * sin((51.2 + 20.2 * $t) * Globals::deg2rad);
		$d = $d + $ss + 0.001964 * sin($om);
		$ff = $ff + $ss - 0.024691 * sin($om);
		$ff = $ff - 0.004328 * sin($om + (275.05 - 2.3 * $t) * Globals::deg2rad);
		$ex = 1.0 - 0.002495 * $t - 0.00000752 * $t2;
		$om = $om * Globals::deg2rad;

		$l1 = $this -> PrimeAngle($l1);
		$m = $this -> PrimeAngle($m);
		$m1 = $this -> PrimeAngle($m1);
		$d = $this -> PrimeAngle($d);
		$ff = $this -> PrimeAngle($ff);
		$om = $this -> PrimeAngle($om);

		$m = $m * Globals::deg2rad;
		$m1 = $m1 * Globals::deg2rad;
		$d = $d * Globals::deg2rad;
		$ff = $ff * Globals::deg2rad;

		// Ecliptic Longitude

		$l = $l1 + 6.28875 * sin($m1) + 1.274018 * sin(2.0 * $d - $m1) + 0.658309 * sin(2.0 * $d);
		$l = $l + 0.213616 * sin(2.0 * $m1) - $ex * 0.185596 * sin($m) - 0.114336 * sin(2.0 * $ff);
		$l = $l + 0.058793 * sin(2.0 * $d - 2.0 * $m1) + $ex * 0.057212 * sin(2.0 * $d - $m - $m1) + 0.05332 * sin(2.0 * $d + $m1);
		$l = $l + $ex * 0.045874 * sin(2.0 * $d - $m) + $ex * 0.041024 * sin($m1 - $m) - 0.034718 * sin($d);
		$l = $l - $ex * 0.030465 * sin($m + $m1) + 0.015326 * sin(2.0 * $d - 2.0 * $ff) - 0.012528 * sin(2.0 * $ff + $m1);

		$l = $l - 0.01098 * sin(2.0 * $ff - $m1) + 0.010674 * sin(4.0 * $d - $m1) + 0.010034 * sin(3.0 * $m1);
		$l = $l + 0.008548 * sin(4.0 * $d - 2.0 * $m1) - $ex * 0.00791 * sin($m - $m1 + 2.0 * $d) - $ex * 0.006783 * sin(2.0 * $d + $m);

		$l = $l + 0.005162 * sin($m1 - $d) + $ex * 0.005 * sin($m + $d) + $ex * 0.004049 * sin($m1 - $m + 2.0 * $d);
		$l = $l + 0.003996 * sin(2.0 * $m1 + 2.0 * $d) + 0.003862 * sin(4.0 * $d) + 0.003665 * sin(2.0 * $d - 3.0 * $m1);

		$l = $l + $ex * 0.002695 * sin(2.0 * $m1 - $m) + 0.002602 * sin($m1 - 2.0 * $ff - 2.0 * $d) + $ex * 0.002396 * sin(2.0 * $d - $m - 2.0 * $m1);

		$l = $l - 0.002349 * sin($m1 + $d) + $ex * $ex * 0.002249 * sin(2.0 * $d - 2.0 * $m) - $ex * 0.002125 * sin(2.0 * $m1 + $m);

		$l = $l - $ex * $ex * 0.002079 * sin(2.0 * $m) + $ex * $ex * 0.002059 * sin(2.0 * $d - $m1 - 2.0 * $m) - 0.001773 * sin($m1 + 2.0 * $d - 2.0 * $ff);

		$l = $l + $ex * 0.00122 * sin(4.0 * $d - $m - $m1) - 0.00111 * sin(2.0 * $m1 + 2.0 * $ff) + 0.000892 * sin($m1 - 3.0 * $d);

		$l = $l - $ex * 0.000811 * sin($m + $m1 + 2.0 * $d) + $ex * 0.000761 * sin(4.0 * $d - $m - 2.0 * $m1) + $ex * $ex * .000717 * sin($m1 - 2.0 * $m);

		$l = $l + $ex * $ex * 0.000704 * sin($m1 - 2.0 * $m - 2.0 * $d) + $ex * 0.000693 * sin($m - 2.0 * $m1 + 2.0 * $d) + $ex * 0.000598 * sin(2.0 * $d - $m - 2.0 * $ff) + 0.00055 * sin($m1 + 4.0 * $d);

		$l = $l + 0.000538 * sin(4.0 * $m1) + $ex * 0.000521 * sin(4.0 * $d - $m) + 0.000486 * sin(2.0 * $m1 - $d);

		$l = $l - 0.001595 * sin(2.0 * $ff + 2.0 * $d);

		// Ecli$ptic latitude

		$b = 5.128189 * sin($ff) + 0.280606 * sin($m1 + $ff) + 0.277693 * sin($m1 - $ff) + 0.173238 * sin(2.0 * $d - $ff);
		$b = $b + 0.055413 * sin(2.0 * $d + $ff - $m1) + 0.046272 * sin(2.0 * $d - $ff - $m1) + 0.032573 * sin(2.0 * $d + $ff);

		$b = $b + 0.017198 * sin(2.0 * $m1 + $ff) + 9.266999e-03 * sin(2.0 * $d + $m1 - $ff) + 0.008823 * sin(2.0 * $m1 - $ff);
		$b = $b + $ex * 0.008247 * sin(2.0 * $d - $m - $ff) + 0.004323 * sin(2.0 * $d - $ff - 2.0 * $m1) + 0.0042 * sin(2.0 * $d + $ff + $m1);

		$b = $b + $ex * 0.003372 * sin($ff - $m - 2.0 * $d) + $ex * 0.002472 * sin(2.0 * $d + $ff - $m - $m1) + $ex * 0.002222 * sin(2.0 * $d + $ff - $m);

		$b = $b + 0.002072 * sin(2.0 * $d - $ff - $m - $m1) + $ex * 0.001877 * sin($ff - $m + $m1) + 0.001828 * sin(4.0 * $d - $ff - $m1);

		$b = $b - $ex * 0.001803 * sin($ff + $m) - 0.00175 * sin(3.0 * $ff) + $ex * 0.00157 * sin($m1 - $m - $ff) - 0.001487 * sin($ff + $d) - $ex * 0.001481 * sin($ff + $m + $m1) + $ex * 0.001417 * sin($ff - $m - $m1) + $ex * 0.00135 * sin($ff - $m) + 0.00133 * sin($ff - $d);

		$b = $b + 0.001106 * sin($ff + 3.0 * $m1) + 0.00102 * sin(4.0 * $d - $ff) + 0.000833 * sin($ff + 4.0 * $d - $m1);

		$b = $b + 0.000781 * sin($m1 - 3.0 * $ff) + 0.00067 * sin($ff + 4.0 * $d - 2.0 * $m1) + 0.000606 * sin(2.0 * $d - 3.0 * $ff);

		$b = $b + 0.000597 * sin(2.0 * $d + 2.0 * $m1 - $ff) + $ex * 0.000492 * sin(2.0 * $d + $m1 - $m - $ff) + 0.00045 * sin(2.0 * $m1 - $ff - 2.0 * $d);

		$b = $b + 0.000439 * sin(3.0 * $m1 - $ff) + 0.000423 * sin($ff + 2.0 * $d + 2.0 * $m1) + 0.000422 * sin(2.0 * $d - $ff - 3.0 * $m1);

		$b = $b - $ex * 0.000367 * sin($m + $ff + 2.0 * $d - $m1) - $ex * 0.000353 * sin($m + $ff + 2.0 * $d) + 0.000331 * sin($ff + 4.0 * $d);

		$b = $b + $ex * 0.000317 * sin(2.0 * $d + $ff - $m + $m1) + $ex * $ex * 0.000306 * sin(2.0 * $d - 2.0 * $m - $ff) - 0.000283 * sin($m1 + 3.0 * $ff);

		$w1 = 0.0004664 * cos($om * Globals::deg2rad);
		$w2 = 0.0000754 * cos(($om + 275.05 - 2.3 * $t) * Globals::deg2rad);
		$bt = $b * (1.0 - $w1 - $w2);

		// parallax calculations

		$p = 0.950724 + 0.051818 * cos($m1) + 0.009531 * cos(2.0 * $d - $m1) + 0.007843 * cos(2.0 * $d) + 0.002824 * cos(2.0 * $m1) + 0.000857 * cos(2.0 * $d + $m1) + $ex * 0.000533 * cos(2.0 * $d - $m) + $ex * 0.000401 * cos(2.0 * $d - $m - $m1);

		$p = $p + 0.000173 * cos(3.0 * $m1) + 0.000167 * cos(4.0 * $d - $m1) - $ex * 0.000111 * cos($m) + 0.000103 * cos(4.0 * $d - 2.0 * $m1) - 0.000084 * cos(2.0 * $m1 - 2.0 * $d) - $ex * 0.000083 * cos(2.0 * $d + $m) + 0.000079 * cos(2.0 * $d + 2.0 * $m1);

		$p = $p + 0.000072 * cos(4.0 * $d) + $ex * 0.000064 * cos(2.0 * $d - $m + $m1) - $ex * 0.000063 * cos(2.0 * $d + $m - $m1);

		$p = $p + $ex * 0.000041 * cos($m + $d) + $ex * 0.000035 * cos(2.0 * $m1 - $m) - 0.000033 * cos(3.0 * $m1 - 2.0 * $d);

		$p = $p - 0.00003 * cos($m1 + $d) - 0.000029 * cos(2.0 * $ff - 2.0 * $d) - $ex * 0.000029 * cos(2.0 * $m1 + $m);

		$p = $p + $ex * $ex * 0.000026 * cos(2.0 * $d - 2.0 * $m) - 0.000023 * cos(2.0 * $ff - 2.0 * $d + $m1) + $ex * 0.000019 * cos(4.0 * $d - $m - $m1);

		$b = $bt * Globals::deg2rad;
		$lm = $l * Globals::deg2rad;
		$moon_dx = 3.0 / (Globals::pi * $p);

		// Convert ecliptic coordinates to equatorial coordinates

		$z = ($jd - 2415020.5) / 365.2422;
		$ob = 23.452294 - (0.46845 * $z + 5.9e-07 * $z * $z) / 3600.0;
		$ob = $ob * Globals::deg2rad;
		$dec = asin(sin($b) * cos($ob) + cos($b) * sin($ob) * sin($lm));
		$ra = acos(cos($b) * cos($lm) / cos($dec));

		if($lm > Globals::pi)
			$ra = Globals::twopi - $ra;

		// ra = right ascension
		// dec = declination

		$n = $obs_geodetic -> lat * Globals::deg2rad;
		// North latitude of tracking station
		$e = -$obs_geodetic -> lon * Globals::deg2rad;
		// East longitude of tracking station

		// Find siderial time in radians

		$t = ($jd - 2451545.0) / 36525.0;
		$teg = 280.46061837 + 360.98564736629 * ($jd - 2451545.0) + (0.000387933 * $t - $t * $t / 38710000.0) * $t;

		while($teg > 360.0)
			$teg -= 360.0;

		$th = $this -> FixAngle(($teg - $obs_geodetic -> lon) * Globals::deg2rad);
		$h = $th - $ra;

		$az = atan2(sin($h), cos($h) * sin($n) - tan($dec) * cos($n)) + Globals::pi;
		$el = asin(sin($n) * sin($dec) + cos($n) * cos($dec) * cos($h));

		$moon_az = $az / Globals::deg2rad;
		$moon_el = $el / Globals::deg2rad;

		// Radial velocity approximation.  This code was derived
		// from "Amateur Radio Software", by John Morris, GM4ANB,
		// published by the RSGB in 1985.

		$mm = $this -> FixAngle(1.319238 + $daynum * 0.228027135);
		// mean moon position
		$t2 = 0.10976;
		$t1 = $mm + $t2 * sin($mm);
		$dv = 0.01255 * $moon_dx * $moon_dx * sin($t1) * (1.0 + $t2 * cos($mm));
		$dv = $dv * 4449.0;
		$t1 = 6378.0;
		$t2 = 384401.0;
		$t3 = $t1 * $t2 * (cos($dec) * cos($n) * sin($h));
		$t3 = $t3 / sqrt($t2 * $t2 - $t2 * $t1 * sin($el));
		$moon_dv = $dv + $t3 * 0.0753125;

		$moon_dec = $dec / Globals::deg2rad;
		$moon_ra = $ra / Globals::deg2rad;
		$moon_gha = $teg - $moon_ra;

		if($moon_gha < 0.0)
			$moon_gha += 360.0;

		$moon_pos = new moon();

		$moon_pos -> lat = 0;
		$moon_pos -> lon = 0;
		$moon_pos -> dv = $moon_dv;
		$moon_pos -> dec = $moon_dec;
		$moon_pos -> ra = $moon_ra;
		$moon_pos -> gha = $moon_gha;
		$moon_pos -> azi = $moon_az;
		$moon_pos -> ele = $moon_el;
	}

	public function SGP4($tsince, &$tle, &$pos, &$vel) {

		// This function is used to calculate the position and velocity
		// of near-earth (period < 225 minutes) satellites. tsince is
		// time since epoch in minutes, tle is a pointer to a tle
		// structure with Keplerian orbital elements and pos and vel
		// are vector structures returning ECI satellite position and
		// velocity. Use Convert_Sat_State() to convert to km and km/s.

		static $aodp;
		static $aycof;
		static $c1;
		static $c4;
		static $c5;
		static $cosio;
		static $d2;
		static $d3;
		static $d4;
		static $delmo;
		static $omgcof;
		static $eta;
		static $omgdot;
		static $sinio;
		static $xnodp;
		static $sinmo;
		static $t2cof;
		static $t3cof;
		static $t4cof;
		static $t5cof;
		static $x1mth2;
		static $x3thm1;
		static $x7thm1;
		static $xmcof;
		static $xmdot;
		static $xnodcf;
		static $xnodot;
		static $xlcof;

		// Initialization

		if(!$this -> SGP4_INITIALIZED_FLAG) {
			// Recover original mean motion (xnodp) and
			// semimajor axis (aodp) from input elements.

			$a1 = pow(Globals::xke / $tle -> xno, Globals::tothrd);
			$cosio = cos($tle -> xincl);
			$theta2 = $cosio * $cosio;
			$x3thm1 = 3 * $theta2 - 1.0;
			$eosq = $tle -> eo * $tle -> eo;
			$betao2 = 1.0 - $eosq;
			$betao = sqrt($betao2);
			$del1 = 1.5 * Globals::ck2 * $x3thm1 / ($a1 * $a1 * $betao * $betao2);
			$ao = $a1 * (1.0 - $del1 * (0.5 * Globals::tothrd + $del1 * (1.0 + 134.0 / 81.0 * $del1)));
			$delo = 1.5 * Globals::ck2 * $x3thm1 / ($ao * $ao * $betao * $betao2);
			$xnodp = $tle -> xno / (1.0 + $delo);
			$aodp = $ao / (1.0 - $delo);

			// For perigee less than 220 kilometers, the "simple"
			// flag is set and the equations are truncated to linear
			// variation in sqrt a and quadratic variation in mean
			// anomaly.  Also, the c3 term, the delta omega term, and
			// the delta m term are dropped.

			if(($aodp * (1 - $tle -> eo) / Globals::ae) < (220 / Globals::xkmper + Globals::ae))
				$this -> SIMPLE_FLAG = 1;
			else
				$this -> SIMPLE_FLAG = 0;

			// For perigees below 156 km, the
			// values of s and qoms2t are altered.

			$s4 = Globals::s;
			$qoms24 = Globals::qoms2t;
			$perigee = ($aodp * (1 - $tle -> eo) - Globals::ae) * Globals::xkmper;

			if($perigee < 156.0) {
				if($perigee <= 98.0)
					$s4 = 20;
				else
					$s4 = $perigee - 78.0;

				$qoms24 = pow((120 - $s4) * Globals::ae / Globals::xkmper, 4);
				$s4 = $s4 / Globals::xkmper + Globals::ae;
			}

			$pinvsq = 1 / ($aodp * $aodp * $betao2 * $betao2);
			$tsi = 1 / ($aodp - $s4);
			$eta = $aodp * $tle -> eo * $tsi;
			$etasq = $eta * $eta;
			$eeta = $tle -> eo * $eta;
			$psisq = abs(1 - $etasq);
			$coef = $qoms24 * pow($tsi, 4);
			$coef1 = $coef / pow($psisq, 3.5);
			$c2 = $coef1 * $xnodp * ($aodp * (1 + 1.5 * $etasq + $eeta * (4 + $etasq)) + 0.75 * Globals::ck2 * $tsi / $psisq * $x3thm1 * (8 + 3 * $etasq * (8 + $etasq)));
			$c1 = $tle -> bstar * $c2;
			$sinio = sin($tle -> xincl);
			$a3ovk2 = -Globals::xj3 / Globals::ck2 * pow(Globals::ae, 3);
			$c3 = $coef * $tsi * $a3ovk2 * $xnodp * Globals::ae * $sinio / $tle -> eo;
			$x1mth2 = 1 - $theta2;

			$c4 = 2 * $xnodp * $coef1 * $aodp * $betao2 * ($eta * (2 + 0.5 * $etasq) + $tle -> eo * (0.5 + 2 * $etasq) - 2 * Globals::ck2 * $tsi / ($aodp * $psisq) * (-3 * $x3thm1 * (1 - 2 * $eeta + $etasq * (1.5 - 0.5 * $eeta)) + 0.75 * $x1mth2 * (2 * $etasq - $eeta * (1 + $etasq)) * cos(2 * $tle -> omegao)));
			$c5 = 2 * $coef1 * $aodp * $betao2 * (1 + 2.75 * ($etasq + $eeta) + $eeta * $etasq);

			$theta4 = $theta2 * $theta2;
			$temp1 = 3 * Globals::ck2 * $pinvsq * $xnodp;
			$temp2 = $temp1 * Globals::ck2 * $pinvsq;
			$temp3 = 1.25 * Globals::ck4 * $pinvsq * $pinvsq * $xnodp;
			$xmdot = $xnodp + 0.5 * $temp1 * $betao * $x3thm1 + 0.0625 * $temp2 * $betao * (13 - 78 * $theta2 + 137 * $theta4);
			$x1m5th = 1 - 5 * $theta2;
			$omgdot = -0.5 * $temp1 * $x1m5th + 0.0625 * $temp2 * (7 - 114 * $theta2 + 395 * $theta4) + $temp3 * (3 - 36 * $theta2 + 49 * $theta4);
			$xhdot1 = -$temp1 * $cosio;
			$xnodot = $xhdot1 + (0.5 * $temp2 * (4 - 19 * $theta2) + 2 * $temp3 * (3 - 7 * $theta2)) * $cosio;
			$omgcof = $tle -> bstar * $c3 * cos($tle -> omegao);
			$xmcof = -Globals::tothrd * $coef * $tle -> bstar * Globals::ae / $eeta;
			$xnodcf = 3.5 * $betao2 * $xhdot1 * $c1;
			$t2cof = 1.5 * $c1;
			$xlcof = 0.125 * $a3ovk2 * $sinio * (3 + 5 * $cosio) / (1 + $cosio);
			$aycof = 0.25 * $a3ovk2 * $sinio;
			$delmo = pow(1 + $eta * cos($tle -> xmo), 3);
			$sinmo = sin($tle -> xmo);
			$x7thm1 = 7 * $theta2 - 1;

			if(!$this -> SIMPLE_FLAG) {
				$c1sq = $c1 * $c1;
				$d2 = 4 * $aodp * $tsi * $c1sq;
				$temp = $d2 * $tsi * $c1 / 3;
				$d3 = (17 * $aodp + $s4) * $temp;
				$d4 = 0.5 * $temp * $aodp * $tsi * (221 * $aodp + 31 * $s4) * $c1;
				$t3cof = $d2 + 2 * $c1sq;
				$t4cof = 0.25 * (3 * $d3 + $c1 * (12 * $d2 + 10 * $c1sq));
				$t5cof = 0.2 * (3 * $d4 + 12 * $c1 * $d3 + 6 * $d2 * $d2 + 15 * $c1sq * (2 * $d2 + $c1sq));
			}

			$this -> SGP4_INITIALIZED_FLAG = 1;
		}

		// Update for secular gravity and atmospheric drag.

		$xmdf = $tle -> xmo + $xmdot * $tsince;
		$omgadf = $tle -> omegao + $omgdot * $tsince;
		$xnoddf = $tle -> xnodeo + $xnodot * $tsince;
		$omega = $omgadf;
		$xmp = $xmdf;
		$tsq = $tsince * $tsince;
		$xnode = $xnoddf + $xnodcf * $tsq;
		$tempa = 1 - $c1 * $tsince;
		$tempe = $tle -> bstar * $c4 * $tsince;
		$templ = $t2cof * $tsq;

		if(!$this -> SIMPLE_FLAG) {
			$delomg = $omgcof * $tsince;
			$delm = $xmcof * (pow(1 + $eta * cos($xmdf), 3) - $delmo);
			$temp = $delomg + $delm;
			$xmp = $xmdf + $temp;
			$omega = $omgadf - $temp;
			$tcube = $tsq * $tsince;
			$tfour = $tsince * $tcube;
			$tempa = $tempa - $d2 * $tsq - $d3 * $tcube - $d4 * $tfour;
			$tempe = $tempe + $tle -> bstar * $c5 * (sin($xmp) - $sinmo);
			$templ = $templ + $t3cof * $tcube + $tfour * ($t4cof + $tsince * $t5cof);
		}

		$a = $aodp * pow($tempa, 2);
		$e = $tle -> eo - $tempe;
		$xl = $xmp + $omega + $xnode + $xnodp * $templ;
		$beta = sqrt(1 - $e * $e);
		$xn = Globals::xke / pow($a, 1.5);

		// Long period periodics

		$axn = $e * cos($omega);
		$temp = 1 / ($a * $beta * $beta);
		$xll = $temp * $xlcof * $axn;
		$aynl = $temp * $aycof;
		$xlt = $xl + $xll;
		$ayn = $e * sin($omega) + $aynl;

		// Solve Kepler's Equation

		$capu = $this -> FMod2p($xlt - $xnode);
		$temp2 = $capu;
		$i = 0;

		do {
			$sinepw = sin($temp2);
			$cosepw = cos($temp2);
			$temp3 = $axn * $sinepw;
			$temp4 = $ayn * $cosepw;
			$temp5 = $axn * $cosepw;
			$temp6 = $ayn * $sinepw;
			$epw = ($capu - $temp4 + $temp3 - $temp2) / (1 - $temp5 - $temp6) + $temp2;

			if(abs($epw - $temp2) <= Globals::e6a)
				break;

			$temp2 = $epw;

		} while ($i++<10);

		// Short period preliminary quantities

		$ecose = $temp5 + $temp6;
		$esine = $temp3 - $temp4;
		$elsq = $axn * $axn + $ayn * $ayn;
		$temp = 1 - $elsq;
		$pl = $a * $temp;
		$r = $a * (1 - $ecose);
		$temp1 = 1 / $r;
		$rdot = Globals::xke * sqrt($a) * $esine * $temp1;
		$rfdot = Globals::xke * sqrt($pl) * $temp1;
		$temp2 = $a * $temp1;
		$betal = sqrt($temp);
		$temp3 = 1 / (1 + $betal);
		$cosu = $temp2 * ($cosepw - $axn + $ayn * $esine * $temp3);
		$sinu = $temp2 * ($sinepw - $ayn - $axn * $esine * $temp3);
		$u = $this -> AcTan($sinu, $cosu);
		$sin2u = 2 * $sinu * $cosu;
		$cos2u = 2 * $cosu * $cosu - 1;
		$temp = 1 / $pl;
		$temp1 = Globals::ck2 * $temp;
		$temp2 = $temp1 * $temp;

		// Update for short periodics

		$rk = $r * (1 - 1.5 * $temp2 * $betal * $x3thm1) + 0.5 * $temp1 * $x1mth2 * $cos2u;
		$uk = $u - 0.25 * $temp2 * $x7thm1 * $sin2u;
		$xnodek = $xnode + 1.5 * $temp2 * $cosio * $sin2u;
		$xinck = $tle -> xincl + 1.5 * $temp2 * $cosio * $sinio * $cos2u;
		$rdotk = $rdot - $xn * $temp1 * $x1mth2 * $sin2u;
		$rfdotk = $rfdot + $xn * $temp1 * ($x1mth2 * $cos2u + 1.5 * $x3thm1);

		// Orientation vectors

		$sinuk = sin($uk);
		$cosuk = cos($uk);
		$sinik = sin($xinck);
		$cosik = cos($xinck);
		$sinnok = sin($xnodek);
		$cosnok = cos($xnodek);
		$xmx = -$sinnok * $cosik;
		$xmy = $cosnok * $cosik;
		$ux = $xmx * $sinuk + $cosnok * $cosuk;
		$uy = $xmy * $sinuk + $sinnok * $cosuk;
		$uz = $sinik * $sinuk;
		$vx = $xmx * $cosuk - $cosnok * $sinuk;
		$vy = $xmy * $cosuk - $sinnok * $sinuk;
		$vz = $sinik * $cosuk;

		// Position and velocity

		$pos -> x = $rk * $ux;
		$pos -> y = $rk * $uy;
		$pos -> z = $rk * $uz;

		$vel -> x = $rdotk * $ux + $rfdotk * $vx;
		$vel -> y = $rdotk * $uy + $rfdotk * $vy;
		$vel -> z = $rdotk * $uz + $rfdotk * $vz;
	}

	private function Deep($ientry, &$tle, &$deep_arg_eosq, &$deep_arg_sinio, &$deep_arg_cosio, &$deep_arg_betao, &$deep_arg_aodp, &$deep_arg_theta2, &$deep_arg_sing, &$deep_arg_cosg, &$deep_arg_betao2, &$deep_arg_xmdot, &$deep_arg_omgdot, &$deep_arg_xnodot, &$deep_arg_xnodp, &$deep_arg_xll, &$deep_arg_omgadf, &$deep_arg_xnode, &$deep_arg_em, &$deep_arg_xinc, &$deep_arg_xn, &$deep_arg_t, &$deep_arg_ds50) {
		// This function is used by SDP4 to add lunar and solar
		// perturbation effects to deep-space orbit objects.

		static $thgr;
		static $xnq;
		static $xqncl;
		static $omegaq;
		static $zmol;
		static $zmos;
		static $savtsn;
		static $ee2;
		static $e3;
		static $xi2;
		static $xl2;
		static $xl3;
		static $xl4;
		static $xgh2;
		static $xgh3;
		static $xgh4;
		static $xh2;
		static $xh3;
		static $sse;
		static $ssi;
		static $ssg;
		static $xi3;
		static $se2;
		static $si2;
		static $sl2;
		static $sgh2;
		static $sh2;
		static $se3;
		static $si3;
		static $sl3;
		static $sgh3;
		static $sh3;
		static $sl4;
		static $sgh4;
		static $ssl;
		static $ssh;
		static $d3210;
		static $d3222;
		static $d4410;
		static $d4422;
		static $d5220;
		static $d5232;
		static $d5421;
		static $d5433;
		static $del1;
		static $del2;
		static $del3;
		static $fasx2;
		static $fasx4;
		static $fasx6;
		static $xlamo;
		static $xfact;
		static $xni;
		static $atime;
		static $stepp;
		static $stepn;
		static $step2;
		static $preep;
		static $pl;
		static $sghs;
		static $xli;
		static $d2201;
		static $d2211;
		static $sghl;
		static $sh1;
		static $pinc;
		static $pe;
		static $shs;
		static $zsingl;
		static $zcosgl;
		static $zsinhl;
		static $zcoshl;
		static $zsinil;
		static $zcosil;

		switch ($ientry) {
			// Entrance for deep space initialization

			case 1 :
				$thgr = $this -> ThetaG($tle -> epoch, $deep_arg_ds50);
				$eq = $tle -> eo;
				$xnq = $deep_arg_xnodp;
				$aqnv = 1 / $deep_arg_aodp;
				$xqncl = $tle -> xincl;
				$xmao = $tle -> xmo;
				$xpidot = $deep_arg_omgdot + $deep_arg_xnodot;
				$sinq = sin($tle -> xnodeo);
				$cosq = cos($tle -> xnodeo);
				$omegaq = $tle -> omegao;

				// Initialize lunar solar terms

				$day = $deep_arg_ds50 + 18261.5;
				// Days since 1900 Jan 0.5

				if($day != $preep) {
					$preep = $day;
					$xnodce = 4.5236020 - 9.2422029E-4 * $day;
					$stem = sin($xnodce);
					$ctem = cos($xnodce);
					$zcosil = 0.91375164 - 0.03568096 * $ctem;
					$zsinil = sqrt(1 - $zcosil * $zcosil);
					$zsinhl = 0.089683511 * $stem / $zsinil;
					$zcoshl = sqrt(1 - $zsinhl * $zsinhl);
					$c = 4.7199672 + 0.22997150 * $day;
					$gam = 5.8351514 + 0.0019443680 * $day;
					$zmol = $this -> FMod2p($c - $gam);
					$zx = 0.39785416 * $stem / $zsinil;
					$zy = $zcoshl * $ctem + 0.91744867 * $zsinhl * $stem;
					$zx = $this -> AcTan($zx, $zy);
					$zx = $gam + $zx - $xnodce;
					$zcosgl = cos($zx);
					$zsingl = sin($zx);
					$zmos = 6.2565837 + 0.017201977 * $day;
					$zmos = $this -> FMod2p($zmos);
				}

				// Do solar terms

				$savtsn = 1E20;
				$zcosg = Globals::zcosgs;
				$zsing = Globals::zsings;
				$zcosi = Globals::zcosis;
				$zsini = Globals::zsinis;
				$zcosh = $cosq;
				$zsinh = $sinq;
				$cc = Globals::c1ss;
				$zn = Globals::zns;
				$ze = Globals::zes;
				$zmo = $zmos;
				$xnoi = 1 / $xnq;

				// Loop breaks when Solar terms are done a second
				// time, after Lunar terms are initialized

				for(; ; ) {
					// Solar terms done again after Lunar terms are done

					$a1 = $zcosg * $zcosh + $zsing * $zcosi * $zsinh;
					$a3 = -$zsing * $zcosh + $zcosg * $zcosi * $zsinh;
					$a7 = -$zcosg * $zsinh + $zsing * $zcosi * $zcosh;
					$a8 = $zsing * $zsini;
					$a9 = $zsing * $zsinh + $zcosg * $zcosi * $zcosh;
					$a10 = $zcosg * $zsini;
					$a2 = $deep_arg_cosio * $a7 + $deep_arg_sinio * $a8;
					$a4 = $deep_arg_cosio * $a9 + $deep_arg_sinio * $a10;
					$a5 = -$deep_arg_sinio * $a7 + $deep_arg_cosio * $a8;
					$a6 = -$deep_arg_sinio * $a9 + $deep_arg_cosio * $a10;
					$x1 = $a1 * $deep_arg_cosg + $a2 * $deep_arg_sing;
					$x2 = $a3 * $deep_arg_cosg + $a4 * $deep_arg_sing;
					$x3 = -$a1 * $deep_arg_sing + $a2 * $deep_arg_cosg;
					$x4 = -$a3 * $deep_arg_sing + $a4 * $deep_arg_cosg;
					$x5 = $a5 * $deep_arg_sing;
					$x6 = $a6 * $deep_arg_sing;
					$x7 = $a5 * $deep_arg_cosg;
					$x8 = $a6 * $deep_arg_cosg;
					$z31 = 12 * $x1 * $x1 - 3 * $x3 * $x3;
					$z32 = 24 * $x1 * $x2 - 6 * $x3 * $x4;
					$z33 = 12 * $x2 * $x2 - 3 * $x4 * $x4;
					$z1 = 3 * ($a1 * $a1 + $a2 * $a2) + $z31 * $deep_arg_eosq;
					$z2 = 6 * ($a1 * $a3 + $a2 * $a4) + $z32 * $deep_arg_eosq;
					$z3 = 3 * ($a3 * $a3 + $a4 * $a4) + $z33 * $deep_arg_eosq;
					$z11 = -6 * $a1 * $a5 + $deep_arg_eosq * (-24 * $x1 * $x7 - 6 * $x3 * $x5);
					$z12 = -6 * ($a1 * $a6 + $a3 * $a5) + $deep_arg_eosq * (-24 * ($x2 * $x7 + $x1 * $x8) - 6 * ($x3 * $x6 + $x4 * $x5));
					$z13 = -6 * $a3 * $a6 + $deep_arg_eosq * (-24 * $x2 * $x8 - 6 * $x4 * $x6);
					$z21 = 6 * $a2 * $a5 + $deep_arg_eosq * (24 * $x1 * $x5 - 6 * $x3 * $x7);
					$z22 = 6 * ($a4 * $a5 + $a2 * $a6) + $deep_arg_eosq * (24 * ($x2 * $x5 + $x1 * $x6) - 6 * ($x4 * $x7 + $x3 * $x8));
					$z23 = 6 * $a4 * $a6 + $deep_arg_eosq * (24 * $x2 * $x6 - 6 * $x4 * $x8);
					$z1 = $z1 + $z1 + $deep_arg_betao2 * $z31;
					$z2 = $z2 + $z2 + $deep_arg_betao2 * $z32;
					$z3 = $z3 + $z3 + $deep_arg_betao2 * $z33;
					$s3 = $cc * $xnoi;
					$s2 = -0.5 * $s3 / $deep_arg_betao;
					$s4 = $s3 * $deep_arg_betao;
					$s1 = -15 * $eq * $s4;
					$s5 = $x1 * $x3 + $x2 * $x4;
					$s6 = $x2 * $x3 + $x1 * $x4;
					$s7 = $x2 * $x4 - $x1 * $x3;
					$se = $s1 * $zn * $s5;
					$si = $s2 * $zn * ($z11 + $z13);
					$sl = -$zn * $s3 * ($z1 + $z3 - 14 - 6 * $deep_arg_eosq);
					$sgh = $s4 * $zn * ($z31 + $z33 - 6);
					$sh = -$zn * $s2 * ($z21 + $z23);

					if($xqncl < 5.2359877E-2)
						$sh = 0;

					$ee2 = 2 * $s1 * $s6;
					$e3 = 2 * $s1 * $s7;
					$xi2 = 2 * $s2 * $z12;
					$xi3 = 2 * $s2 * ($z13 - $z11);
					$xl2 = -2 * $s3 * $z2;
					$xl3 = -2 * $s3 * ($z3 - $z1);
					$xl4 = -2 * $s3 * (-21 - 9 * $deep_arg_eosq) * $ze;
					$xgh2 = 2 * $s4 * $z32;
					$xgh3 = 2 * $s4 * ($z33 - $z31);
					$xgh4 = -18 * $s4 * $ze;
					$xh2 = -2 * $s2 * $z22;
					$xh3 = -2 * $s2 * ($z23 - $z21);

					if($this -> LUNAR_TERMS_DONE_FLAG)
						break;

					// Do lunar terms

					$sse = $se;
					$ssi = $si;
					$ssl = $sl;
					$ssh = $sh / $deep_arg_sinio;
					$ssg = $sgh - $deep_arg_cosio * $ssh;
					$se2 = $ee2;
					$si2 = $xi2;
					$sl2 = $xl2;
					$sgh2 = $xgh2;
					$sh2 = $xh2;
					$se3 = $e3;
					$si3 = $xi3;
					$sl3 = $xl3;
					$sgh3 = $xgh3;
					$sh3 = $xh3;
					$sl4 = $xl4;
					$sgh4 = $xgh4;
					$zcosg = $zcosgl;
					$zsing = $zsingl;
					$zcosi = $zcosil;
					$zsini = $zsinil;
					$zcosh = $zcoshl * $cosq + $zsinhl * $sinq;
					$zsinh = $sinq * $zcoshl - $cosq * $zsinhl;
					$zn = Globals::znl;
					$cc = Globals::c1l;
					$ze = Globals::zel;
					$zmo = $zmol;
					$this -> LUNAR_TERMS_DONE_FLAG = 1;
				}

				$sse = $sse + $se;
				$ssi = $ssi + $si;
				$ssl = $ssl + $sl;
				$ssg = $ssg + $sgh - $deep_arg_cosio / $deep_arg_sinio * $sh;
				$ssh = $ssh + $sh / $deep_arg_sinio;

				// Geopotential resonance initialization for 12 hour orbits

				if(!(($xnq < 0.0052359877) && ($xnq > 0.0034906585))) {
					if(($xnq < 0.00826) || ($xnq > 0.00924))
						return ;

					if($eq < 0.5)
						return ;

					$this -> RESONANCE_FLAG = 1;
					$eoc = $eq * $deep_arg_eosq;
					$g201 = -0.306 - ($eq - 0.64) * 0.440;

					if($eq <= 0.65) {
						$g211 = 3.616 - 13.247 * $eq + 16.290 * $deep_arg_eosq;
						$g310 = -19.302 + 117.390 * $eq - 228.419 * $deep_arg_eosq + 156.591 * $eoc;
						$g322 = -18.9068 + 109.7927 * $eq - 214.6334 * $deep_arg_eosq + 146.5816 * $eoc;
						$g410 = -41.122 + 242.694 * $eq - 471.094 * $deep_arg_eosq + 313.953 * $eoc;
						$g422 = -146.407 + 841.880 * $eq - 1629.014 * $deep_arg_eosq + 1083.435 * $eoc;
						$g520 = -532.114 + 3017.977 * $eq - 5740 * $deep_arg_eosq + 3708.276 * $eoc;
					} else {
						$g211 = -72.099 + 331.819 * $eq - 508.738 * $deep_arg_eosq + 266.724 * $eoc;
						$g310 = -346.844 + 1582.851 * $eq - 2415.925 * $deep_arg_eosq + 1246.113 * $eoc;
						$g322 = -342.585 + 1554.908 * $eq - 2366.899 * $deep_arg_eosq + 1215.972 * $eoc;
						$g410 = -1052.797 + 4758.686 * $eq - 7193.992 * $deep_arg_eosq + 3651.957 * $eoc;
						$g422 = -3581.69 + 16178.11 * $eq - 24462.77 * $deep_arg_eosq + 12422.52 * $eoc;

						if($eq <= 0.715)
							$g520 = 1464.74 - 4664.75 * $eq + 3763.64 * $deep_arg_eosq;
						else
							$g520 = -5149.66 + 29936.92 * $eq - 54087.36 * $deep_arg_eosq + 31324.56 * $eoc;
					}

					if($eq < 0.7) {
						$g533 = -919.2277 + 4988.61 * $eq - 9064.77 * $deep_arg_eosq + 5542.21 * $eoc;
						$g521 = -822.71072 + 4568.6173 * $eq - 8491.4146 * $deep_arg_eosq + 5337.524 * $eoc;
						$g532 = -853.666 + 4690.25 * $eq - 8624.77 * $deep_arg_eosq + 5341.4 * $eoc;
					} else {
						$g533 = -37995.78 + 161616.52 * $eq - 229838.2 * $deep_arg_eosq + 109377.94 * $eoc;
						$g521 = -51752.104 + 218913.95 * $eq - 309468.16 * $deep_arg_eosq + 146349.42 * $eoc;
						$g532 = -40023.88 + 170470.89 * $eq - 242699.48 * $deep_arg_eosq + 115605.82 * $eoc;
					}

					$sini2 = $deep_arg_sinio * $deep_arg_sinio;
					$f220 = 0.75 * (1 + 2 * $deep_arg_cosio + $deep_arg_theta2);
					$f221 = 1.5 * $sini2;
					$f321 = 1.875 * $deep_arg_sinio * (1 - 2 * $deep_arg_cosio - 3 * $deep_arg_theta2);
					$f322 = -1.875 * $deep_arg_sinio * (1 + 2 * $deep_arg_cosio - 3 * $deep_arg_theta2);
					$f441 = 35 * $sini2 * $f220;
					$f442 = 39.3750 * $sini2 * $sini2;
					$f522 = 9.84375 * $deep_arg_sinio * ($sini2 * (1 - 2 * $deep_arg_cosio - 5 * $deep_arg_theta2) + 0.33333333 * (-2 + 4 * $deep_arg_cosio + 6 * $deep_arg_theta2));
					$f523 = $deep_arg_sinio * (4.92187512 * $sini2 * (-2 - 4 * $deep_arg_cosio + 10 * $deep_arg_theta2) + 6.56250012 * (1 + 2 * $deep_arg_cosio - 3 * $deep_arg_theta2));
					$f542 = 29.53125 * $deep_arg_sinio * (2 - 8 * $deep_arg_cosio + $deep_arg_theta2 * (-12 + 8 * $deep_arg_cosio + 10 * $deep_arg_theta2));
					$f543 = 29.53125 * $deep_arg_sinio * (-2 - 8 * $deep_arg_cosio + $deep_arg_theta2 * (12 + 8 * $deep_arg_cosio - 10 * $deep_arg_theta2));
					$xno2 = $xnq * $xnq;
					$ainv2 = $aqnv * $aqnv;
					$temp1 = 3 * $xno2 * $ainv2;
					$temp = $temp1 * Globals::root22;
					$d2201 = $temp * $f220 * $g201;
					$d2211 = $temp * $f221 * $g211;
					$temp1 = $temp1 * $aqnv;
					$temp = $temp1 * Globals::root32;
					$d3210 = $temp * $f321 * $g310;
					$d3222 = $temp * $f322 * $g322;
					$temp1 = $temp1 * $aqnv;
					$temp = 2 * $temp1 * Globals::root44;
					$d4410 = $temp * $f441 * $g410;
					$d4422 = $temp * $f442 * $g422;
					$temp1 = $temp1 * $aqnv;
					$temp = $temp1 * Globals::root52;
					$d5220 = $temp * $f522 * $g520;
					$d5232 = $temp * $f523 * $g532;
					$temp = 2 * $temp1 * Globals::root54;
					$d5421 = $temp * $f542 * $g521;
					$d5433 = $temp * $f543 * $g533;
					$xlamo = $xmao + $tle -> xnodeo + $tle -> xnodeo - $thgr - $thgr;
					$bfact = $deep_arg_xmdot + $deep_arg_xnodot + $deep_arg_xnodot - Globals::thdt - Globals::thdt;
					$bfact = $bfact + $ssl + $ssh + $ssh;
				} else {
					$this -> RESONANCE_FLAG = 1;
					$this -> SYNCHRONOUS_FLAG = 1;

					// Synchronous resonance terms initialization

					$g200 = 1 + $deep_arg_eosq * (-2.5 + 0.8125 * $deep_arg_eosq);
					$g310 = 1 + 2 * $deep_arg_eosq;
					$g300 = 1 + $deep_arg_eosq * (-6 + 6.60937 * $deep_arg_eosq);
					$f220 = 0.75 * (1 + $deep_arg_cosio) * (1 + $deep_arg_cosio);
					$f311 = 0.9375 * $deep_arg_sinio * $deep_arg_sinio * (1 + 3 * $deep_arg_cosio) - 0.75 * (1 + $deep_arg_cosio);
					$f330 = 1 + $deep_arg_cosio;
					$f330 = 1.875 * $f330 * $f330 * $f330;
					$del1 = 3 * $xnq * $xnq * $aqnv * $aqnv;
					$del2 = 2 * $del1 * $f220 * $g200 * Globals::q22;
					$del3 = 3 * $del1 * $f330 * $g300 * Globals::q33 * $aqnv;
					$del1 = $del1 * $f311 * $g310 * Globals::q31 * $aqnv;
					$fasx2 = 0.13130908;
					$fasx4 = 2.8843198;
					$fasx6 = 0.37448087;
					$xlamo = $xmao + $tle -> xnodeo + $tle -> omegao - $thgr;
					$bfact = $deep_arg_xmdot + $xpidot - Globals::thdt;
					$bfact = $bfact + $ssl + $ssg + $ssh;
				}

				$xfact = $bfact - $xnq;

				// Initialize integrator

				$xli = $xlamo;
				$xni = $xnq;
				$atime = 0;
				$stepp = 720;
				$stepn = -720;
				$step2 = 259200;

				return ;

			// Entrance for deep space secular effects

			case 2 :
				$deep_arg_xll = $deep_arg_xll + $ssl * $deep_arg_t;
				$deep_arg_omgadf = $deep_arg_omgadf + $ssg * $deep_arg_t;
				$deep_arg_xnode = $deep_arg_xnode + $ssh * $deep_arg_t;
				$deep_arg_em = $tle -> eo + $sse * $deep_arg_t;
				$deep_arg_xinc = $tle -> xincl + $ssi * $deep_arg_t;

				if($deep_arg_xinc < 0) {
					$deep_arg_xinc = -$deep_arg_xinc;
					$deep_arg_xnode = $deep_arg_xnode + Globals::pi;
					$deep_arg_omgadf = $deep_arg_omgadf - Globals::pi;
				}

				if(!$this -> RESONANCE_FLAG)
					return ;

				do {
					if(($atime == 0) || (($deep_arg_t >= 0) && ($atime < 0)) || (($deep_arg_t < 0) && ($atime >= 0))) {
						// Epoch restart

						if($deep_arg_t >= 0)
							$delt = $stepp;
						else
							$delt = $stepn;

						$atime = 0;
						$xni = $xnq;
						$xli = $xlamo;
					} else {
						if(abs($deep_arg_t) >= abs($atime)) {
							if($deep_arg_t > 0)
								$delt = $stepp;
							else
								$delt = $stepn;
						}
					}

					do {
						if(abs($deep_arg_t - $atime) >= $stepp) {
							$this -> DO_LOOP_FLAG = 1;
							$this -> EPOCH_RESTART_FLAG = 0;
						} else {
							$ft = $deep_arg_t - $atime;
							$this -> DO_LOOP_FLAG = 0;
						}

						if(abs($deep_arg_t) < abs($atime)) {
							if($deep_arg_t >= 0)
								$delt = $stepn;
							else
								$delt = $stepp;

							$this -> DO_LOOP_FLAG = 1;
							$this -> EPOCH_RESTART_FLAG = 1;
						}

						// Dot terms calculated

						if($this -> SYNCHRONOUS_FLAG) {
							$xndot = $del1 * sin($xli - $fasx2) + $del2 * sin(2 * ($xli - $fasx4)) + $del3 * sin(3 * ($xli - $fasx6));
							$xnddt = $del1 * cos($xli - $fasx2) + 2 * $del2 * cos(2 * ($xli - $fasx4)) + 3 * $del3 * cos(3 * ($xli - $fasx6));
						} else {
							$xomi = $omegaq + $deep_arg_omgdot * $atime;
							$x2omi = $xomi + $xomi;
							$x2li = $xli + $xli;
							$xndot = $d2201 * sin($x2omi + $xli - Globals::g22) + $d2211 * sin($xli - Globals::g22) + $d3210 * sin($xomi + $xli - Globals::g32) + $d3222 * sin(-$xomi + $xli - Globals::g32) + $d4410 * sin($x2omi + $x2li - Globals::g44) + $d4422 * sin($x2li - Globals::g44) + $d5220 * sin($xomi + $xli - Globals::g52) + $d5232 * sin(-$xomi + $xli - Globals::g52) + $d5421 * sin($xomi + $x2li - Globals::g54) + $d5433 * sin(-$xomi + $x2li - Globals::g54);
							$xnddt = $d2201 * cos($x2omi + $xli - Globals::g22) + $d2211 * cos($xli - Globals::g22) + $d3210 * cos($xomi + $xli - Globals::g32) + $d3222 * cos(-$xomi + $xli - Globals::g32) + $d5220 * cos($xomi + $xli - Globals::g52) + $d5232 * cos(-$xomi + $xli - Globals::g52) + 2 * ($d4410 * cos($x2omi + $x2li - Globals::g44) + $d4422 * cos($x2li - Globals::g44) + $d5421 * cos($xomi + $x2li - Globals::g54) + $d5433 * cos(-$xomi + $x2li - Globals::g54));
						}

						$xldot = $xni + $xfact;
						$xnddt = $xnddt * $xldot;

						if($this -> DO_LOOP_FLAG) {
							$xli = $xli + $xldot * $delt + $xndot * $step2;
							$xni = $xni + $xndot * $delt + $xnddt * $step2;
							$atime = $atime + $delt;
						}

					} while ($this->DO_LOOP_FLAG && !$this->EPOCH_RESTART_FLAG);

				} while ($this->DO_LOOP_FLAG && $this->EPOCH_RESTART_FLAG);

				$deep_arg_xn = $xni + $xndot * $ft + $xnddt * $ft * $ft * 0.5;
				$xl = $xli + $xldot * $ft + $xndot * $ft * $ft * 0.5;
				$temp = -$deep_arg_xnode + $thgr + $deep_arg_t * Globals::thdt;

				if(!$this -> SYNCHRONOUS_FLAG)
					$deep_arg_xll = $xl + $temp + $temp;
				else
					$deep_arg_xll = $xl - $deep_arg_omgadf + $temp;

				return ;

			// Entrance for lunar-solar periodics

			case 3 :
				$sinis = sin($deep_arg_xinc);
				$cosis = cos($deep_arg_xinc);

				if(abs($savtsn - $deep_arg_t) >= 30) {
					$savtsn = $deep_arg_t;
					$zm = $zmos + Globals::zns * $deep_arg_t;
					$zf = $zm + 2 * Globals::zes * sin($zm);
					$sinzf = sin($zf);
					$f2 = 0.5 * $sinzf * $sinzf - 0.25;
					$f3 = -0.5 * $sinzf * cos($zf);
					$ses = $se2 * $f2 + $se3 * $f3;
					$sis = $si2 * $f2 + $si3 * $f3;
					$sls = $sl2 * $f2 + $sl3 * $f3 + $sl4 * $sinzf;
					$sghs = $sgh2 * $f2 + $sgh3 * $f3 + $sgh4 * $sinzf;
					$shs = $sh2 * $f2 + $sh3 * $f3;
					$zm = $zmol + Globals::znl * $deep_arg_t;
					$zf = $zm + 2 * Globals::zel * sin($zm);
					$sinzf = sin($zf);
					$f2 = 0.5 * $sinzf * $sinzf - 0.25;
					$f3 = -0.5 * $sinzf * cos($zf);
					$sel = $ee2 * $f2 + $e3 * $f3;
					$sil = $xi2 * $f2 + $xi3 * $f3;
					$sll = $xl2 * $f2 + $xl3 * $f3 + $xl4 * $sinzf;
					$sghl = $xgh2 * $f2 + $xgh3 * $f3 + $xgh4 * $sinzf;
					$sh1 = $xh2 * $f2 + $xh3 * $f3;
					$pe = $ses + $sel;
					$pinc = $sis + $sil;
					$pl = $sls + $sll;
				}

				$pgh = $sghs + $sghl;
				$ph = $shs + $sh1;
				$deep_arg_xinc = $deep_arg_xinc + $pinc;
				$deep_arg_em = $deep_arg_em + $pe;

				if($xqncl >= 0.2) {
					// Apply periodics directly

					$ph = $ph / $deep_arg_sinio;
					$pgh = $pgh - $deep_arg_cosio * $ph;
					$deep_arg_omgadf = $deep_arg_omgadf + $pgh;
					$deep_arg_xnode = $deep_arg_xnode + $ph;
					$deep_arg_xll = $deep_arg_xll + $pl;
				} else {
					// Apply periodics with Lyddane modification

					$sinok = sin($deep_arg_xnode);
					$cosok = cos($deep_arg_xnode);
					$alfdp = $sinis * $sinok;
					$betdp = $sinis * $cosok;
					$dalf = $ph * $cosok + $pinc * $cosis * $sinok;
					$dbet = -$ph * $sinok + $pinc * $cosis * $cosok;
					$alfdp = $alfdp + $dalf;
					$betdp = $betdp + $dbet;
					$deep_arg_xnode = $this -> FMod2p($deep_arg_xnode);
					$xls = $deep_arg_xll + $deep_arg_omgadf + $cosis * $deep_arg_xnode;
					$dls = $pl + $pgh - $pinc * $deep_arg_xnode * $sinis;
					$xls = $xls + $dls;
					$xnoh = $deep_arg_xnode;
					$deep_arg_xnode = $this -> AcTan($alfdp, $betdp);

					// This is a patch to Lyddane modification
					// suggested by Rob Matson.

					if(abs($xnoh - $deep_arg_xnode) > Globals::pi) {
						if($deep_arg_xnode < $xnoh)
							$deep_arg_xnode += Globals::twopi;
						else
							$deep_arg_xnode -= Globals::twopi;
					}

					$deep_arg_xll = $deep_arg_xll + $pl;
					$deep_arg_omgadf = $xls - $deep_arg_xll - cos($deep_arg_xinc) * $deep_arg_xnode;
				}
				return ;
		}
	}

	public function SDP4($tsince, &$tle, &$pos, &$vel) {
		// This function is used to calculate the position and velocity
		// of deep-space (period > 225 minutes) satellites. tsince is
		// time since epoch in minutes, tle is a pointer to a tle
		// structure with Keplerian orbital elements and pos and vel
		// are vector structures returning ECI satellite position and
		// velocity. Use Convert_Sat_State() to convert to km and km/s.

		static $x3thm1;
		static $c1;
		static $x1mth2;
		static $c4;
		static $xnodcf;
		static $t2cof;
		static $xlcof;
		static $aycof;
		static $x7thm1;

		// Used by dpinit part of Deep()

		static $deep_arg_eosq;
		static $deep_arg_sinio;
		static $deep_arg_cosio;
		static $deep_arg_betao;
		static $deep_arg_aodp;
		static $deep_arg_theta2;
		static $deep_arg_sing;
		static $deep_arg_cosg;
		static $deep_arg_betao2;
		static $deep_arg_xmdot;
		static $deep_arg_omgdot;
		static $deep_arg_xnodot;
		static $deep_arg_xnodp;

		// Used by dpsec and dpper parts of Deep()

		static $deep_arg_xll;
		static $deep_arg_omgadf;
		static $deep_arg_xnode;
		static $deep_arg_em;
		static $deep_arg_xinc;
		static $deep_arg_xn;
		static $deep_arg_t;

		// Used by thetg and Deep()

		static $deep_arg_ds50;

		// Initialization

		if(!$this -> SDP4_INITIALIZED_FLAG) {
			// Recover original mean motion (xnodp) and semimajor axis (aodp) from input elements.

			$a1 = pow(Globals::xke / $tle -> xno, Globals::tothrd);
			$deep_arg_cosio = cos($tle -> xincl);
			$deep_arg_theta2 = $deep_arg_cosio * $deep_arg_cosio;
			$x3thm1 = 3 * $deep_arg_theta2 - 1;
			$deep_arg_eosq = $tle -> eo * $tle -> eo;
			$deep_arg_betao2 = 1 - $deep_arg_eosq;
			$deep_arg_betao = sqrt($deep_arg_betao2);
			$del1 = 1.5 * Globals::ck2 * $x3thm1 / ($a1 * $a1 * $deep_arg_betao * $deep_arg_betao2);
			$ao = $a1 * (1 - $del1 * (0.5 * Globals::tothrd + $del1 * (1 + 134 / 81 * $del1)));
			$delo = 1.5 * Globals::ck2 * $x3thm1 / ($ao * $ao * $deep_arg_betao * $deep_arg_betao2);
			$deep_arg_xnodp = $tle -> xno / (1 + $delo);
			$deep_arg_aodp = $ao / (1 - $delo);

			// For perigee below 156 km, the values of s and qoms2t are altered.

			$s4 = Globals::s;
			$qoms24 = Globals::qoms2t;
			$perigee = ($deep_arg_aodp * (1 - $tle -> eo) - Globals::ae) * Globals::xkmper;

			if($perigee < 156.0) {
				if($perigee <= 98.0)
					$s4 = 20.0;
				else
					$s4 = $perigee - 78.0;

				$qoms24 = pow((120 - $s4) * Globals::ae / Globals::xkmper, 4);
				$s4 = $s4 / Globals::xkmper + Globals::ae;
			}

			$pinvsq = 1 / ($deep_arg_aodp * $deep_arg_aodp * $deep_arg_betao2 * $deep_arg_betao2);
			$deep_arg_sing = sin($tle -> omegao);
			$deep_arg_cosg = cos($tle -> omegao);
			$tsi = 1 / ($deep_arg_aodp - $s4);
			$eta = $deep_arg_aodp * $tle -> eo * $tsi;
			$etasq = $eta * $eta;
			$eeta = $tle -> eo * $eta;
			$psisq = abs(1 - $etasq);
			$coef = $qoms24 * pow($tsi, 4);
			$coef1 = $coef / pow($psisq, 3.5);
			$c2 = $coef1 * $deep_arg_xnodp * ($deep_arg_aodp * (1 + 1.5 * $etasq + $eeta * (4 + $etasq)) + 0.75 * Globals::ck2 * $tsi / $psisq * $x3thm1 * (8 + 3 * $etasq * (8 + $etasq)));
			$c1 = $tle -> bstar * $c2;
			$deep_arg_sinio = sin($tle -> xincl);
			$a3ovk2 = -Globals::xj3 / Globals::ck2 * pow(Globals::ae, 3);
			$x1mth2 = 1 - $deep_arg_theta2;
			$c4 = 2 * $deep_arg_xnodp * $coef1 * $deep_arg_aodp * $deep_arg_betao2 * ($eta * (2 + 0.5 * $etasq) + $tle -> eo * (0.5 + 2 * $etasq) - 2 * Globals::ck2 * $tsi / ($deep_arg_aodp * $psisq) * (-3 * $x3thm1 * (1 - 2 * $eeta + $etasq * (1.5 - 0.5 * $eeta)) + 0.75 * $x1mth2 * (2 * $etasq - $eeta * (1 + $etasq)) * cos(2 * $tle -> omegao)));
			$theta4 = $deep_arg_theta2 * $deep_arg_theta2;
			$temp1 = 3 * Globals::ck2 * $pinvsq * $deep_arg_xnodp;
			$temp2 = $temp1 * Globals::ck2 * $pinvsq;
			$temp3 = 1.25 * Globals::ck4 * $pinvsq * $pinvsq * $deep_arg_xnodp;
			$deep_arg_xmdot = $deep_arg_xnodp + 0.5 * $temp1 * $deep_arg_betao * $x3thm1 + 0.0625 * $temp2 * $deep_arg_betao * (13 - 78 * $deep_arg_theta2 + 137 * $theta4);
			$x1m5th = 1 - 5 * $deep_arg_theta2;
			$deep_arg_omgdot = -0.5 * $temp1 * $x1m5th + 0.0625 * $temp2 * (7 - 114 * $deep_arg_theta2 + 395 * $theta4) + $temp3 * (3 - 36 * $deep_arg_theta2 + 49 * $theta4);
			$xhdot1 = -$temp1 * $deep_arg_cosio;
			$deep_arg_xnodot = $xhdot1 + (0.5 * $temp2 * (4 - 19 * $deep_arg_theta2) + 2 * $temp3 * (3 - 7 * $deep_arg_theta2)) * $deep_arg_cosio;
			$xnodcf = 3.5 * $deep_arg_betao2 * $xhdot1 * $c1;
			$t2cof = 1.5 * $c1;
			$xlcof = 0.125 * $a3ovk2 * $deep_arg_sinio * (3 + 5 * $deep_arg_cosio) / (1 + $deep_arg_cosio);
			$aycof = 0.25 * $a3ovk2 * $deep_arg_sinio;
			$x7thm1 = 7 * $deep_arg_theta2 - 1;

			// Initialize Deep()

			$this -> Deep(1, $tle, $deep_arg_eosq, $deep_arg_sinio, $deep_arg_cosio, $deep_arg_betao, $deep_arg_aodp, $deep_arg_theta2, $deep_arg_sing, $deep_arg_cosg, $deep_arg_betao2, $deep_arg_xmdot, $deep_arg_omgdot, $deep_arg_xnodot, $deep_arg_xnodp, $deep_arg_xll, $deep_arg_omgadf, $deep_arg_xnode, $deep_arg_em, $deep_arg_xinc, $deep_arg_xn, $deep_arg_t, $deep_arg_ds50);

			$this -> SDP4_INITIALIZED_FLAG = 1;
		}

		// Update for secular gravity and atmospheric drag

		$xmdf = $tle -> xmo + $deep_arg_xmdot * $tsince;
		$deep_arg_omgadf = $tle -> omegao + $deep_arg_omgdot * $tsince;
		$xnoddf = $tle -> xnodeo + $deep_arg_xnodot * $tsince;
		$tsq = $tsince * $tsince;
		$deep_arg_xnode = $xnoddf + $xnodcf * $tsq;
		$tempa = 1 - $c1 * $tsince;
		$tempe = $tle -> bstar * $c4 * $tsince;
		$templ = $t2cof * $tsq;
		$deep_arg_xn = $deep_arg_xnodp;

		// Update for deep-space secular effects

		$deep_arg_xll = $xmdf;
		$deep_arg_t = $tsince;

		$this -> Deep(2, $tle, $deep_arg_eosq, $deep_arg_sinio, $deep_arg_cosio, $deep_arg_betao, $deep_arg_aodp, $deep_arg_theta2, $deep_arg_sing, $deep_arg_cosg, $deep_arg_betao2, $deep_arg_xmdot, $deep_arg_omgdot, $deep_arg_xnodot, $deep_arg_xnodp, $deep_arg_xll, $deep_arg_omgadf, $deep_arg_xnode, $deep_arg_em, $deep_arg_xinc, $deep_arg_xn, $deep_arg_t, $deep_arg_ds50);

		$xmdf = $deep_arg_xll;
		$a = pow(Globals::xke / $deep_arg_xn, Globals::tothrd) * $tempa * $tempa;
		$deep_arg_em = $deep_arg_em - $tempe;
		$xmam = $xmdf + $deep_arg_xnodp * $templ;

		// Update for deep-space periodic effects

		$deep_arg_xll = $xmam;

		$this -> Deep(3, $tle, $deep_arg_eosq, $deep_arg_sinio, $deep_arg_cosio, $deep_arg_betao, $deep_arg_aodp, $deep_arg_theta2, $deep_arg_sing, $deep_arg_cosg, $deep_arg_betao2, $deep_arg_xmdot, $deep_arg_omgdot, $deep_arg_xnodot, $deep_arg_xnodp, $deep_arg_xll, $deep_arg_omgadf, $deep_arg_xnode, $deep_arg_em, $deep_arg_xinc, $deep_arg_xn, $deep_arg_t, $deep_arg_ds50);

		$xmam = $deep_arg_xll;
		$xl = $xmam + $deep_arg_omgadf + $deep_arg_xnode;
		$beta = sqrt(1 - $deep_arg_em * $deep_arg_em);
		$deep_arg_xn = Globals::xke / pow($a, 1.5);

		// Long period periodics

		$axn = $deep_arg_em * cos($deep_arg_omgadf);
		$temp = 1 / ($a * $beta * $beta);
		$xll = $temp * $xlcof * $axn;
		$aynl = $temp * $aycof;
		$xlt = $xl + $xll;
		$ayn = $deep_arg_em * sin($deep_arg_omgadf) + $aynl;

		// Solve Kepler's Equation

		$capu = $this -> FMod2p($xlt - $deep_arg_xnode);
		$temp2 = $capu;
		$i = 0;

		do {
			$sinepw = sin($temp2);
			$cosepw = cos($temp2);
			$temp3 = $axn * $sinepw;
			$temp4 = $ayn * $cosepw;
			$temp5 = $axn * $cosepw;
			$temp6 = $ayn * $sinepw;
			$epw = ($capu - $temp4 + $temp3 - $temp2) / (1 - $temp5 - $temp6) + $temp2;

			if(abs($epw - $temp2) <= Globals::e6a)
				break;

			$temp2 = $epw;

		} while ($i++<10);

		// Short period preliminary quantities

		$ecose = $temp5 + $temp6;
		$esine = $temp3 - $temp4;
		$elsq = $axn * $axn + $ayn * $ayn;
		$temp = 1 - $elsq;
		$pl = $a * $temp;
		$r = $a * (1 - $ecose);
		$temp1 = 1 / $r;
		$rdot = Globals::xke * sqrt($a) * $esine * $temp1;
		$rfdot = Globals::xke * sqrt($pl) * $temp1;
		$temp2 = $a * $temp1;
		$betal = sqrt($temp);
		$temp3 = 1 / (1 + $betal);
		$cosu = $temp2 * ($cosepw - $axn + $ayn * $esine * $temp3);
		$sinu = $temp2 * ($sinepw - $ayn - $axn * $esine * $temp3);
		$u = $this -> AcTan($sinu, $cosu);
		$sin2u = 2 * $sinu * $cosu;
		$cos2u = 2 * $cosu * $cosu - 1;
		$temp = 1 / $pl;
		$temp1 = Globals::ck2 * $temp;
		$temp2 = $temp1 * $temp;

		// Update for short periodics

		$rk = $r * (1 - 1.5 * $temp2 * $betal * $x3thm1) + 0.5 * $temp1 * $x1mth2 * $cos2u;
		$uk = $u - 0.25 * $temp2 * $x7thm1 * $sin2u;
		$xnodek = $deep_arg_xnode + 1.5 * $temp2 * $deep_arg_cosio * $sin2u;
		$xinck = $deep_arg_xinc + 1.5 * $temp2 * $deep_arg_cosio * $deep_arg_sinio * $cos2u;
		$rdotk = $rdot - $deep_arg_xn * $temp1 * $x1mth2 * $sin2u;
		$rfdotk = $rfdot + $deep_arg_xn * $temp1 * ($x1mth2 * $cos2u + 1.5 * $x3thm1);

		// Orientation vectors

		$sinuk = sin($uk);
		$cosuk = cos($uk);
		$sinik = sin($xinck);
		$cosik = cos($xinck);
		$sinnok = sin($xnodek);
		$cosnok = cos($xnodek);
		$xmx = -$sinnok * $cosik;
		$xmy = $cosnok * $cosik;
		$ux = $xmx * $sinuk + $cosnok * $cosuk;
		$uy = $xmy * $sinuk + $sinnok * $cosuk;
		$uz = $sinik * $sinuk;
		$vx = $xmx * $cosuk - $cosnok * $sinuk;
		$vy = $xmy * $cosuk - $sinnok * $sinuk;
		$vz = $sinik * $cosuk;

		// Position and velocity

		$pos -> x = $rk * $ux;
		$pos -> y = $rk * $uy;
		$pos -> z = $rk * $uz;
		$vel -> x = $rdotk * $ux + $rfdotk * $vx;
		$vel -> y = $rdotk * $uy + $rfdotk * $vy;
		$vel -> z = $rdotk * $uz + $rfdotk * $vz;
	}

	private function calc(&$tle, &$obs_geodetic, &$sat_data, $daynum) {
		// This is the stuff we need to do repetitively...

		// Zero vector for initializations

		$zero_vector = new vector();

		// Satellite position and velocity vectors

		$vel = new vector();

		$pos = new vector();

		// Satellite Az, El, Range, Range rate

		$obs_set = new vector();

		// Solar ECI position vector

		$solar_vector = new vector();

		// Solar observed azi and ele vector

		$solar_set = new vector();

		// Satellite's predicted geodetic position

		$sat_geodetic = new geodetic(0, 0, 0);

		$jul_utc = $daynum + 2444238.5;

		// Convert satellite's epoch time to Julian and calculate time since epoch in minutes

		$jul_epoch = $this -> Julian_Date_of_Epoch($tle -> epoch);

		$tsince = ($jul_utc - $jul_epoch) * Globals::xmnpda;

		$sat_data -> age = $jul_utc - $jul_epoch;

		if($tle -> deep)
			$this -> SDP4($tsince, $tle, $pos, $vel);
		else
			$this -> SGP4($tsince, $tle, $pos, $vel);

		// Scale position and velocity vectors to km and km/sec

		$this -> Convert_Sat_State($pos, $vel);

		// Calculate velocity of satellite

		$this -> Magnitude($vel);
		$sat_data -> vel = $vel -> w;

		// All angles in rads. Distance in km. Velocity in km/s
		// Calculate satellite Azi, Ele, Range and Range-rate

		$this -> Calculate_Obs($jul_utc, $pos, $vel, $obs_geodetic, $obs_set);

		// Calculate satellite Lat North, Lon East and Alt.

		$this -> Calculate_LatLonAlt($jul_utc, $pos, $sat_geodetic);

		// Calculate solar position and satellite eclipse depth.
		// Also set or clear the satellite eclipsed flag accordingly.

		$this -> Calculate_Solar_Position($jul_utc, $solar_vector);
		$this -> Calculate_Obs($jul_utc, $solar_vector, $zero_vector, $obs_geodetic, $solar_set);

		$sat_data -> eclipsed = ($this -> Sat_Eclipsed($pos, $solar_vector, $sat_data -> eclipse_depth));

		// Check if the satellite is currently visible to the observer

		$sat_data -> visible = (int)($obs_set -> y >= 0.0);

		$sat_data -> azi = $obs_set -> x / Globals::deg2rad;
		$sat_data -> ele = $obs_set -> y / Globals::deg2rad;

		$sat_data -> range = $obs_set -> z;
		$sat_data -> range_rate = $obs_set -> w;

		$sat_data -> lat = ((($this -> io_lat == 'N') ? +1 : -1) * ($sat_geodetic -> lat / Globals::deg2rad));

		$sat_lon = ($sat_geodetic -> lon / Globals::deg2rad);
		//$sat_data -> lon = ($this -> io_lon == 'W' ? (360.0 - $sat_lon) : $sat_lon);		
		$sat_data->lon = $sat_lon;
		$sat_data -> alt = $sat_geodetic -> alt;

		$this -> sun_azi = $solar_set -> x / Globals::deg2rad;
		$this -> sun_ele = $solar_set -> y / Globals::deg2rad;

		$sat_data -> footprint = 12756.33 * acos(Globals::xkmper / (Globals::xkmper + $sat_data -> alt));

		$this -> rv = (double)floor(($tle -> xno * Globals::xmnpda / Globals::twopi + $sat_data -> age * $tle -> bstar * Globals::ae) * $sat_data -> age + $tle -> xmo / Globals::twopi) + $tle -> orbitnum;
		$this -> irk = (double)($sat_data -> range);
		$this -> isplat = ($sat_data -> lat);
		$this -> isplong = ($sat_data -> lon);
		$this -> iaz = (int)round($sat_data -> azi);
		$this -> iel = (int)round($sat_data -> ele);
		return $pos;
	}

	public function predict(&$tle, &$obs_geodetic, &$predict_data, $daynum, $days=1, $visible_only=0) {
		// This function predicts satellite passes.

		$days_sec = 86400 * ($days - 1);
		$end_secs = $this -> daynum2unix($daynum) + $days_sec;

		// Convert observers geodetic position to radians

		$obs_geodetic -> lat *= +Globals::deg2rad;
		$obs_geodetic -> lon *= -Globals::deg2rad;
		$obs_geodetic -> alt /= 1000.0;

		// Resets all flags

		$this -> reset_flags();

		// Trap geostationary orbits and passes that cannot occur.

		if($this -> AosHappens($tle, $obs_geodetic) && $this -> Geostationary($tle) == 0 && $this -> Decayed($tle, $daynum) == 0) {

			$index = 0;

			$sat_data = new sat();

			do {
				$this -> FindAOS($tle, $obs_geodetic, $sat_data, $daynum);

				$aos_ts = $this -> daynum2unix($daynum);
				$aos_lat = $this -> isplat;
				$aos_lon = $this -> isplong;
				$aos_range = $this -> irk;
				$aos_daynum = $daynum;
				$aos_ele = $this -> iel;
				$aos_azi = $this -> iaz;
				$aos_orbitnum = $this -> rv;

				// Get the satellite passes

				while($this -> iel >= 0) {
					$lastel = $this -> iel;

					$daynum += cos(($sat_data -> ele - 1.0) * Globals::deg2rad) * sqrt($sat_data -> alt) / 25000.0;
					$this -> calc($tle, $obs_geodetic, $sat_data, $daynum);
				}

				// Make sure we found the satellites AOS

				if($lastel != 0) {
					$this -> FindLOS($tle, $obs_geodetic, $sat_data, $daynum);
					$this -> calc($tle, $obs_geodetic, $sat_data, $daynum);

					$predict_data[$index] = new predict();
					$predict_data[$index] -> aos_lat = $aos_lat;
					$predict_data[$index] -> aos_lon = $aos_lon;
					$predict_data[$index] -> aos_range = $aos_range;
					$predict_data[$index] -> aos_daynum = $aos_daynum;
					$predict_data[$index] -> aos_ele = $aos_ele;
					$predict_data[$index] -> aos_azi = $aos_azi;
					$predict_data[$index] -> aos_orbitnum = $aos_orbitnum;
					$predict_data[$index] -> aos_ts = $aos_ts;
					$predict_data[$index] -> los_lat = $this -> isplat;
					$predict_data[$index] -> los_lon = $this -> isplong;
					$predict_data[$index] -> los_range = $this -> irk;
					$predict_data[$index] -> los_daynum = $daynum;
					$predict_data[$index] -> los_ele = $this -> iel;
					$predict_data[$index] -> los_azi = $this -> iaz;
					$predict_data[$index] -> los_orbitnum = $this -> rv;
					$predict_data[$index] -> los_ts = $this -> daynum2unix($daynum);
					$predict_data[$index] -> duration = ($predict_data[$index] -> los_ts - $aos_ts) / 60;

					$index++;

					if($visible_only && !$sat_data -> visible) {
						unset($predict_data[$index]);
						$index--;
					}
				}

				$this -> NextAOS($tle, $obs_geodetic, $sat_data, $daynum);

				$start_secs = $this -> daynum2unix($daynum);

			} while ($this->AosHappens($tle,$obs_geodetic) && $this->Decayed($tle,$daynum)==0 && $start_secs<=$end_secs);

			unset($sat_data);
		}

		// Convert observers geodetic position back to degrees

		$obs_geodetic -> lat /= +Globals::deg2rad;
		$obs_geodetic -> lon /= -Globals::deg2rad;
		$obs_geodetic -> alt *= 1000.0;
	}

	public function track(&$tle, &$obs_geodetic, &$sat_data, &$daynum) {
		// Convert observers geodetic position to radians

		$obs_geodetic -> lat *= Globals::deg2rad;
		$obs_geodetic -> lon *= -Globals::deg2rad;
		$obs_geodetic -> alt /= 1000.0;

		// Resets all flags

		$this -> reset_flags();

		// Calculate the satellites position and velocity in earth orbit
		$pos = new vector();
		$pos = $this -> calc($tle, $obs_geodetic, $sat_data, $daynum);

		// Convert observers geodetic position back to degrees

		$obs_geodetic -> lat /= Globals::deg2rad;
		$obs_geodetic -> lon /= -Globals::deg2rad;
		$obs_geodetic -> alt *= 1000.0;
		return $pos;
	}

}
?>
