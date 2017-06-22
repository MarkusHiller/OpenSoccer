(function () {
    'use strict';

    angular
        .module('os2').config(configure);

    configure.$inject = ['$routeProvider', '$httpProvider', '$locationProvider'];

    /* @ngInject */
    function configure($routeProvider, $httpProvider, $locationProvider) {
        $locationProvider.hashPrefix('');

        $routeProvider
            .when('/', {
                templateUrl: 'js/home/home.html',
                controller: 'HomeController as vm'
            })
            .when('/registrationSuccess', {
                templateUrl: 'js/home/registrationSuccess.html'
            })
            .when('/registrationFailed', {
                templateUrl: 'js/home/registrationFailed.html'
            })
            .when('/selectTeam', {
                templateUrl: 'js/selectTeam/selectTeam.html',
                controller: 'SelectTeamController as vm'
            })
            .when('/central', {
                templateUrl: 'js/office/central/central.html',
                controller: 'CentralController as vm'
            })
            .when('/protocol', {
                templateUrl: 'js/office/protocol/protocol.html',
                controller: 'ProtocolController as vm'
            })
            .when('/notes', {
                templateUrl: 'js/office/notes/notes.html',
                controller: 'NotesController as vm'
            })
            .when('/settings', {
                templateUrl: 'js/office/settings/settings.html',
                controller: 'SettingsController as vm'
            })
            .when('/nomination', {
                templateUrl: 'js/team/nomination/nomination.html',
                controller: 'NominationController as vm'
            })
            .when('/help', {
                templateUrl: 'js/help/help.html',
                controller: 'HelpController'
            })
            .when('/impressum', {
                templateUrl: 'js/impressum/impressum.html'
            })
            .when('/privatcy', {
                templateUrl: 'js/privatcy/privatcy.html'
            })
            .otherwise({
                redirectTo: '/'
            });


        $httpProvider.interceptors.push(['$q', '$location', function ($q, $location) {
            return {
                'request': function (config) {
                    config.headers = config.headers || {};
                    if (window.localStorage.token) {
                        config.headers.Authorization = 'Bearer ' + window.localStorage.token;
                    }
                    return config;
                },
                'responseError': function (response) {
                    if (response.status === 401 || response.status === 403) {
                        $location.path('/');
                    }
                    return $q.reject(response);
                }
            };
        }]);
    }
})();
