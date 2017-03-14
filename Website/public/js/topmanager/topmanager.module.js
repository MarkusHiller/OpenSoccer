angular.module('app.core').directive("topmanager", function () {
    return {
        restrict: "E",
        controller: "TopmanagerController as tm",
        templateUrl: "js/topmanager/topmanager.html"
    };
});