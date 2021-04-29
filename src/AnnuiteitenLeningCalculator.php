<?php

require_once __DIR__ . '/AflossingsSchema.php';
require_once __DIR__ . '/AflossingsSchemaItem.php';

class AnnuiteitenLeningCalculator {
	
	/** @var float */
	private $totaalSchuld;

	/** @var float */
	private $renteVoet;

	/** @var int */
	private $nPerioden;

	/**
	 * @param float $totaalSchuld Het verschuldigde bedrag
	 * @param float $renteVoet Het rentepercentage van toepassing (bv. 0.04)
	 * @param int $nPerioden Het aantal maanden waarin het totale bedrag afgelost wordt
	 */
	public function __construct($totaalSchuld, $renteVoet, $nPerioden) {
		$this->totaalSchuld = $totaalSchuld;
		$this->renteVoet = $renteVoet;
		$this->nPerioden = $nPerioden;
	}

	/**
	 * @param Periode $beginPeriode Periode van eerste aflossing
	 * @param DateTime|null $afsluitingsDatum Datum van afsluiten lening
	 * @return AflossingsSchema
	 */
	public function getAflossingsSchema(
		$beginPeriode, $afsluitingsDatum = null
	) {

		if (!$afsluitingsDatum) {
			$afsluitingsDatum = new DateTime;
		}

		$schema = new AflossingsSchema;
		$datum = new DateTime();
		$datum->setDate(
			$beginPeriode->jaar,
			$beginPeriode->maand,
			$beginPeriode->dag
		);
		$restSchuld = $this->totaalSchuld;
		$periodiekBedrag = $this->getPeriodiekBedrag();

		// initiele schuld toevoegen
		$schema->addItem(new AflossingsSchemaItem(
			$afsluitingsDatum,
			0,
			0,
			$restSchuld
		));

		// overige aflossingsschema items toevoegen
		for ($i = 1; $i <= $this->nPerioden; $i++) {

			$aflossing = $this->getAflossingsBedrag($i);
			$rente = $periodiekBedrag - $aflossing;
			$restSchuld -= $aflossing;

			$schema->addItem(new AflossingsSchemaItem(
				$datum,
				$rente,
				$aflossing,
				$restSchuld
			));

			$datum->setTimestamp(strtotime('last day of this month'));
			$datum->add(new DateInterval("P{$beginPeriode->dag}D"));
		}

		return $schema;
	}

	/**
	 * Bepaal het periodiek bedrag (het vaste bedrag per periode)
	 * @return float Het verschuldigde (vaste) periodieke bedrag
	 * @link https://nl.wikipedia.org/wiki/Annu%C3%AFteit#Vermogensverloop_bij_een_lening
	 */
	public function getPeriodiekBedrag() {

		$bedrag = $this->renteVoet / (
			1 - pow(1 + $this->renteVoet, -$this->nPerioden)
		) * $this->totaalSchuld;

		return $bedrag;
	}

	/**
	 * Bepaal het aflossingsbedrag van een bepaalde periode
	 * @param int $iPeriode Het periode nummer
	 * @return float Het aflossingsbedrag
	 * @link https://nl.wikipedia.org/wiki/Annu%C3%AFteit#Vermogensverloop_bij_een_lening
	 */
	public function getAflossingsBedrag($iPeriode) {

		$periodiekBedrag = $this->getPeriodiekBedrag();

		return (
			pow(1 + $this->renteVoet, $iPeriode - 1) *
			($periodiekBedrag - $this->renteVoet * $this->totaalSchuld)
		);
	}

	/**
	 * Bepaal het rentebedrag van een bepaalde periode
	 * @param int $iPeriode Het periode nummer
	 * @return float Het rentebedrag
	 * @link https://nl.wikipedia.org/wiki/Annu%C3%AFteit#Vermogensverloop_bij_een_lening
	 */
	public function getRenteBedrag($iPeriode) {

		$periodiekBedrag = $this->getPeriodiekBedrag();
		$aflossing = $this->getAflossingsBedrag($iPeriode);
		$rente = $periodiekBedrag - $aflossing;

		return $rente;
	}

	/**
	 * @return float Totale rente
	 */
	public function getTotaleRente() {
		
		$rente = 0;
		for ($i = 1; $i <= $this->nPerioden; $i++) {
			$rente += $this->getRenteBedrag($i);
		}

		return $rente;
	}
}
