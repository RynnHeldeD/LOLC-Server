<?php
namespace LoLCompanion\Manager;
use LoLCompanion\Model\Game;

class GameManager {
    public static $games;
	public static $lastCleanDate;
	
	public static function init(){
		self::$games = array();
		self::$lastCleanDate = microtime(true);
	}
	
	public static function findOrCreate($gameId){
		$response = self::getGame($gameId);
		$game = null;
		
		if($response['game'] === null){
			$game = new Game($gameId);
			array_push(self::$games, $game);
		} else {
			$game = $response['game'];
		}
		
		return $game;
	}
	
	public static function getGame($gameId){
		$response = array('game' => null, 'index' => null);
		
		foreach(self::$games as $index => $game){
			if($game->getId() === $gameId){
				$response['game'] = $game;
				$response['index'] = $index;
				break;
			}
		}
		
		return $response;
	}
	
	public static function cleanGames(){
		$now = microtime(true);
		echo "[SERVER] ".count(self::$games)." games running.\r\n";
		echo "[SERVER] Last clean was ". date('Y-m-d H:i:s', self::$lastCleanDate) ."\r\n";
		if(round(($now - self::$lastCleanDate), 0) > 3600){
			echo "[SERVER] Running game cleaner.\r\n";
			self::$lastCleanDate = $now;
			$gamesToClean = array();
			foreach(self::$games as $index => $game){
				if(round(($now - $game->getStartTime()), 0) > 18000){
					$gamesToClean[] = $index;
				}
			}
			
			if(!empty($gamesToClean)){
				echo "[SERVER] Cleaning " . count($gamesToClean) . " games.\r\n";
				foreach($gamesToClean as $index){
					unset(self::$games[$index]);
				}
				self::$games = array_values(self::$games);
				echo "[SERVER] ".count(self::$games)." games running.\r\n";
			} else {
				echo "[SERVER] No game to clean.\r\n";
			}
		}
	}
}