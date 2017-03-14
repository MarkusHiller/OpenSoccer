angular
  .module('app.core')
  .controller('TopmanagerController', TopmanagerController);

TopmanagerController.$inject = ['$scope', 'commonRepository'];
/* @ngInject */
function TopmanagerController($scope, commonRepo) {
  var self = this;
  self.manager = [];
  
  commonRepo.getTopmanager(success, error);

  function success(result) {
    
    if (result.err) {

    } else {
      self.manager = result.teams;
    }
  }

  function error(result) {

  }
}
