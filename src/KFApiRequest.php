<?php

namespace Danbka\KonturFokusApi;

use Danbka\KonturFokusApi\Exceptions\KFApiException;
use Danbka\KonturFokusApi\TransportClient\Curl\CurlHttpClient;
use Danbka\KonturFokusApi\TransportClient\TransportClientResponse;
use Danbka\KonturFokusApi\TransportClient\TransportRequestException;

class KFApiRequest
{
	const API_HOST = 'https://focus-api.kontur.ru/api3';
	const API_KEY_PARAM = 'key';
	const API_XML_PARAM = 'xml';
	const HTTP_STATUS_CODE_OK = 200;
	const API_POST_METHODS = [
		'monList',
		'lists/import'
	];

	private $apiKey;

	private $apiHost;

	private $httpClient;

	private $settings;

	/**
	 * KFApiRequest constructor
	 *
	 * @param string $apiKey
	 * @param array $settings
	 */
	public function __construct($apiKey, $settings)
	{
		$this->apiKey = $apiKey;
		$this->apiHost = static::API_HOST;
		$this->httpClient = new CurlHttpClient(10);
		$this->settings = $settings;
	}
	
	/**
	 * Makes get request
	 *
	 * @param string $resource
	 * @param array $params
	 *
	 * @return mixed
	 * @throws KFApiException
	 */
	public function loadResource($resource, $params = [])
	{
		$params = $this->formatParams($params);

		$params[static::API_KEY_PARAM] = $this->apiKey;

		if ($this->settings['format'] == 'xml') {
			$params[static::API_XML_PARAM] = 1;
		}

		$url = $this->apiHost . '/' . $resource;
		try {
			if (in_array($resource, static::API_POST_METHODS)) {
				$response = $this->httpClient->post($url, $params);
			} else {
				$response = $this->httpClient->get($url, $params);
			}

		} catch (TransportRequestException $exception) {
			throw new KFApiException($exception);
		}

		return $this->parseResponse($response);
	}

	/**
	 * Formats given array of parameters for making the request
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	private function formatParams($params)
	{
		foreach ($params as $key => $param) {
			if (is_array($param)) {
				$params[$key] = implode(',', $param);
			}
		}

		return $params;
	}

	/**
	 * Decodes the response and checks its status code and whether it has an Api error. Returns decoded response.
	 *
	 * @param TransportClientResponse $response
	 *
	 * @return mixed
	 * @throws KFApiException
	 */
	private function parseResponse($response) {
		$this->checkHttpStatus($response);

		$body = $response->getBody();
		if ($this->settings['format'] == 'array') {
			$body = $this->decodeBody($body);
		}

		return $body;
	}

	/**
	 * Decodes body
	 *
	 * @param string $body
	 *
	 * @return mixed
	 */
	private function decodeBody($body) {
		$decoded_body = json_decode($body, true);
		if ($decoded_body === null || !is_array($decoded_body)) {
			$decoded_body = [];
		}
		return $decoded_body;
	}

	/**
	 * Checks Http status
	 *
	 * @param TransportClientResponse $response
	 *
	 * @throws KFApiException
	 */
	private function checkHttpStatus($response) {
		if ($response->getHttpStatus() != static::HTTP_STATUS_CODE_OK) {
			throw new KFApiException("Invalid http status: {$response->getHttpStatus()}.");
		}
	}
}