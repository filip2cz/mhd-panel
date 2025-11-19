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

echo htmlspecialchars($teplota)

?>