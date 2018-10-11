<?php

namespace Danbka\KonturFokusApi;

use Danbka\KonturFokusApi\Exceptions\KFApiException;

class KFApiClient
{
	const DEFAULT_FORMAT = 'array';
	const FORMATS = [
		'json',
		'xml',
		'array'
	];
	/**
	 * @var KFApiRequest
	 */
	private $request;

	/**
	 * KFApiClient constructor.
	 * @param string $apiKey
	 * @param array $settings
	 */
	public function __construct($apiKey, $settings = [])
	{
		if (!in_array($settings['format'], static::FORMATS)) {
			$settings['format'] = static::DEFAULT_FORMAT;
		}

		$this->request = new KFApiRequest($apiKey, $settings);
	}

	/**
	 * @param $resource
	 * @param $params
	 * @return mixed
	 * @throws KFApiException
	 */
	public function loadResource($resource, $params)
	{
		return $this->request->loadResource($resource, $params);
	}
}