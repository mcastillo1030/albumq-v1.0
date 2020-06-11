<?php
   /*
    * Name: Marlon Castillo
    * File: get_albums_user_r.php
    *
    * This script queries the database for albums in
    * the user's rated library. The response type 
    * can be one of:
    * - Unauthorized: The user is not logged in.
    * - Server: A connection to the server/database could
    *           not be established.
    * - Success: An object containing the user's rated library.
    */
   require 'connect_db.php';
   require 'Rated_Album.php';
   session_start();
   $response = array();

   if (!isset($_SESSION['userid'])) {  // Is user logged in?
      $response['type'] = "Aunauthorized";
      $response['message'] = "You must be logged in to perform that action.";
   } else {
      $userid = $_SESSION['userid'];
      $conn = getConnection();   // Connect to DB

      if (!$conn) {
         $response['type'] = "Server";
         $response['message'] = "Connection to server could not be established. Try again later.";
      } else {
         // Add success message to response
         $response['type'] = "Success";
         $response['message'] = "";

         // Query the DB
         $sql = "SELECT A.AlbumID, AI.Album_Name, ";
         $sql .= "AI.Album_Artist, AI.Album_Label, ";
         $sql .= "AI.Release_Date, A.Last_Updated, ";
         $sql .= "R.Rating, R.Comments ";
         $sql .= "FROM Albums AS A, Album_Info AS AI, ";
         $sql .= "Album_Rating AS R WHERE A.UserID = ".$userid;
         $sql .= " AND R.UserID = A.UserID ";
         $sql .= "AND A.Status = 'R' ";
         $sql .= "AND AI.AlbumID = A.AlbumID ";
         $sql .= "AND R.AlbumID = A.AlbumID";

         $result = $conn->query($sql);
         $db_albums = $result->num_rows;
         $response['album_count'] = $db_albums;

         // Build Albums
         if ($db_albums > 0) {
            $response['albums'] = array();
            while($row = $result->fetch_assoc()) {
               // Create new Rated Album Object
               $album = new Rated_Album();
               $album->id = $row['AlbumID'];
               $album->title = $row['Album_Name'];
               $album->artist = $row['Album_Artist'];
               $album->label = $row['Album_Label'];
               $album->release = $row['Release_Date'];
               $album->last_update = $row['Last_Updated'];
               $album->rating = $row['Rating'];
               $album->comments = $row['Comments'];

               // Query and add album tracks
               $sql = "SELECT Track_Number, Track_Name FROM ";
               $sql .= "Album_Tracks WHERE AlbumID = \"";
               $sql .= $row['AlbumID']."\"";
               $new_result = $conn->query($sql);
               while ($track = $new_result->fetch_assoc()) {
                  $album->addTrack($track['Track_Number'], $track['Track_Name']);
               } // end inner while loop

               // Query and add album images
               $sql = "SELECT Width, Url FROM Album_Image ";
               $sql .= "WHERE AlbumID = \"";
               $sql .= $row['AlbumID']."\"";
               $new_result = $conn->query($sql);
               while($image = $new_result->fetch_assoc()) {
                  $album->addArtwork($image['Width'], $image['Url']);
               } // end inner while loop
               
               // Add album to response object
               $response['albums'][] = $album;
            }// end while loop
         } // end album builder if

         $result->close();
         $conn->close();
      } // end db connection checker if-else
   } // end session checker if-else

   header('Content-type: application/json; charset=utf-8');
   echo json_encode($response, JSON_PRETTY_PRINT | 
         JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | 
         JSON_NUMERIC_CHECK);
?>