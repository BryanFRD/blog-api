<?php 
  
abstract class DatabaseController {
    
    public function __construct($params){
      $id = array_shift($params);
      $this->action = null;
      
      if(isset($id) && !ctype_digit($id)){
        return $this;
      }
      
      $request_body = file_get_contents('php://input');
      $this->body = $request_body ? json_decode($request_body, true) : null;
      
      $this->table = lcfirst(str_replace('Controller', '', get_called_class()));
      
      if($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($id)){
        $this->action = $this->getAll();
      }
      if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($id)){
        $this->action = $this->getOne($id);
      }
      if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $this->action = $this->create();
      }
      if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($id)){
        if($id == 0){
          $this->action = $this->getAllWith($this->body['with']);
        }
        if($id > 0){
          $this->action = $this->getOneWith($id, $this->body['with']);
        }
      }
      if($_SERVER['REQUEST_METHOD'] == 'PUT' && isset($id)){
        $this->action = $this->update($id);
      }
      if($_SERVER['REQUEST_METHOD'] == 'PATCH' && isset($id)){
        $this->action = $this->softDelete($id);
      }
      if($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($id)){
        $this->action = $this->hardDelete($id);
      }
    }
    
    public abstract function affectDataToRow(&$row, $sub_rows);
    
    public function getAll(){
      $dbs = new DatabaseService($this->table);
      $rows = $dbs->selectAll();
      
      return $rows;
    }
    
    public function getAllWith($with){
      $rows = $this->getAll();
      
      foreach($with as $table){
        if(is_array($table)){
          $final_table = key($table);
          $through_table = $table[$final_table];
          
          $dbs = new DatabaseService($through_table);
          $through_table_rows = $dbs->selectWhere();
          
          $dbs = new DatabaseService($final_table);
          $final_table_rows = $dbs->selectAll();
          
          foreach($through_table_rows as $through_table_row){
            $row_to_add = array_filter($final_table_rows,
            function($item) use ($through_table_row, $final_table){
              $prop = 'Id_'.$final_table;
              return $item->{$prop} == $through_table_row->{$prop};
            });
            $through_table_row->$final_table = count($row_to_add) == 1 ? array_pop($row_to_add) : null;
          }
          $sub_rows[$final_table] = $through_table_rows;
          continue;
        }
        
        $dbs = new DatabaseService($table);
        $table_rows = $dbs->selectAll();
        $sub_rows[$table] = $table_rows;
      }
      foreach($rows as $row){
        $this->affectDataToRow($row, $sub_rows);
      }
      
      return $rows;
    }
    
    public function getOne($id){
      $dbs = new DatabaseService($this->table);
      $rows = $dbs->selectOne($id);
      
      return $rows;
    }
    
    public function getOneWith($id, $with){
      $row = $this->getOne($id);
      
      foreach($with as $table){
        if(is_array($table)){
          $final_table = key($table);
          $through_table = $table[$final_table];
          
          $dbs = new DatabaseService($through_table);
          $through_table_rows = $dbs->selectWhere();
          
          $dbs = new DatabaseService($final_table);
          $final_table_rows = $dbs->selectAll();
          
          foreach($through_table_rows as $through_table_row){
            $row_to_add = array_filter($final_table_rows,
              function($item) use ($through_table_row, $final_table){
                $prop = 'Id_'.$final_table;
                return $item->{$prop} == $through_table_row->{$prop};
              });
            $through_table_row->$final_table = count($row_to_add) == 1 ? array_pop($row_to_add) : null;
          }
          $sub_rows[$final_table] = $through_table_rows;
          continue;
        }
        $dbs = new DatabaseService($table);
        $table_rows = $dbs->selectAll();
        $sub_rows[$table] = $table_rows;
      }
      
      $this->affectDataToRow($row, $sub_rows);
      return $row;
    }
    
    public function create(){
      $dbs = new DatabaseService($this->table);
      $row = $dbs->insertOne($this->body);
      return $row;
    }

    public function update($id){
        $dbs = new DatabaseService($this->table);
        if($id != $this->body["Id_$this->table"]){
            return false;
        }
        $row = $dbs->updateOne($this->body);
        return $row;
    }

    public function softDelete($id){
        $dbs = new DatabaseService($this->table);
        $row = $dbs->updateOne(["Id_$this->table" => $id,"is_deleted" => 1]);
        if(isset($row) && $row == false){
            return false;
        }
        return !isset($row);
    }

    public function hardDelete($id){
        $dbs = new DatabaseService($this->table);
        $row = $dbs->deleteOne(["Id_$this->table" => $id]);
        return $row;
    }
    
  }
  
?>