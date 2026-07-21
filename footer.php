<script src="hodiny.js"></script>

<footer class="stranka">
    <div class="footerText">
        <u><span id="teplota" class="vetsiText"> <?php require 'pocasi.php'; ?>
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

        <?php if ($batteryStatus == "true"):
            include 'battery.php';
        endif; ?>

        <?php if ($enableButton == "true"): ?>
            <a href="mhd-button.php" id="odkaz" class="vetsiText">Mapa</a>
        <?php endif; ?>
    </div>
</footer>