(function () {
  'use strict';

  angular.module('os2')
    .factory('teamRepository', ['$http', function ($http) {

      var service = {
        getSelectableTeams: function (success) {
          $http.get('/api.php/getSelectableTeams').then(success, error);
        },
        saveNomination: function(data, success) {
          $http.post('/api.php/updateNomination', data).then(success, error);
        },
        takeNomination: function(data, success) {
          $http.post('/api.php/takeNomination', data).then(success, error);
        },
        getNominationData: function(success) {
          $http.get('/api.php/getNominationData').then(success, error);
        }
      };

      function error() {

      }

      return service;

    }]);
})();
