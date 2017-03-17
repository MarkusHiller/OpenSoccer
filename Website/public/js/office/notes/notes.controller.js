angular
    .module('os2')
    .controller('NotesController', NotesController);

NotesController.$inject = ['$scope', '$location', 'officeRepository'];
/* @ngInject */
function NotesController($scope, $location, $officeRepo) {

    this.saveNote = function (form) {
        $officeRepo.saveNote('', success, error);
        function success(result) {

        }

        function error() {

        }
    }

    $officeRepo.getNotes(success, error);

    function success(result) {

    }

    function error(result) {

    }

}
