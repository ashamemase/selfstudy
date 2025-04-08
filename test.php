 <?php
     phpinfo();
     $dbh =mysqli_connect("localhost","root","m42974298","studyjapanese");
     if($dbh == false){
        die("can't connect to MYSQL".mysqli_connect_error()."\n");
     }else{
        echo "succesfully connect to MYSQL \n";
     }
  ?>