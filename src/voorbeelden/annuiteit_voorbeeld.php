<?php

require_once __DIR__ . '/../AnnuiteitenLening.php';
require_once __DIR__ . '/../Klant.php';
require_once __DIR__ . '/../Periode.php';

$klant = new Klant('John');
$totaleSchuld = 1e5;
$renteVoet = .04;
$nPerioden = 10;
$vandaag = new DateTime();
$beginPeriode = new Periode(
	(int) $vandaag->format('Y'), (int) $vandaag->format('n') + 1
);


// SCHEMA VANUIT CALCULATOR

(new AnnuiteitenLeningCalculator(
	$totaleSchuld,
	$renteVoet,
	$nPerioden
))
	->getAflossingsSchema($beginPeriode)
	->toCSV('annuiteit-calculator.csv');


// SCHEMA VANUIT LENING

(new AnnuiteitenLening(
	$klant,
	$totaleSchuld,
	$renteVoet,
	$beginPeriode,
	$nPerioden
))->getAflossingsSchemaCSV('annuiteit-lening.csv');


// HALVERWEGE LENING VERNIEUWEN

// In onderstaande code wordt nagegaan of het halverwege sluiten van de lening
// en opnieuw verstrekken voor de resterende schuld nadelig is voor de lener.
// Als de totale looptijd onveranderd blijft blijkt dat de totale rente onveranderd blijft.

$initieleLeningCalculator = new AnnuiteitenLeningCalculator(
	$totaleSchuld,
	$renteVoet,
	$nPerioden
);
$initieelTeBetalenRente = $initieleLeningCalculator->getTotaleRente();

// nieuwe lening afgesproken halverwege de looptijd
$iPeriode = 5;
$reedsBetaaldRente = 0;
$reedsAfgelost = 0;
for ($i = 1; $i <= $iPeriode; $i++) {
	$reedsBetaaldRente += $initieleLeningCalculator->getRenteBedrag($i);
	$reedsAfgelost += $initieleLeningCalculator->getAflossingsBedrag($i);
}
$nieuweLeningCalculator = new AnnuiteitenLeningCalculator(
	$totaleSchuld - $reedsAfgelost,
	$renteVoet,
	$nPerioden - $iPeriode
);
$nogTeBetalenRente = $nieuweLeningCalculator->getTotaleRente();
$totaalRente = $reedsBetaaldRente + $nogTeBetalenRente;
$nieuweLeningCalculator->getAflossingsSchema(
	$beginPeriode
)->toCSV('annuiteit-halverwege.csv');

// echo resultaat
echo "Totale rente initiele lening: EUR {$initieelTeBetalenRente}\n";
echo "Totale rente na vernieuwen lennig: EUR {$totaalRente}\n";
