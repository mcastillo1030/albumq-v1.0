<?php
   require_once '../vendor/autoload.php';

   function tokenRefresher($access, $refresh, $exp) {
      $now = time();

      if ($now > $exp) {
         $session = new SpotifyWebAPI\Session(
            '99dd8610f2a84d12a795823818c2a5f1',
            '255a677abf554551baa4d2228717a6cf',
            'https://www.marloncastillo.dev/samples/albumq/scripts/login.php'
         );

         $session->refreshAccessToken($refresh);
         return $session->getAccessToken();
      } else {
         return $access;
      }
   }// end tokenRefresher function

   function getBuilders() {
      $builders = [  // SQL query builders 
         "album_info" => function($album) {
            $s = "INSERT IGNORE INTO Album_Info VALUES (\"";
            $s .= $album->id."\", \"".$album->title."\", \"";
            $s .= $album->artist."\", \"".$album->label."\", \"";
            $s .= $album->release."\")";
   
            return $s;
         },
         "album_tracks" => function($album) {
            $s = "INSERT IGNORE INTO Album_Tracks VALUES ";
   
            // Store associative array so we can move array pointer
            $track_array = $album->tracks;
   
            for ($i = sizeof($track_array); $i > 0; $i--) {
               $number = key($track_array);
   
               $s .= "(\"".$album->id."\", ".$number.", \"";
               $s .= $track_array[$number]."\")";
   
               if ($i > 1) {
                  $s .= ", ";
               } //end if
   
               next($track_array);
            } // end for loop 
   
            return $s;
         }, 
         "album_photos" => function($album) {
            $s = "INSERT IGNORE INTO Album_Image VALUES ";
   
            // Store associative array so we can move array pointer
            $img_array = $album->artwork;
   
            for ($i = sizeof($img_array); $i > 0; $i--) {
               $width = key($img_array);
   
               $s .= "(\"".$album->id."\", \"".$width."\", \"";
               $s .= $img_array[$width]."\")";
   
               if ($i > 1) {
                  $s .= ", ";
               } // end if
   
               next($img_array);
            } // end for loop
   
            return $s;
         }
      ];

      return $builders;
   }
?>