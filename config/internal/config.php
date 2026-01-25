<?php
/**
 * Interní logika konfiguračního panelu.
 *
 * Tento soubor je načítán uvnitř config/index.php po úspěšném přihlášení.
 * Zpracovává odeslané formuláře (POST requesty) pro úpravu config.json
 * a generuje HTML formuláře pro nastavení aplikace.
 *
 * @var string $currentUser Uživatelské jméno přihlášeného uživatele.
 * @var bool $isAdmin Příznak, zda má uživatel administrátorská práva.
 */

$currentUser = isset($_COOKIE['account']) ? basename($_COOKIE['account']) : '';
$userFile = dirname(__DIR__) . "/users/" . $currentUser . ".json";
$isAdmin = false;

if ($currentUser && file_exists($userFile)) {
    $userData = json_decode(file_get_contents($userFile), true);
    if (isset($userData['admin']) && $userData['admin'] === "true") {
        $isAdmin = true;
    }
}

if (!empty($_POST) && !isset($_POST['oldPassword']) && !isset($_POST['uploadProfilePic']) && !isset($_POST['targetUser']) && !isset($_POST['newUsername']) && !isset($_POST['deleteUser']) && !isset($_POST['toggleAdmin'])) {
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
        echo "<p class='errorTextBold'>Nemáte oprávnění měnit nastavení.</p>";
    }
    //"Reloadem stránky po odeslání formuláře nesmí dojít k opakovanému vložení dat"
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

if ($isAdmin && isset($_POST['targetUser']) && isset($_POST['newOtherPassword'])) {
    $targetUser = basename($_POST['targetUser']);
    $targetFile = dirname(__DIR__) . "/users/" . $targetUser . ".json";

    if (file_exists($targetFile)) {
        $targetData = json_decode(file_get_contents($targetFile), true);
        // Hash twice: Plaintext -> SHA256 -> SHA256
        $newPassHash = hash('sha256', hash('sha256', $_POST['newOtherPassword']));
        $targetData['passwd'] = $newPassHash;
        file_put_contents($targetFile, json_encode($targetData, JSON_PRETTY_PRINT));
        echo "<script>alert('Password for user " . htmlspecialchars($targetUser) . " changed.'); window.location.href = window.location.href;</script>";
        exit;
    }
}

if ($isAdmin && isset($_POST['newUsername']) && isset($_POST['newUserPassword'])) {
    $newUsername = trim(basename($_POST['newUsername']));
    $newUserFile = dirname(__DIR__) . "/users/" . $newUsername . ".json";

    if ($newUsername === "") {
        echo "<script>alert('Username cannot be empty.'); window.location.href = window.location.href;</script>";
        exit;
    }

    if (file_exists($newUserFile)) {
        echo "<script>alert('User " . htmlspecialchars($newUsername) . " already exists.'); window.location.href = window.location.href;</script>";
        exit;
    }

    // Hash twice: Plaintext -> SHA256 -> SHA256
    $newPassHash = hash('sha256', hash('sha256', $_POST['newUserPassword']));
    $newUserData = ['passwd' => $newPassHash, 'admin' => 'false'];
    file_put_contents($newUserFile, json_encode($newUserData, JSON_PRETTY_PRINT));
    echo "<script>alert('User " . htmlspecialchars($newUsername) . " created.'); window.location.href = window.location.href;</script>";
    exit;
}

if ($isAdmin && isset($_POST['deleteUser'])) {
    $targetUser = basename($_POST['deleteUser']);
    $targetFile = dirname(__DIR__) . "/users/" . $targetUser . ".json";
    $usersDir = dirname(__DIR__) . "/users/";

    if (file_exists($targetFile)) {
        unlink($targetFile);

        if (file_exists($usersDir . $targetUser . ".jpg")) {
            unlink($usersDir . $targetUser . ".jpg");
        }
        if (file_exists($usersDir . $targetUser . ".png")) {
            unlink($usersDir . $targetUser . ".png");
        }

        echo "<script>alert('User " . htmlspecialchars($targetUser) . " deleted.'); window.location.href = window.location.href;</script>";
        exit;
    }
}

