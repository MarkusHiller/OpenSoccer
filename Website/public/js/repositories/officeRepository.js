(function () {
  'use strict';

  angular.module('os2')
    .factory('officeRepository', ['$http', function ($http) {

      var service = {
        getCentralData: function (success) {
          $http.get('/api.php/getCentralData').then(success, error);
        },
        getProtocolData: function (data, success) {
          $http.get('/api.php/getProtocolData', { params: data }).then(success, error);
        },
        getNotes: function (success, error) {
          $http.get('/api.php/getNotes').then(success, error);
        },
        delNote: function (data, success) {
          $http.delete('/api.php/delNote', { params: data }).then(success, error);
        },
        saveNote: function (data, success, error) {
          $http.post('/api.php/saveNote', data).then(success, error);
        },
        getSettingsData: function (success, error) {
          $http.get('/api.php/getSettingsData').then(success, error);
        },
        searchForTeamOrManager: function (data, success) {
          $http.get('/api.php/searchForTeamOrManager', { params: data }).then(success, error);
        }
      };

      function error() {

      }

      return service;

    }]);
})();
