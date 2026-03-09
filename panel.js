function refreshPage() {
    fetch(window.location.href) // Stáhne aktuální stránku
        .then(response => response.text()) // Převede ji na text
        .then(html => {
            let newDoc = new DOMParser().parseFromString(html, "text/html"); // Vytvoří nový DOM
            document.body.innerHTML = newDoc.body.innerHTML; // Přepíše obsah stránky
        })
        .catch(err => console.error("Chyba při načítání stránky:", err));
}

// Funkce pro přesměrování na jinou stránku při kliknutí kamkoliv
document.addEventListener("click", function (e) {
    if (e.target.closest('#teplota')) {
        weatherInfo();
    } else {
        window.location.href = "./mhd-tabule.php";
    }
});

// Funkce pro zobrazení informací o počasí
function weatherInfo() {
    alert(
        "Zdroj dat o počasí:\n<?php echo htmlspecialchars($GLOBALS['weatherSources'][$GLOBALS['weatherIndex']]) ?>"
    );
}