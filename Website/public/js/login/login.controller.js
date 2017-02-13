angular
    .module('app.login')
    .controller('LoginController', LoginController);

LoginController.$inject = ['$scope', '$location', 'accountRepository'];
/* @ngInject */
function LoginController($scope, $location, accountRepository) {

    $scope.user = {
        email: "",
        password: ""
    };

    $scope.login = function (data) {
        accountRepository.login(data, success, error);

        function success(res) {
            if (res.err) {
                console.log(res.msg); // TODO:: Display for user
            } else {
                console.log(res.token);
                window.localStorage.email = data.email;
                window.localStorage.password = data.password;
                window.localStorage.token = res.token;
                $location.path("/app/mainMenu");
            }
        }

        function error(res) {
            console.log(res.msg); // TODO:: Display for user
        }
    };

}
