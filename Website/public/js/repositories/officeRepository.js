(function () {
  'use strict';

  angular.module('os2')
    .factory('officeRepository', ['$http', function ($http) {

      var service = {
        getCentralData: function (success, error) {
          $http.get('/api.php/getCentralData').then(success, error);
        },
        getProtocolData: function (success, error) {
          $http.get('/api.php/getProtocolData').then(success, error);
        },
        getNotes: function (success, error) {
          $http.get('/api.php/getNotes').then(success, error);
        },
        saveNote: function (data, success, error) {
          $http.post('/api.php/saveNote', data).then(success, error);
        },
        getSettingsData: function (success, error) {
          $http.get('/api.php/getSettingsData').then(success, error);
        },
      };

      return service;

    }]);
})();
