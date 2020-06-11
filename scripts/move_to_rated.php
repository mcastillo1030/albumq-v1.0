<?php
   /*
    * Name: Marlon Castillo
    * File: move_to_rated.php
    *
    * This script moves al album from the user's queue
    * to their rated library. It expects three
    * parameters: an album ID, a rating as a floating 
    * point value, and a comment.
    * The response type can be one of:
    * - Unauthorized: The user is not logged in.
    * - Unexpected: The correct data was not received.
    * - Server: A connection to the server/database could
    *           not be established.
    * - Success: The album was successfully updated.
    */
   require 'connect_db.php';
   session_start();
   $response = array();
   
   if (!isset($_SESSION['userid'])) {  // Is user logged in?
      $response['type'] = "Aunauthorized";
      $response['message'] = "You must be logged in to perform that action.";
   } else {
      $userid = $_SESSION['userid'];

      if (!isset($_REQUEST['albumid']) || 
            !isset($_REQUEST['rating'])) {    // Check if AlbumID was passed
         $response['type'] = "Unexpected";
         $response['message'] = "An unexpected error occurred. Please try again later.";
      } else {
         $id = $_REQUEST['albumid'];
         $rating = $_REQUEST['rating'];
         $comment = (isset($_REQUEST['comment'])) ? trim($_REQUEST['comment']) : "";

         $conn = getConnection();   // Connect to DB

         if (!$conn) {
            $response['type'] = "Server";
            $response['message'] = "Connection to server could not be established. Try again later.";
         } else {
            $sql = "UPDATE Albums SET Status = 'R'";
            $sql .= "WHERE AlbumID = \"".$id."\"";
            $sql .= "AND UserID = ".$userid;

            $conn->query($sql);


            $sql = "INSERT INTO Album_Rating VALUES (";
            $sql .= $userid.", \"".$id."\", ";
            $sql .= $rating.", \"".$comment."\")";

            $conn->query($sql);

            // Add success message to response object
            $response['type'] = "Success";
            $response['message'] = "Album was moved to your rated library.";

            $conn->close();
         } // end db connection checker if-else
      } // end parameters checker if-else
   } // end session checker if-else

   header('Content-type: application/json; charset=utf-8');
   echo json_encode($response, JSON_PRETTY_PRINT | 
   JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | 
   JSON_NUMERIC_CHECK);
?>