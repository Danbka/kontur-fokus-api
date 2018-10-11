<?php

namespace Danbka\KonturFokusApi\TransportClient;

class TransportClientResponse
{
	private $httpStatus;

	private $headers;

	private $body;

	/**
	 * HttpResponse constructor
	 *
	 * @param int|null $httpStatus
	 * @param array|null $headers
	 * @param string|null $body
	 */
	public function __construct($httpStatus, $headers, $body)
	{
		$this->httpStatus = $httpStatus;
		$this->headers = $headers;
		$this->body = $body;
	}

	/**
	 * @return int|null
	 */
	public function getHttpStatus()
	{
		return $this->httpStatus;
	}

	/**
	 * @return array|null
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * @return string|null
	 */
	public function getBody()
	{
		return $this->body;
	}
}