angular.module('app.sidebar', [
    'app.core'
]).directive("sidebar", function () {
    return {
        restrict: "E",
        controller: "SidebarController as vm",
        templateUrl: "js/sidebar/sidebar.html"
    };
});