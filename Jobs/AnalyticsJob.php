<?php

namespace Keboola\YoutubeExtractorBundle\Jobs;

use Keboola\YoutubeExtractorBundle\YoutubeExtractorJob;
use	Keboola\ExtractorBundle\Common\Logger;

class AnalyticsJob extends YoutubeExtractorJob
{
	protected function parse($response)
	{
		if ($response->kind != "youtubeAnalytics#resultTable") {
			Logger::log('warning', "Unknown response kind: {$response->kind}");
		}

		if (empty($response->rows)) {
			Logger::log('warning', "Empty response at {$this->configName}");
			return;
		}

		$parentCols = [];
		foreach($this->parentParams as $k => $v) {
			$k = $this->prependParent($k);
			$parentCols[$k] = $v['value'];
		}

		$type = !empty($this->config['dataType'])
			? $this->config['dataType']
			: $this->config['endpoint'];

		$this->parser->process($response, $type, $parentCols);
	}
}
