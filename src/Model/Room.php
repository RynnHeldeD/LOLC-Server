<?php
namespace LoLCompanion\Model;

class Room {
    private $gameId;
	private $teamId;
	private $channels;
	
	public function __construct($gameId, $teamId){
		$this->gameId = $gameId;
		$this->teamId = $teamId;
		$this->channels = array();
	}
	
	
	/**
	* GETTER / SETTER
	*/
	public function getGameId(){
		return $this->gameId;
	}
	
	public function getTeamId(){
		return $this->teamId;
	}
	
	public function getChannels(){
		return $this->channels;
	}
	
	
	/**
	* FUNCTIONS
	*/
	public function findChannel($passphrase){
		$response = array('channel' => null, 'index' => null);
		
		foreach($this->getChannels() as $index => $ch){
			if($ch->getGameId() == $this->gameId
			&& $ch->getTeamId() == $this->teamId
			&& $ch->getPassphrase() == $passphrase)
			{
				$response['channel'] = $ch;
				$response['index'] = $index;
				break;
			}
		}
		
		return $response;
	}
	
	public function findOrCreateChannel($passphrase = ""){
		$response = $this->findChannel($passphrase);
		
		if($response['channel'] === null){
			$response['channel'] = new Channel($this->gameId, $this->teamId, $passphrase);
			$this->channels[] = $response['channel'];
			$response['index'] = count($this->channels) - 1;
		}
		
		return $response;
	}
	
	
	public function addUserToChannel($user, $passphrase){
		$response = $this->findOrCreateChannel($passphrase);
		
		if($response['channel'] !== null){
			if(count($response['channel']->getAllUsers()) > 0){
				$user->setIsNewInChannel(true);
				} else {
				$user->setIsNewInChannel(false);;
			}
			$response['channel']->addUser($user);
		}
		
		return $response['channel'];
	}
}