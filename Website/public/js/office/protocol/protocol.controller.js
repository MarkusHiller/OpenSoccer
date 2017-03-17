angular
    .module('os2')
    .controller('ProtocolController', [ProtocolController]);

ProtocolController.$inject = ['$scope', '$location', 'officeRepository'];
/* @ngInject */
function ProtocolController($scope, $location, $officeRepo) {
    var self = this;
    this.entries = [];
    this.currentPage;
    this.pages;

    $officeRepo.getProtocolData(success, error);

    function success(result) {
        if (result.err) {

        } else {
            self.entries = result.data.data;
            self.currentPage = result.data.currentPage;
            self.pages = result.data.pages;
        }
    }

    function error(result) {

    }

}