if ($isAdmin && isset($_POST['toggleAdmin'])) {
    $targetUser = basename($_POST['toggleAdmin']);
    $targetFile = dirname(__DIR__) . "/users/" . $targetUser . ".json";

    if (file_exists($targetFile)) {
        $targetData = json_decode(file_get_contents($targetFile), true);
        $isAdminStatus = isset($targetData['admin']) && $targetData['admin'] === 'true';
        $targetData['admin'] = $isAdminStatus ? 'false' : 'true';
        
        file_put_contents($targetFile, json_encode($targetData, JSON_PRETTY_PRINT));
        $msg = $isAdminStatus ? 'demoted from admin' : 'promoted to admin';
        echo "<script>alert('User " . htmlspecialchars($targetUser) . " " . $msg . ".'); window.location.href = window.location.href;</script>";
        exit;
    }
}

?>

<h1>Configuration panel</h1>

<button type="button" id="logoutBtn">Logout</button>

<h2>Panel settings</h2>

<p>*: this item is needed for basic functionality of panel</p>

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
        <label for="refreshTime">Refresh time:*</label>

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
        <label for="mhdUrl">MHD Url:*</label>
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
        <label for="mhdApiKey">MHD Api key:*</label>
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

        <p>Weather Diagnostics: <a href="./pocasi.php" >here</a></p>
        
        <div id="weatherSourcesContainer">
            <?php
            if (!empty($weatherSources)) {
                foreach ($weatherSources as $url) {
                    echo '<div class="weatherSourceRow">';
                    echo '<input type="text" name="weatherSources[]" aria-label="Weather source URL" class="fullWidthInput flexGrow" value="' . htmlspecialchars($url) . '" ' . ($isAdmin ? '' : 'disabled') . '>';
                    if ($isAdmin) {
                        echo '<button type="button" class="moveUpBtn">↑</button>';
                        echo '<button type="button" class="moveDownBtn">↓</button>';
                        echo '<button type="button" class="removeBtn">Remove</button>';
                    }
                    echo '</div>';
                }
            }
            ?>
        </div>
        <?php if ($isAdmin): ?><button type="button" id="addWeatherSourceBtn" class="marginBottom10">Add
                URL</button><?php endif; ?>
    </div>
    <?php if ($isAdmin): ?><button type="submit">Save</button><?php endif; ?>
</form>

<hr>

<?php

if (isset($_POST['oldPassword'])) {
    $currentUser = isset($_COOKIE['account']) ? basename($_COOKIE['account']) : '';
    $userFile = dirname(__DIR__) . "/users/" . $currentUser . ".json";

    if ($currentUser && file_exists($userFile)) {
        $userData = json_decode(file_get_contents($userFile), true);
        $oldPassHash = hash('sha256', $_POST['oldPassword']);

        if (isset($userData['passwd']) && $userData['passwd'] === $oldPassHash) {
            if ($_POST['newPassword'] === $_POST['confirmPassword']) {
                $newPassHash = hash('sha256', $_POST['newPassword']);
                $userData['passwd'] = $newPassHash;

                file_put_contents($userFile, json_encode($userData, JSON_PRETTY_PRINT));

                echo "<script>
                    alert('Password successfully changed.');
                    window.location.href = 'login.php';
                </script>";
                exit;
            } else {
                echo "<script>alert('Password did not change: New passwords do not match.'); window.location.href = window.location.href;</script>";
            }
        } else {
            echo "<script>alert('Password did not change: Old password is incorrect.'); window.location.href = window.location.href;</script>";
            exit;
        }
    } else {
        $msg = "User not found.";
        $msgClass = "errorText";
    }
}

if (isset($_POST['uploadProfilePic'])) {
    $currentUser = isset($_COOKIE['account']) ? basename($_COOKIE['account']) : '';
    $usersDir = dirname(__DIR__) . "/users/";

    if ($currentUser && isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
        $imageInfo = getimagesize($_FILES['profilePic']['tmp_name']);
        if ($imageInfo !== false) {
            $mime = $imageInfo['mime'];
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

            if (array_key_exists($mime, $allowedTypes)) {
                $ext = $allowedTypes[$mime];
                $targetFile = $usersDir . $currentUser . "." . $ext;

                if (file_exists($usersDir . $currentUser . ".jpg"))
                    unlink($usersDir . $currentUser . ".jpg");
                if (file_exists($usersDir . $currentUser . ".png"))
                    unlink($usersDir . $currentUser . ".png");

                if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $targetFile)) {
                    echo "<script>alert('Profile picture updated.'); window.location.href = window.location.href;</script>";
                    exit;
                } else {
                    $msgPic = "Error saving file.";
                }
            } else {
                $msgPic = "Invalid file type. Only JPG and PNG allowed.";
            }
        } else {
            $msgPic = "File is not an image.";
        }
    } else {
        $msgPic = "Upload failed.";
    }
}
?>

