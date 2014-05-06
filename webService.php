<?php

  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

  if(isset($_POST['action']) && !empty($_POST['action'])) {
    $action = $_POST['action'];
    switch($action) {
      case 'logIn' : logIn();break; 
      case 'signUp' : signUp();break;
      case 'logOut' : logOut();break;
      case 'newComp' : newComp();break;
      case 'getFriends' : getFriends();break;
      case 'getAllUsers' : getAllUsers();break; 
      case 'getPending' : getPending();break;
      case 'acceptRequest' : acceptRequest();break;
      case 'addFriends' : addFriends();break;
      case 'getComps' : getComps(); break;
      case 'getComp' : getComp(); break;
      case 'getUser' : getUser(); break;
      case 'getUsers' : getUsers(); break;
      case 'getFriendComps' : getFriendComps(); break;
      case 'isObserver' : isObserver(); break; 
      case 'addObserver' : addObserver();break;
      case 'getObserving' :getObserving();break;
      case 'submitVote' : submitVote();break;
      case 'hasVoted' : hasVoted();break;
      case 'getCompVotes' :getCompVotes();break;
      case 'getThread' : getThread();break;
      case 'newMessage' : newMessage();break;
    }
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
      die(json_encode(array('stat' => 'error', 'code' => "'$decoded' does not exist!")));
    mysqli_close($link);
  }

  function signUp() {
    $decoded = json_decode($_POST['signUpData'],true);
    $fn = $decoded['firstname'];
    $ln = $decoded['lastname'];
    $un = $decoded['username'];
    $pw = $decoded['password'];
    $pn = $decoded['phonenumber'];

    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }
    
    $query = "INSERT into user (username,password,loggedin,firstname,lastname, phonenumber) values ('$un', '$pw', 1, '$fn', '$ln', '$pn')";
    $rs=$link->query($query);
    if($rs) {
      echo json_encode(array('stat' => 'success', 'signUp' => array('username' => $un, 'id'=> mysqli_insert_id($link))));
    } else {
       die(json_encode(array('stat' => 'error', 'code' => "Username already exists!")));
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
    $decoded = json_decode($_POST['newCompData'],true);
    $title = $decoded['title'];
    $creator = $decoded['creator'];
    $creator_username = $decoded['creator_username'];
    $description = $decoded['description'];
    $expiration = $decoded['expiration'];
    $is_first_to = $decoded['is_first_to'];
    $required_evidence = $decoded['required_evidence'];
    $challengers = explode(",", $decoded['challengers']);
    $points = $decoded['points'];
    $created_at = $decoded['created_at'];
    $prv = $decoded['prv'];

    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    $insert = "INSERT into competition (title, description, creator, expiration, points, public, is_first_to, required_evidence, creator_username, created_at) VALUES ('$title', '$description', '$creator', '$expiration', '$points', $prv, $is_first_to, '$required_evidence', '$creator_username', '$created_at')";

    $rs=$link->query($insert);
    $comp_id = mysqli_insert_id($link);
    if($rs) {
      foreach ($challengers as $chal) {
        $insert_query = "INSERT into challenger values ($chal, $comp_id)";
        $link->query($insert_query);
      }
      echo json_encode(array('stat' => 'success', 'newComp' => $comp_id));
    } else {
       die(json_encode(array('stat' => 'error', 'code' => "error on comp creation")));
    }
  }

  function getFriends() {
    $user = $_POST['user_id'];
    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    $query = "SELECT ID FROM (SELECT user1 AS ID FROM friend WHERE user2 = '$user' UNION ALL SELECT user2 FROM friend WHERE user1 = '$user') t GROUP BY ID HAVING COUNT(ID) > 1";
    $rs=$link->query($query);

    $get_ids = array();
    if (mysqli_num_rows($rs) != 0) {
      $rs->data_seek(0);
      while($row = $rs->fetch_assoc()){
          $get_ids[] = $row['ID'];
      }

      $ids = join(',', $get_ids);

      $name_query = "SELECT id as id, username as name from user where id in($ids)";
      $exec=$link->query($name_query);

      $rows = array();
      $exec->data_seek(0);
      if(mysqli_num_rows($exec) != 0) {
        while($row = $exec->fetch_assoc()){
            $rows[] = $row;
        }
         echo json_encode(array('stat' => 'success', 'friends' =>json_encode($rows)));
      }
    } else {
      echo json_encode(array('stat' => 'success', 'friends' => ""));
    }
   
  }

  function getAllUsers() {
    $user = $_POST['user_id'];
    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    $query = "SELECT ID from user where id not in(SELECT ID FROM (SELECT user1 AS ID FROM friend WHERE user2 = '$user' UNION ALL SELECT user2 FROM friend WHERE user1 = '$user') t GROUP BY ID HAVING COUNT(ID) > 1) and id !='$user'";
    $rs=$link->query($query);

    $get_ids = array();

    $rs->data_seek(0);
    while($row = $rs->fetch_assoc()){
        $get_ids[] = $row['ID'];
    }

    $ids = join(',', $get_ids);

    $name_query = "SELECT id as id, username as name from user where id in($ids)";
    $exec=$link->query($name_query);

    $rows = array();
    $exec->data_seek(0);
    if(mysqli_num_rows($exec) != 0) {
      while($row = $exec->fetch_assoc()){
          $rows[] = $row;
      }
       echo json_encode(array('stat' => 'success', 'users' =>json_encode($rows)));
    }
  }

  function getPending() {
    $user = $_POST['user_id'];
    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    $query = "SELECT id from (select user1 as id from friend where user2 = '$user') l where id not in (select user2 from friend where user1 = '$user' and  user2 in (select user1 from friend where user2='$user'));";
    
    $rs=$link->query($query);

    $get_ids = array();
    if (mysqli_num_rows($rs) != 0) {

      $rs->data_seek(0);
      while($row = $rs->fetch_assoc()){
          $get_ids[] = $row['id'];
      }

      $ids = join(',', $get_ids);

      $name_query = "SELECT id as id, username as name from user where id in($ids)";
      $exec=$link->query($name_query);

      $rows = array();
      $exec->data_seek(0);
      if(mysqli_num_rows($exec) != 0) {
        while($row = $exec->fetch_assoc()){
            $rows[] = $row;
        }
         echo json_encode(array('stat' => 'success', 'pendings' =>json_encode($rows)));
      }
    } else {
      echo json_encode(array('stat' => 'success', 'pendings' =>""));
    }
  }

  function acceptRequest() {
    $pending = $_POST['pending_id'];
    $user = $_POST['user_id'];

    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    $insert = "INSERT into friend (user1,user2) VALUES ('$user', '$pending')";

    $rs=$link->query($insert);
    if($rs) {
      echo json_encode(array('stat' => 'success', 'acceptRequest' => array('user' => $user, 'pending'=> $pending)));
    } else {
      die(json_encode(array('stat' => 'error', 'code' => "Failure on Acceptance")));
    }
  }

  function addFriends() {
    $requests = explode(",", json_decode($_POST['requests'],true));
    $user = $_POST['user_id'];
    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    foreach ($requests as &$req) {
      $insert = "INSERT into friend (user1,user2) VALUES ('$user', '$req')";
      $rs=$link->query($insert);
      if($rs) {
        continue;
      } else {
        die(json_encode(array('stat' => 'error', 'code' => "Unable to Add 1 or More Friends")));
      }
    }
    echo json_encode(array('stat' => 'success', 'addFriends' => array('user' => $user, 'requests'=> $requests)));
  }

