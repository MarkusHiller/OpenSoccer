angular
    .module('os2')
    .controller('CentralController', CentralController);

CentralController.$inject = ['$scope', '$location', 'officeRepository'];
/* @ngInject */
function CentralController($scope, $location, $officeRepo) {
    var vm = this;
    $scope.Math = Math;
    vm.data = {};
    vm.showSearchResult = false;

    vm.getAufstellungsstaerke = function (value = 1) {
        return Math.floor(value / 108.9 * 100);
    }

    vm.getKaderstaerke = function (value = 1) {
        return Math.floor(value / 108.9 * 100);
    }

    vm.getPosToSearchName = function (short) {
        switch (short) {
            case 'T': return 'Torwart';
            case 'A': return 'Abwehr';
            case 'M': return 'Mittelfeld';
            case 'S': return 'Sturm';
            default: return '?';
        }
    }

    vm.testspielTrans = function (value) {
        switch (value) {
            case '0': return 'kein Interesse';
            case '1': return 'Interessiert';
            default: return '?';
        }
    }

    vm.getPokalRound = function (round) {
        switch (round) {
            case 1: return 'Vorrunde';
            case 2: return 'Achtelfinale';
            case 3: return 'Viertelfinale';
            case 4: return 'Halbfinale';
            case 5: return 'Finale';
            case 6: return 'Sieger';
            default: return '-';
        }
    }

    vm.getCupRound = function (round) {
        switch (round) {
            case 1: return 'Qualifikation';
            case 2: return 'Vorrunde';
            case 3: return 'Achtelfinale';
            case 4: return 'Viertelfinale';
            case 5: return 'Halbfinale';
            case 6: return 'Finale';
            default: return '-';
        }
    }

    vm.searchForTeamOrManager = function () {
        if (vm.searchInput === "") return;
        $officeRepo.searchForTeamOrManager({searchInput: vm.searchInput}, success);

        function success(result) {
            if (result.data.err) {

            } else {
                vm.matchedTeams = result.data.data.matchedTeams || [];
                vm.matchedManager = result.data.data.matchedManager || [];
                vm.showSearchResult = true;
            }
        }
    }

    $officeRepo.getCentralData(success);

    function success(result) {
        if (result.data.err) {

        } else {
            vm.data = result.data.data;
        }
    }


}
