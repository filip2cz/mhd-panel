<?php
/**
 * Stránka pro zobrazení mapy v celoobrazovkovém režimu.
 *
 * Načítá URL mapy z konfiguračního souboru a zobrazuje ji v iframe.
 * Obsahuje meta tag pro automatický návrat na hlavní tabuli po 5 minutách nečinnosti.
 *
 * @var string $json Obsah konfiguračního souboru.
 * @var array $config Načtená konfigurace.
 * @var string $mapUrl URL adresa mapy.
 */
// Načtení obsahu souboru song.json
$json = file_get_contents('config.json');

// Parsování JSON do PHP pole
$config = json_decode($json, true);

// Získání dat z JSON
$mapUrl = isset($config['mapUrl']) ? $config['mapUrl'] : 0;
?>

<!DOCTYPE html>
<html lang='cs' data-bs-theme="dark">

<head>
    <title>MHD Mapa</title>
    <link rel="icon" type="image/x-icon" href="./favicon.ico">
    <meta charset='utf-8'>
    <meta http-equiv="refresh" content="300; url=mhd-tabule.php">
</head>

<body class="noScroll">

    <link rel="stylesheet" type="text/css" href="./main.css">

    <iframe src="<?php echo $mapUrl; ?>" id="mapa"
        sandbox="allow-scripts allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-presentation allow-same-origin allow-top-navigation allow-top-navigation-by-user-activation"></iframe>

    <div class="stranka">

        <h1><a href="mhd-tabule.php" id="odkaz">Zpět</a></h1>

    </div>

</body>

</html>