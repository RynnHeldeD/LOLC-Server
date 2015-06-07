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
			echo "[SERVER] Sent to (".$client->getConnection()->resourceId.") message : " . $msg . "\r\n";
		}
	}
	
	public static function sendJson($users, $array, $addShareParam = false){
		if($addShareParam){
			$shared = false;
		}
		
		foreach($users as $user){
			if($addShareParam && !$shared && !$user->isNewInRoom){
				$array['share'] = true;
				$shared = true;
			}
			$user->getConnection()->send(json_encode($array));
			echo "[SERVER] Sent to (".$user->getConnection()->resourceId.") message : " . json_encode($array) ."\r\n";
		}
	}
}