(function () {
  'use strict';

  angular.module('os2')
    .factory('commonRepository', ['$http', function ($http) {

      var service = {
        getTopmanager: function (success, error) {
          $http.get('/api.php/getTopmanager').then(success, error);
        }
      };

      return service;

    }]);
})();
