<?php
   require '../vendor/autoload.php';
   require 'Album.php';

   session_start();
   $response = array();

   if (!isset($_SESSION['userid'])) {  // Is user logged in?
      $response['type'] = "Unauthorized";
      $response['message'] = "You must be logged in to perform that action.";
   } else if (!isset($_REQUEST['albumid'])) {
      $response['type'] = "Unexpected";
      $response['message'] = "An unexpected error occurred. Please try again later.";
   } else if (isset($_SESSION['q'][$_REQUEST['albumid']])) {
      // if album is already in array, return that album
      $response['type'] = "Success";
      $response['message'] = "";
      $response['album'] = $_SESSION['q'][$_REQUEST['albumid']];
   } else {
      $userid = $_SESSION['userid'];
      $albumid = $_REQUEST['albumid'];


      // ---------------------------------------------
      //             AUTH FLOW VERSION BELOW          |
      // --------------------------------------------- 
      include 'utils.php';
      $api = new SpotifyWebAPI\SpotifyWebAPI();
      $api->setAccessToken(
         tokenRefresher(
            $_SESSION['access_token'], 
            $_SESSION['refresh_token'], 
            $_SESSION['expires']
         )
      );

      try {
         $result = $api->getAlbum($albumid);
         $album = Album::constructFromObject($result);
      } catch (Exception $e) {
         http_response_code($e->getCode());
         $result = $e->getMessage();
         header('Content-type: application/json; charset=utf-8');
         echo json_encode($result, JSON_PRETTY_PRINT | 
         JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | 
         JSON_NUMERIC_CHECK);
         die();
      }
      
      // Does queue variable exist?
      if (!isset($_SESSION['q'])) {
         $_SESSION['q'] = array(); 
      } 

      // Add album to session variable
      $_SESSION['q'][$album->id] = $album;
      $response['type'] = "Success";
      $response['message'] = "";
      $response['album'] = $album;
   } // end session checker if-else

   header('Content-type: application/json; charset=utf-8');
   echo json_encode($response,  JSON_PRETTY_PRINT | 
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | 
      JSON_NUMERIC_CHECK);
?>