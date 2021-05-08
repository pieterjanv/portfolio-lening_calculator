<?php

require_once __DIR__ . '/AflossingsSchema.php';
require_once __DIR__ . '/AflossingsSchemaItem.php';

class LineaireLeningCalculator {
	
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
		$aflossing = $this->getAflossingsBedrag();

		// initiele schuld toevoegen
		$schema->addItem(new AflossingsSchemaItem(
			$afsluitingsDatum,
			0,
			0,
			$restSchuld
		));

		// overige aflossingsschema items toevoegen
		for ($i = 1; $i <= $this->nPerioden; $i++) {

			$rente = $this->getRenteBedrag($i);
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
	 * Bepaal het periodiek bedrag
	 * @param int $iPeriode Het periode nummer
	 * @return float Het verschuldigde periodieke bedrag
	 */
	public function getPeriodiekBedrag($iPeriode) {

		$bedrag = $this->getAflossingsBedrag() + $this->getRenteBedrag($iPeriode);

		return $bedrag;
	}

	/**
	 * Bepaal het aflossingsbedrag
	 * @return float Het verschuldigde (vaste) aflossingsbedrag
	 */
	public function getAflossingsBedrag() {

		$bedrag = $this->totaalSchuld / $this->nPerioden;

		return $bedrag;
	}

	/**
	 * Bepaal het rentebedrag van een bepaalde periode
	 * @param int $iPeriode Het periode nummer
	 * @return float Het rentebedrag
	 */
	public function getRenteBedrag($iPeriode) {

		$renteSchuld = $this->getRestSchuld($iPeriode - 1);
		$rente = $this->renteVoet * $renteSchuld;

		return $rente;
	}

	/**
	 * Bepaal de restschuld van een bepaalde periode
	 * @param int $iPeriode Het periode nummer
	 * @return float De restSchuld (schuld na $iPeriode aflossingen)
	 */
	public function getRestSchuld($iPeriode) {

		$aflossingsBedrag = $this->getAflossingsBedrag();
		$restSchuld = $this->totaalSchuld - $iPeriode * $aflossingsBedrag;

		return $restSchuld;
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
