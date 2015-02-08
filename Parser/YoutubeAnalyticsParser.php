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

	public function process($data, $type = 'root', $parentId = null)
	{
		$table = $this->getFile($type, $data->columnHeaders);
		foreach($data->rows as $row) {
			$table->writeRow($row);
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
	protected function getFile($name, $headers)
	{
		if (empty($this->results[$name])) {
			$header = [];
			array_walk(
				$headers,
				function ($column) {
					$header[] = $column->name;
				},
				$header
			);
			$this->results[$name] = Table::create($name, $header, $this->temp);
		}

		return $this->results[$name];
	}
}
