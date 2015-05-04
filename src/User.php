<?php
namespace LoLCompanion;

class User {
    protected $connection;
	public $nickname;
	
	public function __construct($conn){
		$this->connection = $conn;
		$this->nickname = "Unknown";
	}
	
	public function getConnectionID(){
		return $this->connection->resourceId;
	}
	
	public function getConnection(){
		return $this->connection;
	}
}