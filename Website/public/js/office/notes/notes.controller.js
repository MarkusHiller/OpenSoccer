angular
    .module('os2')
    .controller('NotesController', NotesController);

NotesController.$inject = ['$scope', '$location', 'officeRepository'];
/* @ngInject */
function NotesController($scope, $location, $officeRepo) {
    var vm = this;
    vm.notes = [];
    vm.note = "";

    vm.saveNote = function (note) {
        $officeRepo.saveNote({ note: note }, success);
        function success(result) {
            if (result.data.err) {

            } else {
                vm.notes.push({ id: result.data.data, text: note });
                vm.note = "";
            }
        }
    }

    vm.delNote = function (note) {
        var result = confirm("Möchtest du diese Notiz wirklichlöschen?");
        if(result == false) return;
        $officeRepo.delNote({ noteId: note.id }, success);
        function success(result) {
            if (result.data.err) {

            } else {
                var index = vm.notes.indexOf(note);
                vm.notes.splice(index, 1);
            }
        }
    }

    $officeRepo.getNotes(success);

    function success(result) {
        if (result.data.err) {

        } else {
            vm.notes = result.data.data;
        }

    }

}
