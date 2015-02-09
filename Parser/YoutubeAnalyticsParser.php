<?php

namespace Keboola\YoutubeExtractorBundle\Parser;

use	Keboola\CsvTable\Table;
use	Keboola\Temp\Temp;

class YoutubeAnalyticsParser
{
	/**
	 * @var Table[]
	 */
	protected $results = [];

	/**
	 * @var Temp
	 */
	protected $temp;

	public function __construct(Temp $temp)
	{
		$this->temp = $temp;
	}

	public function process($data, $type = 'root', $parentData = [])
	{
		$table = $this->getFile($type, $data->columnHeaders, array_keys($parentData));

		foreach($data->rows as $row) {
			$table->writeRow(array_merge($row, $parentData));
		}
	}

	public function getResults()
	{
		return $this->results;
	}

	public function getCsvFiles()
	{
		return $this->getResults();
	}

	/**
	 * @param string $name
	 * @param \StdClass[] $headers Array of objects containing a $name property
	 * @return Table
	 */
	protected function getFile($name, $headers, $parentCols)
	{
		if (empty($this->results[$name])) {
			$header = array_map(
				function ($column) {
					return $column->name;
				},
				$headers
			);

			$this->results[$name] = Table::create($name, array_merge($header, $parentCols), $this->temp);
		}

		return $this->results[$name];
	}
}
