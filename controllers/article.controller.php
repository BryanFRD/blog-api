<?php

class ArticleController extends DatabaseController {
  
  public function affectDataToRow(&$row, $sub_rows){
    if(isset($sub_rows['appuser'])){
      $appusers = array_filter($sub_rows['appuser'], function($item) use ($row) {
        return $item->Id_appUser == $row->Id_appUser;
      });
      $row->appuser = count($appusers) == 1 ? array_shift($appusers) : null;
    }
    
    if(isset($sub_rows['image'])){
      $images = array_filter($sub_rows['image'], function($item) use ($row){
        return $item->Id_article == $row->Id_article;
      });
      if(isset($images)){
        $row->images_list = $images;
      }
    }
    
    if(isset($sub_rows['comment'])){
      $comments = array_filter($sub_rows['comment'], function($item) use ($row){
        return $item->Id_article == $row->Id_article;
      });
      if(isset($comments)){
        $row->comments_list = $comments;
      }
    }
  }
}

?>