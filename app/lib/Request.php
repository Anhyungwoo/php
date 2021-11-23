<?php

class Request {
	/**
	* 요청 데이터 조회(GET, POST, COOKIE)
	*
	* @param $key - $_REQUEST의 키값 
	* @param $default - $key값에 해당하는 값이 없을 경우 
	*/
	public static function get($key, $default = null) {
		$inputData = json_decode(file_get_contents("PHP://input"), true);
		
		$value = isset($inputData[$key])?$inputData[$key]:null;
		
		$value = $value?$value:$default;

		return $value;
	}
}