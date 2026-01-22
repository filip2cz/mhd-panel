<?php
/**
 * Modul pro zobrazení pátrání po pohřešovaných osobách.
 *
 * Kontroluje RSS feed Policie ČR a pokud je vyhlášeno pátrání,
 * stáhne a zobrazí fotografii pohřešované osoby.
 */
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
            else if (preg_match('/<div class="missingPersonImg">\s*<img src="(ViewImage\.aspx\?id=[^"]+)"[^>]*>/i', $html, $matches)) {
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