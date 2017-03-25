angular
    .module('os2')
    .controller('HomeController', HomeController);

HomeController.$inject = ['$scope', '$location', 'accountRepository'];
/* @ngInject */
function HomeController($scope, $location, accRepo) {
    var vm = this;
    vm.showConfirmRegister = false;
    vm.hasError = false;
    vm.errorMsg = '';

    vm.checkDataForRegistration = function () {
        accRepo.checkDataForRegistration(vm.reg, success, error);
        function success(result) {
            if (result.data.err) {
                vm.hasError = true;
                vm.errorMsg = result.data.msg;
            } else {
                vm.showConfirmRegister = true;
            }
        }
        function error(error) {
        }
    };

    vm.cancelRegistration = function () {
        vm.hasError = false;
        vm.errorMsg = '';
        vm.showConfirmRegister = false;
    }

    vm.registerUser = function () {
        accRepo.registerUser(vm.reg, success);
        function success(result) {
            if (result.data.err) {
                $location.path('/registrationFailed');
            } else {
                $location.path('/registrationSuccess');
            }
        }
    }
}
