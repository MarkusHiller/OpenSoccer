angular
    .module('os2')
    .controller('MenuController', MenuController);

MenuController.$inject = ['$scope', '$location', 'accountRepository', 'commonRepository'];
/* @ngInject */
function MenuController($scope, $location, accRepo, commonRepo) {
    var vm = this;
    vm.supportCount = 0;
    vm.requestsCount = 0;
    vm.testgameCount = 0;
    vm.loanCount = 0;
    vm.ligachangeCount = 0;

    vm.logout = function () {
        accRepo.logout(function () {
            $location.path("/logout");
        });
    };

    vm.isAuthenticated = function () {
        return accRepo.isAuthenticated;
    };

    $scope.$watch(accRepo.isAuthenticated, function() {
        if (accRepo.isAuthenticated) {
            updateInfoCounter();
        }
    });

    vm.useDemoAccount = function () {
        var self = this;
        var loginData = {
            username: "Demo",
            password: "demo"
        };
        accRepo.login(loginData, success, error);
        function success(result) {
            if (result.data.err) {
                console.log(result.data.msg); //TODO:: display to users
            }
            else {
                accRepo.isAuthenticated = true;
                $location.path("/central");
            }
        }
        function error(error) {
        }
    };

    function updateInfoCounter() {
        commonRepo.getInfocounts(success);

        function success(result) {
            if (result.data.err) {

            } else {
                vm.supportCount = result.data.data.supportCount;
                vm.requestsCount = result.data.data.testgameCount;
                vm.testgameCount = result.data.data.testgameCount;
                vm.loanCount = result.data.data.loanCount;
                vm.ligachangeCount = result.data.data.ligachangeCount;
            }
        }
    }
}