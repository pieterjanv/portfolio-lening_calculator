<?php

/**
 * Een item van een aflossingschema.
 */
class AflossingsSchemaItem {

	/** @var DateTime */
	private $datum;

	/** @var float */
	private $rente;

	/** @var float */
	private $aflossingsBedrag;

	/** @var float */
	private $restSchuld;

	/**
	 * @param DateTime $datum Datum van aflossing
	 * @param float $rente Te betalen rente
	 * @param float $aflossingsBedrag Te betalen aflossing
	 * @param float @restSchuld Resterende schuld na aflossing
	 */
	public function __construct($datum, $rente, $aflossingsBedrag, $restSchuld) {
		$this->datum = $datum;
		$this->rente = $rente;
		$this->aflossingsBedrag = $aflossingsBedrag;
		$this->restSchuld = $restSchuld;
	}

	/**
	 * @return array Aflossingsschema item als array
	 */
	public function toArray() {
		return [
			'datum' => $this->datum,
			'rente' => $this->rente,
			'aflossing' => $this->aflossingsBedrag,
			'totaal' => $this->rente + $this->aflossingsBedrag,
			'restSchuld' => $this->restSchuld
		];
	}
}
