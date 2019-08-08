<?php
class DBS{
	private $conn = null;
	private $host = 'localhost';
	private $user = 'sql_dczxrbkg';
	private $pwd = 'erohal2017';
	private $db = 'sql_dczxrbkg';
	function __construct(){
		$this->conn = new mysqli($this->host,$this->user,$this->pwd,$this->db);
		$this->conn->query('SET NAMES utf8');
		if(!$this->conn->connect_error){
		    echo $this->conn->connect_error;
		}
	}
	function __destruct(){
		if(isset($conn)){
			$this -> conn -> close();
		}
	}
	public function query($sql){
		return $this -> conn -> query($sql);
	}
	public function close(){
		if(isset($conn)){
			$this -> conn -> close();
			$this -> conn = null;
		}
	}
	public function error(){
		return $this -> conn -> error();
	}
}