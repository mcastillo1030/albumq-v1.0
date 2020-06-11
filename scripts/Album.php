<?php
   /*
    * Name: Marlon Castillo
    * File: Album.php
    *
    * This is the definition of the superclass Album
    */
   
   class Album implements JsonSerializable {

      /*
       * The following variables hold the Album
       * name, artist, label, and release date
       * respectively. The $id variable holds the
       * album's Spotify ID, while the $tracks
       * and $artwork variables are associative 
       * arrays to store multiple tracks and
       * multiple album artwork sizes and
       * their URLs. 
       */
      private $title, $last_update,
         $artist, $label,
         $release, $id,
         $tracks, $artwork;

      // ---------------------------------------------
      //                 HELPER METHODS              |
      // ---------------------------------------------

      /*
       * A helper method that sets the tracks or artwork
       * associative arrays from a Spotify Album object
       * @param   $name The property name
       * @param   $value   The property value
       * @return  none
       */
      protected function setArrayObj($name, $value) {
         if ($name == "tracks") {
            foreach ($value as $track) {
               //$key = $track['track_number'];
               $this->tracks[$track->track_number] = $track->name;
            }
         } else {
            foreach ($value as $image) {
               $this->artwork[$image->width] = $image->url;
            }
         }
      } // end setArrayObj() helper

      protected function setArray($name, $value) {
         if ($name == "tracks") {
            foreach($value as $number => $title) {
               $this->tracks[$number] = $title;
            }
         } else {
            foreach($value as $width => $url) {
               $this->artwork[$width] = $url;
            }
         }// end if=else
      }// end setArray() helper

      // ---------------------------------------------
      //           CONSTRUCTORS SECTION              |
      // ---------------------------------------------
      
      function __construct() {
         $this->title = $this->artist = 
         $this->label = $this->release = 
         $this->id = $this->last_update = "";
         $this->tracks = $this->artwork = array();
      } // end constructor

      /*
       * An alternate function to serve as a type
       * of "overloaded constructor." This function will
       * accept an associative array that represents
       * album information that came to the server
       * directly from the Spotify API and will then
       * extract the information and build an Album
       * object instance and return it.
       * @param   $a An associative array representation of an Album from the Spotify API
       * @return  An Album object instance
       */
      public static function constructFromObject($a) {
         $instance = new self();
         $instance->title = $a->name;
         $instance->artist = $a->artists[0]->name;
         $instance->label = $a->label;
         $instance->release = $a->release_date;
         $instance->id = $a->id;

         /*
         foreach($a['tracks']['items'] as $track) {
            $instance->addTrack($track['track_ number'], $track['name']);
         }

         foreach($a['images'] as $image) {
            $instance->addArtwork($image['width'], $image['url']);
         }*/
         $instance->setArrayObj("tracks", $a->tracks->items);
         $instance->setArrayObj("artwork", $a->images);

         return $instance;
      } // end constructFromJSON()
      
      // ---------------------------------------------
      //              ACCESSOR METHODS              |
      // ---------------------------------------------

      /*
       * A magic accessor method to set the object's
       * attributes.
       * @param   $name The name of the attribute to set
       * @param   $value   The value to set it to
       * @return  none
       */
      public function __set($name, $value) {
         // if $name = tracks or artwork, pass to helper function
         if ($name == "tracks" || $name == "artwork") {
            $this->setArray($name, $value);
         } else {
            $this->$name = $value;
         } // end if-else
      } // end magic __set()

      /*
       * A magic accessor method to get the object's
       * attributes.
       * @param   $name The name of the attribute to set
       * @return  Thee value of the requested attribute
       */
      public function __get($name) {
         return $this->$name;
      }// end magic __get()

      /*
       * A special accessor method to set tracks of 
       * the Album object one at a time.
       * @param   $n   The track's number
       * @param   $t   The track's title
       * @return  none
       */
      public function addTrack($n, $t) {
         $this->tracks[$n] = $t;
      }// end addTrack()

      /*
       * A special accessor method to set the artwork
       * of the Album object one at a time.
       * @param   $w The artwork width
       * @param   $u The artwork URL
       * @return  none
       */
      public function addArtwork($w, $u) {
         $this->artwork[$w] = $u;
      }// end addArtwork()

      /*
       * Implementation of the JsonSerializable interface
       * @param   none
       * @return  Associative array with class' properties
       */
      public function jsonSerialize() {
         $obj = array(
            "title" => $this->title,
            "artist" => $this->artist,
            "label" => $this->label,
            "release" => $this->release,
            "id" => $this->id,
            "last_update" => $this->last_update,
            "artwork" => $this->artwork,
            "tracks" => array()
         );


         foreach($this->tracks as $track_number => $track_name) {
            $newIndex = &$obj['tracks'][];
            $newIndex['track_number'] = $track_number;
            $newIndex['track_name'] = $track_name;
         }

         //return get_object_vars($this);
         return $obj;
      }// end jsonSerialize()
   } // end class
?>