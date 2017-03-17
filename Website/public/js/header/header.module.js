angular.module('os2').directive("osHeader", function () {
    return {
        restrict: "E",
        controller: "HeaderController",
        templateUrl: "js/header/header.html"
    };
});

