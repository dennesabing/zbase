<?php

$countries = require __DIR__ . '/countriesData.php';
$data = [];
foreach ($countries as $countryCodex => $countryx)
{
	$data[$countryCodex] = $countryx['name'];
}
return $data;
