<?php
namespace LoLCompanion\Model;
use LoLCompanion\Manager\UserManager;
use LoLCompanion\Manager\GameManager;

class User {
    private $connection;
	private $gameId;
	private $teamId;
	private $championIconId;
	private $channel;
	private $isNewInChannel;
	
	public function __construct($conn){
		$this->connection = $conn;
		$this->gameId = -1;
		$this->teamId = -1;
		$this->championIconId = -1;
		$this->channel = null;
		$this->isNewInChannel = false;
	}
	
	
	/**
	* GETTER / SETTERS
	*/
	public function getConnection(){
		return $this->connection;
	}
	
	public function getConnectionID(){
		return $this->connection->resourceId;
	}
	
	public function getGameId(){
		return $this->gameId;
	}
	
	public function getTeamId(){
		return $this->teamId;
	}
	
	public function getChampionIconId(){
		return $this->championIconId;
	}
	
	public function setGameId($gameId){
		$this->gameId = $gameId;
	}
	
	public function setTeamId($teamId){
		$this->teamId = $teamId;
	}
	
	public function setChampionIconId($championIconId){
		$this->championIconId = $championIconId;
	}
	
	public function getChannel(){
		return $this->channel;
	}
	
	public function setChannel($channel){
		$this->channel = $channel;
	}
	
	public function setIsNewInChannel($isNewInChannel){
		$this->isNewInChannel = $isNewInChannel;
	}
	
	public function isNewInChannel(){
		return $this->isNewInChannel;
	}
	
	
	/**
	* FUNCTIONS
	*/
	public function switchToChannel($passphrase){
		if($this->channel != null){
			$this->channel->removeUser($this);
		}
		
		$game = GameManager::findOrCreate($this->gameId);
		$room = $game->getRoom($this->teamId);
		$channel = $room->addUserToChannel($this, $passphrase);
		$this->channel = $channel;
		
		//UserManager::update($this);
	}
	
	public function findAllies($includeSelf = true){
		$allies = array();
		$users = $this->channel->getAllUsers();
		
		if(!$includeSelf){
			foreach($users as $u){
				if($u->getConnectionID() != $this->getConnectionID()){
					$allies[] = $u;
				}
			}
		} else {
			$allies = $users;
		}
		
		return $allies;
	}
}