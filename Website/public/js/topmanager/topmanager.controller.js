angular
  .module('os2')
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
      self.manager = result.data.teams;
    }
  }

  function error(result) {

  }
}
