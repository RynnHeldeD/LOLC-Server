<?php
namespace LoLCompanion;

class Room {
    public $gameId;
	public $teamId;
	public $users;
	
	public function __construct($gameId, $teamId){
		$this->gameId = $gameId;
		$this->teamId = $teamId;
		$this->users = array();
	}
	
	public function addUser($user){
		$response = self::getUser($user);
		
		if($response['user'] === null){
			array_push($this->users, $user);
		}
	}
	
	public function removeUser($user){
		$response = self::getUser($user);
		
		if($response['user'] !== null && $response['index'] !== null){
			unset($this->users[$response['index']]);
			$this->users = array_values($this->users);
		}
	}
	
	public static function getUser($user){
		$response = array('user' => null, 'index' => null);
		
		foreach(self::$users as $index => $u){
			if($u->getConnectionId() === $user->getConnectionId()){
				$response['user'] = $u;
				$response['index'] = $index;
				break;
			}
		}
		
		return $response;
	}
}