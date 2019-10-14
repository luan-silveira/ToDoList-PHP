<?php

class DB {
    private static $pdo;

    private function __construct() {
    }

    public static function get() {
        if (empty(self::$pdo)) {
            $config = DatabaseConfig::get();
            $port = $config->port ?: 3306;

            self::$pdo = new PDO("mysql:dbname={$config->database};host={$config->host };port={$port}", $config->user, $config->pwd);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$pdo;
	}
	 
	public static function beginTransaction(){
		return self::get()->beginTransaction();
	}

	public static function commit(){
		return self::get()->commit();
	}

	public static function rollback(){
		return self::get()->rollback();
	}

	public static function table($table){
		return (new QueryBuilder)->from($table);
	}
}