function getComps() {
    $user = $_POST['user_id'];
    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    $query = "select id, title, expiration, completed from competition where (id in (select competition_id from challenger where user_id= '$user' ) or id in (select id from competition where creator = '$user' )) order by expiration";
    $rs=$link->query($query);

    $get_comp = array();

    $rs->data_seek(0);
    if(mysqli_num_rows($rs) != 0) {
    while($row = $rs->fetch_assoc()){
        $get_comp[] = $row;
    }
    }
       echo json_encode(array('stat' => 'success', 'competitions' =>json_encode($get_comp)));
   
}

function getComp() {
    $comp = $_POST['comp_id'];
    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    $query = "select * from competition where id = '$comp'";
    $rs=$link->query($query);

    $get_comp = array();

    $rs->data_seek(0);
    $get_comp[0] = $rs->fetch_assoc();
  

    $query2 = "select user_id from challenger where competition_id = '$comp'";
    $rs2=$link->query($query2);

    if(mysqli_num_rows($rs2) != 0) {
      while($row = $rs2->fetch_assoc()){
          $get_comp[] = $row;
        }
    }
    echo json_encode(array('stat' => 'success', 'competitions' =>json_encode($get_comp)));
   
}

function getUser() {
  $user = $_POST['user_id'];
  $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
          
  if (mysqli_connect_errno()) {
    trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
  }

  $query = "select * from user where id = '$user'";
  $rs=$link->query($query);

  $get_user = array();

  $rs->data_seek(0);
  $get_user[0] = $rs->fetch_assoc();

  echo json_encode(array('stat' => 'success', 'user' =>json_encode($get_user)));
}
  
