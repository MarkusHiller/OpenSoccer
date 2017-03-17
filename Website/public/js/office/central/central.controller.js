angular
    .module('os2')
    .controller('CentralController', CentralController);

CentralController.$inject = ['$scope', '$location', 'officeRepository'];
/* @ngInject */
function CentralController($scope, $location, $officeRepo) {

    $officeRepo.getCentralData(success, error);

    function success(result) {

    }

    function error(result) {

    }

}
