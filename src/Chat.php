<?php
namespace LoLCompanion;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use LoLCompanion\UserManager;
use LoLCompanion\User;
use LoLCompanion\Message;


class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
		UserManager::init();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
		UserManager::add($conn);
		
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
		$jsonMsg = Message::read($msg);
		$response = UserManager::find($from);
		$user = $response['user'];
		
		$action = $jsonMsg['action'];
		switch($action){
			case 'connection':
				if(isset($jsonMsg['nickname']) && !empty($jsonMsg['nickname'])){
					$user->nickname = $jsonMsg['nickname'];
					UserManager::update($user);
					Message::send(
						array($user), 
						'"action":"connection", "status":"200"'
					);
				} else {
					Message::send(
						array($user), 
						'"action":"connection", "status":"400", "message":"Bad Request : Missing \'nickname\' argument."'
					);
				}
				
				break;
			
			default:
				echo "[ERROR] Unsupported message format : " . $msg;
				var_dump($jsonMsg);
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
