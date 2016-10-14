<?php include 'zz1.php'; ?>
<title><?php echo _('Leihgaben'); ?> - <?php echo CONFIG_SITE_NAME; ?></title>
<style type="text/css">
<!--
.jaNein input {
	width: 45px;
}
-->
</style>
<?php include 'zz2.php'; ?>
<?php if ($loggedin == 1) { ?>
<?php
// NUR 2 TRANSFERS ZWISCHEN 2 TEAMS ANFANG
$transfers_mit_team = array();
$n3t1 = "SELECT bieter, besitzer FROM ".$prefix."transfers WHERE bieter = '".$cookie_team."' OR besitzer = '".$cookie_team."'";
$n3t2 = mysql_query($n3t1);
while ($n3t3 = mysql_fetch_assoc($n3t2)) {
    $transfers_mit_team[] = $n3t3['besitzer'];
	$transfers_mit_team[] = $n3t3['bieter'];
}
$transfers_mit_team = array_count_values($transfers_mit_team);
// NUR 2 TRANSFERS ZWISCHEN 2 TEAMS ENDE
if ($_SESSION['transferGesperrt'] == TRUE) {
    addInfoBox(__('Du bist noch für den Transfermarkt %1$s. Wenn Dir unklar ist, warum, frage bitte ein %2$s.', '<a class="inText" href="/sanktionen.php">'._('gesperrt').'</a>', '<a class="inText" href="/post_schreiben.php?id=18a393b5e23e2b9b4da106b06d8235f3">'._('Team-Mitglied').'</a>'));
}
else {
	echo '<h1>'._('Erhaltene Anfragen').'</h1>';
	if (isset($_POST['spieler']) && isset($_POST['aktion']) && isset($_POST['besitzer'])) {
		$ac_spieler = mysql_real_escape_string(trim(strip_tags($_POST['spieler'])));
		$ac_besitzer = mysql_real_escape_string(trim(strip_tags($_POST['besitzer'])));
		$ac_aktion = mysql_real_escape_string(trim(strip_tags($_POST['aktion'])));
		$de1 = "DELETE FROM ".$prefix."transfermarkt_leihe WHERE spieler = '".$ac_spieler."' AND besitzer = '".$ac_besitzer."' AND bieter = '".mysql_real_escape_string($cookie_teamname)."'";
		mysql_query($de1);
		if (mysql_affected_rows() == 0) {
			addInfoBox(_('Der Spieler konnte nicht gefunden werden.'));
		}
		else {
			addInfoBox(_('Die Anfrage wurde zurückgezogen.'));
		}
	}
	// Verleihen oder Verkaufen
	if (isset($_POST['spieler']) && isset($_POST['aktion']) && isset($_POST['bieter']) && isset($_POST['praemie'])) {
		$ac_spieler = mysql_real_escape_string(trim(strip_tags($_POST['spieler'])));
		$ac_bieter = mysql_real_escape_string(trim(strip_tags($_POST['bieter'])));
		$ac_aktion = mysql_real_escape_string(trim(strip_tags($_POST['aktion'])));
		$ac_praemie = bigintval($_POST['praemie']);
		//Auskommenteiren da Prämie = Ablöse
		//if ($ac_praemie > 350000) { $ac_praemie = 0; }
		//Leihe nur bei Prämien von 0 50000, 100000, 150000, 200000, 250000, 300000, 350000
		// Verleihen
		if ($ac_praemie == 0 || $ac_praemie == 50000 || $ac_praemie == 100000 || $ac_praemie == 150000 || $ac_praemie == 200000 || $ac_praemie == 250000 || $ac_praemie == 300000 || $ac_praemie == 350000) {
			if ($ac_aktion == 'Ja') {
				$bid1 = "SELECT ids FROM ".$prefix."teams WHERE name = '".$ac_bieter."'";
				$bid2 = mysql_query($bid1);
				if (mysql_num_rows($bid2) != 0) {
					$ac1 = "UPDATE ".$prefix."transfermarkt_leihe SET akzeptiert = 1 WHERE spieler = '".$ac_spieler."' AND bieter = '".$ac_bieter."' AND akzeptiert = 0";
					$ac2 = mysql_query($ac1);
					if (mysql_affected_rows() != 0) {
						$bid3 = mysql_fetch_assoc($bid2);
						$ac_bieter_id = $bid3['ids'];
						if (!isset($transfers_mit_team[$ac_bieter_id])) { $transfers_mit_team[$ac_bieter_id] = 0; }
						if ($transfers_mit_team[$ac_bieter_id] < 2) {
							$vertragsende = endOfDay(getTimestamp('+29 days')); // 29 Tage
							$sql4 = "UPDATE ".$prefix."spieler SET transfermarkt = 0, startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0, startelf_Test = 0, moral = moral+5, team = '".$ac_bieter_id."', leiher = '".$cookie_team."', praemieProEinsatz = ".$ac_praemie.", praemienAbrechnung = spiele WHERE ids = '".$ac_spieler."' AND leiher = 'keiner' AND team = '".$cookie_team."'";
							$sql5 = mysql_query($sql4);
							$sql4 = "UPDATE ".$prefix."spieler SET vertrag = ".$vertragsende." WHERE ids = '".$ac_spieler."' AND vertrag < ".$vertragsende;
							$sql5 = mysql_query($sql4);
							$getmanager1 = "SELECT vorname, nachname, spiele_verein FROM ".$prefix."spieler WHERE ids = '".$ac_spieler."'";
							$getmanager2 = mysql_query($getmanager1);
							$getmanager3 = mysql_fetch_assoc($getmanager2);
							$getmanager4 = $getmanager3['vorname'].' '.$getmanager3['nachname'];
							if ($ac_praemie > 0) {
								$ac_praemie_str = ' '.__('für %s € pro Pflichtspiel', number_format($ac_praemie, 0, ',', '.'));
							}
							else {
								$ac_praemie_str = ' '._('ohne Prämie');
							}
							$formulierung = __('Du hast den Spieler %s an einen anderen Verein verliehen.', '<a href="/spieler.php?id='.$ac_spieler.'">'.$getmanager4.'</a>'.$ac_praemie_str);
							$sql7 = "INSERT INTO ".$prefix."protokoll (team, text, typ, zeit) VALUES ('".$cookie_team."', '".$formulierung."', 'Transfers', ".time().")";
							$sql8 = mysql_query($sql7);
							$formulierung = __('Du hast den Spieler %s ausgeliehen.', '<a href="/spieler.php?id='.$ac_spieler.'">'.$getmanager4.'</a>'.$ac_praemie_str);
							$sql7 = "INSERT INTO ".$prefix."protokoll (team, text, typ, zeit) VALUES ('".$ac_bieter_id."', '".$formulierung."', 'Transfers', ".time().")";
							$sql8 = mysql_query($sql7);
							$move1 = "INSERT INTO ".$prefix."transfers (spieler, besitzer, bieter, datum, gebot, spiele_verein, leihgebuehr) VALUES ('".$ac_spieler."', '".$cookie_team."', '".$ac_bieter_id."', ".time().", 1, ".$getmanager3['spiele_verein'].", ".$ac_praemie.")";
							mysql_query($move1);
							// ALLE ANDEREN GEBOTE ABLEHNEN ANFANG
							$ac1 = "UPDATE ".$prefix."transfermarkt_leihe SET akzeptiert = 2 WHERE spieler = '".$ac_spieler."' AND bieter != '".$ac_bieter."' AND akzeptiert = 0";
							$ac2 = mysql_query($ac1);
							// ALLE ANDEREN GEBOTE ABLEHNEN ENDE
							addInfoBox(_('Die Anfrage wurde angenommen.'));
                        	if (isset($_SESSION['last_leihgaben_anzahl'])) {
                            	$_SESSION['last_leihgaben_anzahl']--;
                        	}
						}
						else {
							addInfoBox(_('Du hast mit diesem Verein schon zwei Transfers ausgehandelt.'));
						}
					}
				}
			}
			elseif ($ac_aktion == 'Nein') {
				$ac1 = "UPDATE ".$prefix."transfermarkt_leihe SET akzeptiert = 2 WHERE spieler = '".$ac_spieler."' AND bieter = '".$ac_bieter."'";
				$ac2 = mysql_query($ac1);
				addInfoBox(_('Die Anfrage wurde abgelehnt.'));
				$_SESSION['last_leihgaben_anzahl']--;
			}
		}
		// Verkaufen
		else {
			if ($ac_aktion == 'Ja') {
				$bid1 = "SELECT ids FROM ".$prefix."teams WHERE name = '".$ac_bieter."'";
				$bid2 = mysql_query($bid1);
				if (mysql_num_rows($bid2) != 0) {
					$ac1 = "UPDATE ".$prefix."transfermarkt_leihe SET akzeptiert = 1 WHERE spieler = '".$ac_spieler."' AND bieter = '".$ac_bieter."' AND akzeptiert = 0";
					$ac2 = mysql_query($ac1);
					if (mysql_affected_rows() != 0) {
						$bid3 = mysql_fetch_assoc($bid2);
						$ac_bieter_id = $bid3['ids'];
						if (!isset($transfers_mit_team[$ac_bieter_id])) { $transfers_mit_team[$ac_bieter_id] = 0; }
						if ($transfers_mit_team[$ac_bieter_id] < 2) {
							$vertragsende = endOfDay(getTimestamp('+29 days')); // 29 Tage
							$neuesGehalt = round(pow(($ac_praemie/1000), (1.385+0.006*3)));
							// Hole Spieler Daten von aktuellem Verein ANFANG
							//$sql1 = "SELECT vorname, nachname, marktwert, spiele_verein, staerke FROM ".$prefix."spieler WHERE ids = '".$ac_spieler."' AND team = '".$cookie_team."' AND leiher = 'keiner'";
							//$sql2 = mysql_query($sql1);
							// Hole Spieler Daten von aktuellem Verein ENDE
							// Wechsel Verein in DB ANFANG
							$sql4 = "UPDATE ".$prefix."spieler SET transfermarkt = 0, startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0, startelf_Test = 0, moral = moral+5, vertrag = ".$vertragsende.", gehalt = ".$neuesGehalt.", team = '".$ac_bieter_id."', leiher = 'keiner' WHERE ids = '".$ac_spieler."' AND leiher = 'keiner' AND team = '".$cookie_team."'";
							$sql5 = mysql_query($sql4);
							// Wechsel Verein in DB ENDE
							// Von DB Tranfermarkt_Leihe löschen
							$sql4 = "DELETE FROM ".$prefix."transfermarkt_leihe WHERE spieler = '".$ac_spieler."'";
							$sql5 = mysql_query($sql4);
							// TRANSFERSTEUER für Käufer ANFANG
							$transfersteuer = round($ac_praemie*0.05);
							$tsbuch1 = "INSERT INTO ".$prefix."buchungen (team, verwendungszweck, betrag, zeit) VALUES ('".$ac_bieter_id."', 'Transfersteuer', -".$transfersteuer.", ".time().")";
							$tsbuch2 = mysql_query($tsbuch1);
							// TRANSFERSTEUER für Käufer ENDE
							// Buchung Verkäufer ANFANG
							$upKon1 = "UPDATE ".$prefix."teams SET konto = konto+".round($ac_praemie-$transfersteuer)." WHERE ids = '".$cookie_team."'";
							$upKon2 = mysql_query($upKon1);
							$tfbuch1 = "INSERT INTO ".$prefix."buchungen (team, verwendungszweck, betrag, zeit) VALUES ('".$cookie_team."', 'Ablöse', ".$ac_praemie.", ".time().")";
							$tfbuch2 = mysql_query($tfbuch1);
							//Buchung Verkäufer ENDE
							// Buchung Käufer ANFANG
							$upKon3 = "UPDATE ".$prefix."teams SET konto = konto-".round($ac_praemie+$transfersteuer)." WHERE ids = '".$ac_bieter_id."'";
							$upKon4 = mysql_query($upKon3);
							$tfbuch3 = "INSERT INTO ".$prefix."buchungen (team, verwendungszweck, betrag, zeit) VALUES ('".$ac_bieter_id."', 'Ablöse', -".$ac_praemie.", ".time().")";
							$tfbuch4 = mysql_query($tfbuch3);
							//Buchung Käufer ENDE
							$getmanager1 = "SELECT vorname, nachname, spiele_verein, marktwert FROM ".$prefix."spieler WHERE ids = '".$ac_spieler."'";
							$getmanager2 = mysql_query($getmanager1);
							$getmanager3 = mysql_fetch_assoc($getmanager2);
							$getmanager4 = $getmanager3['vorname'].' '.$getmanager3['nachname'];
							if ($ac_praemie > 0) {
								$ac_praemie_str = ' '.__('für eine Ablösesumme von %s €', number_format($ac_praemie, 0, ',', '.'));
							}
							// Protokoll Verkäufer ANFANG
							$formulierung1 = __('Du hast den Spieler %s an einen anderen Verein verkauft.', '<a href="/spieler.php?id='.$ac_spieler.'">'.$getmanager4.'</a>'.$ac_praemie_str);
							$sql7 = "INSERT INTO ".$prefix."protokoll (team, text, typ, zeit) VALUES ('".$cookie_team."', '".$formulierung1."', 'Transfers', ".time().")";
							$sql8 = mysql_query($sql7);
							// Protokoll Verkäufer ENDE
							// Protokoll Käufer ANFANG
							$formulierung2 = __('Du hast den Spieler %s von einem anderen Verein gekauft.', '<a href="/spieler.php?id='.$ac_spieler.'">'.$getmanager4.'</a>'.$ac_praemie_str);
            				$sql9 = "INSERT INTO ".$prefix."protokoll (team, text, typ, zeit) VALUES ('".$ac_bieter_id."', '".$formulierung2."', 'Transfers', ".time().")";
            				$sql10 = mysql_query($sql9);
            				// Protokoll Käufer ENDE
							// Transfers eintragen ANFANG
							$marktwertalt = $getmanager3['marktwert'];
							$move1 = "INSERT INTO ".$prefix."transfers (spieler, besitzer, bieter, datum, gebot, damaligerWert, spiele_verein) VALUES ('".$ac_spieler."', '".$cookie_team."', '".$ac_bieter_id."', ".time().", ".$ac_praemie.", ".$marktwertalt.", ".$getmanager3['spiele_verein'].")";
							mysql_query($move1);
							// Transfers eintragen ENDE
							// ALLE ANDEREN GEBOTE ABLEHNEN ANFANG
							$ac1 = "UPDATE ".$prefix."transfermarkt_leihe SET akzeptiert = 2 WHERE spieler = '".$ac_spieler."' AND bieter != '".$ac_bieter."' AND akzeptiert = 0";
							$ac2 = mysql_query($ac1);
							// ALLE ANDEREN GEBOTE ABLEHNEN ENDE
							addInfoBox(_('Die Anfrage wurde angenommen.'));
                        	if (isset($_SESSION['last_leihgaben_anzahl'])) {
                            	$_SESSION['last_leihgaben_anzahl']--;
                        	}
						}
						else {
							addInfoBox(_('Du hast mit diesem Verein schon zwei Transfers ausgehandelt.'));
						}
					}
				}
			}
			elseif ($ac_aktion == 'Nein') {
				$ac1 = "UPDATE ".$prefix."transfermarkt_leihe SET akzeptiert = 2 WHERE spieler = '".$ac_spieler."' AND bieter = '".$ac_bieter."'";
				$ac2 = mysql_query($ac1);
				addInfoBox(_('Die Anfrage wurde abgelehnt.'));
				$_SESSION['last_leihgaben_anzahl']--;
			}
		}
	}
	$an1 = "SELECT a.spieler, a.bieter, a.praemie, b.vorname, b.nachname, c.ids FROM ".$prefix."transfermarkt_leihe AS a JOIN ".$prefix."spieler AS b ON a.spieler = b.ids JOIN ".$prefix."teams AS c ON a.bieter = c.name WHERE a.besitzer = '".$cookie_team."' AND a.akzeptiert = 0 ORDER BY b.vorname ASC, b.nachname ASC, c.staerke ASC";
	$an2 = mysql_query($an1);
	$an2a = mysql_num_rows($an2);
	if ($an2a > 0) { echo '<p><table class="jaNein"><thead><tr class="odd"><th scope="col">'._('Spieler').'</th><th scope="col">'._('Team').'</th><th scope="col">'._('Transfer').'</th><th scope="col">'._('Preis').'</th><th scope="col">'._('Annehmen?').'</th></tr></thead><tbody>';
		while ($an3 = mysql_fetch_assoc($an2)) {
			if (isset($transfers_mit_team[$an3['ids']])) {
				if ($transfers_mit_team[$an3['ids']] >= 2) {
					$dl1 = "UPDATE ".$prefix."transfermarkt_leihe SET akzeptiert = 2 WHERE spieler = '".$an3['spieler']."' AND bieter = '".$an3['bieter']."'";
					$dl2 = mysql_query($dl1);
					$_SESSION['last_leihgaben_anzahl']--;
					echo '<tr><td colspan="4">'._('Dein Co-Trainer hat die Anfrage abgelehnt (2-Transfers-Sperre)').'</td></tr>';
					continue;
				}
			}
			if ($an3['praemie'] == 0 || $an3['praemie'] == 50000 || $an3['praemie'] == 100000 || $an3['praemie'] == 150000 || $an3['praemie'] == 200000 || $an3['praemie'] == 250000 || $an3['praemie'] == 300000 || $an3['praemie'] == 350000) {
				$praemieabloese = 'p. P.';
				$kaufleihe = 'Verleihen';
			}
			else {
				$praemieabloese = 'Ablöse';
				$kaufleihe =	'Verkaufen';
			}
			echo '<tr><td class="link"><a href="/spieler.php?id='.$an3['spieler'].'">'.$an3['vorname'].' '.$an3['nachname'].'</a></td><td class="link"><a href="/team.php?id='.$an3['ids'].'">'.$an3['bieter'].'</a></td><td>'.$kaufleihe.'</td><td>'.number_format($an3['praemie'], 0, ',', '.').' € '.$praemieabloese.'</td>';
			echo '<td><form action="/leihgaben.php" method="POST" accept-charset="utf-8"><input type="hidden" name="spieler" value="'.$an3['spieler'].'" /><input type="hidden" name="bieter" value="'.$an3['bieter'].'" /><input type="hidden" name="praemie" value="'.$an3['praemie'].'" /><button type="submit" name="aktion" value="Ja"'.noDemoClick($cookie_id).'>'._('Zustimmen').'</button>&nbsp;<br><br><button type="submit" name="aktion" value="Nein"'.noDemoClick($cookie_id).'>'._('Ablehnen').'</button></form></td>';
		}
		echo '</tbody></table></p>';
		echo '<p><strong>'._('Hinweis:').'</strong> '._('Bei mehreren Anfragen für denselben Spieler sind die anfragenden Teams nach Kaderstärke sortiert. Das Angebot des schwächsten Teams steht oben.').'</p>';
	}
	else {
		echo '<p>'._('Zurzeit keine Anfragen!').'</p>';
	}
}
?>
<h1><?php echo _('Gesendete Anfragen'); ?></h1>
<?php
$an1 = "SELECT a.spieler, a.besitzer, a.praemie, b.vorname, b.nachname FROM ".$prefix."transfermarkt_leihe AS a JOIN ".$prefix."spieler AS b ON a.spieler = b.ids WHERE a.bieter = '".mysql_real_escape_string($cookie_teamname)."' AND a.akzeptiert = 0 ORDER BY b.vorname ASC, b.nachname ASC";
$an2 = mysql_query($an1) or die(mysql_error());
$an2a = mysql_num_rows($an2);
if ($an2a > 0) { echo '<p><table><thead><tr class="odd"><th scope="col">'._('Spieler').'</th><th scope="col">'._('Team').'</th><th scope="col">'._('Transfer').'</th><th scope="col">'._('Preis').'</th><th scope="col">'._('Aktion').'</th></tr></thead><tbody>';
    while ($an3 = mysql_fetch_assoc($an2)) {
    	$tlink1 = "SELECT name FROM ".$prefix."teams WHERE ids = '".$an3['besitzer']."'";
    	$tlink2 = mysql_query($tlink1);
    	$tlink3 = mysql_fetch_assoc($tlink2);
    	if ($an3['praemie'] == 0 || $an3['praemie'] == 50000 || $an3['praemie'] == 100000 || $an3['praemie'] == 150000 || $an3['praemie'] == 200000 || $an3['praemie'] == 250000 || $an3['praemie'] == 300000 || $an3['praemie'] == 350000) {
			$praemieabloese = 'p. P.';
			$kaufleihe = 'Leihen';
		}
		else {
			$praemieabloese = 'Ablöse';
			$kaufleihe =	'Kaufen';
		}
		echo '<tr><td class="link"><a href="/spieler.php?id='.$an3['spieler'].'">'.$an3['vorname'].' '.$an3['nachname'].'</a></td><td class="link"><a href="/team.php?id='.$an3['besitzer'].'">'.$tlink3['name'].'</a></td><td>'.$kaufleihe.'</td><td>'.number_format($an3['praemie'], 0, ',', '.').' € '.$praemieabloese.'</td>';
		echo '<td><form action="/leihgaben.php" method="POST" accept-charset="utf-8"><input type="hidden" name="spieler" value="'.$an3['spieler'].'" /><input type="hidden" name="besitzer" value="'.$an3['besitzer'].'" /><input type="submit" name="aktion" value="'._('Zurückziehen').'"'.noDemoClick($cookie_id).' /></form></td>';
    }
    echo '</tbody></table></p>';
}
else {
	echo '<p>'._('Zurzeit keine Anfragen!').'</p>';
}
?>
<h1><?php echo _('Verliehene Spieler'); ?></h1>
<?php
$sql1 = "SELECT a.ids, a.position, a.vorname, a.nachname, a.wiealt, a.staerke, a.spiele, a.team, a.praemieProEinsatz, b.name FROM ".$prefix."spieler AS a JOIN ".$prefix."teams AS b ON a.team = b.ids WHERE a.leiher = '".$cookie_team."' ORDER BY a.position DESC";
$sql2 = mysql_query($sql1);
if (mysql_num_rows($sql2) > 0) {
?>
<p><?php echo _('Du hast die folgenden Spieler in der aktuellen Saison verliehen. Sie kehren nach Saisonende zu Deinem Verein zurück.'); ?></p>
<table>
<thead>
<tr class="odd">
<th scope="col" title="Mannschaftsteil"><?php echo _('P'); ?></th>
<th scope="col" title="Name des Spielers"><?php echo _('Name'); ?></th>
<th scope="col" title="Alter"><?php echo _('AL'); ?></th>
<th scope="col" title="Stärke"><?php echo _('ST'); ?></th>
<th scope="col" title="Pflichtspiele"><?php echo _('PS'); ?></th>
<th scope="col" title="Ausleihendes Team"><?php echo _('Team'); ?></th>
<th scope="col" title="Prämie pro Pflichtspiel"><?php echo _('Prämie p. P.'); ?></th>
</tr>
</thead>
<tbody>
<?php
$counter = 0;
while ($sql3 = mysql_fetch_assoc($sql2)) {
	if ($counter % 2 == 0) { echo '<tr>'; } else { echo '<tr class="odd">'; }
	echo '</td><td>'.$sql3['position'].'</td><td class="link"><a href="/spieler.php?id='.$sql3['ids'].'">'.$sql3['vorname'].' '.$sql3['nachname'].'</a></td><td>'.floor($sql3['wiealt']/365).'</td><td>'.number_format($sql3['staerke'], 1, ',', '.').'</td><td>'.$sql3['spiele'].'</td>';
	echo '<td class="link"><a href="/team.php?id='.$sql3['team'].'">'.$sql3['name'].'</a></td>';
	echo '<td>'.number_format($sql3['praemieProEinsatz'], 0, ',', '.').' €</td>';
	echo '</tr>';
	$counter++;
}
?>
</tbody>
</table>
<p><strong><?php echo _('Überschriften:').'</strong> '._('P: Position, AL: Alter, ST: Stärke, PS: Pflichtspiele, p. P.: pro Pflichtspiel'); ?></p>
<p><strong><?php echo _('Positionen:').'</strong> '._('T: Torwart, A: Abwehr, M: Mittelfeld, S: Sturm'); ?>
<?php } else { echo '<p>'._('Du hast in dieser Saison noch keine Spieler verliehen.'); } ?>
</p>
<h1><?php echo _('Ausgeliehene Spieler'); ?></h1>
<?php
$sql1 = "SELECT a.ids, a.position, a.vorname, a.nachname, a.wiealt, a.staerke, a.spiele, a.praemieProEinsatz, a.leiher, b.name FROM ".$prefix."spieler AS a JOIN ".$prefix."teams AS b ON a.leiher = b.ids WHERE a.team = '".$cookie_team."' AND a.leiher != 'KEINER' ORDER BY a.position DESC";
$sql2 = mysql_query($sql1);
if (mysql_num_rows($sql2) > 0) {
?>
<p><?php echo _('Du hast die folgenden Spieler in der aktuellen Saison von einem anderen Verein ausgeliehen. Sie verlassen Dein Team nach Saisonende wieder.'); ?></p>
<table>
<thead>
<tr class="odd">
<th scope="col" title="Mannschaftsteil"><?php echo _('P'); ?></th>
<th scope="col" title="Name des Spielers"><?php echo _('Name'); ?></th>
<th scope="col" title="Alter"><?php echo _('AL'); ?></th>
<th scope="col" title="Stärke"><?php echo _('ST'); ?></th>
<th scope="col" title="Pflichtspiele"><?php echo _('PS'); ?></th>
<th scope="col" title="Verleihendes Team"><?php echo _('Team'); ?></th>
<th scope="col" title="Prämie pro Pflichtspiel"><?php echo _('Prämie p. P.'); ?></th>
</tr>
</thead>
<tbody>
<?php
$counter = 0;
while ($sql3 = mysql_fetch_assoc($sql2)) {
	if ($counter % 2 == 0) { echo '<tr>'; } else { echo '<tr class="odd">'; }
	echo '</td><td>'.$sql3['position'].'</td><td class="link"><a href="/spieler.php?id='.$sql3['ids'].'">'.$sql3['vorname'].' '.$sql3['nachname'].'</a></td><td>'.floor($sql3['wiealt']/365).'</td><td>'.number_format($sql3['staerke'], 1, ',', '.').'</td><td>'.$sql3['spiele'].'</td>';
	echo '<td class="link"><a href="/team.php?id='.$sql3['leiher'].'">'.$sql3['name'].'</a></td>';
	echo '<td>'.number_format($sql3['praemieProEinsatz'], 0, ',', '.').' €</td>';
	echo '</tr>';
	$counter++;
}
?>
</tbody>
</table>
<p><strong><?php echo _('Überschriften:').'</strong> '._('P: Position, AL: Alter, ST: Stärke, PS: Pflichtspiele, TO: Tore, p. P.: pro Pflichtspiel'); ?></p>
<p><strong><?php echo _('Positionen:').'</strong> '._('T: Torwart, A: Abwehr, M: Mittelfeld, S: Sturm'); ?>
<?php } else { echo '<p>'._('Du hast in dieser Saison noch keine Spieler ausgeliehen.'); } ?>
</p>
<?php } else { ?>
<h1><?php echo _('Leihgaben'); ?></h1>
<p><?php echo _('Du musst angemeldet sein, um diese Seite aufrufen zu können!'); ?></p>
<?php } ?>
<?php include 'zz3.php'; ?>