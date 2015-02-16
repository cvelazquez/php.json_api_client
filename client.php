<?php
/**
 * Abstract class to generate a client to consume a RESTFUL JSON API
 * that requires only an API KEY (like mandrill)
 **/
namespace API;

class APIException extends \Exception {}

/**
 * Client Class
 *
 * @author Christian Velazquez
 * @link https://github.com/cvelazquez/php.json_api_client
 * @version 0.1
 **/
abstract class Client {

/**
 * @param string $key API KEY
 **/
	private $_apiKey = '';

/**
 * @var string Base URL to consume the API
 **/
	public $base = "http://www.yoursite.com/api/";

/**
 * @param string $key API KEY
 * @throws APIException
 **/
	public function __construct($key){
		if ( empty($key) === true || !is_string($key) ) {
			throw new APIException("Invalid API Key", 1);
		}
		$this->_apiKey = $key;
		if ( substr($this->base, -1) != '/' ) {
			$this->base .= '/';
		}
	}

/**
 * Consume an API's method
 *
 * @param string $endpoint
 * @param array $params
 * @param string $method
 * @return mixed
 **/
	public function call($endpoint = '/controlador/metodo', $params = array(), $method = 'GET') {
		$endpoint = strtolower($endpoint);
		$endpoint = (substr($endpoint, 0,1) != '/') ? '/' . $endpoint : $endpoint;

		$params = array_merge((array)$params, array('key'=>$this->_apiKey));
		$json = json_encode($params, JSON_FORCE_OBJECT);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if ( $method == 'GET' ) {
			if ( empty($params) !== false ) {
				$endpoint .= "?" . http_build_query($params);
			}
		} else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			if ( $method == 'POST' ) {
				curl_setopt($ch, CURLOPT_POST, 1);
			}
			if ( $method != "DELETE" ) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($json)));
			}
		}
		curl_setopt($ch, CURLOPT_URL, "{$this->base}{$endpoint}");
		$result = curl_exec($ch);
		curl_close($ch);

		$decoded = json_decode($result);
		
		return is_null($decoded) ? $result : $decoded;
	}

}
