angular.module('os2').directive("osFooter", function () {
    return {
        restrict: "E",
        controller: "FooterController",
        templateUrl: "js/footer/footer.html"
    };
});