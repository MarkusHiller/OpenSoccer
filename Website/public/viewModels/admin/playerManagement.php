<script type="text/javascript">
    function ViewModel() {
        var self = this;
        self.anzPlayerTransfer = ko.observable(<?php echo $transferPlayers ?>);
        self.anzFreePlayer = ko.observable(<?php echo $freePlayers ?>);
        self.anzFreePlayerT = ko.observable(<?php echo $freePlayersT ?>);
        self.anzFreePlayerA = ko.observable(<?php echo $freePlayersA ?>);
        self.anzFreePlayerM = ko.observable(<?php echo $freePlayersM ?>);
        self.anzFreePlayerS = ko.observable(<?php echo $freePlayersS ?>);

        self.createPlayers = function (position) {
            $.ajax({
                url: 'public/api.php/createPlayers',
                data: {position: position},
                type: "POST",
                dataType: 'json'
            }).done(function (result) {
                if (result === true) {
                    self.anzFreePlayer(self.anzFreePlayer() + 100);
                    self['anzFreePlayer' + position](self['anzFreePlayer' + position]() + 100);
                    $.notify("100 " + position + "-Spieler wurden erzeugt.", "success");
                } else {
                    $.notify("Es ist ein Fehler aufgetreten.", "error");
                }
            });
        };

    }

    ko.applyBindings(new ViewModel());
</script>