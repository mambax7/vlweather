<?php

// KSFO == San Francisco, KORD = Chicago
// US == US (duh)

$vlweatherset['icao'] = 'KSFO';
$vlweatherset['country'] = 'US';

// Possibilities are:
// both_imperial  (imperial first, metric in parens)
// only_imperial
// both_metric  (metric first, imperial in parens)
// only_metric

$vlweatherset['pref_units'] = 'both_imperial';

// default time offset from GMT
// -8 for SFO, -6 for Chicago, etc.

$vlweatherset['timeoffset'] = -8.0;

?>
