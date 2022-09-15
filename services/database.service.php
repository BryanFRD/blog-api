<?php

class DatabaseService {
  
  public function __construct($table){
    $this->table = $table;
  }
  
  private static $connection = null;
  
  private function connect(){
    if(self::$connection == null){
      $host = 'localhost';
      $port = '3306';
      $dbName = 'db_blog';
      $dsn = "mysql:host=$host;port=$port;dbname=$dbName";
      $user = 'root';
      $pass = '';
      
      try {
        $db_connection = new PDO($dsn, $user, $pass, array(
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ));
      } catch(PDOException $ex){
        die("Erreur de connexion à la base de données : $ex->getMessage()");
      }
      self::$connection = $db_connection;
    }
    
    return self::$connection;
  }
  
  public function query($sql, $params){
    $statment = $this->connect()->prepare($sql);
    $result = $statment->execute($params);
    return (object)['result' => $result, 'statement' => $statment];
  }
  
  public function selectAll(){
    $sql = "SELECT * FROM $this->table WHERE table_schema = ?";
    $resp = $this->query($sql, [0]);
    $rows = $resp->statement->fetchAll(PDO::FETCH_COLUMN);
    
    return $rows;
  }
  
}

?>