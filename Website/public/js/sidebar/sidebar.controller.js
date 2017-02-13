angular
  .module('app.sidebar')
  .controller('SidebarController', SidebarController);

SidebarController.$inject = ['$scope', '$location', 'accountRepository'];
/* @ngInject */
function SidebarController($scope, $location, accRepo) {
  this.isAuthenticated = function () {
    return localStorage.getItem("token") != undefined;
  };
  
  this.login = function (username, password) {
    var self = this;
    var loginData = {
      username: username,
      password: password
    };
    accRepo.login(loginData, success, error);
    function success(result) {
      if (result.err) {
        console.log(result.msg); //TODO:: display to users
      } else {
        window.localStorage.setItem("token", "blub");
        if (result.hasTeam) {
          $location.path("/central");
        }
        else {
          $location.path("/selectTeam");
        }
      }
    }
    function error(error) {
    }
  };

}
