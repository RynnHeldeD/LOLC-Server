<?php
namespace LoLCompanion;

class Message {
	public static function read($jsonMsg){
		return json_decode($jsonMsg, true);
	}
	
	public static function send($clients, $msg){
		foreach($clients as $client){
			$client->getConnection()->send($msg);
		}
	}
}