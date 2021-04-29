<?php

/**
 * Aflossing door klant op een lening
 */
class Aflossing {

	/** @var int */
	public $periodeNummer;

	/** @var DateTime */
	public $datum;

	/** @var float */
	public $schuld;

	/** @var float */
	public $aflossing;

	/** @var float */
	public $rente;

	/**
	 * @param int $periodeNummer
	 * @param float $schuld Resterende schuld na deze aflossing
	 * @param float $aflossing Afgelost bedrag
	 * @param float $rente Rente betaald in deze periode
	 */
	public function __construct($periodeNummer, $schuld, $aflossing, $rente) {
		$this->periodeNummer = $periodeNummer;
		$this->datum = new DateTime();
		$this->schuld = $schuld;
		$this->aflossing = $aflossing;
		$this->rente = $rente;
	}

	/**
	 * @return array [
	 *   'periodeNummer' => int,
	 *   'datum' => DateTime,
	 *   'schuld' => float,
	 *   'aflossing' => float,
	 *   'rente' => float
	 * ]
	 */
	public function toArray() {
		return [
			'periodeNummer' => $this->periodeNummer,
			'datum' => $this->datum,
			'schuld' => $this->schuld,
			'aflossing' => $this->aflossing,
			'rente' => $this->rente
		];
	}
}
