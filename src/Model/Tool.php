<?php
namespace LoLCompanion\Model;

class Tool {
	public static function checkVariables($array, $variablesNames){
		$response = array(
			'error' => false, 
			'message' => ''
		);
		
		foreach($variablesNames as $var){
			if(!isset($array[$var])){
				$response['error'] = true;
				$response['message'] = "'$var' " . 'argument is missing.';
				break;
			}
		}
		
		return $response;
	}
	
	public static function log($message, $origin = "server" ){
		echo '['.date('Y-m-d-H:i:s') . '] ' . "[" . strtoupper($origin) . "] " . $message . "\r\n";
	}
}