function getUsers() {
  $user = $_POST['user_id'];
  $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
          
  if (mysqli_connect_errno()) {
    trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
  }
  $i = 0;
  $get_user=array();
  foreach ($user as $u){
    $query = "select username from user where id = '$u'";
    $rs=$link->query($query);
    $rs->data_seek(0);
    $get_user[$i] = $rs->fetch_assoc();
    ++$i;
  }
 
  echo json_encode(array('stat' => 'success', 'challengers' =>json_encode($get_user)));
}

function rowSort( $a, $b ) {
    return $a['expiration'] == $b['expiration'] ? 0 : ( $a['expiration'] > $b['expiration'] ) ? 1 : -1;
}

function getFriendComps() {
  $user = json_decode($_POST['friends'],true);
  $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
          
  if (mysqli_connect_errno()) {
    trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
  }

  
  $i = 0;
  $get_comp = array();
  foreach ($user as $u) {
    $query = "select id, title, creator, creator_username, expiration from competition where id in (select competition_id from challenger where user_id= '$u' ) or id in (select id from competition where creator = '$u' )";
    $rs=$link->query($query);
    $rs->data_seek(0);
    if(mysqli_num_rows($rs) != 0) {
      while($row = $rs->fetch_assoc()){
        $get_comp[] = $row;
      }
    }
  }
  usort( $get_comp, 'rowSort' );
  echo json_encode(array('stat' => 'success', 'friendCompetitions' =>json_encode($get_comp)));
}

function isObserver() {
  $user = $_POST['user_id'];
  $comp = $_POST['competition_id'];
  $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
          
  if (mysqli_connect_errno()) {
    trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
  }

  $query = "SELECT * from observer where user_id = '$user' and competition_id ='$comp'";
  $rs=$link->query($query);
  if($rs) {
    $rs->data_seek(0);
    if(mysqli_num_rows($rs) != 0) {
      echo json_encode(array('stat' => 'success', 'observer' =>true));
    } else {
      echo json_encode(array('stat' => 'success', 'observer' =>false));
    }
  } else {
      die(json_encode(array('stat' => 'error', 'code' => "Failure on isObserver")));
  }

}

function addObserver() {
  $user = $_POST['user_id'];
  $comp = $_POST['competition_id'];
  $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
          
  if (mysqli_connect_errno()) {
    trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
  }

  $query = "INSERT into observer (user_id,competition_id) values ('$user','$comp')";
  $rs=$link->query($query);
    if($rs) {
      echo json_encode(array('stat' => 'success', 'observe' => array('user' => $user, 'comp'=> $comp)));
    } else {
       die(json_encode(array('stat' => 'error', 'code' => "Error adding Observer!")));
    }
}

function getObserving() {
  $user = $_POST['user_id'];
  $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
          
  if (mysqli_connect_errno()) {
    trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
  }

  $query = "SELECT id, expiration, creator_username, creator, title from competition where id in (select competition_id from observer where user_id = '$user')";
  $rs=$link->query($query);
    if($rs) {
      $get_observing = array();
      if(mysqli_num_rows($rs) != 0) {
        while($row = $rs->fetch_assoc()){
          $get_observing[] = $row;
        }
      }
      echo json_encode(array('stat' => 'success', 'observing' =>json_encode($get_observing)));
    } else {
       die(json_encode(array('stat' => 'error', 'code' => "Error getting observing!")));
    }
}

