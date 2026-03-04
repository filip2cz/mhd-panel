<span class="vetsiText">
    <?php
        echo $batteryText;
        $battery = file_get_contents('battery');
        echo $battery;
    ?>%
</span>