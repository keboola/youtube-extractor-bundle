<?php

namespace Keboola\YoutubeExtractorBundle;

use Keboola\ExtractorBundle\Extractor\Jobs\JsonJob;
use	Keboola\Utils\Utils;
use Syrup\ComponentBundle\Exception\SyrupComponentException;
use	Keboola\Google\ClientBundle\Google\RestApi;

class YoutubeExtractorJob extends JsonJob
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
	 * @brief Return a download request
	 *
	 * @return \GuzzleHttp\Message\Request
	 */
	protected function firstPage()
	{
		$params = Utils::json_decode($this->config["params"], true);
		$url = Utils::buildUrl(trim($this->config["endpoint"], "/"), $params);

		$this->configName = preg_replace("/[^A-Za-z0-9\-\._]/", "_", trim($this->config["endpoint"], "/"));

		return $this->client->createRequest("GET", $url)->setHeader('Authorization', $this->getAuthHeader());
	}

	/**
	 * @brief Return a download request OR false if no next page exists
	 *
	 * @param $response
	 * @return \Keboola\ExtractorBundle\Client\SoapRequest | \GuzzleHttp\Message\Request | false
	 */
	protected function nextPage($response, $data)
	{
		/* TODO
		if (empty($response->pagination->next_url)) {
			return false;
		}

		return $this->client->createRequest("GET", $response->pagination->next_url);
		*/
		return false;
	}

	public function setGoogleClient(RestApi $client)
	{
		$this->googleClient = $client;
	}

	protected function getAuthHeader() {
		// Refresh token if it expires within next 15min
		// (allows a buffer for backoff)
		if ($this->accessToken['expires'] < $time() + 15*60) {
			$token = $this->googleClient->refreshToken();
			$this->accessToken['token'] = $token["access_token"];
			$this->accessToken['type'] = $token["token_type"];
			$this->accessToken['expires'] = time() + $token["expires_in"];
		}

		return $this->accessToken['type'] . " " . $this->accessToken['token'];
	}

}
