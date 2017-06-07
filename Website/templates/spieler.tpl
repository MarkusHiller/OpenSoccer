<style type="text/css">
    .selectVisible {
        visibility: visible;
    }

    .selectHidden {
        visibility: hidden;
        width: 0;
        height: 0;
    }
</style>

<h1>Spieler: {$vorname} {$nachname}</h1>
<p style="text-align:right">
    {if $loggedin == 1}
        <a href="/transfermarkt_watch.php?id={$ids}" class="pagenava" onclick="return {$onWatchClick}">
        {if $watch3 == 0}
            Spieler beobachten
        {else}
            Beobachtung beenden
        {/if}
        </a> 
    {/if}
    <a href="/spieler_historie.php?id={$ids}" class="pagenava">Zur Historie</a>
</p>
<table>
    <thead>
        <tr class="odd">
            <th scope="col">Bereich</th>
            <th scope="col">Wert</th>
        </tr>
    </thead>
    <tbody>
        <tr class="odd">
            <td>Position</td>
            <td>
                {if $position == 'T'} 
                    Torwart 
                {elseif $position == 'A'} 
                    Abwehr 
                {elseif $position == 'M'} 
                    Mittelfeld 
                {elseif $position == 'S'} 
                    Sturm 
                {/if}
            </td>
        </tr>
        <tr>
            <td>Alter</td>
            <td>{$alter} Jahre</td>
        </tr>
        <tr class="odd">
            <td>Stärke</td>
            <td>{number_format($staerke, 1, ',', '.')}</td>
        </tr>
        <tr>
            <td>Frische</td>
            <td>
                <img src="/images/balken/{round($frische)}.png" alt="{round($frische)}%" title="{round($frische)}%" width="104" />
            </td>
        </tr>
        <tr class="odd">
            <td>Moral</td>
            <td>
                <img src="/images/balken/{round($moral)}.png" alt="{round($moral)}%" title="{round($moral)}%" width="104" />
            </td>
        </tr>
        <tr>
            <td>Marktwert</td>
            <td>{number_format($marktwert, 0, ',', '.')} €</td>
        </tr>
        <tr class="odd">
            <td>Gehalt / Saison</td>
            <td>{$gehaltContent}</td>
        </tr>
        <tr>
            <td>Team</td>
            {if $team == 'frei'}
                <td>außerhalb Europas</td>
            {else}
                <td class="link">
                    <a href="/team.php?id={$team}">{$teamname}</a>
                </td>
            {/if}
        </tr>
        <tr class="odd">
            <td>Vertrag bis</td>
            <td>
	        {if $team != 'frei'}
                {date('d.m.Y H:i', $vertrag)}
            {else}
                unbekannt
            {/if}
            </td>
        </tr>
        <tr>
            <td>Transferstatus</td>
            <td>{$transferStat}</td>
        </tr>
        <tr class="odd">
            <td>Spiele für Verein</td>
            <td>{$spiele_verein}</td>
        </tr>
        <tr>
            <td>Pflichtspieltore</td>
            <td>
            {if $live_scoring_spieltyp_laeuft == ''} 
                {$tore}
            {else}
                ?
            {/if}
            </td>
        </tr>
        <tr class="odd">
            <td>Pflichtspiele (Spiele diese Saison)</td>
            <td>{$spiele} ({$spiele_saison})</td>
        </tr>
        <tr>
            <td>Gesundheit</td>
            <td>
            {if $verletzung == 0} 
	            Gesund 
            {else}
	            <span style="color:red">Verletzt ({$verletzung} Tag{if $verletzung > 1}e{/if})</span> 
            {/if}
            </td>
        </tr>
        <tr class="odd">
            <td>Pokal-Sperre</td>
            <td>
            {if $pokalNurFuer == ''}
	            Nein
            {else}
                {if $pokalNurFuer == $team}
                    nach Transfer
                {else}
                    Ja
                {/if}
            {/if}
            </td>
        </tr>
        <tr>
            <td>Talent</td>
            <td>
            {for starts_full 1 $talentStars}
                <img src="/images/stern.png" alt="+" width="16" />
            {/for}
            {if min($talentStars+1, 6) <= 5}
                {for stars_empty $talentStars+1 5}
                    <img src="/images/stern_leer.png" alt="0" width="16" />
                {/for}
            {/if}
            </td>
        </tr>
        <tr class="odd">
            <td colspan="2">
            {if $schaetzungVomScout <= $staerke}
	            Dein Scout glaubt, dass dieser Spieler seinen Höhepunkt bereits erreicht hat.
            {else}
                Dein Scout glaubt, dass dieser Spieler eine Stärke von {number_format($schaetzungVomScout, 1, ',', '.')} erreichen kann.
            {/if}
            </td>
        </tr>
        {if $loggedin == 1 && $team == $cookie_team && $leiher == 'keiner'}
        <tr>
            {if $marktwert > 0}
                <td colspan="2" class="link">
                    <a href="/vertrag_verlaengern.php?id={$ids}">Vertrag verlängern</a>
                </td>
            {else}
                <td colspan="2">Der Spieler bietet Dir noch keine Vertragsverlängerung an.</td>
            {/if}
        </tr>
        <tr class="odd">
            {if $vertrag < getTimestamp('+48 hours')}
                <td colspan="2">Der Vertrag des Spielers läuft aus, Du kannst ihn deshalb nicht mehr entlassen</td>
            {else}
                <td colspan="2" class="link">
                    <a href="/spieler_entlassen.php?id={$ids}" onclick="return confirm('Bist Du sicher?')">Für {number_format($entlassungskosten, 0, ',', '.')} € entlassen</a>
                </td>
            {/if}
        </tr>
        {/if}
    </tbody>
