<?php

require_once __DIR__ . '/AflossingsSchemaItem.php';

/**
 * Schema van te betalen bedragen ter aflossing van lening.
 */
class AflossingsSchema {

	/** @var AflossingsSchemaItem[] */
	private $items = [];

	/**
	 * @param AflossingsSchemaItem
	 */
	public function addItem($item) {
		$this->items[] = $item;
	}

	/**
	 * Exporteer het schema naar CSV
	 * @param string $filename Absoluut bestandspad, bv. 'C:\Users\John\export.csv'
	 */
	public function toCSV($filename) {

		$schema = $this->toArray();
		$fh = fopen($filename, 'w');

		// write columns
		$columns = array_keys($schema[0]);
		fputcsv($fh, $columns);

		// write data
		foreach ($schema as $row) {
			$values = array_map(function($value, $key) {

				if ('datum' === $key) {
					return $value->format('Y-m-d');
				}

				if (is_float($value)) {
					return round($value, 2);
				}

				return $value;
			}, array_values($row), array_keys($row));
			fputcsv($fh, $values);
		}

		fclose($fh);
	}

	/**
	 * @return array Schema in array vorm
	 */
	private function toArray() {
		return array_map(fn($item) => $item->toArray(), $this->items);
	}
}
