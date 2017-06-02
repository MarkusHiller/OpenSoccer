</div>
</div>
<div id="footer"></div>
</div>
<?php echo showInfoBox($showInfoBox); /* Meldungen ausgeben */ ?>
<div><span id="rfooter" style="color:#666; width:820px; margin-left:auto; margin-right:auto; height:55px; text-align:center; font-size:80%; text-decoration:none">
    <span title="<?php echo I18N::getBrowserLanguage(); ?>"><?php echo _('Sprache:'); ?></span> <a rel="nofollow" href="/index.php?setLocale=de_DE">Deutsch</a> &middot; <a rel="nofollow" href="/index.php?setLocale=en_US">English</a> &middot; <a rel="nofollow" href="/index.php?setLocale=es_ES">Español</a><br />
	<a href="/apps/OpenSoccer2.apk" download target="_blank"><?php echo _('Android App'); ?></a> &middot;
	<a href="/regeln.php#regeln" rel="nofollow"><?php echo _('Regeln'); ?></a> &middot;
    <?php if (!is_null(CONFIG_ANDROID_APP_URL)) { echo '<a href="'.htmlspecialchars(CONFIG_ANDROID_APP_URL).'">'._('Android-App').'</a> &middot;'; } ?>
	<a href="/impressum.php" rel="nofollow"><?php echo _('Impressum'); ?></a> &middot;
	<a href="/regeln.php#datenschutz" rel="nofollow"><?php echo _('Datenschutz'); ?></a> &middot;
	<a href="https://github.com/delight-im/OpenSoccer"><?php echo _('Open Source'); ?></a><br />
	<?php echo _('Alle Vereine, Spieler und Sponsoren sind frei erfunden und haben keinen Bezug zu realen Ligen. Das Geld im Spiel ist nur virtuell und es erfolgen niemals Auszahlungen.'); ?></span>
</div>
</body>
</html>