function submitVote() {
  $voter = $_POST['user_id'];
  $competition_id = $_POST['competition_id'];
  $contestant = $_POST['contestant'];
  $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
          
  if (mysqli_connect_errno()) {
    trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
  }
  $query = "INSERT into pick_side (voter,competition_id, contestant) values ('$voter','$competition_id', $contestant)";
  $rs=$link->query($query);
    if($rs) {
      $checkObs = "SELECT * from observer where user_id = '$vote' and competition_id ='$competition_id'";
      $exec=$link->query($checkObs);
      if($exec) {
        $exec->data_seek(0);
        if(mysqli_num_rows($exec) == 0) {
          $addObs = "INSERT into observer (user_id,competition_id) values ('$voter','$competition_id')";
          $execAdd=$link->query($addObs);
          if($execAdd) {
            echo json_encode(array('stat' => 'success', 'submitVote' => array('voter' => $voter, 'competition_id'=> $competition_id, 'contestant' => $contestant, 'addedObs' =>true)));
            return;
          } else {
            die(json_encode(array('stat' => 'error', 'code' => "Error adding observer when voting!")));
          }
        }
      }
      echo json_encode(array('stat' => 'success', 'submitVote' => array('voter' => $voter, 'competition_id'=> $competition_id, 'contestant' => $contestant)));
    } else {
       die(json_encode(array('stat' => 'error', 'code' => "Error submitting vote!")));
    }
}

function hasVoted() {
  $voter = $_POST['user_id'];
  $competition_id = $_POST['competition_id'];

  $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
          
  if (mysqli_connect_errno()) {
    trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
  }

  $query = "SELECT contestant from pick_side where voter = '$voter' and competition_id ='$competition_id'";
  $rs=$link->query($query);
  if($rs) {
    $rs->data_seek(0);
    if(mysqli_num_rows($rs) != 0) {
      $rs->data_seek(0);
      $row =  $rs->fetch_assoc();
      echo json_encode(array('stat' => 'success', 'hasVoted' =>true, 'contestant' => $row['contestant']));
    } else {
      echo json_encode(array('stat' => 'success', 'hasVoted' =>false));
    }
  } else {
      die(json_encode(array('stat' => 'error', 'code' => "Failure on hasVoted")));
  }
}

function getCompVotes() {
  $competition_id = $_POST['competition_id'];

  $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
          
  if (mysqli_connect_errno()) {
    trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
  }

  $query = "SELECT username, count(contestant) as votes from pick_side, user where id=contestant and competition_id='$competition_id' group by username";
  $rs=$link->query($query);
  if($rs) {
    $votes = array();
    if(mysqli_num_rows($rs) != 0) {
      while($row = $rs->fetch_assoc()){
        $votes[$row['username']] = $row['votes'];
      }
    }
    echo json_encode(array('stat' => 'success', 'voteArray' =>json_encode($votes)));
  } else {
     die(json_encode(array('stat' => 'error', 'code' => "Error getting votes!")));
  }
}

function getThread(){
  $comp = $_POST['comp_id'];
    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    $query = "SELECT user_name, message, tstamp from thread where competition_id='$comp' order by tstamp desc";
    $rs=$link->query($query);

    $get_messages = array();

    $rs->data_seek(0);
    if(mysqli_num_rows($rs) != 0) {
    while($row = $rs->fetch_assoc()){
        $get_messages[] = $row;
    }
    }
       echo json_encode(array('stat' => 'success', 'messages' =>json_encode($get_messages)));


}

function newMessage() {
    
    $decoded = json_decode($_POST['newMessage'],true);
    $comp_id = $decoded['c_id'];
    $user_id = $decoded['u_id'];
    $user_name = $decoded['u_name'];
    $message =$decoded['mess'];
    $t = $decoded['timestamp'];

    $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');            
            
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    $insert = "INSERT into thread (user_id, user_name, competition_id, message, tstamp) VALUES ($user_id, '$user_name', $comp_id, '$message', '$t')";
//here
    $rs=$link->query($insert);
    if($rs) {
      echo json_encode(array('stat' => 'success', 'message' => array('message' => $message, 'id'=> mysqli_insert_id($link))));
    } else {
       die(json_encode(array('stat' => 'error', 'code' => "problem with message insertion")));
    }
  }


?>