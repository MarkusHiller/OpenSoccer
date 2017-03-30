(function () {
  'use strict';

  angular.module('os2')
    .factory('accountRepository', ['$http', function ($http) {

      function init() {
        $http.get("/api.php/getLoginState").then(function (result) {
          service.isAuthenticated = result.data === "true" ? true : false;
        }, null)
      }

      var service = {
        isAuthenticated: false,
        username: '',
        login: function (data, success, error) {
          this.username = data.username;
          $http.post('/api.php/login', data).then(success, error);
        },
        logout: function (callback) {
          this.isAuthenticated = false;
          $http.get('/api.php/logout');
          callback();
        },
        changePassword: function (data, success, error) {
          $http.put('/api.php/changePassword', data).then(success, error);
        },
        changeTeam: function (data, success) {
          $http.post('/api.php/changeTeam', data).then(success, error);
        },
        forgotPassword: function (data, success, error) {
          $http.post(baseUrl + '/forgotPassword', data).then(success, error);
        },
        checkDataForRegistration: function (data, success, error) {
          $http.post('/api.php/checkDataForRegistration', data).then(success, error);
        },
        registerUser: function (data, success) {
          $http.post('/api.php/registerUser', data).then(success, error);
        }
      };

      function error() {

      }

      init();

      return service;

    }]);
})();
