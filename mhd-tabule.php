<?php
// Načtení obsahu souboru config.json
$json = file_get_contents('config.json');

// Parsování JSON do PHP pole
$config = json_decode($json, true);
$missingPerson = isset($config['missingPerson']) ? $config['missingPerson'] : "false";

$windowHeight = $_COOKIE['window_height'];

$missingPersonImgData;
$missingPersonActive = "false";
?>

<?php
if ($missingPerson == "true") {
    // URL RSS feedu
    $url = "https://aplikace.policie.gov.cz/patrani-osoby/Rss.ashx";

    // Stažení a načtení RSS jako SimpleXML
    $rss = simplexml_load_file($url);

    if ($rss === false) {
        die("Chyba při načítání RSS.");
    } else {
        // Získání prvního <item> (nejnovější záznam)
        $latestItem = $rss->channel->item[0];

        // Popis posledního záznamu
        $description = trim((string) $latestItem->description);

        // Podmínky pro kontrolu
        $pattern1 = "Bylo vyhlášeno pátrání po pohřešované osobě, pomozte dle svých možností k jejímu nalezení.";
        $pattern2 = "/Pátrání po pohřešované osobě \(vyhlášené .*?\) bylo aktualizováno\./";

        if ($description === $pattern1 || preg_match($pattern2, $description)) {
            // URL detailu osoby
            $detailUrl = trim((string) $latestItem->link);

            // Stažení HTML stránky detailu
            $html = file_get_contents($detailUrl);

            if ($html === false) {
            }
            // Hledání obrázku pomocí regulárního výrazu
            else if (preg_match('/<div style="float:right;">\s*<img src="(ViewImage\.aspx\?id=[^"]+)"[^>]*>/i', $html, $matches)) {
                $imageSrc = "https://aplikace.policie.gov.cz/patrani-osoby/" . htmlspecialchars($matches[1]);

                // Nastavení kontextu s HTTP hlavičkou Referer
                $context = stream_context_create([
                    'http' => [
                        'header' => "Referer: $detailUrl"
                    ]
                ]);

                // Stažení obrázku s nastaveným refererem
                $imageData = file_get_contents($imageSrc, false, $context);

                if ($imageData !== false) {
                    // Uložení výšky obrázku
                    $imageInfo = getimagesizefromstring($imageData);
                    $imageHeight = $imageInfo[1];
                    $GLOBALS['windowHeight'] = $windowHeight - $imageHeight;
                    $GLOBALS['missingPersonImgData'] = $imageData;
                    $GLOBALS['missingPersonActive'] = "true";
                }
            }
        }
    }
}
?>

<?php
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

$weatherSources = isset($config['weatherUrl']) ? $config['weatherUrl'] : [];
$weatherSourcesCount = count($weatherSources);

// Pomocné proměnné
$weatherIndex = 0;
?>

<?php

// Funkce pro získání dat z API
function ziskejTeplotu($url)
{
    if (preg_match("/api.open-meteo.com/", $url)) {
        try {
            // Inicializace cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FAILONERROR, true); // Chyby HTTP budou také zachyceny

            // Načtení dat
            $response = curl_exec($ch);

            // Ověření chyby při načítání
            if ($response === false) {
                throw new Exception(curl_error($ch));
            }

            // Zavření cURL
            curl_close($ch);

            // Dekódování JSON odpovědi
            $data = json_decode($response, true);

            // Ověření struktury dat
            if (isset($data['current_weather']['temperature'])) {
                return $data['current_weather']['temperature'];
            } else {
                throw new Exception("Nesprávná struktura odpovědi API");
            }
        } catch (Exception $e) {
            $GLOBALS['weatherIndex'] += 1;
            if ($GLOBALS['weatherIndex'] <= $GLOBALS['weatherSourcesCount']) {
                return ziskejTeplotu($GLOBALS['weatherSources'][$GLOBALS['weatherIndex']]);
            } else {
                return "Nepodařilo se načíst teplotu: " . $e->getMessage();
            }
        }
    } else if (preg_match("/meteo-pocasi.cz/", $url)) {
        try {
            // Inicializace cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FAILONERROR, true); // Chyby HTTP budou také zachyceny

            // Načtení dat
            $response = curl_exec($ch);

            // Ověření chyby při načítání
            if ($response === false) {
                throw new Exception(curl_error($ch));
            }

            // Zavření cURL
            curl_close($ch);

            // Načtení HTML do DOMDocument
            $dom = new DOMDocument();
            libxml_use_internal_errors(true); // Potlačení chybového výstupu kvůli nevalidnímu HTML
            $dom->loadHTML($response);
            libxml_clear_errors();

            // Použití DOMXPath pro jednodušší hledání prvků
            $xpath = new DOMXPath($dom);

            // Scrapování stavu komunikace
            $status = $xpath->query("//div[contains(@class, 'status_meteo_text')]");
            $stavKomunikace = $status->length > 0 ? trim($status[0]->nodeValue) : 'Neznámý';

            // Scrapování teploty
            $temperature = $xpath->query("//div[contains(@class, 'svalue')]");
            $teplota = $temperature->length > 0 ? trim($temperature[0]->nodeValue) : 'Neznámá';

            if ($stavKomunikace == "on-line") {
                return $teplota;
            } else {
                $GLOBALS['weatherIndex'] += 1;
                if ($GLOBALS['weatherIndex'] <= $GLOBALS['weatherSourcesCount']) {
                    return ziskejTeplotu($GLOBALS['weatherSources'][$GLOBALS['weatherIndex']]);
                } else {
                    return "ERROR: all weather are not working";
                }
            }
        } catch (Exception $e) {
            $GLOBALS['weatherIndex'] += 1;
            if ($GLOBALS['weatherIndex'] <= $GLOBALS['weatherSourcesCount']) {
                return ziskejTeplotu($GLOBALS['weatherSources'][$GLOBALS['weatherIndex']]);
            } else {
                return "ERROR: all weather are not working";
            }
        }
    }

}

