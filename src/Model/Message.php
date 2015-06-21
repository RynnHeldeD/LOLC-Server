<?php
namespace LoLCompanion\Model;

use LoLCompanion\Model\Tool;

class Message {
	public static function read($jsonMsg){
		return json_decode($jsonMsg, true);
	}
	
	public static function send($clients, $action, $errorStatus, $message){
		$msg = '"action":"' . $action . '", "error":'. $errorStatus .', "message":"' . $message . '"';
		foreach($clients as $client){
			$client->getConnection()->send($msg);
			Tool::log("On $action : Sent :" . $message, 'server to ('. $client->getConnectionID() .')');
		}
	}
	
	public static function sendJson($users, $params, $addShareParam = false){
		$shared = false;
		foreach($users as $user){
			if($addShareParam && $shared){
				if(isset($params['share'])){
					unset($params['share']);
				}
			}
			
			if($addShareParam && !$shared && !$user->isNewInChannel()){
				$params['share'] = true;
				$shared = true;
			}
			$user->getConnection()->send(json_encode($params));
			Tool::log("On " .$params['action'] . " : Sent :" . json_encode($params), 'server to ('. $user->getConnectionID() .')');
		}
	}
	
	public static function sendErrorMessage($users, $action, $message){
		$msg = '"action":"' . $action . '", "error":true, "message":"' . $message . '"';
		foreach($users as $user){
			$user->getConnection()->send($msg);
			Tool::log("Error on " . $action . " : Sent '" . $message . "' to (" . $user->getConnectionID() .')');
		}
	}
}