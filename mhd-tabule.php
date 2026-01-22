<?php
/**
 * Hlavní soubor informační tabule (Dashboard).
 *
 * Tento skript sestavuje výslednou stránku zobrazovanou na kiosku.
 * Načítá konfiguraci, určuje rozložení stránky podle velikosti okna (responzivita přes cookies),
 * a dynamicky vkládá moduly pro odjezdy (PID/MPVNet), počasí a pátrání po osobách.
 *
 * @var string $json Obsah konfiguračního souboru.
 * @var array $config Načtená konfigurace.
 * @var int $windowHeight Výška okna prohlížeče získaná z cookies.
 * @var int $mhdLimit Vypočítaný počet řádků odjezdů, které se vejdou na obrazovku.
 */
$windowHeight = $_COOKIE['window_height'];

// Načtení obsahu souboru config.json
$json = file_get_contents('config.json');

// Parsování JSON do PHP pole
$config = json_decode($json, true);

// Načtení velikosti okna
if (isset($_COOKIE['window_height']) && isset($_COOKIE['window_width'])) {
    $mhdLimit = floor($windowHeight / 52) - 4;
} else {
    $mhdLimit = 5;
}

// Získání dat z JSON
$refreshTime = isset($config['refreshTime']) ? $config['refreshTime'] : 10;
$mhdUrl = isset($config['mhdUrl']) ? $config['mhdUrl'] . "&limit=$mhdLimit" : 0;
$mhdApiKey = isset($config['mhdApiKey']) ? $config['mhdApiKey'] : 0;
$zastavka = isset($config['zastavka']) ? $config['zastavka'] : 0;
$enableMap = isset($config['enableMap']) ? $config['enableMap'] : 0;
$missingPerson = isset($config['missingPerson']) ? $config['missingPerson'] : "false";

$weatherSources = isset($config['weatherUrl']) ? $config['weatherUrl'] : [];
$weatherSourcesCount = count($weatherSources);

// Pomocné proměnné
$weatherIndex = 0;
$missingPersonImgData;
$missingPersonActive = "false";
?>

<!DOCTYPE html>
<html lang='cs' data-bs-theme="dark">

<head>
    <title>MHD Tabule</title>
    <link rel="icon" type="image/x-icon" href="./favicon.ico">
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <noscript>
        <meta http-equiv="refresh" content="<?php echo htmlspecialchars($refreshTime); ?>; url=mhd-tabule.php">
    </noscript>
</head>

<body class="noScroll">

    <link rel="stylesheet" type="text/css" href="./main.css">
    <script src="panel.js"></script>

    <!--
    <style>
        @media screen and (min-width: 1900px) {
            body {
                zoom: 2;
            }
        }
    </style>
    -->

    <script>
        // README: Tato funkce zde musí být takto, protože se do ní vkládají PHP data.
        // Automatická aktualizace každých X sekund
        setInterval(refreshPage, <?php echo htmlspecialchars($refreshTime * 1000); ?>);
    </script>

    <div class="stranka">

        <h1><span class="nadpis">Odjezdy z <?php echo htmlspecialchars($zastavka); ?></span></h1>

        <?php

        if (str_contains($mhdUrl, 'api.golemio.cz/v2/public/departureboards')) {
            require 'pid-public-departs.php';
        } else if (str_contains($mhdUrl, 'mpvnet.cz/pid/tab/departures')) {
            require 'mpvnet.php';
        }

        ?>

        <?php
        include 'missing-person.php';
        ?>

        <?php

        require 'footer.php';

        ?>

        <pre id="testOutput"></pre>

        <script>
            // README: Tato funkce zde musí být takto, protože se do ní vkládají PHP data.
            // Funkce pro zobrazení informací o počasí
            function weatherInfo() {
                alert(
                    "Zdroj dat o počasí:\n<?php echo htmlspecialchars($GLOBALS['weatherSources'][$GLOBALS['weatherIndex']]) ?>"
                );
            }
        </script>

    </div>

</body>

</html>