<?php

/**
 * Datum van aflossing verschuldigde bedrag in voorgaande periode
 */
class Periode {

	/** @var int */
	public $dag;

	/** @var int */
	public $maand;

	/** @var int */
	public $jaar;

	public function __construct($jaar, $maand, $dag = 1) {
		$this->jaar = $jaar;
		$this->maand = $maand;
		$this->dag = $dag;
	}
}
