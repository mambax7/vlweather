<?php

include_once('../../mainfile.php');
include_once('../../header.php');

define('VLWEATHER_BASE_DIR', XOOPS_ROOT_PATH."/modules/vlweather");
include_once(VLWEATHER_BASE_DIR . '/class/vlweather.php');

function disp_complete_vlweather() {
	global $xoopsDB, $xoopsUser, $xoopsConfig;

	include_once(VLWEATHER_BASE_DIR . '/cache/config.php');
	$icao = $vlweatherset['icao'];
	$vl_country = $vlweatherset['country'];
	$vl_format = $vlweatherset['pref_units'];
	$vl_offset = $vlweatherset['timeoffset'];

	$language = $xoopsConfig['language'];
	// Include the appropriate language file.
	if(file_exists(XOOPS_ROOT_PATH.'/modules/vlweather/language/'.$xoopsConfig['language'].'/main.php')) {
		include_once(XOOPS_ROOT_PATH.'/modules/vlweather/language/'.$xoopsConfig['language'].'/main.php');
	} else {
		include_once(XOOPS_ROOT_PATH.'/modules/vlweather/language/english/main.php');
	}

	if(file_exists(XOOPS_ROOT_PATH.'/modules/vlweather/language/'.$xoopsConfig['language'].'/blocks.php')) {
		include_once(XOOPS_ROOT_PATH.'/modules/vlweather/language/'.$xoopsConfig['language'].'/blocks.php');
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
	}
	if (! empty($icao)) {
		$bweather->set_icao($icao);
	}

	$metar = $bweather->get_metar();

	$forecasturl = $bweather->get_forecast_url();
	echo "<p><a href=\"" . $forecasturl . "\">" . _VL_MN_FFYL . "</a></p>";
	echo "<p><a href=\"http://weather.noaa.gov/weather/current/" . $icao . ".html\">" . _VL_MN_DCFYL . "</a></p>";
	echo "<p><a href=\"http://www.nws.noaa.gov/sat_tab.html\">" . _VL_MN_USSAT . "</a></p>";
	echo "<p><a href=\"http://www.nws.noaa.gov/radar_tab.html\">" . _VL_MN_CUSRI . "</a></p>";
	echo "<p><a href=\"http://www.nws.noaa.gov/\">" . _VL_MN_CUSWW . "</a></p>";
	echo "<p><a href=\"http://www.hpc.ncep.noaa.gov\">" . _VL_MN_VUSWM . "</a></p>";

	if (!empty($metar)) {

		echo "<p>" . _VL_MN_RAWM . " <code>". $bweather->get_metar() . "</code></p>\n";

	}
	echo "<p><a href=\"http://tgftp.nws.noaa.gov/pub/data/observations/metar/decoded/" . $icao . ".TXT\">" . _VL_MN_DECM . "</a></p>";
}

disp_complete_vlweather();

include('../../footer.php');

?>