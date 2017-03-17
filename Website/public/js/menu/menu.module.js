angular.module('os2').directive("osMenu", function () {
    return {
        restrict: "E",
        controller: "MenuController as vm",
        templateUrl: "js/menu/menu.html"
    };
});