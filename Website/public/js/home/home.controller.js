angular
    .module('os2')
    .controller('HomeController', HomeController);

HomeController.$inject = ['$scope', '$location'];
/* @ngInject */
function HomeController($scope, $location) {
    this.checkDataForRegistration = function () {
        var self = this;
        var regData = {
            username: this.rUsername,
            email: this.rEmail
        };
        this._accRepo.checkDataForRegistration(regData, success, error);
        function success(result) {
            self._location.path("/confirmRegistration");
        }
        function error(error) {
        }
    };
}
