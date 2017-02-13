angular.module('app.footer', [
    'app.core'
]).directive("osFooter", function () {
    return {
        restrict: "E",
        controller: "FooterController",
        templateUrl: "js/footer/footer.html"
    };
});