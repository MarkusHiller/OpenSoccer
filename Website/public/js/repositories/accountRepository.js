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
        login: function (data, success, error) {
          $http.post('/api.php/login', data).then(success, error);
        },
        logout: function(callback) {
          this.isAuthenticated = false;
          $http.get('/api.php/logout');
          callback();
        },
        changePassword: function (data, success, error) {
          $http.put('/api.php/changePassword', data).then(success, error);
        },
        forgotPassword: function (data, success, error) {
          $http.post(baseUrl + '/forgotPassword', data).then(success, error);
        }
      };

      init();

      return service;

    }]);
})();
