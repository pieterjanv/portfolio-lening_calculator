<?php

require_once __DIR__ . '/LineaireLeningCalculator.php';
require_once __DIR__ . '/Aflossing.php';
require_once __DIR__ . '/exceptions/AflossingsBedragException.php';
require_once __DIR__ . '/exceptions/AfschrijvingsDagException.php';
require_once __DIR__ . '/exceptions/AfsluitingsDatumException.php';
require_once __DIR__ . '/exceptions/LeningGeslotenException.php';

/**
 * Een lening op basis van lineaire aflossing
 */
class LineaireLening {

	/** @var Klant */
	private $klant;

	/** @var DateTime */
	private $afsluitingsDatum;

	/** @var float */
	private $totaalSchuld;

	/** @var float */
	private $restSchuld;

	/** @var float */
	private $renteVoet;

	/** @var Periode */
	private $beginPeriode;

	/** @var int */
	private $nPerioden;

	/** @var Aflossing[] */
	private $aflossingen = [];

	/** @var int */
	private $iPeriode = 1;

	/** @var bool */
	private $gesloten = false;

	/**
	 * @param Klant $klant De klant
	 * @param float $totaalSchuld Het totaal verschuldigde bedrag
	 * @param float $renteVoet Het rentepercentage / 100
	 * @param Periode $beginPeriode Periode van eerste aflossing
	 * @param int $nPerioden Looptijd van de lening in aantal perioden
	 * @param DateTime|null $afsluitingsDatum Datum van afsluiten lening
	 * @param int|null $afschrijvingsDag Dag v/d maand waarop de aflossingen moeten plaatsvinden, moet kleiner zijn dan 28
	 */
	public function __construct(
		$klant, $totaalSchuld, $renteVoet, $beginPeriode, $nPerioden, $afsluitingsDatum = null
	) {

		// valideer afschrijvingsdag
		$afschrijvingsDag = $beginPeriode->dag;
		if ($afschrijvingsDag > 27) {
			throw new AfschrijvingsDagException('Afschrijvingsdag moet voor de 28e zijn.');
		}

		if (!$afsluitingsDatum) {
			$afsluitingsDatum = new DateTime;
		}

		// valideer beginperiode
		$afsluitingsJaar = (int) $afsluitingsDatum->format('Y');
		$afsluitingsMaand = (int) $afsluitingsDatum->format('n');
		$afsluitingsDag = (int) $afsluitingsDatum->format('j');
		if (
			$afsluitingsJaar > $beginPeriode->jaar ||
			(
				$afsluitingsJaar === $beginPeriode->jaar &&
				$afsluitingsMaand > $beginPeriode->maand				
			) ||
			(
				$afsluitingsJaar === $beginPeriode->jaar &&
				$afsluitingsMaand === $beginPeriode->maand &&
				$afsluitingsDag > $beginPeriode->dag
			)
		) {
			throw new AfsluitingsDatumException(
				'Afsluitingsdatum mag niet na eerste aflossing zijn.'
			);
		}

		$this->klant = $klant;
		$this->afsluitingsDatum = $afsluitingsDatum;
		$this->totaalSchuld = $totaalSchuld;
		$this->restSchuld = $totaalSchuld;
		$this->renteVoet = $renteVoet;
		$this->beginPeriode = $beginPeriode;
		$this->nPerioden = $nPerioden;
	}

	/**
	 * Voeg een aflossing toe
	 * @param float $bedrag Totaal betaald bedrag (aflossing + rente)
	 */
	public function addAflossing($bedrag) {

		if ($this->gesloten) {
			throw new LeningGeslotenException('De lening is reeds gesloten.');
		}

		$calculator = new LineaireLeningCalculator(
			$this->totaalSchuld,
			$this->renteVoet,
			$this->nPerioden
		);

		$rente = $calculator->getRenteBedrag($this->iPeriode);
		$aflossing = $calculator->getAflossingsBedrag();
		$vereistBedrag = round($rente + $aflossing, 2);

		if ($bedrag !== $vereistBedrag) {
			throw new AflossingsBedragException('Aflossingsbedrag niet gelijk aan vereist bedrag.');
		}

		$this->aflossingen[] = new Aflossing(
			$this->iPeriode,
			$this->restSchuld,
			$aflossing,
			$rente
		);

		$this->restSchuld -= $aflossing;
		$this->iPeriode++;

		if ($this->iPeriode > $this->nPerioden) {
			$this->gesloten = true;
		}
	}

	/**
	 * Bepaal het periodiek bedrag
	 * @param int $iPeriode Het periode nummer
	 * @return float Het verschuldigde periodieke bedrag
	 */
	public function getPeriodiekBedrag($iPeriode) {
		return (new LineaireLeningCalculator(
			$this->totaalSchuld,
			$this->renteVoet,
			$this->nPerioden
		))->getPeriodiekBedrag($iPeriode);
	}

	/**
	 * Exporteer het aflossingsschema als csv
	 * @param string|null $filename Het absolute bestandspad, bv. 'C:\Users\John\export.csv'
	 */
	public function getAflossingsSchemaCSV($filename = null) {

		$datum = new DateTime();

		if (!$filename) {
			$filename = (
				__DIR__ . "/aflossingsschema-{$this->klant->naam}-{$datum->format('Y-m-d h:i:s')}"
			);
		}

		$schema = $this->getAflossingsSchema();
		$schema->toCSV($filename);
	}

	/**
	 * @return float Totaal betaalde rente na aflossen lening
	 */
	public function getTotaleRente() {

		$calculator = new LineaireLeningCalculator(
			$this->totaalSchuld,
			$this->renteVoet,
			$this->nPerioden
		);

		return $calculator->getTotaleRente();
	}

	public function getResterendeSchuld() {
		return $this->restSchuld;
	}

	/**
	 * Sluit de lening
	 */
	public function sluiten() {
		$this->gesloten = true;
	}

	/**
	 * @return AflossingsSchema
	 */
	private function getAflossingsSchema() {

		$calculator = new LineaireLeningCalculator(
			$this->totaalSchuld,
			$this->renteVoet,
			$this->nPerioden
		);

		$schema = $calculator->getAflossingsSchema(
			$this->beginPeriode,
			$this->afsluitingsDatum
		);

		return $schema;
	}
}
