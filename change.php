<?php

include_once('../../mainfile.php');

if (! $xoopsUser ) {
	redirect_header(XOOPS_URL."/",3,_NOPERM);
} else {
	include_once('../../header.php');
	include_once('class/vlweather.php');
	include_once('cache/config.php');

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
		include_once(XOOPS_ROOT_PATH.'/modules/vlweather/language/english/blocks.php');
	}

	define('VLWEATHER_BASE_DIR', XOOPS_ROOT_PATH."/modules/vlweather");

	$bweather = new vlweather();

	if (empty($HTTP_GET_VARS['vl_country'])) {
		$userid = $xoopsUser->getVar("uid");
		$result = $xoopsDB->query("SELECT icao, format FROM " . $xoopsDB->prefix("vlwusers") . " WHERE userid=$userid");
		$num_rows = $xoopsDB->getRowsNum($result);
		if ($num_rows == 1) {
			list($vl_icao, $vl_format) = $xoopsDB->fetchRow($result);
			if (! empty($vl_icao)) {
				$bweather->set_icao($vl_icao);
				$cc_temp = $bweather->get_country_code();
				if ($cc_temp === false) {
					/* turn icao off, it is not valid */
					$vl_save = 0;
					$vl_icao = $vlweatherset['default_icao'];
					$vl_country = $vlweatherset['default_country'];
					$vl_format = $vlweatherset['default_format'];
				} else {
					/* use resolved country code */
					$vl_country = $cc_temp;
				}
			}
		} else {
			$vl_save = 0;
			$vl_icao = $vlweatherset['default_icao'];
			$vl_country = $vlweatherset['default_country'];
			$vl_format = $vlweatherset['default_format'];
		}
	} else {
		$vl_country = stripslashes($HTTP_GET_VARS['vl_country']);
	}


	if (! empty($HTTP_GET_VARS['vl_icao'])) {
		$vl_icao = stripslashes($HTTP_GET_VARS['vl_icao']);
		if ($vl_icao != '') {
			/* icao was passed, we resolve country code */
			$bweather->set_icao($vl_icao);
			$cc_temp = $bweather->get_country_code();
			if ($cc_temp === false) {
				/* turn icao off, it is not valid */
				if (isset($vl_save) && ($vl_save == 1)) {
					$vl_save = 0;
					$vl_icao = $vlweatherset['default_icao'];
					$vl_country = $vlweatherset['default_country'];
					$vl_format = $vlweatherset['default_format'];
				}
			} else {
				/* use resolved country code */
				$vl_country = $cc_temp;
			}
		}
		elseif ($vl_icao == '') {
			if (isset($vl_save) && ($vl_save == 1)) {
				$vl_save = 0;
			}
		}
	}


	if (!empty($HTTP_GET_VARS['vl_save'])) {
		$vl_save = $HTTP_GET_VARS['vl_save'];
	}

	if (!empty($HTTP_GET_VARS['vl_format'])) {
		$vl_format = stripslashes($HTTP_GET_VARS['vl_format']);
	}

	if (!isset($vl_save) || ($vl_save == 0)) {

		echo '<div align="left">';

		echo '<form action="change.php" method="GET">';

		echo '<strong>' . _VL_MN_SELCNTRY . '</strong><br />';
		echo '<input type="hidden" name="vl_format" value="' . $vl_format . '">';

		echo '<select name="vl_country" onchange="submit()">';
		$country_data =  $bweather->get_countries();
		while (list($k, $v) = each($country_data)) {
			if ($k == $vl_country) {
				echo "<option value=\"$k\" selected=\"selected\">$v</option>";
			} else {
				echo "<option value=\"$k\">$v</option>";
			}
		}
		echo '</select>';
		if (!empty($vl_country)) {
			echo ' (' . _VL_MN_CC . ': ' . $vl_country . ')<br />';
		}
		echo '</form>';

		if (! empty($vl_country)) {
			echo '<form action="change.php" method="GET">';
			echo '<input type="hidden" name="vl_save" value="0">';
			echo '<strong>' . _VL_MN_SELSTA . '</strong><br />';
			echo '<select name="vl_icao" onchange="submit()">';

			$icao_data = $bweather->get_icaos($vl_country);
			while (list($k, $v) = each($icao_data)) {
				if ($k == $vl_icao) {
					echo "<option value=\"$k\" selected=\"selected\">$v</option>";
				} else {
					echo "<option value=\"$k\">$v</option>";
				}
			}
			echo '</select>';
			if (! empty($vl_icao)) {
				echo ' (' . _VL_MN_SC . ': ' . $vl_icao . ')<br />';
			}
			echo '<input type="hidden" name="vl_country" value="' . $vl_country . '">';
			echo '<br /><br />';

			echo '<input onclick="submit()" onchange="submit()" type="radio" name="vl_format" value="both_imperial" ';
			if ($vl_format == 'both_imperial') {
				echo 'checked';
			}
			echo ' > ' . _VL_MN_BOTHIMP . '<br />';
			echo '<input onclick="submit()" onchange="submit()" type="radio" name="vl_format" value="both_metric" ';
			if ($vl_format == 'both_metric') {
				echo 'checked';
			}
			echo ' > ' . _VL_MN_BOTHMET . '<br />';
			echo '<input onclick="submit()" onchange="submit()" type="radio" name="vl_format" value="only_imperial" ';
			if ($vl_format == 'only_imperial') {
				echo 'checked';
			}
			echo ' > '. _VL_MN_ONLYIMP . '<br />';
			echo '<input onclick="submit()" onchange="submit()" type="radio" name="vl_format" value="only_metric" ';
			if ($vl_format == 'only_metric') {
				echo 'checked';
			}
			echo ' > ' . _VL_MN_ONLYMET . '<br />';
			echo '</form><br />';

		}
		if (! empty ($vl_icao)) {
			$forecastlink = $bweather->get_forecast_link();
			if (!empty($forecastlink)) {
				echo _VL_MN_FLAVAIL . "<br /><br />";
			} else {
				echo _VL_MN_FLNAVAIL . "<br /><br />";
			}
		}

		if (! empty($vl_icao) && !empty($vl_country) && !empty($vl_format)) {
			echo '<form action="change.php" method="GET">';
			echo '<input type="hidden" name="vl_save" value="1">';
			echo '<input type="hidden" name="vl_country" value="' . $vl_country . '">';
			echo '<input type="hidden" name="vl_icao" value="' . $vl_icao . '">';
			echo '<input type="hidden" name="vl_format" value="' . $vl_format . '">';
			echo ' <br /><input type="submit" value="' . _VL_MN_SAVTS . '">';
			echo '</form>';
		}
		echo "<table class=\"indexboxtitle\"><tr><td>\n";
		echo "<center><strong>" . _VL_MN_TIMEMSG . "</strong></center>\n";
		echo "</td></tr></table>\n";

		echo '</div><br />';

	}
	elseif (isset($vl_save) && ($vl_save == 1) && !empty($vl_icao) && !empty($vl_format)) {
		$userid = $xoopsUser->getVar("uid");
		$result = $xoopsDB->query("SELECT * from " . $xoopsDB->prefix("vlwusers") . " WHERE userid=$userid");
		$num_rows = $xoopsDB->getRowsNum($result);
		if ($num_rows == 1) {
			$result = $xoopsDB->queryF("UPDATE " . $xoopsDB->prefix("vlwusers") . " SET icao='$vl_icao', format='$vl_format' WHERE userid=$userid");
		} else {
			$result = $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("vlwusers") . " (userid, icao, format) VALUES ($userid, '$vl_icao', '$vl_format')");
		}
		redirect_header(XOOPS_URL . "/");
	}
	include('../../footer.php');
}



?>
