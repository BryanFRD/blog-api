<?php

class DatabaseService {
  
  public function __construct($table){
    $this->table = $table;
  }
  
  private static $connection = null;
  
  private function connect(){
    if(self::$connection == null){
      $dbConfig = $_ENV['config']->db;
      
      $host = $dbConfig->host;
      $port = $dbConfig->port;
      $dbName = $dbConfig->dbName;
      $dsn = "mysql:host=$host;port=$port;dbname=$dbName";
      $user = $dbConfig->user;
      $pass = $dbConfig->pass;
      
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
    return (object)['result' => $result, 'statment' => $statment];
  }
  
  public function getColumns(){
    $sql = "DESCRIBE $this->table";
    $resp = $this->query($sql, []);
    $columns = $resp->statment->fetchAll(PDO::FETCH_COLUMN);
  }
  
  public function getTables(){
    $dbs = new DatabaseService(null);
    $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = ?";
    $resp = $dbs->query($sql, [$_ENV['config']->db->dbName]);
    $tables = $resp->statment->fetchAll(PDO::FETCH_COLUMN);
    
    return $tables
  }  
  public function selectAll(){
    $sql = "SELECT * FROM $this->table WHERE is_deleted = ?";
    $resp = $this->query($sql, [0]);
    $rows = $resp->statment->fetchAll(PDO::FETCH_CLASS);
    
    return $rows;
  }
  public function selectOne($id){
    $sql = "SELECT * FROM $this->table WHERE is_deleted = ? AND Id_$this->table = ?";
    $resp = $this->query($sql, [0, $id]);
    $rows = $resp->statment->fetchAll(PDO::FETCH_CLASS);
    
    $row = $resp->result && count($rows) == 1 ? $rows[0] : null;
    
    return $row;
  }
  
  public function selectWhere($where = null){
    $sql = "SELECT * FROM $this->table".(isset($where) ?? " WHERE $where").";";
    $resp = $this->query($sql, [0]);
    $rows = $resp->statment->fetchAll(PDO::FETCH_CLASS);
    
    return $rows;
  }
  
  public function insertOne($body = []){
    $columns = "";
    $values = "";
    
    foreach($body as $key => $value){
      $columns .= $key . ",";
      $values .= "?,";
    }
    
    
    $sql = "INSERT INTO $this->table (".trim($columns, ',').") VALUES (".trim($values, ',').")";
    $resp = $this->query($sql, array_values($body));
    $row = $resp->statment;
    
    return $row;
  }
  
  public function update($body){
    $set = "";
    $valuesToBind = array();
    $id = $body["Id_$this->table"];
    
    if(isset($body["Id_$this->table"])){
      unset($body["Id_$this->table"]);
    }
    
    foreach($body as $key => $value){
      $set .= $k."=?,";
      array_push($valuesToBind, $v);
    }
    
    $set = trim($set, ",");
    $where = "Id_$this->table = ?";
    array_push($valuesToBind, $id);
    
    $sql = "UPDATE $this->table SET $set WHERE $where";
    $resp = $this->query($sql, $valuesToBind);
    
    if($resp->result){
      $row = $this->selectOne($id);
      return $row;
    }
    
    return false;
  }
  
  public function deleteOne($body){
    $id = $body["Id_$this->table"];
    $where = "Id_$this->table = ?";
    
    $sql = "DELETE FROM $this->table WHERE $where";
    $resp = $this->query($sql, [$id]);
    
    if($resp->result && $resp->statment->rowCount() <= 1){
      $row = $this->selectOne($id);
      return !isset($row);
    }
    
    return false;
  }
  
}

?>