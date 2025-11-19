<?php
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

    <div class="stranka">

        <h1><span class="vetsiText">Odjezdy z <?php echo htmlspecialchars($zastavka); ?></span></h1>

        <?php

            require 'pid-public-departs.php';
        
        ?>

        <?php
            include 'missing-person.php';
        ?>

        <?php

            require 'footer.php';
        
        ?>

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