</table>
{if $loggedin == 1 && $team == $cookie_team && $leiher == 'keiner' && $marktwert > 0}
	{if ($spiele_verein > 5 && $alter < 34) OR ($spiele <= 6 && $alter < 34)}
		{if $transferGesperrt == FALSE}
			<h1>Transfermarkt</h1>
			{if $transfermarkt == 0}
				<p>
                    Du kannst diesen Spieler auf dem Transfermarkt verkaufen oder ihn zur Leihgabe anbieten. Wenn Du ihn zum Verkauf anbietest, 
                    wird er direkt gegen eine Ablöse an ein anderes Team verkauft. Wenn Du den Spieler zur Leihgabe anbietest, kannst Du 
                    später noch die Angebote prüfen und entscheiden, ob Du eins davon annimmst.
                </p>
				{if $spiele_verein > 5 && $alter < 34}
					<form action="/transfermarkt_aktion.php" method="post" accept-charset="utf-8">
					    <p>
                            <select id="aukTyp" name="typ" size="1">
					            <option value="Kauf">Verkauf für {number_format($marktwert, 0, ',', '.')} €</option>
					        </select>
                            <input type="hidden" name="spieler" value="{$ids}" />
                            <input type="submit" value="Jetzt verkaufen" onclick="return {$onWatchClick}" />
                        </p>
					</form>
                {/if}
				{if $spiele <= 6 && $alter < 34}
					<form action="/transfermarkt_aktion.php" method="post" accept-charset="utf-8">
					    <p>
                            <select id="aukTyp" name="typ" size="1">
					            <option value="999999">zur Leihgabe (ohne Prämie)</option>
					            <option value="5000000">zur Leihgabe (50.000 Prämie p.P.)</option>
					            <option value="10000000">zur Leihgabe (100.000 Prämie p.P.)</option>
					            <option value="15000000">zur Leihgabe (150.000 Prämie p.P.)</option>
					            <option value="20000000">zur Leihgabe (200.000 Prämie p.P.)</option>
					            <option value="25000000">zur Leihgabe (250.000 Prämie p.P.)</option>
					            <option value="30000000">zur Leihgabe (300.000 Prämie p.P.)</option>
					            <option value="35000000">zur Leihgabe (350.000 Prämie p.P.)</option>
					        </select>
                            <input type="hidden" name="spieler" value="{$ids}" />
                            <input type="submit" value="Anbieten zur Leihgabe" onclick="return {$onWatchClick}" />
                        </p>
					</form>
				{/if}
			{/if}
		{/if}
	{else}
		<h1>Spieler anbieten</h1>
		<p>
            Dein Spieler muss für Deinen Verein mindestens 6 Spiele absolviert haben, bevor Du ihn verkaufen kannst. Damit Du ihn verleihen kannst, 
            darf er höchstens 6 Einsätze in der aktuellen Saison haben.
        </p>
		<p>
            Spieler, die über 33 Jahre alt sind, können grundsätzlich nicht mehr verkauft oder verliehen werden.
        </p>
	{/if}
{/if}
{if $loggedin == 1 && $scoutHasTime && $playForAi}
    <h2>Scout beauftragen</h2>
    <p>
        <a href="/spieler.php?id={$ids}&scout=1" onclick="return {$onWatchClick}" class="pagenava">Spieler begutachten</a>
    </p>
{/if}
{if $loggedin == 1 && $canSubmitAnOffer && $playForAi}
    <h2>Transferangebot</h2>
    <form action="/spieler.php?id={$ids}" method="post" accept-charset="utf-8">
        <p>
            <input id="offer" type="number" value="0" title="Dein Kontostand: {number_format($konto, 0, ',', '.')} €" />
            <label style="display: inline;" for="offer"> Mio.</label>
            <input type="submit" value="Angebot unterbreiten" onclick="return {$onWatchClick}" />
        </p>
    </form>
{/if}