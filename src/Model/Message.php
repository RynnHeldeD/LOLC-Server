<?php
namespace LoLCompanion\Model;

class Message {
	public static function read($jsonMsg){
		return json_decode($jsonMsg, true);
	}
	
	public static function send($clients, $action, $errorStatus, $message){
		$msg = '"action":"' . $action . '", "error":'. $errorStatus .', "message":"' . $message . '"';
		foreach($clients as $client){
			$client->getConnection()->send($msg);
			echo "[SERVER] Sent to (".$client->getConnection()->resourceId.") message : " . $msg . "\r\n";
		}
	}
	
	public static function sendJson($users, $params, $addShareParam = false){
		if($addShareParam){
			$shared = false;
		}
		
		foreach($users as $user){
			if($addShareParam && !$shared && !$user->isNewInRoom){
				$params['share'] = true;
				$shared = true;
			}
			$user->getConnection()->send(json_encode($params));
			echo "[SERVER] Sent to (".$user->getConnection()->resourceId.") message : " . json_encode($params) ."\r\n";
		}
	}
	
	public static function sendErrorMessage($users, $action, $message){
		$msg = '"action":"' . $action . '", "error":true, "message":"' . $message . '"';
		foreach($users as $user){
			$user->getConnection()->send($msg);
			echo "[SERVER] Sent to (".$user->getConnection()->resourceId.") message : " . $msg . "\r\n";
		}
	}
}