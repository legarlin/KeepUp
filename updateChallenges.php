 <?php
require("Services/Twilio.php");



  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header("Access-Control-Allow-Headers: Origin, X-Requested-With,
  Content-Type, Accept");
  updateComp();
function updateComp() {
$AccountSid = "AC2ec969262047c83326a2cfa6f1c5bbdc";
$AuthToken = "ce41f865554bbb3fc9eeaefc85179714";
 
$client = new Services_Twilio($AccountSid, $AuthToken);
   $link = mysqli_connect('keepup.cw8gzyaihfxq.us-east-1.rds.amazonaws.com:3306', 'gldr','keepup2014', 'keepup');                    

    if (mysqli_connect_errno()) {
      trigger_error('Database connection failed: '  . mysqli_connect_error(), E_USER_ERROR);
    }

    $query = "select id,title,is_first_to,required_evidence from competition where expiration< now()-interval 4 hour  and completed !=1";
    $rs=$link->query($query);
    if($rs) {
      $rs->data_seek(0);
      while($row = $rs->fetch_assoc()){
	  $currid = $row['id'];
	  $currcomp = $row['title'];
	  $currisfirst = $row['is_first_to'];
          $currrequired = $row['required_evidence'];



$MyApplicationId = 'u1iaKaYFymF8F4RuTcCDvYzfVR0bPWg4u8ZQSMwR';
$MyParseRestAPIKey = '4ExLgePEAglhmrLaXJMk0eHB0s3jckTJwG6Fa9Oy';
$query = urlencode('where={"competition":"'. $currid . '", "approved":true}');
//$query2 = urlencode('count:1');

$objectData =  urlencode('count=1');
$query2 = urlencode('keys=user_id,user_name');
//$query3 = urlencode('limit=1');

$url = 'https://api.parse.com/1/classes/ParsePicture?' . $query . '&' . $query2;
//echo($url);

$headers = array(
    "Content-Type: application/json",
    "X-Parse-Application-Id: " . $MyApplicationId,
    "X-Parse-REST-API-Key: " . $MyParseRestAPIKey
);

$handle = curl_init(); 
curl_setopt($handle, CURLOPT_URL, $url);
curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    $data = curl_exec($handle);
curl_close($handle);
//echo($data);
$array = json_decode($data) -> {'results'};
//echoarray_count_values($array);
///////////////////////////////
$user= array();
$username= array();
foreach($array as $arraypoint){
  $user[] = $arraypoint->{'user_id'};
  $username[] = $arraypoint->{'user_name'};

  echo($arraypoint->{'user_id'} . ': ' );
  



}
$countarr = (array_count_values($user));
$unique = array_unique($user);
$count=0;
$cuser=0;
$samecount=0;
foreach($unique as $u){
  echo($countarr[$u]);
  if($countarr[$u]> $count){
    $count = $countarr[$u];
    $cuser = $u;
    $samecount=0;
  }else if($countarr[$u]== $count){
    $samecount=1;
  }
}
echo($cuser);
$countarrname = (array_count_values($username));
$uniquename = array_unique($username);
$countname=0;
$cusername=0;
foreach($uniquename as $uname){
  echo($countarrname[$uname]);
  if($countarrname[$uname]> $countname){
    $countname = $countarrname[$uname];
    $cusername = $uname;
  }
}
echo($cusername);


    mysqli_query($link,"UPDATE competition SET winner='$cuser', winner_username='$cusername'
      WHERE id='$currid'");
	  $query2 = "select phonenumber from user where id in (select creator from competition where id = '$currid') or id in (select user_id from challenger where competition_id='$currid')";
	  $rs2=$link->query($query2);
	  if(mysqli_num_rows($rs2) !=0) {
	    $rs2->data_seek(0);
	    while($row2 = $rs2->fetch_assoc()){
	      $ph = ($row2['phonenumber']);
	      try {
		if($cuser=="0"){
		  $message = $client->account->sms_messages->create("630-473-6728", $row2['phonenumber'], "The competition '$currcomp' has ended! There was no winner!'");
		}
		else if($currisfirst==1 && $count<$currrequired){
		  $message = $client->account->sms_messages->create("630-473-6728", $row2['phonenumber'], "The competition '$currcomp' has ended! No one got to the required evidence amount!'");
		}
		else if($samecount==1){
		  $message = $client->account->sms_messages->create("630-473-6728", $row2['phonenumber'], "The competition '$currcomp' has ended! There was a tie!'");
		}
		else{
		  $message = $client->account->sms_messages->create("630-473-6728", $row2['phonenumber'], "The competition '$currcomp' has ended! The winner was '$cusername'!");
		}
	       echo "Sent message to '$ph'";
       } catch(Exception $e) {
          echo 'Error: ' . $e->getMessage();
       }
	    }
	  }
        }
      

     
    //mysqli_close($link);


}





    mysqli_query($link,"UPDATE competition SET completed=1
      WHERE expiration < now()-interval 4 hour and completed !=1");
     
   mysqli_close($link);
 
   

}


?>
<html>
  <head>
    <meta http-equiv="refresh" content="10">
  </head>
  <body>
    <?php
      echo "Watch the page reload itself in 10 seconds!";
    ?>
    </body>
</html>
