angular
  .module('os2')
  .controller('NextMatchesController', NextMatchesController);

NextMatchesController.$inject = ['$scope', 'gameRepository'];
/* @ngInject */
function NextMatchesController($scope, gameRepo) {
  var self = this;
  self.matches = [];
  
  gameRepo.getNextMatches(success, error);

  function success(result) {
    
    if (result.err) {

    } else {
      self.matches = result.data.data; //TODO:: nicht schoen nach 1.6.x update
    }
  }

  function error(result) {

  }
}
