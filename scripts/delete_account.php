<?php
   session_start();

   if (!isset($_SESSION['userid'])) {
      header('Location: ../index.html');
      exit;
   } else {
      include 'connect_db.php';
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

      $userid = $_SESSION['userid'];
      
      $conn = getConnection();   // Connect to DB

      if (!$conn) {
         // incorporate as a template & echo to the screen
         echo "<h1>Server Error</h1>";
         echo "<p>Connection to server could not be established. Try again later.</p>";
      } else {
         // Initialize queue and rated arrays
         $q = $rated = array();

         $sql = "SELECT AlbumID FROM Albums WHERE UserID = ".$userid;
         $sql .= " AND Status = 'Q'";

         $result = $conn->query($sql);
         if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
               $q[] = $row['AlbumID'];
            }// end while
         } // end if

         $sql = "SELECT AlbumID FROM Albums WHERE UserID = ".$userid;
         $sql .= " AND Status = 'R'";

         $result = $conn->query($sql);
         if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
               $rated[] = $row['AlbumID'];
            }// end while
         }// end if
         
         if (count($q) > 0) { // If not empty
            removeHandler('q', $q);
         }
         
         if (count($rated) > 0) { // If not empty
            removeHandler('r', $rated);
         }
         
         // Delete user from Users table
         $sql = "DELETE FROM Users WHERE UserID = ".$userid;
         $conn->query($sql);

         $result->close();
         $conn->close();

         // Logout
         include 'logout.php';
         
      }// end db connection checker if-else
   } // end if-else

   function testMultiUser($id) {
      global $conn;

      $s = "SELECT UserID FROM Albums WHERE ";
      $s .= "AlbumID = \"".$id."\"";

      $rslt = $conn->query($s);
      
      return $rslt->num_rows;
   }

   function removeHandler($type, $ary) {
      global $userid;
      global $conn;
      global $builders;

      foreach($ary as $albumid) {
         // Test whether more than one user has album added to Q or Library
         if(testMultiUser($albumid) == 1) {
            foreach ($builders as $fn) {
               // Call methods to remove album information, tracks, and images
               $conn->query(call_user_func($fn, $albumid));
            }// end nested foreach
         }// end if

         // If Library album, delete from Album_Rating
         if ($type == 'r') {
            $s = "DELETE FROM Album_Rating WHERE ";
            $s .= "UserID = ".$userid." AND ";
            $s .= "AlbumID = \"".$albumid."\"";

            $conn->query($s);
         }

         // Delete from Albums table
         $s = "DELETE FROM Albums WHERE ";
         $s .= "UserID = ".$userid." AND ";
         $s .= "AlbumID = \"".$albumid."\"";


         $conn->query($s);
      }// end foreach

   }// end removeHandler
?>