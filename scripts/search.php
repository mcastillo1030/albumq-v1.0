<?php
  require '../vendor/autoload.php';
   
  /*
   * Name: Marlon Castillo
   * File: search.php
   *
   * This script handles user search queries. It 
   * uses the vendor scripts create a new API 
   * wrapper to communicate with the Spotify
   * search endpoint, then returns the result
   * data object back to the front end for
   * rendering.
   */

  // ---------------------------------------------
  //             AUTH FLOW VERSION BELOW          |
  // --------------------------------------------- 

  session_start(); // this line under vendor autoload
  include 'utils.php';

  $api = new SpotifyWebAPI\SpotifyWebAPI();
  $api->setAccessToken(
     tokenRefresher(
        $_SESSION['access_token'], 
        $_SESSION['refresh_token'], 
        $_SESSION['expires']
     )
  );

  $query = htmlspecialchars($_REQUEST['query']);

  try {
     $result = $api->search($query, 'album');
  } catch (Exception $e) {
     http_response_code($e->getCode());
     $result = $e->getMessage();
  }
  
  header('Content-type: application/json; charset=utf-8');
  echo json_encode($result, JSON_PRETTY_PRINT | 
  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | 
  JSON_NUMERIC_CHECK);
?>