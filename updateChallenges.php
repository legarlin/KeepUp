 <?php
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

    mysqli_query($link,"UPDATE competition SET completed=1
      WHERE expiration < Curdate()");

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
