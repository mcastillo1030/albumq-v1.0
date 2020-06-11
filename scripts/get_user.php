<?php
   require 'connect_db.php';
   session_start();
   $response = array();

   if (!isset($_SESSION['userid'])) {
      $response['type'] = "Unauthorized";
      $response['message'] = "You must be logged in to perform that action.";
   } else {
      $userid = $_SESSION['userid'];
      $conn = getConnection();

      if (!$conn) {
         $response['type'] = "Server";
         $response['message'] = "Connection to server could not be established. Try again later.";
      } else {
         // Add success message to response
         $response['type'] = "Success";
         $response['message'] = "";

         $sql = "SELECT Name FROM Users WHERE UserID = ".$userid;
         $result = $conn->query($sql);

         $row = $result->fetch_assoc();

         $response['user_name'] = $row['Name'];

         $result->close();
         $conn->close();
      }// end db connection checker if-else
   }// end session checker if-else

   header('Content-type: application/json; charset=utf-8');
   echo json_encode($response, JSON_PRETTY_PRINT | 
         JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | 
         JSON_NUMERIC_CHECK);
?>