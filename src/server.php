<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use LoLCompanion\App;
require ('src/App.php');

    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new App()
            )
        ),
        8080
    );

	try {		
		$server->run();
	} catch(\Exception $e) {
		echo "[ERROR] An error has occurred: {$e->getMessage()}\r\n";
	}
