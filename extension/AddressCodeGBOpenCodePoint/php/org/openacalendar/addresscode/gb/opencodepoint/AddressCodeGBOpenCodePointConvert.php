<?php

namespace org\openacalendar\addresscode\gb\opencodepoint;


/**
 *
 * @package org.openacalendar.addresscode.gb.opencodepoint
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://bramp.net/blog/2008/06/04/os-easting-northing-to-lat-long/
 * @link http://bramp.net/blog/2008/06/04/os-easting-northing-to-lat-long/
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 */
class AddressCodeGBOpenCodePointConvert {
	

	static function Marc($bf0, $n, $PHI0, $PHI) {
		/*
		Compute meridional arc.
		Input: - 
		 ellipsoid semi major axis multiplied by central meridian scale factor (bf0) in meters; 
		 n (computed from a, b and f0); 
		 lat of false origin (PHI0) 
		 initial or final latitude of point (PHI) IN RADIANS.
		*/

		$n2 = pow($n, 2);
		$n3 = pow($n, 3);

		$ans  = ((1 + $n + ((5 / 4) * ($n2)) + ((5 / 4) * $n3)) * ($PHI - $PHI0));
		$ans -= (((3 * $n) + (3 * $n2) + ((21 / 8 ) * $n3)) * (sin($PHI - $PHI0)) * (cos($PHI + $PHI0)));
		$ans += ((((15 / 8 ) * $n2) + ((15 / 8 ) * $n3)) * (sin(2 * ($PHI - $PHI0))) * (cos(2 * ($PHI + $PHI0))));
		$ans -= (((35 / 24) * $n3) * (sin(3 * ($PHI - $PHI0))) * (cos(3 * ($PHI + $PHI0))));

		return $bf0 * $ans;
	}

	static function initialLat($North, $n0, $afo, $PHI0, $n, $bfo) {
		/*
		Compute initial value for Latitude (PHI) IN RADIANS.
		Input: - _
		northing of point (North) and northing of false origin (n0) in meters; 
		semi major axis multiplied by central meridian scale factor (af0) in meters; 
		latitude of false origin (PHI0) IN RADIANS;
		n (computed from a, b and f0) 
		ellipsoid semi major axis multiplied by central meridian scale factor (bf0) in meters.
		*/

		//First PHI value (PHI1)
		$PHI1 = (($North - $n0) / $afo) + $PHI0;

		//Calculate M
		$M = self::Marc($bfo, $n, $PHI0, $PHI1);

		//Calculate new PHI value (PHI2)
		$PHI2 = (($North - $n0 - $M) / $afo) + $PHI1;

		//Iterate to get final value for InitialLat
		while ( abs($North - $n0 - $M) > 0.00001 ) {
			$PHI2 = (($North - $n0 - $M) / $afo) + $PHI1;
			$M = self::Marc($bfo, $n, $PHI0, $PHI2);
			$PHI1 = $PHI2;
		}

		return $PHI2;
	}

	static function Convert($East, $North) {

		$a  = 6377563.396; // Semi-major axis, a
		$b  = 6356256.910; //Semi-minor axis, b
		$e0 = 400000.000; //True origin Easting, E0	
		$n0 = -100000.000; //True origin Northing, N0	
		$f0 = 0.999601271700; //Central Meridan Scale, F0

		$PHI0 = 49.0; // True origin latitude, j0
		$LAM0 = -2.0; // True origin longitude, l0

		//Convert angle measures to radians
		$RadPHI0 = $PHI0 * (M_PI / 180);
		$RadLAM0 = $LAM0 * (M_PI / 180);

		//Compute af0, bf0, e squared (e2), n and Et
		$af0 = $a * $f0;
		$bf0 = $b * $f0;
		$e2 = ($af0*$af0 - $bf0*$bf0 ) / ($af0*$af0);
		$n = ($af0 - $bf0) / ($af0 + $bf0);
		$Et = $East - $e0;

		//Compute initial value for latitude (PHI) in radians
		$PHId = self::InitialLat($North, $n0, $af0, $RadPHI0, $n, $bf0);

		$sinPHId2 = pow(sin($PHId),  2);
		$cosPHId  = pow(cos($PHId), -1);

		$tanPHId  = tan($PHId);
		$tanPHId2 = pow($tanPHId, 2);
		$tanPHId4 = pow($tanPHId, 4);
		$tanPHId6 = pow($tanPHId, 6);

		//Compute nu, rho and eta2 using value for PHId
		$nu = $af0 / (sqrt(1 - ($e2 * $sinPHId2)));
		$rho = ($nu * (1 - $e2)) / (1 - $e2 * $sinPHId2);
		$eta2 = ($nu / $rho) - 1;

		//Compute Longitude
		$X    = $cosPHId / $nu;
		$XI   = $cosPHId / (   6 * pow($nu, 3)) * (($nu / $rho)         +  2 * $tanPHId2);
		$XII  = $cosPHId / ( 120 * pow($nu, 5)) * (5  + 28 * $tanPHId2  + 24 * $tanPHId4);
		$XIIA = $cosPHId / (5040 * pow($nu, 7)) * (61 + 662 * $tanPHId2 + 1320 * $tanPHId4 + 720 * $tanPHId6);

		$VII  = $tanPHId / (  2 * $rho * $nu);
		$VIII = $tanPHId / ( 24 * $rho * pow($nu, 3)) * ( 5 +  3 * $tanPHId2 + $eta2 - 9 * $eta2 * $tanPHId2 );
		$IX   = $tanPHId / (720 * $rho * pow($nu, 5)) * (61 + 90 * $tanPHId2 + 45 * $tanPHId4 );

		$long = (180 / M_PI) * ($RadLAM0 + ($Et * $X) - pow($Et,3) * $XI + pow($Et,5) * $XII - pow($Et,7) * $XIIA);
		$lat  = (180 / M_PI) * ($PHId - (pow($Et,2) * $VII) + (pow($Et, 4) * $VIII) - (pow($Et, 6) * $IX));

		return array($lat, $long);
	}
	
	
}

