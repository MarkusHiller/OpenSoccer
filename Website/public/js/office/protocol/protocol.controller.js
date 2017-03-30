angular
    .module('os2')
    .controller('ProtocolController', ProtocolController);

ProtocolController.$inject = ['$scope', '$location', 'officeRepository'];
/* @ngInject */
function ProtocolController($scope, $location, $officeRepo) {
    var vm = this;
    vm.entries = [];
    vm.currentPage = 1;
    vm.pages = 1;
    vm.selectedType = '';

    vm.page = function (page) {
        if (page < 1 || page > vm.pages) return;
        vm.currentPage = page;
        vm.loadData();
    }

    vm.loadData = function () {
        var filter = {
            page: vm.currentPage,
            type: vm.selectedType
        }
        $officeRepo.getProtocolData(filter, success);

        function success(result) {
            if (result.data.err) {

            } else {
                vm.entries = result.data.data;
                vm.currentPage = result.data.currentPage;
                vm.pages = result.data.pages;
            }
        }
    }

    vm.loadData();

}
