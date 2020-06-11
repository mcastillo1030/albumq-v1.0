<?php
   /*
    * Name: Marlon Castillo
    * File: Rated_Album.php
    *
    * This is the definition of the class Rated_Album
    */
   
   // Load Album class definition
   require ('Album.php');

   class Rated_Album extends Album implements JsonSerializable {
      /*
       * The following variables hold the Album
       * rating from the user and any comments
       * that they may have stored. 
       */
      private $rating, $comments;

      // ---------------------------------------------
      //           CONSTRUCTORS SECTION              |
      // ---------------------------------------------
      function __construct() {
         parent::__construct();
         $this->rating = "";
         $this->comments = "";
      } // end default constructor

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
         //$instance =  Album::constructFromJSON($a);
         $instance = new self();
         $instance->title = $a->name;
         $instance->artist = $a->artists[0]->name;
         $instance->label = $a->label;
         $instance->release = $a->release_date;
         $instance->id = $a->id;
         $instance->rating = "";
         $instance->comments = "";

         $instance->setArray("tracks", $a->tracks->items);
         $instance->setArray("artwork", $a->images);

         return $instance;
      }// end constructFromJSON()

      public static function constructFromAlbum($a) {
         $instance = new self();
         $instance->title = $a->title;
         $instance->artist = $a->artist;
         $instance->label = $a->label;
         $instance->release = $a->release;
         $instance->id = $a->id;
         $instance->rating = "";
         $instance->comments = "";

         $instance->tracks = $a->tracks;
         $instance->artwork = $a->artwork;

         return $instance;
      }

      // ---------------------------------------------
      //              ACCESSOR METHOD              |
      // ---------------------------------------------

      /*
       * A modified version of the parent class' magic
       * accessor method to set the object's attributes.
       * 
       * @param   $name The name of the attribute to set
       * @param   $value   The value to set it to
       * @return  none
       */
      public function __set($name, $value) {
         if ($name == "comments" || $name == "rating") {
            $this->$name = $value;
         } else {
            parent::__set($name, $value);
         }
      } // end magic __set()

      /*
       * A modified version of the parent class' magic
       * accessor method to get the object's attributes.
       * 
       * @param   $name The name of the attribute to set
       * @return  The attribute's value
       */
      public function __get($name) {
         if($name == "comments" || $name == "rating") {
            return $this->$name;
         } else {
            return parent::__get($name);
         }
      }

      /*
       * Overrides the parent class' jsonSerialize method.
       * Implementation of the JsonSerializable interface
       * 
       * @param   none
       * @return  Associative array with class' properties
       */
      public function jsonSerialize() {
         $obj = parent::jsonSerialize();
         $obj['rating'] = $this->rating;
         $obj['comments'] = $this->comments;

         //return get_object_vars($this);
         return $obj;
      }// end jsonSerialize()
   } // end class
?>