<?php
namespace LoLCompanion;
use LoLCompanion\Game;

class GameManager {
    public static $games;
	
	public static function init(){
		self::$games = array();
	}
	
	public static function createIfNotExists($gameId){
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
			if($game->id === $gameId){
				$response['game'] = $game;
				$response['index'] = $index;
				break;
			}
		}
		
		return $response;
	}
	
}