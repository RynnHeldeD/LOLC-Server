<?php
namespace LoLCompanion;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use LoLCompanion\Query;
use LoLCompanion\Manager\UserManager;
use LoLCompanion\Manager\GameManager;
use LoLCompanion\Model\User;
use LoLCompanion\Model\Message;
use LoLCompanion\Model\Tool;


class App implements MessageComponentInterface {
    protected $clients;
	protected $games;

    public function __construct() {
		Tool::log("Initializing...");
        $this->clients = new \SplObjectStorage;
		UserManager::init();
		GameManager::init();
		Tool::log("Done initializing.");
    }

    public function onOpen(ConnectionInterface $conn) {
        Tool::log("New connection! ({$conn->resourceId})\r\nWaiting for client information (pickedChampion)");
        
		$this->clients->attach($conn);
		UserManager::addPendingUser($conn);
		Tool::log("Actually " . count(UserManager::$users) . ' players connected and '. count(UserManager::$pendingUsers) .' pending players.');
    }

    public function onMessage(ConnectionInterface $from, $msg) {
		Tool::log('(' . $from->resourceId . ") Incoming query : \r\n" . $msg, 'client');
		
		$jsonMsg = Message::read($msg);
		$response = UserManager::findPendingUser($from);
		
		if($response['user'] === null){
			$response = UserManager::findByConnection($from);
		}
		
		if($response['user'] !== null){
			$user = $response['user'];
			$action = $jsonMsg['action'];
			
			if($action == 'pickedChampion' || $user->isReady()) {
				switch($action){
					case 'pickedChampion':
						$response = Tool::checkVariables($jsonMsg, array('gameId', 'teamId', 'championIconId', 'passphrase'));
						if($response['error'] === false){
							Query::pickedChampion($user, $jsonMsg);
						} else {
							Message::sendErrorMessage(array($user), $action, $response['message']);
						}
						break;
					
					case "sentTimers":
						$response = Tool::checkVariables($jsonMsg, array('timers', 'cdr', 'ultiLevel', 'timestamp'));
						if($response['error'] === false){
							Query::sentTimers($user, $jsonMsg);
						} else {
							Message::sendErrorMessage(array($user), $action, $response['message']);
						}
						break;
					
					case 'timerActivation':
						$response = Tool::checkVariables($jsonMsg, array('idSortGrille', 'timestampDeclenchement'));
						if($response['error'] === false){
							Query::timerActivation($user, $jsonMsg);
						} else {
							Message::sendErrorMessage(array($user), $action, $response['message']);
						}
						break;
					
					case 'switchChannel':
						$response = Tool::checkVariables($jsonMsg, array('channel'));
						if($response['error'] === false){
							Query::switchChannel($user, $jsonMsg);
						} else {
							Message::sendErrorMessage(array($user), $action, $response['message']);
						}
						break;
						
					case 'timerDelay':
						$response = Tool::checkVariables($jsonMsg, array('idSortGrille'));
						if($response['error'] === false){
							Query::timerDelay($user, $jsonMsg);
						} else {
							Message::sendErrorMessage(array($user), $action, $response['message']);
						}
						break;
						
					case 'stopTimer':
						$response = Tool::checkVariables($jsonMsg, array('idSortGrille'));
						if($response['error'] === false){
							Query::stopTimer($user, $jsonMsg);
						} else {
							Message::sendErrorMessage(array($user), $action, $response['message']);
						}
						break;
						
					case 'sentCooldown':
						$response = Tool::checkVariables($jsonMsg, array('champUlti', 'cdr'));
						if($response['error'] === false){
							Query::sentCooldown($user, $jsonMsg);
						} else {
							Message::sendErrorMessage(array($user), $action, $response['message']);
						}
						break;
						
					case 'shareUltimateLevel':
						$response = Tool::checkVariables($jsonMsg, array('buttonId', 'ultiLevel'));
						if($response['error'] === false){
							Query::shareUltimateLevel($user, $jsonMsg);
						} else {
							Message::sendErrorMessage(array($user), $action, $response['message']);
						}
						break;

					default:
						Message::sendErrorMessage(array($user), $action, "No such action defined or JSON broken.");
						break;
				}
			} else {
				Tool::log('(' . $user->getConnectionID() . ") User is null or not ready (no pickedChampion ?). Requesting champion.", 'error');
				Message::sendJSON(
					array($user), 
					array(
						'action' => 'requestChampion',
						'error' => false,
					)
				);
			}
		}
	}

    public function onClose(ConnectionInterface $conn) {
		try {			
			Tool::log('Connection {'.$conn->resourceId.'} has disconnected', 'client');
			UserManager::remove($conn);
			$this->clients->detach($conn);			
			GameManager::cleanGames();
			Tool::log("Actually " . count(UserManager::$users) . ' players connected and '. count(UserManager::$pendingUsers) .' pending players.');
		} catch (\Exception $e){
			Tool::log("An error has occurred on closing connection : {$e->getMessage()}", 'error');
		}
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        Tool::log("An general error has occurred: {$e->getMessage()}", 'error');
		UserManager::remove($conn);
		$this->clients->detach($conn);			
        $conn->close();
		Tool::log("Actually " . count(UserManager::$users) . ' players connected and '. count(UserManager::$pendingUsers) .' pending players.');
    }
}
