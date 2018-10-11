<?php

namespace Danbka\KonturFokusApi\TransportClient;

interface TransportClient
{
	/**
	 * @param string $url
	 * @param array $params
	 * @return TransportClientResponse
	 */
	public function get($url, $params = []);

	/**
	 * @param string $url
	 * @param array $params
	 * @return TransportClientResponse
	 */
	public function post($url, $params = []);
}