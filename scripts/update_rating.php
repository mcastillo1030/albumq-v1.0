<?php
   /*
    * Name: Marlon Castillo
    * File: update_rating.php
    *
    * This script updates the rating of an existing album
    * on a user's rated library. It expects three
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
         $rating = htmlspecialchars($_REQUEST['rating']);
         $comment = (isset($_REQUEST['comment'])) ? htmlspecialchars($_REQUEST['comment']) : "";

         $conn = getConnection();   // Connect to DB

         if (!$conn) {
            $response['type'] = "Server";
            $response['message'] = "Connection to server could not be established. Try again later.";
         } else {
            // Update Album_Rating table
            $sql = "UPDATE Album_Rating SET Rating = ";
            $sql .= $rating.", Comments = \"";
            $sql .= $comment."\" WHERE AlbumID = \"";
            $sql .= $id."\" AND UserID = ".$userid;

            $conn->query($sql);

            // Update "Last Update" column in Albums table
            $sql = "UPDATE Albums SET Last_Update = ";
            $sql .= "CURRENT_TIMESTAMP WHERE UserID = ";
            $sql .= $userid." AND AlbumID = \"".$id."\"";

            $conn->query($sql);

            // Add success message to response object
            $response['type'] = "Success";
            $response['message'] = "Album rating was updated successfully.";

            $conn->close();
         } // end db connection checker if-else
      }// end parameters checker if-else
   } // end session checer if-else

   header('Content-type: application/json; charset=utf-8');
   echo json_encode($response, JSON_PRETTY_PRINT | 
   JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | 
   JSON_NUMERIC_CHECK);
?>