<h2>User settings</h2>

<?php if (isset($msg)) {
    echo "<p class='$msgClass'>$msg</p>";
} ?>

<form method="POST" id="passwordForm">
    <div class="form-group">
        <label for="oldPassword">Old password:</label>
        <input type="password" id="oldPassword" name="oldPassword" required class="fullWidthInput">
        <br><br>

        <label for="newPassword">New password:</label>
        <input type="password" id="newPassword" name="newPassword" required class="fullWidthInput">
        <br><br>

        <label for="confirmPassword">Confirm new password:</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required class="fullWidthInput">
    </div>
    <br>
    <button type="submit">Change password</button>
</form>

<hr>

<h3>Profile picture</h3>

<?php if (isset($msgPic)) {
    echo "<p class='errorText'>$msgPic</p>";
} ?>

<?php
$currentUser = isset($_COOKIE['account']) ? basename($_COOKIE['account']) : '';
$usersDir = dirname(__DIR__) . "/users/";
$picPath = "";

if (file_exists($usersDir . $currentUser . ".jpg")) {
    $picPath = "/config/users/" . $currentUser . ".jpg";
} elseif (file_exists($usersDir . $currentUser . ".png")) {
    $picPath = "/config/users/" . $currentUser . ".png";
}

if ($picPath) {
    echo "<img src='$picPath?t=" . time() . "' alt='Profile Picture' class='profilePic'>";
}
?>

<form method="POST" enctype="multipart/form-data">
    <label for="profilePic">Select image:</label>
    <input type="file" id="profilePic" name="profilePic" accept="image/png, image/jpeg" required>
    <button type="submit" name="uploadProfilePic">Upload picture</button>
</form>

<?php
if ($isAdmin) {
    ?>

    <hr>

    <h2>Other user settings</h2>

    <?php

    $folderPath = dirname(__DIR__) . '/users/';

    if (is_dir($folderPath)) {
        $files = glob($folderPath . '*.json');
        if ($files) {
            echo "<ul>";
            foreach ($files as $file) {
                $userData = json_decode(file_get_contents($file), true);
                if ($userData === null)
                    continue;

                $username = basename($file, '.json');
                if (isset($_COOKIE['account']) && $username == $_COOKIE['account'])
                    continue;

                $isUserAdmin = isset($userData['admin']) && $userData['admin'] === 'true';
                $adminLabel = $isUserAdmin ? " (Admin)" : "";
                $adminBtnText = $isUserAdmin ? "Demote" : "Promote";

                echo "<li class=\"userlist\"><strong>Username:</strong> " . htmlspecialchars($username) . $adminLabel . " | ";
                echo '<form method="POST" class="inlineFormFirst">
                    <input type="hidden" name="targetUser" value="' . htmlspecialchars($username) . '">
                    <input type="text" name="newOtherPassword" placeholder="New password" aria-label="New password" required>
                    <button type="submit">Change</button>
                </form>';
                echo '<form method="POST" class="inlineForm">
                    <input type="hidden" name="toggleAdmin" value="' . htmlspecialchars($username) . '">
                    <button type="submit">' . $adminBtnText . '</button>
                </form>';
                echo '<form method="POST" class="inlineForm" onsubmit="return confirm(\'Are you sure you want to delete user ' . htmlspecialchars($username) . '?\');">
                    <input type="hidden" name="deleteUser" value="' . htmlspecialchars($username) . '">
                    <button type="submit">Delete</button>
                </form>';
                echo "</li>";
            }
            echo "</ul>";
        }
    }
    ?>

    <h3>Create new user</h3>
    <form method="POST" class="marginBottom20">
        <input type="text" name="newUsername" placeholder="Username" aria-label="New username" required>
        <input type="password" name="newUserPassword" placeholder="Password" aria-label="New user password" required>
        <button type="submit">Create</button>
    </form>

    <?php
}
?>

<script src="config.js"></script>