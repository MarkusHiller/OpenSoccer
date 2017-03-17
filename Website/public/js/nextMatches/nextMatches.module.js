angular.module('os2').directive("nextmatches", function () {
    return {
        restrict: "E",
        controller: "NextMatchesController as nm",
        templateUrl: "js/nextmatches/nextmatches.html"
    };
});