<?php
   require '../vendor/autoload.php';
   session_start();

   $session = new SpotifyWebAPI\Session(
      '99dd8610f2a84d12a795823818c2a5f1',
      '255a677abf554551baa4d2228717a6cf',
      'https://www.marloncastillo.dev/samples/albumq/scripts/login.php'
   );
  
   if (!isset($_GET['code'])) {
      header('Location: ../index.html');
      die();
   } else {
      // Request an access token using the code from Spotify
      $session->requestAccessToken($_GET['code']);
      $accessToken = $session->getAccessToken();
      $refreshToken = $session->getRefreshToken();
   
      // Store the access and refresh tokens somewhere.
      $_SESSION['access_token'] = $accessToken;
      $_SESSION['refresh_token'] = $refreshToken;
      $_SESSION['expires'] = time() + $session->getExpires();

      $api = new SpotifyWebAPI\SpotifyWebAPI();
      $api->setAccessToken($accessToken);
      $user = $api->me();

      require 'connect_db.php';
      $conn = getConnection();

      if (!$conn) {
         // Use a template instead
         echo "<h1>Server</h1>";
         echo "<p>Connection to server could not be established. Try again later.</p>";
      } else {
         $sql = "SELECT UserID FROM Users WHERE Name =\"".$user->display_name."\"";
         $result = $conn->query($sql);

         if ($result->num_rows === 0) {
            $sql = "INSERT INTO Users(Name) VALUES(\"".$user->display_name."\")";
            $conn->query($sql);
            $userid = $conn->insert_id;
         } else {
            $row = $result->fetch_assoc();
            $userid = $row['UserID'];
         }// end nested if-else

         // Store the User ID somewhere
         $_SESSION['userid'] = $userid;

         // Close db connection
         $result->close();
         $conn->close();

         // Send the user along and fetch some data!
         header('Location: ../home.html');
         die();
      }// end db connection checker if-else
   } // end auth code checker if-else
?>