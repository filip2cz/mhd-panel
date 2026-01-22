<?php
/**
 * Patička stránky (footer).
 *
 * Zajišťuje zobrazení spodní lišty s teplotou, hodinami a případným odkazem na mapu.
 * Načítá také JavaScript pro hodiny a PHP modul pro počasí.
 *
 * @var string $enableMap Určuje, zda je povoleno zobrazení mapy ("true"/"false").
 */
?>
<script src="hodiny.js"></script>

<footer class="stranka">
    <div class="footerText">
        <u><span id="teplota" class="vetsiText" onclick="weatherInfo()"> <?php require 'pocasi.php'; ?>
                °C</span></u>
        <div id="hodiny" class="vetsiText">
            <?php
            // Nastav časovou zónu (volitelné, pokud není nastavena v konfiguraci serveru)
            date_default_timezone_set('Europe/Prague');

            // Získání aktuálního času ve formátu H:i:s (hodiny:minuty:vteřiny)
            $cas = date('H:i:s');

            // Zobrazení času na stránce
            echo "$cas";
            ?>
        </div>
        <?php if ($enableMap == "true"): ?>
            <a href="mhd-mapa.php" id="odkaz" class="vetsiText">Mapa</a>
        <?php endif; ?>
    </div>
</footer>