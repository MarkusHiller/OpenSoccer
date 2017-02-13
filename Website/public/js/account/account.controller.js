(function () {
    'use strict';

    angular
        .module('app.account')
        .controller('AccountController', AccountController);

  AccountController.$inject = ['$scope', '$location', 'accountRepository'];
    /* @ngInject */
    function AccountController($scope, $location, accountRepository) {

      loadData();

      $scope.company = null;
      $scope.newPassword = {
        pw: "",
        pwConfirm: ""
      };

      $scope.data = [];
      $scope.data.selectedOption = {name: '2 Monate', value: 60};
      $scope.selectOptions = [
        {name: '1 Monat', value: 30},
        {name: '2 Monate', value: 60},
        {name: '3 Monate', value: 90},
        {name: '6 Monate', value: 180},
        {name: '9 Monate', value: 270},
        {name: '1 Jahr', value: 360}
      ];

      $scope.changeSelection = function(event) {
        $scope.company.defaultStoreTime = $scope.data.selectedOption.value;
      };

      $scope.cancel = function() {
        $location.path('/app/accountMenu');
      };

      $scope.saveCompany = function() {

        companyRepository.updateCompany($scope.company, success, error);

        function success(res) {
          if (res.err) {
            toastr.error(res.msg);
          } else {
            $location.path("/app/mainMenu");
            toastr.success('Änderungen gespeichert');
          }
        }

        function error(res) {
          toastr.error(res.msg);
        }
      };

      $scope.changePassword = function () {
        if ($scope.newPassword.pw == $scope.newPassword.pwConfirm) {
          accountRepository.changePassword($scope.newPassword.pw, success, error);
        } else {
          toastr.error("Die Passwörter stimmen nicht überein.");
        }

        function success(res) {
          if (res.err) {
            toastr.error(res.data.message);
          } else {
            window.localStorage.password = pw;
            $location.path("/app/mainMenu");
            toastr.success("Das Passwort wurde geändert.");
          }
        }

        function error(res) {
          toastr.error(res.data.message);
        }
      };

      function loadData() {
        companyRepository.getCompanyByUser(success, error);

        function success(res) {
          if (res.err) {
            toastr.error(res.msg);
          } else {
            $scope.company = res.data;
            var option = _.find($scope.selectOptions, function(option) {
              return option.value === $scope.company.defaultStoreTime;
            });
            $scope.data.selectedOption = option;
          }
        }

        function error(res) {
          toastr.error(res.msg);
        }
      }
    }
})();
