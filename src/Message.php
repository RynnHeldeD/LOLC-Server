<?php
namespace LoLCompanion;

class Message {
	public static function read($jsonMsg){
		return json_decode($jsonMsg, true);
	}
	
	public static function send($clients, $action, $errorStatus, $message){
		$msg = '"action":"' . $action . '", "error":'. $errorStatus .', "message":"' . $message . '"';
		foreach($clients as $client){
			$client->getConnection()->send($msg);
		}
	}
	
	public static function sendJson($users, $array){
		foreach($users as $user){;
			$user->getConnection()->send(json_encode($array));
		}
	}
}