 <?php
require('~/twilio-php-master/Services/Twilio.php');

$AccountSid = "AC2ec969262047c83326a2cfa6f1c5bbdcX";
$AuthToken = "ce41f865554bbb3fc9eeaefc85179714";
 
$client = new Services_Twilio($AccountSid, $AuthToken);

  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header("Access-Control-Allow-Headers: Origin, X-Requested-With,
  Content-Type, Accept");
  updateComp();
function updateComp() {
    echo "here";
   $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');                    
    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    $query = "select id,competition from competition where expiration< Curdate() and completed !=1";
    $rs=$link->query($query);

    if (mysqli_num_rows($rs) != 0) {
      $rs->data_seek(0);
      while($row = $rs->fetch_assoc()){
	  $currid = $row['id'];
	  $currcomp = $row['competition'];
	  $query2 = "select phonenumber from user where id in (select creator from competition where id = '$currid') or id in (select user_id from challenger where competition_id='$currid')";
	  $rs2=$link->query($query2);
	  if(mysqli_num_row($rs2) !=0) {
	    $rs2->data_seek(0);
	    while($row2 = $rs2->fetch_assoc()){
	      $message = $client->account->messages->create(
    "630-473-6728", // From this number
    $row2['phonenumber'], // To this number
    "The competition '$currcomp' has ended!"
);
echo "Sent message {$message->sid}";
	    }
	  }

      }
    }
    mysqli_query($link,"UPDATE competition SET completed=1
      WHERE expiration < Curdate() and completed !=1");

   mysqli_close($link);
 
   
}


?>
<html>
      <head>
         <meta http-equiv="refresh" content="10">
      </head>
      <body>
                          <?php
                                  echo "Watch the page reload itself in 10
                                  second!";
                                      ?>
    </body>
</html>
