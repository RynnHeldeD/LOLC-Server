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
		if(count($this->users) == 0){
			$user->isNewInRoom = false;
			array_push($this->users, $user);
		} else {
			$response = $this->getUser($user);
			
			if($response['user'] === null){
				$user->isNewInRoom = true;
				array_push($this->users, $user);
			}
		}
	}
	
	public function removeUser($user){
		$response = $this->getUser($user);
		
		if($response['user'] !== null && $response['index'] !== null){
			unset($this->users[$response['index']]);
			$this->users = array_values($this->users);
		}
	}
	
	public function getUser($user){
		$response = array('user' => null, 'index' => null);
		
		foreach($this->users as $index => $u){
			if($u->getConnectionId() == $user->getConnectionId()){
				$response['user'] = $u;
				$response['index'] = $index;
				break;
			}
		}
		
		return $response;
	}
	
	public function getUsers(){
		return $this->users;
	}
}