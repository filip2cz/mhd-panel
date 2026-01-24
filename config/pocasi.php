<?php
/**
 * Diagnostic script for displaying temperatures from all configured sources.
 * Allows verification of individual APIs and scraping sources.
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang='en' data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Weather Sources Test</title>
    <link rel="stylesheet" type="text/css" href="../main.css">
    <link rel="stylesheet" type="text/css" href="pocasi.css">
</head>

<body class="stranka">

    <div class="container">
        <h1>Weather Diagnostics</h1>
        <p><a href="./">Back to administration</a></p>

        <?php
        // Load configuration
        $configFile = '../config.json';
        if (file_exists($configFile)) {
            $json = file_get_contents($configFile);
            $config = json_decode($json, true);
            $weatherSources = isset($config['weatherUrl']) ? $config['weatherUrl'] : [];
        } else {
            $weatherSources = [];
            echo "<p class='status-err'>ERROR: Configuration file config.json not found.</p>";
        }

        if (empty($weatherSources)) {
            echo "<p>No weather sources defined in configuration.</p>";
        } else {
            $totalSources = count($weatherSources);
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            if ($page > $totalSources) $page = $totalSources;

            $index = $page - 1;
            $url = $weatherSources[$index];

            echo "<div class='source-card'>";
            echo "<div><strong>Source #" . ($index + 1) . " of " . $totalSources . "</strong></div>";
            echo "<div class='url'>" . htmlspecialchars($url) . "</div><hr>";
            
            $result = "Unknown source type";
            $isError = true;

            if (preg_match("/api.open-meteo.com/", $url)) {
                try {
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FAILONERROR, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout 5s
                    $response = curl_exec($ch);
                    
                    if ($response === false) {
                        throw new Exception(curl_error($ch));
                    }
                    curl_close($ch);
                    
                    $data = json_decode($response, true);
                    if (isset($data['current_weather']['temperature'])) {
                        $result = $data['current_weather']['temperature'] . " °C";
                        $isError = false;
                    } else {
                        throw new Exception("Invalid API response structure");
                    }
                } catch (Exception $e) {
                    $result = "Error: " . $e->getMessage();
                }
            } else if (preg_match("/meteo-pocasi.cz/", $url)) {
                try {
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FAILONERROR, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    $response = curl_exec($ch);
                    
                    if ($response === false) {
                        throw new Exception(curl_error($ch));
                    }
                    curl_close($ch);
                    
                    $dom = new DOMDocument();
                    libxml_use_internal_errors(true);
                    $dom->loadHTML($response);
                    libxml_clear_errors();
                    $xpath = new DOMXPath($dom);
                    
                    $statusNode = $xpath->query("//div[contains(@class, 'status_meteo_text')]");
                    $tempNode = $xpath->query("//div[contains(@class, 'svalue')]");
                    
                    $stav = $statusNode->length > 0 ? trim($statusNode[0]->nodeValue) : 'Unknown';
                    $teplota = $tempNode->length > 0 ? trim($tempNode[0]->nodeValue) : 'Unknown';
                    
                    $result = "Temperature: $teplota (Status: $stav)";
                    $isError = ($stav != "on-line");
                } catch (Exception $e) {
                    $result = "Error: " . $e->getMessage();
                }
            }

            echo "<div>Result: <span class='" . ($isError ? "status-err" : "status-ok") . "'>" . htmlspecialchars($result) . "</span></div>";
            echo "</div>";

            echo "<div class='pagination'>";
            if ($page > 1) {
                echo "<a href='?page=" . ($page - 1) . "' class='pagination-btn pagination-btn-prev'>&laquo; Previous</a>";
            }
            echo "<span class='pagination-info'>Page $page / $totalSources</span>";
            if ($page < $totalSources) {
                echo "<a href='?page=" . ($page + 1) . "' class='pagination-btn pagination-btn-next'>Next &raquo;</a>";
            }
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>