<span class="vetsiText">
    Baterie:
    <?php
        $battery = file_get_contents('battery');
        echo $battery;
    ?>%
</span>