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
		GameManage::init();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
		UserManager::add($conn);
		
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
		echo "[DEBUG] " . $msg;
		$jsonMsg = Message::read($msg);
		$response = UserManager::find($from);
		$user = $response['user'];
		
		$action = $jsonMsg['action'];
		switch($action){
			case 'pickedChampion':
				$response = Tool::checkVariables($jsonMsg, array('gameId', 'teamId', 'championIconId', 'passphrase'));
				if($response['error'] === false){
					$user->requestPickedChampion($jsonMsg['gameId'], $jsonMsg['teamId'], $jsonMsg['championIconId'], $jsonMsg['passphrase']);
					UserManager::update($user);
					$game = GameManager::createIfNotExists($jsonMsg['gameId']);
					$game->addUserToRoom($user, $json['teamId']);
					
					Message::sendJSON(
						array($user), 
						array(
							'action' => 'firstMessage',
							'error' => false,
							'allies' => array(1, 2, 3)
						)
					);
				} else {
					Message::send(
						array($user), 
						'pickedChampion',
						true,
						'Bad Request : ' . $response['message']
					);
				}
				break;
				
			default:
				echo "[ERROR] Unsupported message format : " . $msg;
				break;
		}
		
		/*
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }
        }
		*/
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
