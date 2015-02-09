<?php

namespace Keboola\YoutubeExtractorBundle;

use Keboola\ExtractorBundle\Extractor\Extractors\JsonExtractor as Extractor;
use Syrup\ComponentBundle\Exception\SyrupComponentException,
	Syrup\ComponentBundle\Exception\UserException;
use GuzzleHttp\Client as Client;
use Keboola\YoutubeExtractorBundle\Jobs,
	Keboola\YoutubeExtractorBundle\Parser\YoutubeAnalyticsParser;
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
		$clients['analytics'] = new Client([
			'base_url' => 'https://www.googleapis.com/youtube/analytics/v1/',
			'defaults' => [
				'headers' => [
					'Accept' => 'application/json'
				]
			]
		]);

		$clients['data'] = new Client([
			'base_url' => 'https://www.googleapis.com/youtube/v3/',
			'defaults' => [
				'headers' => [
					'Accept' => 'application/json'
				]
			]
		]);

		$clientId = $this->apiKeys['client-id'];
		$clientSecret = $this->apiKeys['client-secret'];
		$restApi = new RestApi($clientId, $clientSecret, null, $config['attributes']['oauth']['refresh_token']);

		$analyticsParser = new YoutubeAnalyticsParser($this->getTemp());

		foreach($config['jobs'] as $jobConfig) {
			switch ($jobConfig->getConfig()['api']) {
				case 'data':
					$job = new Jobs\DataJob($jobConfig, $clients['data'], $this->parser);
					break;
				case 'analytics':
					$job = new Jobs\AnalyticsJob($jobConfig, $clients['analytics'], $analyticsParser);
					break;
				default:
					throw new UserException('API must be one of [data,analytics]. ' . $jobConfig->getConfig()['api'] . ' configured');
					break;
			}

			$job->setParsers(['data' => $this->parser, 'analytics' => $analyticsParser]);
			$job->setClients($clients);
			$job->setGoogleClient($restApi);
			$job->run();
		}

		$this->sapiUpload($this->parser->getCsvFiles());
		$this->sapiUpload($analyticsParser->getCsvFiles());
	}
}
