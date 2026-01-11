<?php

$currentUser = isset($_COOKIE['account']) ? basename($_COOKIE['account']) : '';
$userFile = dirname(__DIR__) . "/users/" . $currentUser . ".json";
$isAdmin = false;

if ($currentUser && file_exists($userFile)) {
    $userData = json_decode(file_get_contents($userFile), true);
    if (isset($userData['admin']) && $userData['admin'] === "true") {
        $isAdmin = true;
    }
}

if (!empty($_POST) && !isset($_POST['oldPassword']) && !isset($_POST['uploadProfilePic'])) {
    if ($isAdmin) {
        $configFile = dirname(__DIR__, 2) . "/config.json";
        $configData = json_decode(file_get_contents($configFile), true);

        foreach ($_POST as $key => $value) {
            if ($key === 'weatherSources') {
                $configData['weatherUrl'] = array_map('strip_tags', $value);
            } elseif ($key === 'refreshTime') {
                if (is_numeric($value)) {
                    $configData[$key] = (int) $value;
                }
            } elseif ($key === 'enableMap' || $key === 'missingPerson') {
                $configData[$key] = ($value === 'true') ? 'true' : 'false';
            } else {
                $configData[$key] = strip_tags($value);
            }
        }

        file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } else {
        echo "<p style='color: red; font-weight: bold;'>Nemáte oprávnění měnit nastavení.</p>";
    }
    //"Reloadem stránky po odeslání formuláře nesmí dojít k opakovanému vložení dat"
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

?>

<h1>Configuration panel</h1>

<button type="button" onclick="logout()">Logout</button>

<h2>Panel settings</h2>

<?php
$jsonconfig = file_get_contents(dirname(__DIR__, 2) . "/config.json");

$config = json_decode($jsonconfig, true);

$refreshTime = isset($config['refreshTime']) ? $config['refreshTime'] : 10;
$mhdUrl = isset($config['mhdUrl']) ? $config['mhdUrl'] : "empty";
$mhdApiKey = isset($config['mhdApiKey']) ? $config['mhdApiKey'] : "empty";
$zastavka = isset($config['zastavka']) ? $config['zastavka'] : "empty";
$enableMap = isset($config['enableMap']) ? $config['enableMap'] : "false";
$mapUrl = isset($config['mapUrl']) ? $config['mapUrl'] : "empty";
$missingPerson = isset($config['missingPerson']) ? $config['missingPerson'] : "false";

$weatherSources = isset($config['weatherUrl']) ? $config['weatherUrl'] : [];
?>

<form method="POST">
    <div class="form-group">
        <label for="refreshTime">Refresh time:</label>

        <small id="refreshHelp" class="help-text">
            Time in seconds, how often does data refresh (for example: 10)
        </small>

        <br><br>

        <input type="number" id="refreshTime" name="refreshTime" aria-describedby="refreshHelp"
            value="<?php echo htmlspecialchars($refreshTime) ?>" min="1" <?php echo $isAdmin ? '' : 'disabled'; ?>>
    </div>

    <br>

    <?php if ($isAdmin): ?><button type="submit">Save</button><?php endif; ?>
</form>

<hr>

<form method="POST">
    <div class="form-group">
        <label for="mhdUrl">MHD Url:</label>
        <small id="mhdUrlHelp" class="help-text">
            Url to fetch MHD data from<br><br>
        </small>

            <input type="text" id="mhdUrl" name="mhdUrl" aria-describedby="mhdUrlHelp" class="fullWidthInput"
                value="<?php echo isset($config['mhdUrl']) ? htmlspecialchars($config['mhdUrl']) : '' ?>" <?php echo $isAdmin ? '' : 'disabled'; ?>><br><br>

            Supported APIs:<br>
            <ul>
                <li><a href="https://api.golemio.cz/pid/docs/openapi/#/%F0%9F%95%92%20Public%20Departures%20(v2)/get_v2_public_departureboards"
                        target="_blank">Golemio Public Departures (v2)</a></li>
            </ul>
    </div>
    <?php if ($isAdmin): ?><button type="submit">Save</button><?php endif; ?>
</form>

<hr>

<form method="POST">
    <div class="form-group">
        <label for="mhdApiKey">MHD Api key:</label>
        <small id="mhdApiKeyHelp" class="help-text">Api key for MHD Url, if required</small>
        <br><br>

        <input type="text" id="mhdApiKey" name="mhdApiKey" aria-describedby="mhdApiKeyHelp" class="fullWidthInput"
            value="<?php echo isset($config['mhdApiKey']) ? htmlspecialchars($config['mhdApiKey']) : '' ?>" <?php echo $isAdmin ? '' : 'disabled'; ?>>

        <br><br>
    </div>
    <?php if ($isAdmin): ?><button type="submit">Save</button><?php endif; ?>
</form>

<hr>

<form method="POST">
    <div class="form-group">
        <label for="zastavka">Station name:</label>
        <small id="stationNameHelp" class="help-text">Set name of station displayed on top of the panel</small>

        <br><br>

        <input type="text" id="zastavka" name="zastavka" aria-describedby="stationNameHelp"
            value="<?php echo isset($config['zastavka']) ? htmlspecialchars($config['zastavka']) : '' ?>" <?php echo $isAdmin ? '' : 'disabled'; ?>>

        <br><br>
    </div>
    <?php if ($isAdmin): ?><button type="submit">Save</button><?php endif; ?>
</form>

<hr>

<form method="POST" onsubmit="return validateMapSettings()">
    <div class="form-group">
        <label for="enableMap">Enable map:</label>
        <small id="enableMapHelp" class="help-text">If you panel has touch screen, you can enable map button on the
            footer, where user can see map of stations, trams, busses and other public transport things.</small>

        <br><br>

        <input type="hidden" name="enableMap" value="false">
        <input type="checkbox" id="enableMap" name="enableMap" aria-describedby="enableMapHelp" value="true" <?php echo ($enableMap == 'true') ? 'checked' : '' ?> <?php echo $isAdmin ? '' : 'disabled'; ?>>
    </div>
    <br>
    <?php if ($isAdmin): ?><button type="submit">Save</button><?php endif; ?>
</form>

<hr>

<form method="POST" onsubmit="return validateMapSettings()">
    <div class="form-group">
        <label for="mapUrl">Map url:</label>
        <small id="mapUrlHelp" class="help-text">Set url to open when Map button is clicked</small>

        <br><br>

        <input type="text" id="mapUrl" name="mapUrl" aria-describedby="mapUrlHelp" class="fullWidthInput"
            value="<?php echo isset($config['mapUrl']) ? htmlspecialchars($config['mapUrl']) : '' ?>" <?php echo $isAdmin ? '' : 'disabled'; ?>>

    </div>
    <br>
    <?php if ($isAdmin): ?><button type="submit">Save</button><?php endif; ?>
</form>

<hr>

<form method="POST">
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
            <?php echo ($missingPerson == 'true') ? 'checked' : '' ?> <?php echo $isAdmin ? '' : 'disabled'; ?>>
    </div>

    <br>

    <?php if ($isAdmin): ?><button type="submit">Save</button><?php endif; ?>
</form>

<hr>

<form method="POST">
    <div class="form-group">
        <label>Weather info sources:</label>
        <small class="help-text">Urls to fetch data about weather from. You can set multiple of them, so if one of them
            is offline, another one will be used. (Usefull for weather stations, where is posibility that it will be
            offline sometimes)<br><br>
        </small>

            Supported sources:
            <ul>
                <li><a href="https://www.meteo-pocasi.cz/" target="_blank">www.meteo-pocasi.cz</a></li>
                <li>api.open-meteo.com/v1/forecast (more info <a href="https://open-meteo.com/en/docs"
                        target="_blank">here</a>)</li>
            </ul>
        <div id="weatherSourcesContainer">
            <?php
            if (!empty($weatherSources)) {
                foreach ($weatherSources as $url) {
                    echo '<div style="display: flex; gap: 5px; margin-bottom: 5px;">';
                    echo '<input type="text" name="weatherSources[]" class="fullWidthInput" value="' . htmlspecialchars($url) . '" style="flex-grow: 1;" ' . ($isAdmin ? '' : 'disabled') . '>';
                    if ($isAdmin) {
                        echo '<button type="button" onclick="moveUp(this)">↑</button>';
                        echo '<button type="button" onclick="moveDown(this)">↓</button>';
                        echo '<button type="button" onclick="this.parentElement.remove()">Remove</button>';
                    }
                    echo '</div>';
                }
            }
            ?>
        </div>
        <?php if ($isAdmin): ?><button type="button" onclick="addWeatherSource()" style="margin-bottom: 10px;">Add
                URL</button><?php endif; ?>
    </div>
    <?php if ($isAdmin): ?><button type="submit">Save</button><?php endif; ?>
</form>

<hr>
</h2>

<?php include 'users.php'; ?>
<script src="/config/config.js"></script>