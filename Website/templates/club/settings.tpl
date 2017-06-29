<h1>Einstellungen</h1>
{if $loggedin == 1}
<p>Hier kannst du vereinsspeziefische Einstellungen vornehmen.</p>

<h1>Wappen</h1>
<img class="emblem-big" src="/images/emblems/{$emblem}" />
<form enctype="multipart/form-data" action="/club/settings.php" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
    <p>Datei auswählen: <input name="emblem" type="file" /></p>
    <p>
        <input type="submit" value="Speichern" />
    </p>
</form>
<p>{$emblemResult}</p>

<h1>Verein in eine andere Liga umziehen</h1>
<p>Ein Wechsel in eine andere Liga ist nur alle 45 Tage möglich und auch nur in den ersten fünf Spieltagen der Saison. Wärend der Livespiele ist der Ligawechsel ebenfalls nicht möglich.</p>
    {if $ligachangeResult != ''}
        {if $ligachangeResult == 'true'} 
            <p>
                Du hast die Liga erfolgreich gewechselt.
            </p>
        {else}
            <p>
                Leider konnte der Wechsel nicht erfolgreich durchgeführt werden. Eventuell ist kein freies Team in der Liga vorhanden.
            </p>
        {/if}
    {*elseif $daysToWait > 0*}
    <p>Du musst noch <strong>{$daysToWait} Tage</strong> warten, bis Du wieder die Liga wechseln kannst.</p>
    {*elseif $matchDay > 5*}
    <p>Der Verband erlaubt einen Ligatausch nur an den <strong>ersten fünf Spieltagen</strong>. Bitte warte bis zur nächsten Saison.</p>
    {*elseif $liveScoringType != ''*}
    <p>Zurzeit laufen <strong>{$liveScoringType}-Spiele</strong>. Deshalb kannst Du leider keine Liga-Wechsel durchführen. Bitte warte, bis die Spiele beendet sind.</p>
    {else}
    <form action="/club/settings.php" method="POST">
        <label for="selectedLiga">Liga wählen</label>
        <select id="selectedLiga" name="selectedLiga">
            {foreach $ligen val}
            <option value="{$val.ids}">{$val.name}</option>
            {/foreach}
        </select>
        <input type="submit" value="Wechseln" onclick="return confirm('Bist Du sicher?');" />
    </form>
    {/if}
{else}
<p>Du musst angemeldet sein, um diese Seite aufrufen zu können!</p>
{/if}