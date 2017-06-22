angular
    .module('os2')
    .controller('NominationController', NominationController);

NominationController.$inject = ['$scope', '$location', 'teamRepository'];
/* @ngInject */
function NominationController($scope, $location, teamRepository) {
    var vm = this;
    vm.aufstellungsType = "Liga";
    vm.markierungen = [];
    vm.players = [];
    vm.selectedOptionTake = "";
    vm.choosablePlayers = [];
    vm.selectedPosition = "";
    vm.anzPlayer = "";
    vm.aufstellungsstaerke = "";

    vm.changeType = function (type) {
        vm.aufstellungsType = type;
        updateInfoBox();
    };

    vm.percent = function (value) {
        return Math.round(value) + "%";
    };

    vm.color = function (playerIds) {
        var playerMark = _.find(vm.markierungen(), function (mark) {
            return mark.spieler() === playerIds;
        });

        if (playerMark) {
            switch (playerMark.farbe()) {
                case 'Blau':
                    return '#00f';
                case 'Gelb':
                    return '#ff0';
                case 'Rot':
                    return '#f00';
                case 'Gruen':
                    return '#0f0';
                case 'Pink':
                    return '#f0f';
                case 'Aqua':
                    return '#0ff';
                case 'Silber':
                    return '#c0c0c0';
                case 'Lila':
                    return '#800080';
                case 'Oliv':
                    return '#808000';
                default:
                    return '';
            }
        } else {
            return '';
        }
    };

    vm.play = function (player) {
        return player['startelf_' + vm.aufstellungsType()]() > 0;
    };

    vm.isOnPosition = function (player) {
        if (parseInt(player['startelf_' + vm.aufstellungsType()]()) === vm.selectedPosition()) {
            return 1;
        } else if (parseInt(player['startelf_' + vm.aufstellungsType()]()) > 0) {
            return 2;
        }
        return 0;
    };

    vm.availableOptions = function () {
        var options = ko.observableArray();
        if (vm.aufstellungsType() !== "Liga")
            options.push("Liga");
        if (vm.aufstellungsType() !== "Pokal")
            options.push("Pokal");
        if (vm.aufstellungsType() !== "Cup")
            options.push("Cup");
        if (vm.aufstellungsType() !== "Test")
            options.push("Test");
        return options;
    };

    vm.saveNomination = function () {
        let data = {
            type: vm.aufstellungsType(),
            data: vm.players
        };
        teamRepository.saveNomination(data, success);

        function success(result) {
            if (result.data.err === false) {
                $.notify("Aufstellung gespeichert", "success");
                if (result.taskDone) $('#infoBoxMp').show();
            } else {
                $.notify("Es ist ein Fehler aufgetreten.", "error");
            }
        }
    };

    vm.takeNomination = function () {
        var result = confirm('Bist Du sicher?');
        if (result) {
            _.forEach(vm.players(), function (player) {
                player['startelf_' + vm.aufstellungsType()](player['startelf_' + vm.selectedOptionTake()]());
            });

            let data = {
                from: vm.selectedOptionTake(),
                to: vm.aufstellungsType()
            };
            teamRepository.takeNomination(data, success);

            function success(result) {
                if (result.err === false) {
                    $.notify("Aufstellung gespeichert", "success");
                    if (result.taskDone) $('#infoBoxMp').show();
                } else {
                    $.notify("Es ist ein Fehler aufgetreten.", "error");
                }
                updateInfoBox();
            };
        }
    };

    vm.getPlayerByPos = function (pos) {
        var player = _.find(vm.players, function (player) {
            return player["startelf_" + vm.aufstellungsType] == pos;
        });
        return {
            hasPlayer: player ? true : false,
            player: player
        };
    };

    vm.openChoosePlayerDialog = function (pos) {
        vm.selectedPosition(pos);
        vm.choosablePlayers([]);
        switch (pos) {
            case 1:
            case 2:
                vm.choosablePlayers(_.filter(vm.players(), function (player) {
                    return player.position() === "S" && player.verletzung() == 0;
                }));
                break;
            case 3:
            case 4:
            case 5:
            case 6:
                vm.choosablePlayers(_.filter(vm.players(), function (player) {
                    return player.position() === "M" && player.verletzung() == 0;
                }));
                break;
            case 7:
            case 8:
            case 9:
            case 10:
                vm.choosablePlayers(_.filter(vm.players(), function (player) {
                    return player.position() === "A" && player.verletzung() == 0;
                }));
                break;
            case 11:
                vm.choosablePlayers(_.filter(vm.players(), function (player) {
                    return player.position() === "T" && player.verletzung() == 0;
                }));
                break;
            default:
                break;
        }
        $("#playerSelectDialog").show();
    };

    vm.setPlayerToPosition = function (player) {
        var oldPlayer = _.find(vm.players(), function (player) {
            return player["startelf_" + vm.aufstellungsType()]() == vm.selectedPosition();
        });

        if (player) {
            player["startelf_" + vm.aufstellungsType()](vm.selectedPosition());
        }

        if (oldPlayer) {
            oldPlayer["startelf_" + vm.aufstellungsType()](0);
        }

        vm.closeDialog();
    };

    vm.closeDialog = function () {
        updateInfoBox();
        $("#playerSelectDialog").hide();
    };

    function updateInfoBox() {
        var anz = _.countBy(vm.players, function (player) {
            return player["startelf_" + vm.aufstellungsType] > 0;
        })['true'];
        vm.anzPlayer = anz;
        var staerke = _.reduce(vm.players, function (memo, player) {
            return memo += player["startelf_" + vm.aufstellungsType] > 0 ? parseFloat(player.staerke) : 0;
        }, 0);

        vm.aufstellungsstaerke = Math.round(staerke * 10) / 10;
    }

    teamRepository.getNominationData(loadDataSuccess);

    function loadDataSuccess(result) {
        if (result.data.err) {

        } else {
            vm.markierungen = result.data.data.markierungen;
            vm.players = result.data.data.players;
        }
    }
}
