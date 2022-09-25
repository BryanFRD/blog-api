<?php
  header('Access-Control-Allow-Origin: http://localhost:3000');
  
  $_ENV['current'] = 'dev';
  $config = file_get_contents('config/'.$_ENV['current'].'.config.json');
  $_ENV['config'] = json_decode($config);
  
  require_once 'services/database.service.php';
  require_once 'controllers/database.controller.php';
  
  $route = trim($_SERVER['REQUEST_URI'], '/');
  $route = filter_var($route, FILTER_SANITIZE_URL);
  $route = explode('/', $route);
  
  $controllerName = array_shift($route);
  
  if($_ENV['current'] == 'dev' && $controllerName == 'init'){
    $dbs = new DatabaseService(null);
    $query_resp = $dbs->query('SELECT table_name FROM information_schema.tables WHERE table_schema = ?', ['db_blog']);
    $rows = $query_resp->statment->fetchAll(PDO::FETCH_COLUMN);
    
    foreach($rows as $tableName){
      $controllerFile = "controllers/$tableName.controller.php";
      if(!file_exists($controllerFile)){
        $fileContent = 
        "<?php\n\rclass ".ucfirst($tableName)."Controller extends DatabaseController {}\n\r?>";
        file_put_contents($controllerFile, $fileContent);
        
        echo ucfirst($tableName)."Controller created!\n\r";
      }
    }
    
    echo 'api initialized!';
  }
  
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
  
  echo json_encode($response);
?>