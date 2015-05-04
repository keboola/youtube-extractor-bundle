<?php

namespace Keboola\YoutubeExtractorBundle;

use Keboola\ExtractorBundle\Extractor\Extractors\JsonExtractor as Extractor;
use Syrup\ComponentBundle\Exception\SyrupComponentException,
	Syrup\ComponentBundle\Exception\UserException;
use GuzzleHttp\Client as Client;
use Keboola\YoutubeExtractorBundle\Jobs,
	Keboola\YoutubeExtractorBundle\Parser\YoutubeAnalyticsParser;
use	Keboola\Google\ClientBundle\Google\RestApi;
use	Keboola\Code\Builder;

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

	public function run($config)
	{
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

		$builder = new Builder();

		foreach($config['jobs'] as $jobConfig) {
			$this->saveLastJobTime($jobConfig->getJobId(), "start");
			$startTime = time();

			foreach(['start', 'success', 'error', 'success_startTime'] as $timeAttr) {
				if (empty($config['attributes']['job'][$jobConfig->getJobId()][$timeAttr])) {
					$config['attributes']['job'][$jobConfig->getJobId()][$timeAttr] = date(DATE_W3C, 0);
				}
			}

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
			$job->setAttributes($config['attributes']);
			$job->setBuilder($builder);

			try {
				$job->run();
			} catch(\Exception $e) {
				$this->saveLastJobTime($jobConfig->getJobId(), "error");
				$this->saveLastJobTime(
					$jobConfig->getJobId(),
					"error_startTime",
					date(DATE_W3C, $startTime)
				);
				throw $e;
			}

			$jobTimes[$jobConfig->getJobId()]['success'] = date(DATE_W3C);
			$jobTimes[$jobConfig->getJobId()]['success_startTime'] = date(DATE_W3C, $startTime);

		}

		$this->sapiUpload($this->parser->getCsvFiles());
		$this->sapiUpload($analyticsParser->getCsvFiles());

		foreach($jobTimes as $jobId => $times) {
			$this->saveLastJobTime($jobId, "success", $times['success']);
			$this->saveLastJobTime($jobId, "success_startTime", $times['success_startTime']);
		}
	}
}
