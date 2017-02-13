angular.module('app.header', [
    'app.core'
]).directive("osHeader", function () {
    return {
        restrict: "E",
        controller: "HeaderController",
        templateUrl: "js/header/header.html"
    };
});

