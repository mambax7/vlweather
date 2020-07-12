<?php

function b_vlweather($options) {
	global $xoopsDB, $xoopsUser, $xoopsConfig;

	error_reporting("E_ALL");

	include_once XOOPS_ROOT_PATH.'/modules/vlweather/class/vlweather.php';

	$xoopsDB =& Database::getInstance();
	$module_handler =& xoops_gethandler('module');
	
	$block = array();
	$vlweatherset = array();

//	include_once(XOOPS_ROOT_PATH . "/modules/vlweather/cache/config.php');
	
	$vlweatherset['icao'] = $options[0];
//	$vlweatherset['country'] = 'US';
	$vlweatherset['pref_units'] = $options[1];
//	$vlweatherset['timeoffset'] = -8.0;

	$vl_icao = $vlweatherset['icao'];
//	$vl_country = $vlweatherset['country'];
	$vl_format = $vlweatherset['pref_units'];
//	$vl_offset = $vlweatherset['timeoffset'];

	$bweather = new vlweather($vlweatherset);

	$block = array();
	$block['title'] = _VL_BL_BLOCKTITLE;

	if ( $xoopsUser ) {
		$userid = $xoopsUser->getVar("uid");
		$result = $xoopsDB->query("SELECT icao, format FROM " . $xoopsDB->prefix("vlwusers") . " WHERE userid=$userid");
		$num_rows = $xoopsDB->getRowsNum($result);
		if ($num_rows == 1) {
			list($vl_icao, $vl_format) = $xoopsDB->fetchRow($result);
		}
		if (! empty($vl_icao)) {
			$bweather->set_icao($vl_icao);
		}
		if (! empty($vl_format)) {
			$bweather->set_format($vl_format);
		}
	}

	$metar = $bweather->get_metar();

	if (!empty($metar)) {
		$imblock = $bweather->interpret_metar();

		$thisloc = $imblock['location'];
		if (!empty($thisloc)) {
			$block['location'] = $thisloc;
		}
		$timestring = $bweather->get_timestring();
		$block['time'] = $timestring;
		$imageloc = $bweather->get_vlweather_image();
		$forecasturl = $bweather->get_forecast_url();
		$moreurl = XOOPS_URL . "/modules/vlweather/vlindex.php";
		if (!empty($imageloc)) {
			$block['moreurl'] = $moreurl;
			$block['image'] = XOOPS_URL . "/modules/vlweather/icons/" . $imageloc;
		}
		if (!empty($imblock['sky'])) {
			$block['conditions'] = $imblock['sky'];
		}
		if (!empty($imblock['conditions'])) {
			$block['conditions'] .= ", " . $imblock['conditions'];
		}
		if (!empty($imblock['curr_snow'])) {
			$block['conditions'] .= ", " . $imblock['curr_snow'];
		}
		if (!empty($block['conditions'])) {
			$block['conditions'] .= "<br>";
		}
		if (!empty($imblock['temp'])) {
			$block['temp'] = _VL_BL_TEMP . ": " . $imblock['temp'] ."";
			if (!empty($imblock['feels'])) {
				$block['feels'] = "<br>" . _VL_BL_FEELS . ": " . $imblock['feels'];
			}
			if (!empty($imblock['rel_humidity'])) {
				$block['rel_humidity'] = "<br>" . _VL_BL_RELHUM . ": " . $imblock['rel_humidity'];
			}
			if (!empty($imblock['dew'])) {
				$block['dew'] = "<br>" . _VL_BL_DEW . ": " . $imblock['dew'];
			}
		}
		if (!empty($imblock['bar'])) {
			$block['bar'] = "<br>" . $imblock['bar'];
			if (!empty($imblock['bardir'])) {
				$block['bardir'] = $imblock['bardir'];
			} else {
				$block['bardir'] = '';
			}
		}
		if (!empty($imblock['windspeed'])) {
			$block['windspeed'] = "<br>" . _VL_BL_WIND;
			if (!empty($imblock['windtrend'])) {
				if ($imblock['windtrend'] == '+') {
					$block['windspeed'] .= " " . _VL_BL_INCR . "<br />";
				} else {
					$block['windspeed'] .= " " . _VL_BL_DECR . "<br />";
				}
			} else {
				$block['windspeed'] .= ": ";
			}
			if (!empty($imblock['wdirshort'])) {
				if ($imblock['wdirshort'] == _VL_BL_VARSHORT) {
					$block['windspeed'] .= _VL_BL_VARLONG;
				} else {
					$block['windspeed'] .= $imblock['wdirshort'];
				}
			}
			$block['windspeed'] .= " " . _VL_BL_AT . " " . $imblock['windspeed'];
			if (!empty($imblock['gusts'])) {
				$block['windspeed'] .= "<br />" . _VL_BL_GUSTS . " " . $imblock['gusts'];
			}
		} else {
			$block['windspeed'] = '<br />' . _VL_BL_CALM;
		}
		
		if (!empty($imblock['rec_precip']) || !empty($imblock['rec_precip_3_6']) || !empty($imblock['precip_24h']) || !empty($imblock['rec_snow'])) {
			if (!empty($imblock['rec_precip'])) {
				$block['rec_precip'] = _VL_BL_PRESHORT . ": " . $imblock['rec_precip'];
			}
			if (!empty($imblock['rec_precip_3_6'])) {
				if (! empty($imblock['rec_precip'])) {
					$block['rec_precip'] .= "<br>";
				}
				$block['rec_precip'] .=	_VL_BL_3TO6PRESHORT . ": " . $imblock['rec_precip_3_6'];
			}
			if (!empty($imblock['precip_24h'])) {
				if (!empty($imblock['rec_precip']) || !empty($imblock['rec_precip_3_6'])) {
					$block['rec_precip'] .= "<br>";
				}
				$block['rec_precip'] .= _VL_BL_24PSHORT . ": " . $imblock['precip_24h'];
			}
			if (!empty($imblock['rec_snow'])) {
				if (!empty($imblock['rec_precip']) || !empty($imblock['rec_precip_3_6']) || !empty($imblock['precip_24h'])) {
					$block['rec_precip'] .= "<br>";
				}
				$block['rec_precip'] .= _VL_BL_SNFL . ": " . $imblock['rec_precip'];
			}
		}
		$forecastlink = $bweather->get_forecast_link();
		if (!empty($forecastlink)) {
			$block['forecast'] = "<br>" . $forecastlink;
		}
		if ($xoopsUser) {
			$block['usersettings'] = "<br><center><a href=\"".XOOPS_URL."/modules/vlweather/change.php\">"._VL_BL_CHNGSET."</A></center>\n";
		}
	} else {
		$block['location'] .= _VL_BL_NODATA;
		if ($xoopsUser) {
			$block['usersettings'] = "<br><center><a href=\"".XOOPS_URL."/modules/vlweather/change.php\">"._VL_BL_CHNGSET."</A></center>\n";
		}
	}
	return $block;
}


function b_edit_vlweather($options) {
	$form .= "&nbsp;<br>"._VL_EDIT_ICAO."&nbsp;<input type='text' name='options[0]' value='".$options[0]."' /><br />";

	$form .= _VL_BL_HOWREP . "<BR />";
  $form .= '<input type="radio" name="options[1]" value="both_imperial" ';
	if ($options[1] == 'both_imperial') {
				$form .= 'checked';
	}
	$form .= ' > ' . _VL_BL_BOTHIMP . '<br />';
  $form .= '<input type="radio" name="options[1]" value="both_metric" ';
	if ($options[1] == 'both_metric') {
		$form .= 'checked';
  }
	$form .= ' > ' . _VL_BL_BOTHMET . '<br />';
	$form .= '<input type="radio" name="options[1]" value="only_imperial" ';
	if ($options[1] == 'only_imperial') {
			$form .= 'checked';
	}
	$form .= ' > '. _VL_BL_ONLYIMP . '<br />';
	$form .= '<input type="radio" name="options[1]" value="only_metric" ';
	if ($options[1] == 'only_metric') {
			$form .= 'checked';
	}
  $form .= ' > ' . _VL_BL_ONLYMET . '<br />';

  return $form;

}

?>