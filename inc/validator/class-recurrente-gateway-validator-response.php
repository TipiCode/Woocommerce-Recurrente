<?php

/**
 * Payment Gateway class for Recurrente Online
 *
 * @package Abzer
 */
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Recurrente_Gateway_Validator_Response class.
 */
class Recurrente_Gateway_Validator_Response {


	/**
	 * Signed function
	 *
	 * @param type $params Describes what parameters are passing
	 * @return type
	 */
	public function sign( $params, $secretKey) { 
			return $this->signData($this->buildDataToSign($params), $secretKey);
	}

	/**
	 * SignData
	 *
	 * @param type $data
	 * @param type $secretKey
	 * @return type
	 */
	public function signData( $data, $secretKey) {
		return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
	}

	/**
	 * BuildDataToSign
	 *
	 * @param type $params
	 * @return type
	 */
	public function buildDataToSign( $params) {
		$signedFieldNames = explode(',', $params['signed_field_names']);
		foreach ($signedFieldNames as $field) {
			$dataToSign[] = $field . '=' . $params[$field];
		}
		return $this->commaSeparate($dataToSign);
	}

	/**
	 * CommaSeparate
	 *
	 * @param type $dataToSign
	 * @return type
	 */
	public function commaSeparate( $dataToSign) {
		return implode(',', $dataToSign);
	}
}
