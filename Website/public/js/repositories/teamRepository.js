(function () {
  'use strict';

  angular.module('os2')
    .factory('teamRepository', ['$http', function ($http) {

      var service = {
        getSelectableTeams: function (success) {
          $http.get('/api.php/getSelectableTeams').then(success, error);
        }
      };

      function error() {

      }

      return service;

    }]);
})();
