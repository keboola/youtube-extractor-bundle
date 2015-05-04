<?php

namespace Keboola\YoutubeExtractorBundle\Jobs;

use Keboola\YoutubeExtractorBundle\YoutubeExtractorJob;

class DataJob extends YoutubeExtractorJob
{

	protected function nextPage($response, $data)
	{
		if (!empty($response->nextPageToken)) {
			$request = $this->firstPage();
			$request->getQuery()->add('pageToken', $response->nextPageToken);
			return $request;
		} else {
			return false;
		}
	}
}
