<?php

class ThemeController {
  
  public function __construct($params){
    $id = array_shift($params);
    $this->action = null;
    
    if(isset($id) && !ctype_digit($id))
      return $this;
    
    $request_body = file_get_contents('php://input');
    $this->body = $request_body ? json_decode($request_body, true) : null;
      
    if($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($id)){
      $this->action = $this->getAll();
    }
    if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($id)){
      $this->action = $this->getOne($id);
    }
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $this->action = $this->create();
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
  
  public function getAll(){
    $dbs = new DatabaseService('theme');
    $rows = $dbs->selectAll();
    
    return $rows;
  }
  
  public function getOne($id){
    $dbs = new DatabaseService('theme');
    $rows = $dbs->selectOne($id);
    
    return $rows;
  }
  
  public function create(){
    return 'Insert a new row in table theme : ' . urldecode(http_build_query($this->body, '', ', '));
  }
  
  public function update($id){
    return "Update row with id = $id in table theme : " . urldecode(http_build_query($this->body, '', ', '));
  }
  
  public function softDelete($id){
    return "Delete (soft) row with id = $id in table theme";
  }
  
  public function hardDelete($id){
    return "Delete (hard) row with id = $id in table theme";
  }
  
}

?>