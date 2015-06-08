<?php
namespace LoLCompanion\Model;

class Game {
    private $id;
	private $purpleRoom;
	private $blueRoom;
	private $startTime;
	
	public function __construct($gameId){
		$this->id = $gameId;
		$this->purpleRoom = new Room($gameId, Team::$purple);
		$this->blueRoom = new Room($gameId, Team::$blue);
		$this->startTime = microtime(true);
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function getTimestamp(){
		return round((microtime(true) - $this->startTime)*1000, 0);
	}
	
	public function getStartTime(){
		return $this->startTime;
	}
		
	public function getRoom($teamId){
		$room = null;
		
		if($teamId == Team::$purple){
			$room = $this->purpleRoom; 
		} elseif ($teamId == Team::$blue) {
			$room = $this->blueRoom;
		}
		
		return $room;
	}
}