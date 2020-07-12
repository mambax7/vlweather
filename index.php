<?php

include_once('../../mainfile.php');
include_once('../../header.php');

define('VLWEATHER_BASE_DIR', XOOPS_ROOT_PATH."/modules/vlweather");
include_once(VLWEATHER_BASE_DIR . '/class/base_object.php');
include_once(VLWEATHER_BASE_DIR . '/class/vlweather.php');

if (! $xoopsUser ) {
	redirect_header(XOOPS_URL."/",3,_NOPERM);
}

function disp_complete_vlweather() {
	global $xoopsDB, $xoopsUser;

	$language = $xoopsConfig['language'];
	// Include the appropriate language file.
	if(file_exists(XOOPS_ROOT_PATH.'/modules/vlweather/language/'.$xoopsConfig['language'].'/main.php')){
		include_once(XOOPS_ROOT_PATH.'/modules/vlweather/language/'.$xoopsConfig['language'].'/main.php');
	} else {
		include_once(XOOPS_ROOT_PATH.'/modules/vlweather/language/english/main.php');
	}

	$bweather = new vlweather();

	if ( $xoopsUser ) {
		$userid = $xoopsUser->getVar("uid");
		$result = $xoopsDB->query("SELECT icao FROM " . $xoopsDB->prefix("vlwusers") . " WHERE userid=$userid");
		$num_rows = $xoopsDB->getRowsNum($result);
		if ($num_rows == 1) {
			list($icao) = $xoopsDB->fetchRow($result);
		}
		if (! empty($icao)) {
			$bweather->set_icao($icao);
		}
	}

	$metar = $bweather->get_metar();

	$forecasturl = $bweather->get_forecast_url();
	echo "<p><a href=\"" . $forecasturl . "\">Forecast for your location</a></p>";
	echo "<p><a href=\"http://weather.noaa.gov/weather/current/" . $icao . ".html\">Detailed conditions for your location</a></p>";
	echo "<p><a href=\"http://www.nws.noaa.gov/sat_tab.html\">US current satellite image</a></p>";
	echo "<p><a href=\"http://www.nws.noaa.gov/radar_tab.html\">US current radar image</a></p>";
	echo "<p><a href=\"http://www.nws.noaa.gov/\">US warnings and watches</a></p>";
	echo "<p><a href=\"http://www.hpc.ncep.noaa.gov\">Various US weather maps</a></p>";

	if (!empty($metar)) {

	  	echo "<p>The raw METAR is <code>". $bweather->get_metar() . "</code></p>\n";

	}
	echo "<p><a href=\"http://tgftp.nws.noaa.gov/pub/data/observations/metar/decoded/" . $icao . ".TXT\">Decoded Metar</a></p>";
}

disp_complete_vlweather();

include('../../footer.php');

?>