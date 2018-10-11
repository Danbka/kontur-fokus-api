<?php

namespace Danbka\KonturFokusApi\TransportClient\Curl;

use Danbka\KonturFokusApi\TransportClient\TransportClientResponse;
use Danbka\KonturFokusApi\TransportClient\TransportRequestException;

class CurlHttpClient
{
	const QUESTION_SIGN = '?';

	/**
	 * @var array
	 */
	protected $curlOpts;

	/**
	 * CurlHttpClient constructor.
	 * @param int $timeout
	 */
	public function __construct($timeout)
	{
		$this->curlOpts = [
			CURLOPT_HEADER => true,
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_RETURNTRANSFER => true
		];
	}

	/**
	 * Makes get request
	 *
	 * @param string $url
	 * @param array|null $fields
	 *
	 * @return TransportClientResponse
	 * @throws TransportRequestException
	 */
	public function get($url, $fields = null)
	{
		return $this->sendRequest(
			$url . static::QUESTION_SIGN . http_build_query($fields),
			[]
		);
	}

	/**
	 * Makes post request
	 *
	 * @param string $url
	 * @param array|null $fields
	 *
	 * @return TransportClientResponse
	 * @throws TransportRequestException
	 */
	public function post($url, $fields = null)
	{
		return $this->sendRequest(
			$url,
			[
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $fields
			]
		);
	}

	/**
	 * Makes and sends request
	 *
	 * @param string $url
	 * @param array $opts
	 *
	 * @return TransportClientResponse
	 * @throws TransportRequestException
	 */
	private function sendRequest($url, $opts)
	{
		$curl = curl_init($url);

		$opts[CURLOPT_PROXY] = 'gillian.vi.ci';
		$opts[CURLOPT_PROXYPORT] = '3128';

		curl_setopt_array($curl, $this->curlOpts + $opts);

		$response = curl_exec($curl);

		$curl_error_code = curl_errno($curl);
		$curl_error = curl_error($curl);

		curl_close($curl);

		if ($curl_error || $curl_error_code) {
			$error_msg = "Failed curl request. Curl error {$curl_error_code}";
			if ($curl_error) {
				$error_msg .= ": {$curl_error}";
			}
			$error_msg .= '.';
			throw new TransportRequestException($error_msg);
		}

		return $this->parseRawResponse($response);
	}

	/**
	 * Breaks the raw response down into its headers, body and http status code
	 * Разбивает исходный ответ на заголовки тело и статус
	 *
	 * @param string $response
	 *
	 * @return TransportClientResponse
	 */
	private function parseRawResponse($response)
	{
		list($raw_headers, $body) = $this->extractResponseHeadersAndBody($response);
		list($http_status, $headers) = $this->getHeaders($raw_headers);
		return new TransportClientResponse($http_status, $headers, $body);
	}

	/**
	 * Extracts the headers and the body into a two-part array:
	 * 		0: headers
	 * 		1: body
	 * Извлекает заголовки и тело запроса и формирует массив из 2-х элементов:
	 * 		0: заголовки ответа
	 * 		1: тело ответа
	 *
	 * @param string $response
	 *
	 * @return array
	 */
	private function extractResponseHeadersAndBody($response)
	{
		$parts = explode("\r\n\r\n", $response);
		$raw_body = array_pop($parts);
		$raw_headers = implode("\r\n\r\n", $parts);
		return [trim($raw_headers), trim($raw_body)];
	}

	/**
	 * Parses the raw headers and sets as an array:
	 * 		0: http status
	 * 		1: headers
	 * Парсит исходные заголовки и формирует массив:
	 * 		0: http статус
	 * 		1: заголовки
	 *
	 * @param $raw_headers
	 *
	 * @return array
	 */
	protected function getHeaders($raw_headers)
	{
		$raw_headers = str_replace("\r\n", "\n", $raw_headers);

		$header_collection = explode("\n\n", trim($raw_headers));

		$raw_header = array_pop($header_collection);
		$header_components = explode("\n", $raw_header);
		$result = [];
		$http_status = 0;
		foreach ($header_components as $line) {
			if (strpos($line, ': ') === false) {
				$http_status = $this->getHttpStatus($line);
			} else {
				list($key, $value) = explode(': ', $line, 2);
				$result[$key] = $value;
			}
		}
		return [$http_status, $result];
	}

	/**
	 * Sets the HTTP response code from a raw header
	 * Возвращает код ответа из исходного заголовка
	 *
	 * @param string $raw_response_header
	 * @return int
	 */
	protected function getHttpStatus($raw_response_header)
	{
		preg_match('|HTTP/\d\.\d\s+(\d+)\s+.*|', $raw_response_header, $match);
		return (int) $match[1];
	}
}