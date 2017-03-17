angular.module('os2').directive("sidebar", function () {
    return {
        restrict: "E",
        controller: "SidebarController as vm",
        templateUrl: "js/sidebar/sidebar.html"
    };
});