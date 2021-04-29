<?php

require_once __DIR__ . '/../LineaireLeningCalculator.php';
require_once __DIR__ . '/../LineaireLening.php';
require_once __DIR__ . '/../Klant.php';
require_once __DIR__ . '/../Periode.php';

$klant = new Klant('John');
$totaleSchuld = 1e5;
$renteVoet = .04;
$nPerioden = 10;
$beginPeriode = new Periode(2021, 05);


// SCHEMA VANUIT CALCULATOR

(new LineaireLeningCalculator(
	$totaleSchuld,
	$renteVoet,
	$nPerioden
))
	->getAflossingsSchema(new Periode(2021, 05))
	->toCSV('lineair-calculator.csv');


// SCHEMA VANUIT LENING

(new LineaireLening(
	$klant,
	$totaleSchuld,
	$renteVoet,
	$beginPeriode,
	$nPerioden
))
	->getAflossingsSchemaCSV('lineair-lening.csv');

