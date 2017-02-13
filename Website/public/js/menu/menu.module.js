angular.module('app.menu', [
    'app.core'
]).directive("osMenu", function () {
    return {
        restrict: "E",
        controller: "MenuController as vm",
        templateUrl: "js/menu/menu.html"
    };
});