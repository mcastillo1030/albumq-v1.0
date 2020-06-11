<?php
   /*
    * Name: Marlon Castillo
    * File: add_album_q.php
    *
    * This script adds performs similar functions to the
    * add_album_q.php script. The differences are that this 
    * script assumes that the caller will pass not just
    * a valid Spotify album ID string, but a rating (as a
    * floating point value) and a comment as well.
    * The response type can be one of:
    * - Unauthorized: The user is not logged in.
    * - Unexpected: The correct data was not received.
    * - Server: A connection to the server/database could
    *           not be established.
    * - Duplicate: The album is already in the user's library.
    */

   require 'Rated_Album.php';
   require 'connect_db.php';

   session_start();
   $response = array();

   include 'utils.php';
   $builders = getBuilders();

   function ratingBuilder($album, $userid) {
      $s = "INSERT IGNORE INTO Album_Rating VALUES (\"";
      $s .= $userid."\", \"".$album->id."\", ";
      $s .= $album->rating.", \"";
      $s .= $album->comments."\")";

      return $s;
   }

   if (!isset($_SESSION['userid'])) {  // Is user logged in?
      $response['type'] = "Unauthorized";
      $response['message'] = "You must be logged in to perform that action.";
   } else if (!isset($_SESSION['q']) || // test album array
            !isset($_REQUEST['albumid']) || // test albumid
            !isset($_SESSION['q'][$_REQUEST['albumid']]) ||
            !isset($_REQUEST['rating'])) { 
      $response['type'] = "Unexpected";
      $response['message'] = "An unexpected error occurred. Please try again later.";
   } else {
      // comments are optional
      $comment = (isset($_REQUEST['comment'])) ? htmlspecialchars($_REQUEST['comment']) : "";
      $userid = $_SESSION['userid'];
      $albumid = $_REQUEST['albumid'];
      $rating = htmlspecialchars($_REQUEST['rating']);
      $album = Rated_Album::constructFromAlbum($_SESSION['q'][$albumid]);
      
      
      // Add Rating and Comments
      $album->rating = $rating;
      $album->comments = $comment;
      
      $conn = getConnection();   // Connect to DB

      if (!$conn) {
         $response['type'] = "Server";
         $response['message'] = "Connection to server could not be established. Try again later.";
      } else {
         // Query the database
         $sql = "SELECT * FROM Albums WHERE UserID = '";
         $sql .= $userid."' AND AlbumID = '".$album->id."'";

         $result = $conn->query($sql);
         
         // Store resulting row as an associative array
         $db_album = $result->fetch_assoc();

         if (!empty($db_album)) {
            // If there is a match in the database, test 'Status'
            if ($db_album['Status'] == 'R') {
               $response['type'] = "Duplicate"; 
               $response['message'] = "Album is already in your rated library.";
            } else { // will this ever get called?
               $sql = "UPDATE Albums SET Status = 'R' WHERE ";
               $sql .= "UserID = \"".$userid."\" AND ";
               $sql .= "AlbumID = \"".$album->id."\"";

               $conn->query($sql);



               $conn->query(ratingBuilder($album, $userid));

               // Add success message to response object
               $response['type'] = "Success";
               $response['message'] = "Album was added as rated to your library.";
            }// end result status checker if-else
         } else {
            // Insert into all tables
            $sql = "INSERT INTO Albums (UserID, AlbumID, Status) VALUES ('";
            $sql .= $userid."', '".$album->id."', 'R')";               
            
            $conn->query($sql);

            foreach($builders as $q) {
               $conn->query(call_user_func($q, $album));
            }

            $conn->query(ratingBuilder($album, $userid));

            // Add success message to response object
            $response['type'] = "Success";
            $response['message'] = "Album was added as rated to your library.";
         } // end result checker if-else

         $result->close();
         $conn->close();
      } // end db connection if-else

      unset($_SESSION['q'][$albumid]);
   } // end session checker if-else

   header('Content-type: application/json; charset=utf-8');
   echo json_encode($response, JSON_PRETTY_PRINT | 
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | 
      JSON_NUMERIC_CHECK);
?>