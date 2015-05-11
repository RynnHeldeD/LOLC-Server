<?php
namespace LoLCompanion;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use LoLCompanion\UserManager;
use LoLCompanion\User;
use LoLCompanion\Message;


class App implements MessageComponentInterface {
    protected $clients;
	protected $games;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
		UserManager::init();
		GameManager::init();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
		UserManager::add($conn);
		
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
		echo "\r\n[GET] " . $msg . "\r\n";
		$jsonMsg = Message::read($msg);
		$response = UserManager::find($from);
		$user = $response['user'];
		
		$action = $jsonMsg['action'];
		switch($action){
			case 'pickedChampion':
				$response = Tool::checkVariables($jsonMsg, array('gameId', 'teamId', 'championIconId', 'passphrase'));
				if($response['error'] === false){
					// User
					$user->requestPickedChampion($jsonMsg['gameId'], $jsonMsg['teamId'], $jsonMsg['championIconId'], $jsonMsg['passphrase']);
					UserManager::update($user);
					
					// Game
					$game = GameManager::createIfNotExists($user->gameId);
					$game->addUserToRoom($user, $user->teamId);
					$allies = $game->getUsersFromRoom($user->teamId, $user->passphrase);
					
					// Response
					Message::sendJSON(
						$allies, 
						array(
							'action' => 'playerList',
							'error' => false,
							'allies' => User::getUsersChampionsIconsId($allies)
						)
					);
				} else {
					Message::send(
						array($user), 
						$action,
						true,
						'Bad Request : ' . $response['message']
					);
				}
				break;
			
			case 'timerActivation':
				$response = Tool::checkVariables($jsonMsg, array('idSortGrille', 'timestampDeclenchement'));
				if($response['error'] === false){
					$response = GameManager::getGame($user->gameId);
					$allies = $response['game']->getUsersFromRoom($user->teamId, $user->passphrase, $user);
					
					// Response
					Message::sendJSON(
						$allies, 
						array(
							'action' => 'timer',
							'error' => false,
							'idSortGrille' => $jsonMsg['idSortGrille']
						)
					);
				} else {
					Message::send(
						array($user), 
						$action,
						true,
						'Bad Request : ' . $response['message']
					);
				}
				break;
				
			case 'switchChannel':
				$response = Tool::checkVariables($jsonMsg, array('channel', 'oldChannel'));
				if($response['error'] === false){
					$response = GameManager::getGame($user->gameId);
					$oldAllies = $response['game']->getUsersFromRoom($user->teamId, $user->passphrase, $user);
					$user->passphrase = $jsonMsg['channel'];
					UserManager::update($user);
					$newAllies = $response['game']->getUsersFromRoom($user->teamId, $user->passphrase);
					
					Message::sendJSON(
						$oldAllies, 
						array(
							'action' => 'playerList',
							'error' => false,
							'allies' => User::getUsersChampionsIconsId($oldAllies)
						)
					);
					
					Message::sendJSON(
						$newAllies, 
						array(
							'action' => 'playerList',
							'error' => false,
							'allies' => User::getUsersChampionsIconsId($newAllies)
						)
					);
				} else {
					Message::send(
						array($user), 
						$action,
						true,
						'Bad Request : ' . $response['message']
					);
				}
				break;
				
			case 'timerDelay':
				$response = Tool::checkVariables($jsonMsg, array('idSortGrille'));
				if($response['error'] === false){
					$response = GameManager::getGame($user->gameId);
					$allies = $response['game']->getUsersFromRoom($user->teamId, $user->passphrase, $user);
					
					// Response
					Message::sendJSON(
						$allies, 
						array(
							'action' => 'timerDelay',
							'error' => false,
							'idSortGrille' => $jsonMsg['idSortGrille']
						)
					);
				} else {
					Message::send(
						array($user), 
						$action,
						true,
						'Bad Request : ' . $response['message']
					);
				}
				break;
				
			case 'RaZTimer':
				$response = Tool::checkVariables($jsonMsg, array('idSortGrille'));
				if($response['error'] === false){
					$response = GameManager::getGame($user->gameId);
					$allies = $response['game']->getUsersFromRoom($user->teamId, $user->passphrase, $user);
					
					// Response
					Message::sendJSON(
						$allies, 
						array(
							'action' => 'RaZTimer',
							'error' => false,
							'idSortGrille' => $jsonMsg['idSortGrille']
						)
					);
				} else {
					Message::send(
						array($user), 
						$action,
						true,
						'Bad Request : ' . $response['message']
					);
				}
				break;
			
			default:
				echo "[ERROR] Unsupported message format : " . $msg;
				Message::send(
						array($user), 
						$action,
						true,
						'Bad Request : Unsupported message format : ' . $msg
					);
				break;
		}
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
