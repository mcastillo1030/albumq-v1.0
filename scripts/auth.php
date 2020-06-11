<?php
   require '../vendor/autoload.php';
   $session = new SpotifyWebAPI\Session(
      '99dd8610f2a84d12a795823818c2a5f1',
      '255a677abf554551baa4d2228717a6cf',
      'https://www.marloncastillo.dev/samples/albumq/scripts/login.php'
   );

   $options = [
      'scope' => [
         'user-read-email',
         'user-read-private',
      ],
   ];

   header('Location: '.$session ->getAuthorizeUrl($options));
   die();
?>