<script type="text/javascript">
  function ViewModel() {
    var self = this;
    self.aufstellungsType = ko.observable("Liga");
    self.markierungen = ko.mapping.fromJS(<?php echo json_encode($mark); ?>);
    self.players = ko.mapping.fromJS(<?php echo json_encode($players); ?>);
    self.selectedOptionTake = ko.observable();
    self.choosablePlayers = ko.observableArray();
    self.selectedPosition = ko.observable();
    self.anzPlayer = ko.observable();
    self.aufstellungsstaerke = ko.observable();

    self.changeType = function(type) {
      self.aufstellungsType(type);
      updateInfoBox();
    };

    self.percent = function(value) {
      return Math.round(value) + "%";
    };

    self.color = function(playerIds) {
      var playerMark = _.find(self.markierungen(), function(mark) {
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

    self.play = function(player) {
      return player['startelf_' + self.aufstellungsType()]() > 0;
    };

    self.isOnPosition = function(player) {
      if (parseInt(player['startelf_' + self.aufstellungsType()]()) === self.selectedPosition()) {
        return 1;
      } else if (parseInt(player['startelf_' + self.aufstellungsType()]()) > 0) {
        return 2;
      }
      return 0;
    };

    self.availableOptions = function() {
      var options = ko.observableArray();
      if (self.aufstellungsType() !== "Liga")
        options.push("Liga");
      if (self.aufstellungsType() !== "Pokal")
        options.push("Pokal");
      if (self.aufstellungsType() !== "Cup")
        options.push("Cup");
      if (self.aufstellungsType() !== "Test")
        options.push("Test");
      return options;
    };

    self.saveAufstellung = function() {
      $.ajax({
        url: '/api.php/updateAufstellung',
        data: {
          type: self.aufstellungsType(),
          data: ko.mapping.toJS(self.players)
        },
        type: "POST",
        dataType: 'json',
        contentType: 'application/json;charset=UTF-8'
      }).done(function(result) {
        if (result.err === false) {
          $.notify("Aufstellung gespeichert", "success");
          if (result.taskDone) $('#infoBoxMp').show();
        } else {
          $.notify("Es ist ein Fehler aufgetreten.", "error");
        }
      });
    };

    self.takeAufstellung = function() {
      var result = confirm('Bist Du sicher?');
      if (result) {
        _.forEach(self.players(), function(player) {
          player['startelf_' + self.aufstellungsType()](player['startelf_' + self.selectedOptionTake()]());
        });
        $.ajax({
          url: '/api.php/takeAufstellung',
          data: {
            from: self.selectedOptionTake(),
            to: self.aufstellungsType()
          },
          type: "POST",
          dataType: 'json',
          contentType: 'application/json'
        }).done(function(result) {
          if (result.err === false) {
            $.notify("Aufstellung gespeichert", "success");
            if (result.taskDone) $('#infoBoxMp').show();
          } else {
            $.notify("Es ist ein Fehler aufgetreten.", "error");
          }
          updateInfoBox();
        });
      }
    };

    self.getPlayerByPos = function(pos) {
      var player = _.find(self.players(), function(player) {
        return player["startelf_" + self.aufstellungsType()]() == pos;
      });
      return {
        hasPlayer: player ? true : false,
        player: player
      };
    };

    self.openChoosePlayerDialog = function(pos) {
      self.selectedPosition(pos);
      self.choosablePlayers([]);
      switch (pos) {
        case 1:
        case 2:
          self.choosablePlayers(_.filter(self.players(), function(player) {
            return player.position() === "S" && player.verletzung() == 0;
          }));
          break;
        case 3:
        case 4:
        case 5:
        case 6:
          self.choosablePlayers(_.filter(self.players(), function(player) {
            return player.position() === "M" && player.verletzung() == 0;
          }));
          break;
        case 7:
        case 8:
        case 9:
        case 10:
          self.choosablePlayers(_.filter(self.players(), function(player) {
            return player.position() === "A" && player.verletzung() == 0;
          }));
          break;
        case 11:
          self.choosablePlayers(_.filter(self.players(), function(player) {
            return player.position() === "T" && player.verletzung() == 0;
          }));
          break;
        default:
          break;
      }
      $("#playerSelectDialog").show();
    };

    self.setPlayerToPosition = function(player) {
      var oldPlayer = _.find(self.players(), function(player) {
        return player["startelf_" + self.aufstellungsType()]() == self.selectedPosition();
      });

      if (player) {
        player["startelf_" + self.aufstellungsType()](self.selectedPosition());
      }

      if (oldPlayer) {
        oldPlayer["startelf_" + self.aufstellungsType()](0);
      }

      self.closeDialog();
    };

    self.closeDialog = function() {
      updateInfoBox();
      $("#playerSelectDialog").hide();
    };

    function updateInfoBox() {
      var anz = _.countBy(self.players(), function(player) {
        return player["startelf_" + self.aufstellungsType()]() > 0;
      })['true'];
      self.anzPlayer(anz);
      var staerke = _.reduce(self.players(), function(memo, player) {
        return memo += player["startelf_" + self.aufstellungsType()]() > 0 ? parseFloat(player.staerke()) : 0;
      }, 0);

      self.aufstellungsstaerke(Math.round(staerke * 10) / 10);
    }

  }

  ko.applyBindings(new ViewModel());
</script>