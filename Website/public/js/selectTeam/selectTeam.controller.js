angular
    .module('os2')
    .controller('SelectTeamController', SelectTeamController);

SelectTeamController.$inject = ['$scope', '$location', 'accountRepository', 'teamRepository'];
/* @ngInject */
function SelectTeamController($scope, $location, accRepo, teamRepo) {
    var vm = this;
    vm.teamName = undefined;
    vm.username = accRepo.username;
    vm.selectableTeams = [];

    teamRepo.getSelectableTeams(success);
    function success(result) {
        vm.selectableTeams = result.data.data;
    }

    vm.chooseTeam = function (team) {
        accRepo.changeTeam({ teamId: team.ids, ligaId: team.liga }, success);

        function success(result) {
            if (result.data.err) {

            } else {
                vm.teamName = team.name;
            }
        }
    };
}
