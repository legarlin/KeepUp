<?php

  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

  if(isset($_POST['action']) && !empty($_POST['action'])) {
    $action = $_POST['action'];
    switch($action) {
      case 'test' : test();break; 
      case 'logIn' : logIn();break; 
      case 'signUp' : signUp();break;
      case 'logOut' : logOut();break;
      case 'newComp' : newComp();break; 
    }
  }

  function test() { 
    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }
    $sql='SELECT * FROM test';
    $rs=$link->query($sql);
     
    $output = array();

    $rs->data_seek(0);
    while($row = $rs->fetch_assoc()){
        $output[] = $row['testcol'];
        
    }

    echo json_encode(array('stat' => 'success', 'items' => $output));
    mysqli_close($link);
  }

  function logIn() {
    $decoded = json_decode($_POST['logInData'],true);
    $user = $decoded['username'];
    $pass = $decoded['password'];
    
    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    $query = "SELECT * FROM user where username = '$user'";
    $rs=$link->query($query);
    if (mysqli_num_rows($rs) != 0) {
      $row = $rs->fetch_assoc();
      $db_pass = $row['password'];
      if($db_pass == $pass) {
        $log_in = "UPDATE user SET loggedin = (1) where username = '$user'";
        $exec=$link->query($log_in);
        //get user's unique id
        $user_id = $row['id'];
        $username = $row['username'];
        echo json_encode(array('stat' => 'success', 'logIn' => array('username' => $username, 'id'=> $user_id)));
      }
      else
        die(json_encode(array('stat' => 'error', 'code' => "incorrect username/password!")));
    } 
    else
      die(json_encode(array('stat' => 'error', 'code' => "user does not exist!")));
    mysqli_close($link);
  }

  function signUp() {
    $decoded = json_decode($_POST['signUpData'],true);
    $fn = $decoded['firstname'];
    $ln = $decoded['lastname'];
    $un = $decoded['username'];
    $pw = $decoded['password'];

    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }
    
    $query = "INSERT into user (username,password,loggedin,firstname,lastname) values ('$un', '$pw', 1, '$fn', '$ln')";
    $rs=$link->query($query);
    if($rs) {
      echo json_encode(array('stat' => 'success', 'signUp' => array('username' => $un, 'id'=> mysqli_insert_id($link))));
    } else {
       die(json_encode(array('stat' => 'error', 'code' => "error on sign up, try again!")));
    }
  }

  function logOut() {
    $user = $_POST['user'];

    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    $query = "UPDATE user set loggedin = (0) where username = '$user'";
    $rs=$link->query($query);
    if($rs) {
      echo json_encode(array('stat' => 'success', 'logOut' => $_POST['user']));
    } else {
       die(json_encode(array('stat' => 'error', 'code' => "error on log out, try again!")));
    }

  }

  function newComp() {
    
  }

?>