<?php
   /*
    * Name: Marlon Castillo
    * File: add_album_q.php
    *
    * This script adds an album into the user's queue if 
    * not already there. It assumes that the caller will pass
    * a valid Spotify album ID. This script assumes that the 
    * session variable already includes an Album object array
    * i.e. that this script has been called after the
    * getAlbum() PHP script. See documentation for that script.
    * This script also tests whether an Album object with 
    * the specified ID exists in the associative array. The 
    * response has two elements: a type and a message. 
    * The response type can be one of:
    * - Unauthorized: The user is not logged in.
    * - Unexpected: The correct data was not received or
    *           the Album object array is empty, or an 
    *           an Album objec with the specified ID does
    *           not exist in said array.
    * - Server: A connection to the server/database could
    *           not be established.
    * - Duplicate: The album is already in the user's queue.
    * - Success: The album was successfully added to the queue.
    */
   
   require 'Album.php';
   require 'connect_db.php';

   session_start();
   $response = array();

   include 'utils.php';
   $builders = getBuilders();

   if (!isset($_SESSION['userid'])) {  // Is user logged in?
      $response['type'] = "Unauthorized";
      $response['message'] = "You must be logged in to perform that action.";
   } else if (!isset($_SESSION['q']) || // test album array
            !isset($_REQUEST['albumid']) || // test albumid
            !isset($_SESSION['q'][$_REQUEST['albumid']])) { 
      $response['type'] = "Unexpected";
      $response['message'] = "An unexpected error occurred. Please try again later.";
   } else {
      $userid = $_SESSION['userid'];
      $albumid = $_REQUEST['albumid'];
      $album = $_SESSION['q'][$albumid];

      $conn = getConnection();   // connect to DB

      if (!$conn) { // If connection to DB failed for any reason
         $response['type'] = "Server";
         $response['message'] = "Connection to server could not be established. Try again later.";
      } else {
         // Query the database
         $sql = "SELECT * FROM Albums WHERE UserID = '";
         $sql .= $userid."' AND AlbumID = '".$albumid."'";

         $result = $conn->query($sql);
         
         // Store resulting row as an associative array
         $db_album = $result->fetch_assoc();

         if (!empty($db_album)) {
            // If there is a match in the database:
            $response['type'] = "Duplicate"; 
            $response['message'] = "Album is already in your ";
            if ($db_album['Status'] == 'Q') {
               $response['message'] .= "queue.";
            } else {
               $response['message'] .= "rated library.";
            }// end result status checker if-else
         } else {
            // Insert album into Albums table with 
            // status of 'Q' representing the user's queue
            $sql = "INSERT INTO Albums (UserID, AlbumID, Status) VALUES ('";
            $sql .= $userid."', '".$album->id."', 'Q')";               
            
            $conn->query($sql);

            foreach($builders as $q) {
               $conn->query(call_user_func($q, $album));
            }

            // Add success message to response object
            $response['type'] = "Success";
            $response['message'] = "Album was added to your queue.";
         } // end DB result checker if-else

         $result->close();
         $conn->close();
      } // end db connection checker if-else

      unset($_SESSION['q'][$albumid]);
   }// end session checker if-else

   header('Content-type: application/json; charset=utf-8');
   echo json_encode($response, JSON_PRETTY_PRINT | 
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | 
      JSON_NUMERIC_CHECK);
?>