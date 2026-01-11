<?php

if (!empty($_POST)) {
    echo "<h3>DEBUG: Received POST data:</h3><pre>";
    print_r($_POST);
    echo "</pre><hr>";

    $configFile = dirname(__DIR__, 2) . "/config.json";
    $configData = json_decode(file_get_contents($configFile), true);

    foreach ($_POST as $key => $value) {
        if ($key === 'weatherSources') {
            $configData['weatherUrl'] = $value;
        } else {
            $configData[$key] = $value;
        }
    }

    file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

?>

<h1>Configuration panel</h1>

<h2>Panel settings</h2>

<?php
$jsonconfig = file_get_contents(dirname(__DIR__, 2) . "/config.json");

$config = json_decode($jsonconfig, true);

$refreshTime = isset($config['refreshTime']) ? $config['refreshTime'] : 10;
$mhdUrl = isset($config['mhdUrl']) ? $config['mhdUrl'] . "&limit=$mhdLimit" : "empty";
$mhdApiKey = isset($config['mhdApiKey']) ? $config['mhdApiKey'] : "empty";
$zastavka = isset($config['zastavka']) ? $config['zastavka'] : "empty";
$enableMap = isset($config['enableMap']) ? $config['enableMap'] : "false";
$mapUrl = isset($config['mapUrl']) ? $config['mapUrl'] : "empty";
$missingPerson = isset($config['missingPerson']) ? $config['missingPerson'] : "false";

$weatherSources = isset($config['weatherUrl']) ? $config['weatherUrl'] : [];
?>

<form method="POST" action="">
    <div class="form-group">
        <label for="refreshTime">Refresh time:</label>

        <small id="refreshHelp" class="help-text">
            Time in seconds, how often does data refresh (for example: 10)
        </small>

        <br><br>

        <input type="number" id="refreshTime" name="refreshTime" aria-describedby="refreshHelp"
            value="<?php echo $refreshTime ?>">
    </div>

    <br>

    <button type="submit">Save</button>
</form>

<hr>

<form method="POST" action="">
    <div class="form-group">
        <label for="mhdUrl">MHD Url:</label>
        <small id="mhdUrlHelp" class="help-text">
            Url to fetch MHD data from<br><br>

            <input type="text" id="mhdUrl" name="mhdUrl" aria-describedby="mhdUrlHelp" class="fullWidthInput"
                value="<?php echo isset($config['mhdUrl']) ? $config['mhdUrl'] : '' ?>"><br><br>

            Supported APIs:<br>
            <ul>
                <li><a href="https://api.golemio.cz/pid/docs/openapi/#/%F0%9F%95%92%20Public%20Departures%20(v2)/get_v2_public_departureboards"
                        target="_blank">Golemio Public Departures (v2)</a></li>
            </ul>
        </small>
    </div>
    <button type="submit">Save</button>
</form>

<hr>

<form method="POST" action="">
    <div class="form-group">
        <label for="mhdApiKey">MHD Api key:</label>
        <small id="mhdApiKeyHelp" class="help-text">Api key for MHD Url, if required</small>
        <br><br>

        <input type="text" id="mhdApiKey" name="mhdApiKey" aria-describedby="mhdApiKeyHelp" class="fullWidthInput"
            value="<?php echo isset($config['mhdApiKey']) ? $config['mhdApiKey'] : '' ?>">

        <br><br>
    </div>
    <button type="submit">Save</button>
</form>

<hr>

<form method="POST" action="">
    <div class="form-group">
        <label for="zastavka">Station name:</label>
        <small id="stationNameHelp" class="help-text">Set name of station displayed on top of the panel</small>

        <br><br>

        <input type="text" id="zastavka" name="zastavka" aria-describedby="stationNameHelp"
            value="<?php echo isset($config['zastavka']) ? $config['zastavka'] : '' ?>">

        <br><br>
    </div>
    <button type="submit">Save</button>
</form>

<hr>

<form method="POST" action="">
    <div class="form-group">
        <label for="enableMap">Enable map:</label>
        <small id="enableMapHelp" class="help-text">If you panel has touch screen, you can enable map button on the
            footer, where user can see map of stations, trams, busses and other public transport things.</small>

        <br><br>

        <input type="hidden" name="enableMap" value="false">
        <input type="checkbox" id="enableMap" name="enableMap" aria-describedby="enableMapHelp" value="true" <?php echo ($enableMap == 'true') ? 'checked' : '' ?>>
    </div>
    <br>
    <button type="submit">Save</button>
</form>

<hr>

<form method="POST" action="">
    <div class="form-group">
        <label for="mapUrl">Map url:</label>
        <small id="mapUrlHelp" class="help-text">Set url to open when Map button is clicked</small>

        <br><br>

        <input type="text" id="mapUrl" name="mapUrl" aria-describedby="mapUrlHelp" class="fullWidthInput"
            value="<?php echo isset($config['mapUrl']) ? $config['mapUrl'] : '' ?>">

    </div>
    <br>
    <button type="submit">Save</button>
</form>

<hr>

<form method="POST" action="">
    <div class="form-group">
        <label for="missingPerson">Missing person:</label>

        <small id="missingPersonHelp" class="help-text">If there is missing person marked as "Child or senior in
            danger", their picture and phone number to police will be displayed on panel. More information is here: <a
                href="https://aplikace.policie.gov.cz/patrani-osoby/DiteVOhrozeni.aspx"
                target="_blank">https://aplikace.policie.gov.cz/patrani-osoby/DiteVOhrozeni.aspx
            </a>
        </small>

        <br><br>

        <input type="hidden" name="missingPerson" value="false">
        <input type="checkbox" id="missingPerson" name="missingPerson" aria-describedby="missingPersonHelp" value="true"
            <?php echo ($missingPerson == 'true') ? 'checked' : '' ?>>
    </div>

    <br>

    <button type="submit">Save</button>
</form>

<hr>

<form method="POST" action="">
    <div class="form-group">
        <label>Weather info sources:</label>
        <small class="help-text">Urls to fetch data about weather from. You can set multiple of them, so if one of them
            is offline, another one will be used. (Usefull for weather stations, where is posibility that it will be
            offline sometimes)<br><br>

            Supported sources:
            <ul>
                <li><a href="https://www.meteo-pocasi.cz/" target="_blank">www.meteo-pocasi.cz</a></li>
                <li>api.open-meteo.com/v1/forecast (more info <a href="https://open-meteo.com/en/docs"
                        target="_blank">here</a>)</li>
            </ul>
        </small>
        <div id="weatherSourcesContainer">
            <?php
            if (!empty($weatherSources)) {
                foreach ($weatherSources as $url) {
                    echo '<div style="display: flex; gap: 5px; margin-bottom: 5px;">';
                    echo '<input type="text" name="weatherSources[]" class="fullWidthInput" value="' . $url . '" style="flex-grow: 1;">';
                    echo '<button type="button" onclick="moveUp(this)">↑</button>';
                    echo '<button type="button" onclick="moveDown(this)">↓</button>';
                    echo '<button type="button" onclick="this.parentElement.remove()">Remove</button>';
                    echo '</div>';
                }
            }
            ?>
        </div>
        <button type="button" onclick="addWeatherSource()" style="margin-bottom: 10px;">Add URL</button>
    </div>
    <button type="submit">Save</button>
</form>

<hr>
</h2>

<h2>User settings</h2>

<h2>Other user settings</h2>

<?php

$folderPath = 'users/';

if (!is_dir($folderPath)) {
    die("Error: The directory '$folderPath' does not exist.");
}

$files = glob($folderPath . '*.json');

if ($files) {
    echo "<ul>";

    foreach ($files as $file) {
        $jsonContent = file_get_contents($file);

        $userData = json_decode($jsonContent, true);

        // Skip if JSON is invalid
        if ($userData === null) {
            continue;
        }

        $username = basename($file, '.json');

        if ($username == $_COOKIE['account']) {
            continue;
        }

        $password = $userData['passwd'] ?? 'Nezadáno';

        // Output the result
        echo "<li><strong>Username:</strong> " . htmlspecialchars($username) .
            " | <strong>Password hash:</strong> " . htmlspecialchars($password) . "</li>";
    }

    echo "</ul>";
} else {
    echo "No users found. How did you evet got here, when you had to login as some user to see this?";
}

?>

<script src="/config/config.js"></script>