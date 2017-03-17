angular
    .module('os2')
    .controller('SettingsController', SettingsController);

SettingsController.$inject = ['$scope', '$location', 'officeRepository', 'accountRepository'];
/* @ngInject */
function SettingsController($scope, $location, $officeRepo, $accRepo) {
    var self = this;
    self.email = '';
    self.pwChangeModel = {
        oldPw: '',
        newPw: '',
        newPwConfirm: ''
    }

    self.changePassword = function() {
        $accRepo.changePassword(self.pwChangeModel, success, error);

        function success(result) {
            if(result.data.err) {

            } else {

            }
        }

        function error(result) {

        }
    }

    $officeRepo.getSettingsData(success, error);

    function success(result) {
        if (result.data.err) {

        } else {
            self.email = result.data.data;
        }
    }

    function error() {

    }

}
