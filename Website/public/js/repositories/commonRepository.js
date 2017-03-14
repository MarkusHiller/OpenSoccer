(function () {
  'use strict';

  angular.module('app.core')
    .factory('commonRepository', ['$http', function ($http) {

      var service = {
        getTopmanager: function (success, error) {
          $http.get('/api.php/getTopmanager').success(success).error(error);
        }
      };

      return service;

    }]);
})();
