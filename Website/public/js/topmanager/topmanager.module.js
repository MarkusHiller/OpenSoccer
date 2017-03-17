angular.module('os2').directive("topmanager", function () {
    return {
        restrict: "E",
        controller: "TopmanagerController as tm",
        templateUrl: "js/topmanager/topmanager.html"
    };
});