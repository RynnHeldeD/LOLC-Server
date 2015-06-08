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
		$response = UserManager::findByConnection($from);
		
		if($response['user'] !== null && $response['user']->isReady()) {
			$user = $response['user'];
			$action = $jsonMsg['action'];
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
					$response = Tool::checkVariables($jsonMsg, array('timers', 'timestamp'));
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

				default:
					echo "[ERROR] Unsupported message format : " . $msg;
					Message::sendErrorMessage(array($user), $action, $response['message']);
					break;
			}
		} else {
			echo "[ERROR] User is null or not ready (no pickedChampion ?)\r\n";
		}
	}

    public function onClose(ConnectionInterface $conn) {
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
		UserManager::remove($conn);
		$this->clients->detach($conn);			
        $conn->close();
    }
}
