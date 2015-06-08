<?php
namespace LoLCompanion\Manager;
use LoLCompanion\Model\User;
use LoLCompanion\Query;

class UserManager {
    public static $users;
	
	public static function init(){
		self::$users = array();
	}
	
	public static function findByConnection($conn){
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
	
	public static function add($conn){
		$response = self::findByConnection($conn);
		
		if($response['user'] === null){
			$user = new User($conn);
			array_push(self::$users, $user);
		}
	}
	
	public static function remove($conn){
		$userResponse = self::findByConnection($conn);
		
		if($userResponse['user'] !== null){
			$user = $userResponse['user'];
			Query::switchChannel($user, array('channel' => ''));
			$gameResponse = GameManager::getGame($user->getGameId());
			if($gameResponse['game'] !== null){
				$game = $gameResponse['game'];
				$room = $game->getRoom($user->getTeamId());
				$channelResponse = $room->findChannel("");
				if($channelResponse['channel'] !== null){
					$channelResponse['channel']->removeUser($user);
				}
			}
			unset(self::$users[$userResponse['index']]);
			self::$users = array_values(self::$users);
		}
	}
	
	public static function update($user){
		$response = self::findByConnection($user->getConnection());
		
		if($response['index'] != -1){
			self::$users[$response['index']] = $user;
		}
	}
	
	public static function getUsersChampionsIconsId($users){
		$championsIconsId = array();
		
		foreach($users as $user){
			if(!in_array($user->getChampionIconId(), $championsIconsId)){
				$championsIconsId[] = $user->getChampionIconId();
			}
		}
		
		return $championsIconsId;
	}
}