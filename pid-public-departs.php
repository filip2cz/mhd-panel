<?php
/**
 * Skript pro získání a zobrazení odjezdů z Golemio API (PID).
 *
 * Tento soubor je určen k inkluzi do hlavní stránky.
 * Generuje HTML tabulku s odjezdy.
 *
 * @var string $mhdUrl URL API endpointu (očekáváno z configu).
 * @var string $mhdApiKey API klíč (očekáváno z configu).
 */

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

// echo $response;

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
    echo '<colgroup>';
    echo '<col style="width: 120px;">';
    echo '<col style="width: auto;">';
    echo '<col style="width: 120px;">';
    echo '<col style="width: 190px;">';
    echo '</colgroup>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Číslo</th>';
    echo '<th class="smer">Směr</th>';
    echo '<th>Čas</th>';
    echo '<th>Zpoždění</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

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