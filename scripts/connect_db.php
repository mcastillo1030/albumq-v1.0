<?php
   function getConnection() {
      $root = dirname(__DIR__, 4).'/includes/details.php';
      include $root;

      $servername = "localhost";
      $user = getUserName();
      $pwd = getPwd();

      $conn = new mysqli($servername, $user, $pwd);

      // The below code may be redundant b/c mysqli object
      // returns bool value of false if either of these
      // operations are not successful.

      //print_r($conn->connect_errno);
      if ($conn->connect_error) {
         //echo "<p>Connection error:".mysqli_connect_error()."</p>";
         //$conn = false;
         return false;
      }

      if (!$conn->select_db("mcastill_albumq")) {
         return false;
      }

      return $conn;
   }
?>