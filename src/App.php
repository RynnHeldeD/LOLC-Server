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
		echo "[SERVER] Initializing...\r\n";
        $this->clients = new \SplObjectStorage;
		UserManager::init();
		GameManager::init();
		echo "[SERVER] Done initializing.\r\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
		UserManager::add($conn);
		
        echo "[SERVER] New connection! ({$conn->resourceId})\r\nWaiting for client information (pickedChampion)\r\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
		echo "[CLIENT] (".$from->resourceId.") Incoming query :\r\n" . $msg . "\r\n";
		$jsonMsg = Message::read($msg);
		$response = UserManager::find($from);
		$user = $response['user'];
		
		$action = $jsonMsg['action'];
		try {
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
					$allies = $game->getUsersFromRoom($user);
					
					// Response
					// Send to new player his allies
					Message::sendJSON(
						array($user), 
						array(
							'action' => 'playerList',
							'error' => false,
							'allies' => User::getUsersChampionsIconsId($allies),
							'timestamp' => $game->getTimestamp()
						)
					);
					
					// Send his allies an update and to one non-new ally the order to share his timers
					$allies = $game->getUsersFromRoom($user, true);
					$oldAlly = UserManager::getANonNewUser($allies);
					$allies = $game->getUsersFromRoom($oldAlly, true)
					Message::sendJSON(
						$allies, 
						array(
							'action' => 'playerList_toNewAllies',
							'error' => false,
							'allies' => User::getUsersChampionsIconsId($allies)
						)
					);
					
					Message::sendJSON(
						$allies, 
						array(
							'action' => 'playerList_toNewAllies',
							'error' => false,
							'allies' => User::getUsersChampionsIconsId($allies),
							'share' => true
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
			
			case "sentTimers":
				$response = Tool::checkVariables($jsonMsg, array('timers', 'timestamp'));
				if($response['error'] === false){
					$response = GameManager::getGame($user->gameId);
					$allies = $response['game']->getNewUsersFromRoom($user, true);
					
					// Response
					Message::sendJSON(
						$allies, 
						array(
							'action' => 'sharedTimers',
							'error' => false,
							'timers' => $jsonMsg['timers'],
							'timestamp' => $jsonMsg['timestamp']
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
					$allies = $response['game']->getUsersFromRoom($user, true);
					
					// Response
					Message::sendJSON(
						$allies, 
						array(
							'action' => 'timer',
							'error' => false,
							'idSortGrille' => $jsonMsg['idSortGrille'],
							'timestampDeclenchement' => $jsonMsg['timestampDeclenchement']
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
				$response = Tool::checkVariables($jsonMsg, array('channel'));
				if($response['error'] === false){
					$response = GameManager::getGame($user->gameId);
					$oldAllies = $response['game']->getUsersFromRoom($user, true);
					Message::sendJSON(
						$oldAllies, 
						array(
							'action' => 'playerList_toOldAllies',
							'error' => false,
							'allies' => User::getUsersChampionsIconsId($oldAllies)
						)
					);
					
					$user->passphrase = $jsonMsg['channel'];
					UserManager::update($user);
					$newAllies = $response['game']->getUsersFromRoom($user);
					
					Message::sendJSON(
						$newAllies, 
						array(
							'action' => 'playerList_toNewAllies',
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
					$allies = $response['game']->getUsersFromRoom($user, true);
					
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
				
			case 'razTimer':
				$response = Tool::checkVariables($jsonMsg, array('idSortGrille', 'timestampDeclenchement'));
				if($response['error'] === false){
					$response = GameManager::getGame($user->gameId);
					$allies = $response['game']->getUsersFromRoom($user, true);
					
					// Response
					Message::sendJSON(
						$allies, 
						array(
							'action' => 'razTimer',
							'error' => false,
							'idSortGrille' => $jsonMsg['idSortGrille'],
							'timestampDeclenchement' => $jsonMsg['timestampDeclenchement']
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

			case 'stopTimer':
				$response = Tool::checkVariables($jsonMsg, array('idSortGrille'));
				if($response['error'] === false){
					$response = GameManager::getGame($user->gameId);
					$allies = $response['game']->getUsersFromRoom($user, true);

					// Response
					Message::sendJSON(
						$allies,
						array(
							'action' => 'stopTimer',
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
		} catch (\Exception $e){
			echo "[ERROR] An error has occurred: {$e->getMessage()}\r\n";
		}
	}

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
		try {			
			UserManager::remove($conn);
			$this->clients->detach($conn);			
			echo "[SERVER] Connection {$conn->resourceId} has disconnected\r\n";
		} catch (\Exception $e){
			echo "[ERROR] An error has occurred: {$e->getMessage()}\r\n";
		}
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "[ERROR] An error has occurred: {$e->getMessage()}\r\n";

        $conn->close();
    }
}
