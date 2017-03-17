angular
  .module('os2')
  .controller('SidebarController', SidebarController);

SidebarController.$inject = ['$scope', '$location', 'accountRepository'];
/* @ngInject */
function SidebarController($scope, $location, accRepo) {

  this.hasError = false;

  this.isAuthenticated = function () {
    return accRepo.isAuthenticated;
  };

  this.login = function (username, password) {
    var self = this;
    var loginData = {
      username: username,
      password: password
    };

    accRepo.login(loginData, success, error);

    function success(result) {
      if (result.data.err) {
        self.hasError = true;

      } else {
        self.hasError = false;
        accRepo.isAuthenticated = true;

        if (result.data.hasTeam) {
          $location.path("/central");
        } else {
          $location.path("/selectTeam");
        }
      }
    }

    function error(error) {
    }

  };
}
