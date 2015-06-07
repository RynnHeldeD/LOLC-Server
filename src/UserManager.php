<?php
namespace LoLCompanion;
use LoLCompanion\User;

class UserManager {
    public static $users;
	
	public static function init(){
		self::$users = array();
	}
	
	public static function add($conn){
		$user = new User($conn);
		array_push(self::$users, $user);
	}
	
	public static function remove($conn){
		$userResponse = self::find($conn);
		$gameResponse = GameManager::getGame($userResponse['user']->gameId);
		$gameResponse['game']->removeUser($userResponse['user']);
	}
	
	public static function update($user){
		$response = self::find($user->getConnection());
		if($response['index'] != -1){
			self::$users[$response['index']] = $user;
		}
	}
	
	public static function find($conn){
		$response = array(
			'user' => null,
			'index' => -1,
		);
		
		foreach(self::$users as $index => $user){
			if($user->getConnectionID() == $conn->resourceId){
				$response['user'] = $user;
				$response['index'] = $index;
				break;
			}
		}
		
		return $response;
	}
	
	public static function debug(){
		foreach(self::$users as $user){
			
			echo "[". $user->getConnectionID() . "] " . $user->nickname;
		}
	}
	
	public static function getANonNewUser($users){
		$user = null;
		
		foreach($users as $u){
			if(!$u->isNewInRoom){
				$user = $u;
				break;
			}
		}
		
		return $user;
	}
}