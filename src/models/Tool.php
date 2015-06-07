<?php
namespace LoLCompanion;

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
}