(function () {
    'use strict';

    angular
        .module('os2')
        .filter('pages', pages);

    function pages() {
        return function (input, total) {
            total = parseInt(total);

            for (var i = 1; i <= total; i++) {
                input.push(i);
            }

            return input;
        };
    }
})();