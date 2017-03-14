angular
    .module('app.menu')
    .controller('MenuController', MenuController);

MenuController.$inject = ['$scope', '$location', 'accountRepository'];
/* @ngInject */
function MenuController($scope, $location, accRepo) {

    if (!window.localStorage.getItem("token")) {
        $location.path("/home");
    }

    this.logout = function () {
        accRepo.logout(function () {
            $location.path("/logout");
        });
    };

    this.isAuthenticated = function () {
        return accRepo.isAuthenticated;
    };

    this.useDemoAccount = function () {
        var self = this;
        var loginData = {
            username: "Demo",
            password: "demo"
        };
        accRepo.login(loginData, success, error);
        function success(result) {
            if (result.err) {
                console.log(result.msg); //TODO:: display to users
            }
            else {
                accRepo.isAuthenticated = true;
                $location.path("/central");
            }
        }
        function error(error) {
        }
    };
}