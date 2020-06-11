var app = angular.module("albumq", ['ngRoute', 'ngAnimate'])
   .config(function($routeProvider, $httpProvider) {
      $routeProvider
      .when("/", {
         templateUrl : "pages/dashboard.htm",
         controller : "dashCtrl"
      })
      .when("/search", {
         templateUrl : "pages/search.htm",
         controller : "searchCtrl"
      })
      .when("/library", {
         templateUrl : "pages/library.htm",
         controller : "libraryCtrl"
      })
      .when("/account", {
         templateUrl : "pages/account.htm",
         controller : "accountCtrl"
      })
      .otherwise({redirectTo: "/"});

      $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
   })
   .run(['$rootScope', '$location', function ($rootScope, $location) {
      let path = function () {
         return $location.path();
      };

      $rootScope.$watch(path, function(newVal, oldVal) {
         $rootScope.activetab = newVal;
      });
   }])
   .directive('qAlbumCard', function () {
      let directive = {};

      directive.restrict = 'A';
      directive.templateUrl = "pages/q-album-template.htm";
      directive.scope = {
         album: '=',
         remove: '&',
         move: '&',
      };
      directive.controller = function ($scope) {

         $scope.delete = function () {
            $scope.remove()($scope.album.id);
         };

         $scope.rate = function (rating, comment) {
            $scope.move()(
               $scope.album.id,
               rating, comment
            );
         };

         $scope.release = new Date($scope.album.release);
         
         $scope.pad = function (number) {
            return (number < 10) ? "0" + number : number;
         };
      };

      return directive;
   })
   .directive('ratedAlbumCard', function () {
      let directive = {};

      directive.restrict = 'A';
      directive.templateUrl = "pages/rated-album-template.htm";
      directive.scope = {
         album: '=',
         remove: '&',
         update: '&',
      };

      directive.controller = function ($scope) {
         // Fixes outputs for iOS
         function dateFixer(s) {
            let str = s.replace(' ', 'T');
            return str;
         }

         $scope.updated = new Date(dateFixer($scope.album.last_update));

         $scope.release = new Date($scope.album.release);

         $scope.delete = function () {
            $scope.remove()($scope.album.id);
         };

         $scope.rate = function(r, c) {
            $scope.update()($scope.album.id, r, c);
         };

         $scope.pad = function (number) {
            return (number < 10) ? "0" + number : number;
         };    
         
      };

      return directive;
   })
   .directive('optionsExpandBtn', function () {
      let directive = {
         link:  link,
         restrict: 'A',
         template: "Options",
         require: ['^?ratedAlbumCard', '^?qAlbumCard']
      };

      return directive;

      function link(scope, element, attrs, btnCtrl) {
         element.on('click', function() {
            function toggleShow() {
               if ($(this).css("visibility") == "hidden") {
                  return "visible";
               } else {
                  return "hidden"
               }
            }

            let body = $(element).parents(".tile-footer").prev();

            $(element).toggleClass("attention");
            if ($(element).hasClass("attention")) {
               $(element).text("Options");
               $([document.documentElement, document.body]).animate({
                  scrollTop: $(element).parents(".album-tile").offset().top
               }, 1000);
            } else {
               $(element).text("Cancel");
               $([document.documentElement, document.body]).animate({
                  scrollTop: $(body).offset().top
               }, 1000);
            }
            
            $(body).css("max-height", function () {
               let height = $(this)[0].scrollHeight;

               if ($(this).css("max-height") == "0px") {
                  return height + "px";
               } else {
                  return 0;
               }
            });

            $(element).next().css("visibility", toggleShow);
            $(element).next().next().css("visibility", toggleShow).toggleClass("attention");
            
         });
      }
   })
   .directive('ratingExpandBtn', function ()  {
      let directive = {
         link: link,
         restrict: 'A',
         require: ['^?ratedAlbumCard', '^?qAlbumCard', '^?searchResultOverlay']
      };

      return directive;

      function link(scope, element, attrs) {
         element.click(function () {
            function animateHeight() {
               let height = $(this)[0].scrollHeight;

               if ($(this).css("max-height") == "0px") {
                  return height + "px";
               } else {
                  return 0;
               }
            }

            let ratingGroup = $(this).parent().prev();
            let buttonGroup = $(this).parent();
            let searchBody = $(this).parent().parent().prev();

            $(ratingGroup).css("max-height", animateHeight);
            $([document.documentElement, document.body]).animate({
               scrollTop: $(ratingGroup).offset().top - (($(ratingGroup).offset().top - $(window).scrollTop()) / 2)
            }, 1000);
            $(buttonGroup).css("max-height", animateHeight);

            if($(searchBody).hasClass("overlay-tile-body")) {
               $(searchBody).css("max-height", animateHeight);
            }
         });
      }
   })
   .directive('ratingShrinkBtn', function () {
      let directive = {
         restrict: 'A',
         require: '^ratingGroup',
         scope: {
            type: '@',
            input: '@',
            submit: '&'
         }
      };
      
      directive.controller = function ($scope, $element) {
         $element.click(function () {
            function animateHeight() {

               if ($(this).css("max-height") == "0px") {
                  return "2.5rem";
               } else {
                  return 0;
               }
            }

            let validInput = false;

            if ($scope.type == "submit") {
               let regex = /^[0-9](\.[0-9])?$|^10$/;
               if (regex.test($scope.input)) {
                  validInput = true;
               }
            } else {
               validInput = true;
            }

            if (validInput) {
               console.log("valid input in dom manipulation");
               let ratingGroup = $(this).parents(".card-rating-group");
               let buttonGroup = $(this).parents(".card-rating-group").next();
   
               $(ratingGroup).css("max-height", animateHeight);
               $(buttonGroup).css("max-height", animateHeight);
               $([document.documentElement, document.body]).animate({
                  scrollTop: $(buttonGroup).parents(".tile-footer").prev().offset().top
               }, 1000);
   
               let searchBody = $(this).parents(".tile-footer").prev();
               if($(searchBody).hasClass("overlay-tile-body")) {
                  $(searchBody).css("max-height", "15rem");
               }

               if ($scope.type == "submit") {
                  $scope.submit()($scope.input);
               }
            }

            
         });

         
      }

      return directive;

   })
   .directive('searchResult', function() {
      let directive = {};
      
      directive.restrict = 'A';
      directive.templateUrl = "pages/search-result-template.htm";

      return directive;
   })
   .directive('searchResultOverlay', function () {
      let directive = {};

      directive.restrict = 'A';
      directive.templateUrl = "pages/search-result-overlay-template.htm";
      directive.scope = {
         album: '=',
         queue: '&',
         rated: '&'
      };
      directive.controller = function ($scope) {

         $scope.q = function (e) {
            $scope.queue()($scope.album.id);
            $scope.cancel(e);
         };

         $scope.r = function (rating, comment) {
            $scope.rated()($scope.album.id, rating, comment);
         };

         $scope.cancel = function (e) {
            $scope.$emit('closeClick', e);
         };

         $scope.release = new Date($scope.album.release);
      };

      return directive;
   })
   .directive('ratingGroup', function () {
      let directive = {};

      directive.restrict = 'A';
      directive.templateUrl = "pages/rating-group-template.htm";
      directive.scope = {
         rate: '=',
         comment: '@',
         hide: '&',
         action: '&',
      };
      directive.controller = function ($scope) {
         $scope.rating = ($scope.rate) ? $scope.rate : 0;

         $scope.save = function() {
            $scope.action()(
               Number.parseFloat($scope.rating).toFixed(1),
               $scope.comment
            );
         };// end save function

         $scope.close = function () {
            $scope.hide();
         };
      };// end controller

      return directive;
   })
   .directive('deleteModal', function() {
      let directive = {
         restrict: 'A',
         templateUrl: "pages/delete-modal-template.htm",
      };

      directive.controller = function ($scope, $window) {
         $scope.delete = function () {
            $window.location.href = "scripts/delete_account.php";
         };

         $scope.cancel = function (e) {
            $scope.$emit('cancelClick', e);
         };
      };

      return directive;
   })
   .factory('notificationSvc', function () {
      var notificationSvc = {};

      // ----------------------------------------------
      //          Set up notifications system         |
      // ----------------------------------------------
      notificationSvc.type = "";
      notificationSvc.message = "";

      notificationSvc.setType = function (t) {
         if (t == 'Initial') {
            notificationSvc.shown();
         } else {
            notificationSvc.type = t;
         }
      };

      notificationSvc.setMessage = function (s) {
         if (notificationSvc.type) {
            notificationSvc.message = s;
         }

         notificationSvc.monitor();
      };

      notificationSvc.isShowable = function () {
         return (notificationSvc.type && notificationSvc.message) ? true : false;
      }

      notificationSvc.shown = function () {
         notificationSvc.type = "";
         notificationSvc.message = "";
      }

      notificationSvc.monitor = function () {
         if (notificationSvc.isShowable()) {
            // DOM manipulation here
            switch (notificationSvc.type) {
               case "Unexpected":
               case "Duplicate":
                  console.log("I'm in duplicate");
                  $("#alert-inner img")
                  .attr("src", "images/unexpected-icon.svg")
                  .attr("alt", "An exclamation point signaling a warning.");
                  $("#alert").css({
                     "background-color": "var(--accent-color)",
                     "color": "var(--card-font-color)"
                  });
                  break;
               case "Server":
                  $("#alert-inner img")
                  .attr("src", "images/server-icon.svg")
                  .attr("alt", "A large X signaling an error.");
                  $("#alert").css({
                     "background-color": "var(--danger-color)",
                     "color": "var(--highlight-color)"
                  });
                  break;
               case "Success":
                  $("#alert-inner img")
                  .attr("src", "images/success-icon.svg")
                  .attr("alt", "A large check mark signaling success.")
                  $("#alert").css({
                     "background-color": "var(--success-color)",
                     "color": "var(--card-font-color)"
                  });
                  break;
               default:
                  notificationSvc.shown();
            }

            $("#alert-inner p").text(notificationSvc.message);

            $("#alert").animate({
               "max-height": "5rem"
            }).delay(700).animate({
               "max-height": 0
            });

            notificationSvc.shown();
         }
      };

      return notificationSvc;
   })
   .factory('albumService', function ($http, $httpParamSerializer, $window, notificationSvc) {
      var albumService = {};
      albumService.q = {};
      albumService.rated = {};
      
      
      albumService.q.albums = [];
      albumService.rated.albums = [];
      
      // ----------------------------------------------
      //       Helper functions for albumService      |
      // ----------------------------------------------

      // Validates the message received from server
      function validatedResponse(type, message) {
         let valid = false;
         if (type == "Unauthorized") {
            $window.location.href = "index.html";
         } else if (type == "Success") {
            valid = true;
         } // end if
         
         notificationSvc.setType(type);
         notificationSvc.setMessage(message);
         
         return valid;
      } // end validatedResponse
      
      // Helper function to fill albums arrays
      function initialize(list, r) {
         if (validatedResponse(r.type, r.message)) {
            notificationSvc.setType("Initial");
            
            if (r.album_count > 0) {
               if (list == 'q') {
                  albumService.q.albums = r.albums;
               } else {
                  albumService.rated.albums = r.albums;
               } // end nested if-else
            }// end nested if
         } // end if
      } // end initialize

      function getTimestamp() {
         let now = new Date();
         let stamp = now.getFullYear() + "-";
         
         stamp += (now.getMonth() < 9) ? "0" + (now.getMonth() + 1) : now.getMonth() + 1;
         stamp += "-";
         stamp += (now.getDate() < 10) ? "0" + now.getDate() : now.getDate();
         stamp += " ";
         stamp += (now.getHours() < 10) ? "0" + now.getHours() : now.getHours();
         stamp += ":";
         stamp += (now.getMinutes() < 10) ? "0" + now.getMinutes() : now.getMinutes();
         stamp += ":";
         stamp += (now.getSeconds() < 10) ? "0" + now.getSeconds() : now.getSeconds();

         return stamp;
      }// end getTimestamp helper
      
      function addHelper(list, album) {
         if (list == 'q') {
            albumService.q.albums.push(album);
         } else {
            album.last_update = getTimestamp();
            albumService.rated.albums.push(album);
         } // end if-else
      } // end addHelper
   
      function removeHelper(list, id) {
         let ary = (list == 'q') ? albumService.q.albums : albumService.rated.albums;
         
         for (let i = 0; i < ary.length; i++) {
            let thisAlbum = ary[i];
            if (thisAlbum.id == id) {
               ary.splice(i, 1);
            } // end if
         } // end for
      } // end removeHelper
      
      function getFromQHelper(id) {
         let result;

         for (let i = 0; i < albumService.q.albums.length; i++) {
            if (albumService.q.albums[i].id == id) {
               result = albumService.q.albums[i];
            }
         } // end for

         return result;
      } // end getFromQHelper
      
      function updateHelper(id, r, c) {
         let ary = albumService.rated.albums;
         for (let i = 0; i < ary.length; i++) {
            if (ary[i].id == id) {
               ary[i].rating = r;
               ary[i].comments = c;
               ary[i].last_update = getTimestamp();
            }
         } // end for
      } // end updateHelper function
      
      // Make initial HTTP requests to fill arrays
      $http.get("scripts/get_albums_user_q.php")
      .then(function (response) {
         initialize('q', response.data);
      });
      
      $http.get("scripts/get_albums_user_r.php")
      .then(function (response) {
         initialize('r', response.data);
      });
      
      albumService.q.list = function () {
         return albumService.q.albums;
      };
      
      albumService.rated.list = function () {
         return albumService.rated.albums;
      };
      
      albumService.q.add = function (id) {
         $http.get("scripts/get_album.php?albumid=" + id)
         .then(function (response) {
            var temp;
            if (validatedResponse(response.data.type, response.data.message)) {
               temp = response.data.album;
               $http.post("scripts/add_album_q.php?albumid=" + id)
               .then(function (response) {
                  if (validatedResponse(response.data.type, response.data.message)) {
                     addHelper('q', temp);
                  } // end if
               });
            } // end if
         });
      }; // end q.add function
      
      albumService.rated.add = function (id, r, c) {
         $http.get("scripts/get_album.php?albumid=" + id)
         .then(function (response) {
            var temp;
            if (validatedResponse(response.data.type, response.data.message)) {
               temp = response.data.album;
               temp.rating = r;
               temp.comments = c;
               
               let data = {
                  albumid : id,
                  rating : r,
                  comment : c
               };
               
               let scriptUrl = "scripts/add_album_r.php?" + $httpParamSerializer(data);
               
               $http.post(scriptUrl)
               .then(function (response) {
                  if (validatedResponse(response.data.type, response.data.message)) {
                     addHelper('r', temp);
                  }// end if
               });
            }// end if
         });
      }; // end rated.add function
      
      albumService.q.remove = function (id) {
         $http.post("scripts/remove_q.php?albumid=" + id)
         .then(function (response) {
            if (validatedResponse(response.data.type, response.data.message)) {
               removeHelper('q', id)
            }// end if
         });
      }; // end q.remove function
      
      albumService.rated.remove = function (id) {
         $http.post("scripts/remove_rated.php?albumid=" + id)
         .then(function (response) {
            if (validatedResponse(response.data.type, response.data.message)) {
               removeHelper('r', id);
            }// end if
         });
      };//  end rated.remove function
      
      albumService.q.moveToRated = function (id, r, c) {
         // call script to move album
         let data = {
            albumid : id,
            rating : r,
            comment : c
         };
         
         let scriptUrl = "scripts/move_to_rated.php?" + $httpParamSerializer(data);
         
         $http.post(scriptUrl)
         .then(function (response) {
            if (validatedResponse(response.data.type, response.data.message)) {
               let album = getFromQHelper(id);
               album.rating = r;
               album.comments = c;
               addHelper('r', album);
               removeHelper('q', id);
            }// end if
         });
         // on success, copy album from q, add r & call and add to rated array
         // remove from queue array
      }; // end q.moveToRated function
      
      albumService.rated.updateRating = function (id, r, c) {
         let data = { albumid : id,
            rating : r,
            comment : c
         };
         
         let scriptUrl = "scripts/update_rating.php?" + $httpParamSerializer(data);
         
         $http.post(scriptUrl)
         .then(function (response) {
            if (validatedResponse(response.data.type, response.data.message)) {
               updateHelper(id, r, c);
            }
         });
      }; // end r.updateRating function
      
      return albumService;
   })
   .factory('userService', function ($http, $window) {
      var userService = {};
      userService.userName = "";
      userService.notification = {};

      function validatedResponse(type, message) {
         let valid = false;
         if (type == "Unauthorized") {
            $window.location.href = "index.html";
         } else if (type == "Success") {
            valid = true;
         }// end if-else

         userService.notification.type = type;
         userService.notification.message = message;

         return valid;
      }

      function initialize(r) {
         userService.userName = r.user_name;
         userService.notification.type = "Initial";
      }// end initialize 

      $http.get("scripts/get_user.php")
      .then(function (response) {
         if (validatedResponse(response.data.type, response.data.message)) {
            initialize(response.data);
         }// end if
      });

      userService.getName = function () {
         return userService.userName;
      };

      userService.hasNotification = function () {
         return (userService.notification.type);
      };

      return userService;
   });

app.controller('mainCtrl', function ($scope, albumService) {
   $scope.q = albumService.q.list;
   $scope.r = albumService.rated.list;
});
