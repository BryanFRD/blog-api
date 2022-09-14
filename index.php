<?php
  header('Access-Control-Allow-Origin: http://localhost:3000');
  
  $route = trim($_SERVER['REQUEST_URI'], '/');
  $route = filter_var($route, FILTER_SANITIZE_URL);
  $route = explode('/', $route);
  
  $controllerName = array_shift($route);
  
  $controllerFilePath = "controllers/$controllerName.controller.php";
  if(!file_exists($controllerFilePath)){
    header('HTTP/1.0 404 Not Found');
    die;
  }
  
  require_once $controllerFilePath;
  $controllerClassName = ucfirst($controllerName)."Controller";
  $controller = new $controllerClassName($route);
  
  $response = $controller->action;
  if(!isset($response)){
    header('HTTP/1.0 404 Not Found');
    die;
  }
  
  echo $response;
?>