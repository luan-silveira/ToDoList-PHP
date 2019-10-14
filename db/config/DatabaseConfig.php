<?php

class DatabaseConfig {
	
	private static $instance;

	private $config = [];

	private function __construct(){
		$this->config = parse_ini_file('database.ini');
	}

	private static function get(){
		if (empty(self::$instance)){
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __get($value){
		return $this->config[$value];
	}

}