// Získání teploty
$teplota = ziskejTeplotu(isset($weatherSources[$weatherIndex]) ? $weatherSources[$weatherIndex] : null);
?>

<!DOCTYPE html>
<html lang='cs' data-bs-theme="dark">

<head>
    <title>MHD Tabule</title>
    <link rel="icon" type="image/x-icon" href="./favicon.ico">
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

    <link rel="stylesheet" type="text/css" href="./main.css">

    <noscript>
        <meta http-equiv="refresh" content="<?php echo htmlspecialchars($refreshTime); ?>;url=mhd-tabule.php">
    </noscript>

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
        function setWindowSizeCookie() {
            document.cookie = "window_height=" + window.innerHeight + "; path=/";
            document.cookie = "window_width=" + window.innerWidth + "; path=/";
        }

        // Zavoláme při načtení a změně velikosti okna
        window.onload = setWindowSizeCookie;
        window.onresize = setWindowSizeCookie;
    </script>

    <script>
        function refreshPage() {
            fetch(window.location.href) // Stáhne aktuální stránku
                .then(response => response.text()) // Převede ji na text
                .then(html => {
                    let newDoc = new DOMParser().parseFromString(html, "text/html"); // Vytvoří nový DOM
                    document.body.innerHTML = newDoc.body.innerHTML; // Přepíše obsah stránky
                })
                .catch(err => console.error("Chyba při načítání stránky:", err));
        }

        // Automatická aktualizace každých X sekund
        setInterval(refreshPage, <?php echo htmlspecialchars($refreshTime * 1000); ?>);
    </script>

    <script src="hodiny.js"></script>

    <div class="stranka">

        <h1><span class="vetsiText">Odjezdy z <?php echo htmlspecialchars($zastavka); ?></span></h1>

        <?php

        date_default_timezone_set('Europe/Prague');

        // Inicializace cURL
        $ch = curl_init();

        // Nastavení cURL
        curl_setopt($ch, CURLOPT_URL, $mhdUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Access-Token: $mhdApiKey",
            "Accept: application/json"
        ]);

        // Vykonání požadavku
        $response = curl_exec($ch);

        // debug: vypsání odpovědi
        //echo htmlspecialchars($response);

        // Kontrola chyb
        if (curl_errno($ch)) {
            echo "cURL error: " . curl_error($ch);
            exit;
        }

        // Zavření cURL
        curl_close($ch);

        // Zpracování dat
        $data = json_decode($response, true);

        if (isset($_COOKIE['maxLetters'])) {
            $maxLength = $_COOKIE['maxLetters'];
        } else {
            $maxLength = 100000000;
        }

        // Generování tabulky
        if (!empty($data)) {
            echo '<table class="output" id="busSchedule">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Číslo</th>';
            echo '<th class="smer">Směr</th>';
            echo '<th>Čas</th>';
            echo '<th>Zpoždění</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            echo '<colgroup>';
            echo '<col style="width: 120px;">';
            echo '<col style="width: auto;">';
            echo '<col style="width: 120px;">';
            echo '<col style="width: 190px;">';
            echo '</colgroup>';

            foreach ($data[0] as $entry) {
                $bus = $entry['route']['short_name'];
                $time = date('H:i', strtotime($entry['departure']['timestamp_scheduled']));
                $delaySeconds = $entry['departure']['delay_seconds'];
                $delayMinutes = floor($delaySeconds / 60);
                $headsign = $entry['trip']['headsign'];

                echo '<tr>';
                echo "<td>$bus</td>";
                echo "<td class=\"smer\">$headsign</td>";
                echo "<td>$time</td>";
                echo "<td>$delayMinutes</td>";
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo "Žádná data nebyla nalezena.";
        }
        ?>

        <?php
        if ($GLOBALS['missingPersonActive'] == "true") {
            if ($GLOBALS['missingPersonImgData'] !== false) {
                // Uložení výšky obrázku
                $imageInfo = getimagesizefromstring($imageData);
                $imageHeight = $imageInfo[1];
                $GLOBALS['windowHeight'] = $windowHeight - $imageHeight;
                $GLOBALS['missingPersonImgData'] = $imageData;

                echo '<div class="missingPerson">';
                echo "<div class=\"p-2 text-center\"><h1>Bylo vyhlášeno pátrání po pohřešované osobě, pomozte dle svých možností k jejímu nalezení.<br>Pokud osobu na fotografii uvidíte, zavolejte 158.</h1></div>";
                echo "<div class=\"p-2\"><img src='data:image/jpeg;base64," . base64_encode($GLOBALS['missingPersonImgData']) . "'></div>";
                echo '</div>';
            }
        }
        ?>

        <footer class="stranka">
            <h1 style="display: flex; justify-content: space-between;">
                <u><span id="teplota" class="vetsiText" onclick="weatherInfo()"><?php echo htmlspecialchars($teplota) ?>
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
            </h1>
        </footer>

        <pre id="testOutput"></pre>

        <script>
            // Funkce pro přesměrování na jinou stránku při kliknutí kamkoliv
            document.addEventListener("click", function () {
                window.location.href = "./mhd-tabule.php";  // Změň na URL, kam chceš přesměrovat
            });
        </script>

        <script>
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