<?php

namespace Keboola\YoutubeExtractorBundle;

use Keboola\ExtractorBundle\Extractor\Extractors\JsonExtractor as Extractor;
use Syrup\ComponentBundle\Exception\SyrupComponentException;
use GuzzleHttp\Client as Client;
use Keboola\YoutubeExtractorBundle\YoutubeExtractorJob;
use	Keboola\Google\ClientBundle\Google\RestApi;

class YoutubeExtractor extends Extractor
{
	protected $name = "youtube";

	/**
	 * @var array
	 */
	protected $apiKeys;

	public function __construct($params)
	{
		$this->apiKeys = $params;
	}

	public function run($config) {
		$client = new GuzzleClient([
			"base_url" => "https://www.googleapis.com/youtube/analytics/v1/reports",
			"defaults" => [
				"headers" => [
					"Accept" => "application/json"
				]
			]
		]);

		$clientId = $this->apiKeys["client-id"];
		$clientSecret = $this->apiKeys["client-secret"];
		$restApi = new RestApi($clientId, $clientSecret, null, $config["attributes"]["refresh_token"]);

		foreach($config["jobs"] as $jobConfig) {
			// $this->parser is, by default, only pre-created when using JsonExtractor
			// Otherwise it must be created like Above example, OR within the job itself
			$job = new YoutubeExtractorJob($jobConfig, $client, $this->parser);
			$job->setGoogleClient($restApi);
			$job->run();
		}

		// ONLY available in the Json/Wsdl parsers -
		// otherwise just pass an array of CsvFile OR Common/Table files to upload
		$this->sapiUpload($this->parser->getCsvFiles());
	}
}
