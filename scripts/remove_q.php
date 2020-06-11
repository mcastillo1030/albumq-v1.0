<?php
   /*
    * Name: Marlon Castillo
    * File: remove_q.php
    *
    * This script removes an existing album
    * from a user's queue. It expects one
    * parameter: an album ID.
    * The response type can be one of:
    * - Unauthorized: The user is not logged in.
    * - Unexpected: The correct data was not received.
    * - Server: A connection to the server/database could
    *           not be established.
    * - Success: The album was successfully removed.
    */
   require 'connect_db.php';
   session_start();
   $response = array();
   $builders = [
      "album_info" => function($albumid) {
         $s = "DELETE FROM Album_Info WHERE AlbumID = \"";
         $s .= $albumid."\"";

         return $s;
      }, 
      "album_tracks" => function($albumid) {
         $s = "DELETE FROM Album_Tracks WHERE AlbumID = \"";
         $s .= $albumid."\"";

         return $s;
      },
      "album_photo" => function($albumid) {
         $s = "DELETE FROM Album_Image WHERE AlbumID = \"";
         $s .= $albumid."\"";

         return $s;
      }
   ];

   if (!isset($_SESSION['userid'])) {  // Is user logged in?
      $response['type'] = "Aunauthorized";
      $response['message'] = "You must be logged in to perform that action.";
   } else {
      $userid = $_SESSION['userid'];

      if (!isset($_REQUEST['albumid'])) {    // Check if AlbumID was passed
         $response['type'] = "Unexpected";
         $response['message'] = "An unexpected error occurred. Please try again later.";
      } else {
         $id = $_REQUEST['albumid'];

         $conn = getConnection();   // Connect to DB

         if (!$conn) {
            $response['type'] = "Server";
            $response['message'] = "Connection to server could not be established. Try again later.";
         } else {
            $sql = "SELECT UserID FROM Albums WHERE ";
            $sql .= "AlbumID = \"".$id."\"";
            $result = $conn->query($sql);
            $db_results = $result->num_rows;

            if ($db_results == 1) {
               foreach($builders as $fn)                {
                  $conn->query(call_user_func($fn, $id));
               }
            }

            $sql = "DELETE FROM Albums WHERE UserID = ";
            $sql .= $userid." AND AlbumID = \"";
            $sql .= $id."\" AND Status = 'Q'";

            $conn->query($sql);

            // Add success message to response object
            $response['type'] = "Success";
            $response['message'] = "Album was removed from your queue.";

            $result->close();
            $conn->close();
         } // end db connection chcker if-else
      } // end parameter checker if -else
   } // end session checker if-else

   header('Content-type: application/json; charset=utf-8');
   echo json_encode($response, JSON_PRETTY_PRINT | 
   JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | 
   JSON_NUMERIC_CHECK);
?>