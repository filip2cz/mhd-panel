<h1>Configuration panel</h1>

<h2>Panel settings</h2>

<?php
$jsonconfig = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/config.json");

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

<h3>Refresh time</h3>
<p>
    <i>Time in seconds, how often does data refresh</i><br>
    current value: <input type="text" readonly class="disabled" value="<?php echo $refreshTime ?>">
</p>

 <form action="index.php">
  <label for="fname">First name:</label>
  <input type="text" id="fname" name="fname"><br><br>
  <label for="lname">Last name:</label>
  <input type="text" id="lname" name="lname"><br><br>
  <input type="submit" value="Submit">
</form> 

<h3>MHD Url</h3>
<p>
    <i>Url to fetch MHD data from</i><br>
    Supported APIs:<br>
    <ul>
        <li><a href="https://api.golemio.cz/pid/docs/openapi/#/%F0%9F%95%92%20Public%20Departures%20(v2)/get_v2_public_departureboards" target="_blank">Golemio Public Departures (v2)</a></li>
    </ul>
    current value: <input type="text" readonly class="disabled fullWidthInput" value="<?php echo $mhdUrl ?>">
</p>

<h3>MHD Api key</h3>
<p>
    <i>Api key for MHD Url, if required</i><br>
    current value: <input type="text" readonly class="disabled fullWidthInput" value="<?php echo $mhdApiKey ?>">
</p>

<h3>Station name</h3>
<p>
    <i>Set name of station displayed on top of the panel</i><br>
    current value: <input type="text" readonly class="disabled" value="<?php echo $zastavka ?>">
</p>

<h3>Enable map</h3>
<p>
    <i>If you panel has touch screen, you can enable map button on the footer, where user can see map of stations, trams, busses and other public transport things.</i><br>
    current value: <input type="text" readonly class="disabled" value="<?php echo $enableMap ?>">
</p>

<h3>Map url</h3>
<p>
    <i>Set url to open when Map button is clicked</i><br>
    current value: <input type="text" readonly class="disabled fullWidthInput" value="<?php echo $mapUrl ?>">
</p>

<h3>Missing person</h3>
<p>
    <i>If there is missing person marked as "Child or senior in danger", their picture and phone number to police will be displayed on panel. More information is here: <a href="https://aplikace.policie.gov.cz/patrani-osoby/DiteVOhrozeni.aspx" target="_blank¨">https://aplikace.policie.gov.cz/patrani-osoby/DiteVOhrozeni.aspx</a></i><br>
    current value: <input type="text" readonly class="disabled" value="<?php echo $missingPerson ?>">
</p>

<h3>Weather info sources</h3>
<p>
    <i>Urls to fetch data about weather from. You can set multiple of them, so if one of them is offline, another one will be used. (Usefull for weather stations, where is posibility that it will be offline sometimes)</i><br>
    current value: <input type="text" readonly class="disabled" value="TODO">
    Supported sources:<br>
    <ul>
        <li><a href="https://www.meteo-pocasi.cz/" target="_blank">www.meteo-pocasi.cz</a></li>
        <li>api.open-meteo.com/v1/forecast (more info <a href="https://open-meteo.com/en/docs" target="_blank">here</a>)</li>
    </ul>
    current values: 
    
    <?php
if (!empty($weatherSources)) {
    foreach ($weatherSources as $url) {
        echo '<input type="text" readonly class="disabled fullWidthInput" value="' . $url . '"><br>';
    }
} else {
    echo "No weather sources found.";
}
    ?>
</p>

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