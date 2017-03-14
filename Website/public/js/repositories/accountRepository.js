(function () {
  'use strict';

  angular.module('app.core')
    .factory('accountRepository', ['$http', function ($http) {

      function init() {
        $http.get("/api.php/getLoginState").success(function (result) {
          service.isAuthenticated = result === "true" ? true : false;
        })
      }

      var service = {
        isAuthenticated: false,
        login: function (data, success, error) {
          $http.post('/api.php/login', data).success(success).error(error);
        },
        changePassword: function (pw, success, error) {
          $http.put(baseUrl + '/changePassword', { password: pw }).success(success).error(error);
        },
        forgotPassword: function (data, success, error) {
          $http.post(baseUrl + '/forgotPassword', data).success(success).error(error);
        }
      };

      init();

      return service;

    }]);
})();
