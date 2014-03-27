<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');

  if(isset($_POST['action']) && !empty($_POST['action'])) {
    $action = $_POST['action'];
    switch($action) {
      case 'test' : test();break; 
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

    echo json_encode(array('status' => 'success', 'items' => $output));
    mysqli_close($link);
  }
?>