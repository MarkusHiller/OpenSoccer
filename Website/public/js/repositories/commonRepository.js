(function () {
  'use strict';

  angular.module('os2')
    .factory('commonRepository', ['$http', function ($http) {

      var service = {
        getTopmanager: function (success, error) {
          $http.get('/api.php/getTopmanager').then(success, error);
        },
        getInfocounts: function (success) {
          $http.get('/api.php/getInfocounts').then(success, error);
        }
      };

      function error() {

      }

      return service;

    }]);
})();
