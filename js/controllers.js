app.controller('dashCtrl', function ($scope, albumService) {
   $scope.ratingGroup = false;

   $scope.deleteFromQ = function (id) {
      albumService.q.remove(id);
   };

   $scope.deleteFromR = function (id) {
      albumService.rated.remove(id);
   }

   $scope.moveToR = function (id, r, c) {
      albumService.q.moveToRated(id, r, c);
   };

   $scope.updateRating = function (id, r, c) {
      albumService.rated.updateRating(id, r, c);
   };
});

app.controller('searchCtrl', function ($scope, $http, $window, albumService) {
   $scope.isLoading = false;
   $scope.noResults = false;
   $scope.showEnlarged = false;

   $scope.search = function(e) {
      e.preventDefault();
      $scope.isLoading = true;

      $http.get("scripts/search.php?query=" + $scope.query.trim())
      .then(function (response) {
         if (response.status == 200 && response.data.albums.total > 0) {
            $scope.results = response.data.albums.items;
         } else if (response.status == 204 || response.data.albums.total == 0) {
            $scope.noResults = true;
         }
      }, function (response) {
         if (response.status == 401) {
            $window.location.href = "index.html";
         }
      }).finally(function () {
         $scope.isLoading = false;
      });
   };

   $scope.clear = function () {
      $scope.noResults = false;
      $scope.results = null;
   };

   $scope.enlarge = function(id) {
      $scope.isLoading = true;
      $http.get("scripts/get_album.php?albumid=" + id)
      .then(function (response) {

         if(response.data.album) {
            $scope.selected = response.data.album;
            $scope.showEnlarged = true;
         } // end nested if
      }).finally(function () {
         $scope.isLoading = false;
      });
      // set showEnlarged to true
   };

   $scope.hide = function (e) {
      let modal = document.getElementById("search-result-overlay");
      if (e.target == modal) {
         $scope.showEnlarged = false;
      }
   }

   $scope.$on('closeClick', function(event, data) {
      event.preventDefault();
      let closeBtn = document.getElementById("modal-close");
      let qBtn = document.getElementById("q-btn")
      if (data.target == closeBtn || data.target == qBtn) {
         $scope.showEnlarged = false;
      }
   });

   $scope.addQ = function (id) {
      albumService.q.add(id);
   }

   $scope.addR = function (id, r, c) {
      albumService.rated.add(id, r, c);
   };
});

app.controller('libraryCtrl', function ($scope, albumService) {
   $scope.libraryRemove = function (id) {
      albumService.rated.remove(id);
   };

   $scope.libraryUpdate = function (id, r, c) {
      albumService.rated.updateRating(id, r, c);
   };

   $scope.sortOptions = [
      { value: 'releaseComparator', name: 'Release'},
      { value: 'titleComparator', name: 'Title'},
      { value: 'recentComparator', name: 'Recent'},
      { value: 'artistComparator', name: 'Artist'}
   ];

   $scope.releaseComparator = function (v1, v2) {
      if (v1 && v2) {
         let date1 = new Date(v1.value.release);
         let date2 = new Date(v2.value.release);

         return (date1 < date2) ? -1 : 1;
      }
   };

   $scope.titleComparator = function (v1, v2) {
      if (v1 && v2) {
         return (v1.value.title < v2.value.title) ? -1 : 1;
      }
   };

   $scope.recentComparator = function (v1, v2) {
      if (v1 && v2) {
         let date1 = new Date(v1.value.last_update);
         let date2 = new Date(v2.value.last_update);

         return (date1 > date2) ? -1 : 1;
      }
   };

   $scope.artistComparator = function (v1, v2) {
      if (v1 && v2) {
         return (v1.value.artist < v2.value.artist) ? -1 : 1;
      }
   };
});

app.controller('accountCtrl', function ($scope, userService, $window) {
   $scope.showModal = false;
   $scope.username = userService.getName;

   $scope.logout = function () {
      $window.location.href = "scripts/logout.php";
   };

   $scope.hide = function (e) {
      let overlay = document.getElementById("delete-overlay");
      if (e.target == overlay) {
         $scope.showModal = false;
      }
   }

   $scope.$on('cancelClick', function(event, data) {
      event.preventDefault();
      let closeBtn = document.getElementById("delete-close");
      if (data.target == closeBtn) {
         $scope.showModal = false;
      }
   });

   $scope.deleteAccount = function () {
      $scope.showModal = true;
   }
});