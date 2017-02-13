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
        window.localStorage.removeItem("token");
        $location.path("/confirmRegistration");
    };

    this.isAuthenticated = function () {
        return localStorage.getItem("token") != undefined;
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
                window.localStorage.setItem("token", result.token);
                $location.path("/central");
            }
        }
        function error(error) {
        }
    };
}