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

		$parentCols = [];
		foreach($this->parentParams as $k => $v) {
			$k = $this->prependParent($k);
			$parentCols[$k] = $v['value'];
		}

		$this->parser->process($response, $this->configName, $parentCols);
	}
}
