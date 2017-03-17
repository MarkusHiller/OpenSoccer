(function () {
  'use strict';

  angular.module('os2')
    .factory('gameRepository', ['$http', function ($http) {

      var service = {
        getNextMatches: function (success, error) {
          $http.get('/api.php/getNextMatches').then(success, error);
        }
      };

      return service;

    }]);
})();
