<?php
  require_once( './includes/database.php');

  // get the HTTP method, path and body of the request
  $method = $_SERVER['REQUEST_METHOD'];
  
  // create SQL based on HTTP method
  switch ($method) {
    case 'GET':
      parse_str($_SERVER['QUERY_STRING']);
      if(isset($id))
      {
        $sql = "select * from `tech` WHERE techId=$id"; 
      }
      else
      {
        $sql = "select * from `tech`"; 
      }  
      break;
    case 'PUT':
      $incomingData = file_get_contents('php://input');
      $entityBody = explode('&',$incomingData);
      $body = json_decode($entityBody[0]);
      if($body->techId!==null)
      {
        $sql = '';
        foreach ($body as $key => $value)
        {
          if($sql!=='')
          {
            $sql .= ',';  
          }
          if(gettype($value)=="string")
          {
           $sql .= $key."='".$value."'"; 
          }
          else
          {
            $sql .= $key.'='.$value;  
          }          
        }
        if($sql=='')
        {
          unset($sql);  
        }
        else
        {
          $sql = "UPDATE `tech` SET ".$sql." WHERE techId=$body->techId";
        }
      }
      else
      {
        unset($sql);
      }
      break;
    case 'POST':
      $entityBody = file_get_contents('php://input');
      //$sql = "insert into `tech` set $set"; 
      break;
    case 'DELETE':
      parse_str($_SERVER['QUERY_STRING']);
      if(isset($id))
      {
        $sql = "DELETE FROM `tech` WHERE techId=$id"; 
      }
      else
      {
        unset($sql);
      }  
      break;
  }
 

  
  header("Access-Control-Allow-Origin: *");
  if(isset($sql))
  {
    header('Content-Type: application/json');
    // excecute SQL statement
    $result = $database->query($sql);

    // die if SQL statement failed
    if (!$result) {
      http_response_code(404);
      die(mysqli_error());
    }

    // print results, insert id or affected row count
    if ($method == 'GET') 
    {
      if(!isset($id))
      {
        echo '[';  
      }
      
      for ($i=0;$i<mysqli_num_rows($result);$i++) {
        echo ($i>0?',':'').json_encode(mysqli_fetch_object($result));
      }
      
      if(!isset($id))
      {
        echo ']';  
      }
    } 
    elseif ($method == 'POST') 
    {
      $result = array('InsertedRecordId' => mysqli_insert_id($database->connection));
      echo json_encode($result);
    }
    elseif ($method == 'PUT') 
    {
      $result = array('UpdatedRecords' => mysqli_affected_rows($database->connection));
      echo json_encode($result);
    }
    else 
    {
      $result = array('DelededRecords' => mysqli_affected_rows($database->connection));
      echo json_encode($result);
    }

  }
  else
  {
    http_response_code(400);
  }

?>
