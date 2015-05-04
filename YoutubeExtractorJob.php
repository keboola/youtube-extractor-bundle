<?php

namespace Keboola\YoutubeExtractorBundle;

use Keboola\ExtractorBundle\Extractor\Jobs\JsonRecursiveJob,
	Keboola\ExtractorBundle\Common\JobConfig;
use	Keboola\Utils\Utils;
use Syrup\ComponentBundle\Exception\SyrupComponentException;
use	Keboola\Google\ClientBundle\Google\RestApi;
use GuzzleHttp\Client as GuzzleClient;
use	Keboola\Code\Builder;

class YoutubeExtractorJob extends JsonRecursiveJob
{
	protected $configName;

	/**
	 * @var RestApi
	 */
	protected $googleClient;

	/**
	 * @var string
	 */
	protected $accessToken = [
		'expires' => 0
	];

	/**
	 * @var GuzzleClient[]
	 */
	protected $guzzleClients;

	/**
	 * @var array
	 */
	protected $parsers;

	/**
	 * @var array
	 */
	protected $attributes;

	/**
	 * @var Builder
	 */
	protected $stringBuilder;

	/**
	 * @brief Return a download request
	 *
	 * @return \GuzzleHttp\Message\Request
	 */
	protected function firstPage()
	{
		$params = (array) Utils::json_decode($this->config["params"]);

		array_walk($params, function(&$value, $key){
			$value = is_scalar($value) ? $value : $this->stringBuilder->run($value, ['attr' => $this->attributes]);
		});

		$url = Utils::buildUrl(trim($this->config["endpoint"], "/"), $params);
var_dump($url);
		$this->configName = preg_replace("/[^A-Za-z0-9\-\._]/", "_", trim($this->config["endpoint"], "/"));

		return $this->client->createRequest("GET", $url)->setHeader('Authorization', $this->getAuthHeader());
	}

	/**
	 * @brief Return a download request OR false if no next page exists
	 *
	 * @param object $response
	 * @param array $data
	 * @return \GuzzleHttp\Message\Request | false
	 */
	protected function nextPage($response, $data)
	{
		return false;
	}

	public function setGoogleClient(RestApi $client)
	{
		$this->googleClient = $client;
	}

	protected function getAuthHeader() {
		// Refresh token if it expires within next 15min
		// (allows a buffer for backoff)
		if ($this->accessToken['expires'] < time() + 15*60) {
			$token = $this->googleClient->refreshToken();
			$this->accessToken['token'] = $token["access_token"];
			$this->accessToken['type'] = $token["token_type"];
			$this->accessToken['expires'] = time() + $token["expires_in"];
		}

		return $this->accessToken['type'] . " " . $this->accessToken['token'];
	}

	/**
	 * Create a child job with current client and parser
	 * @param JobConfig $config
	 * @return static
	 */
	protected function createChild(JobConfig $config)
	{
		$jobs = [
			'analytics' => '\Keboola\YoutubeExtractorBundle\Jobs\AnalyticsJob',
			'data' => '\Keboola\YoutubeExtractorBundle\Jobs\DataJob'
		];
		$job = new $jobs[$config->getConfig()['api']](
			$config,
			$this->guzzleClients[$config->getConfig()['api']],
			$this->parsers[$config->getConfig()['api']]
		);
		$job->setGoogleClient($this->googleClient);
		$job->setClients($this->guzzleClients);
		$job->setParsers($this->parsers);
		return $job;
	}

	/**
	 * @param GuzzleClient[] $clients
	 */
	public function setClients(array $clients)
	{
		$this->guzzleClients = $clients;
	}

	/**
	 * @param GuzzleClient[] $clients
	 */
	public function setParsers(array $parsers)
	{
		$this->parsers = $parsers;
	}

	/**
	 * @param array $attributes
	 */
	public function setAttributes(array $attributes)
	{
		$this->attributes = $attributes;
	}

	/**
	 * @param Builder $builder
	 */
	public function setBuilder(Builder $builder)
	{
		$this->stringBuilder = $builder;
	}
}
