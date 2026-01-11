/**
 * Nastaví cookies s aktuální výškou a šířkou okna prohlížeče.
 * Tyto cookies jsou využívány backendem pro responzivní výpočet počtu řádků.
 */
function setWindowSizeCookie() {
    document.cookie = "window_height=" + window.innerHeight + "; path=/";
    document.cookie = "window_width=" + window.innerWidth + "; path=/";
}

// Zavoláme při načtení a změně velikosti okna
window.onload = setWindowSizeCookie;
window.onresize = setWindowSizeCookie;

/**
 * Obnoví obsah stránky stažením aktuálního HTML a nahrazením obsahu body.
 * Zabraňuje problikávání celé stránky při refresh.
 */
function refreshPage() {
    fetch(window.location.href) // Stáhne aktuální stránku
        .then(response => response.text()) // Převede ji na text
        .then(html => {
            let newDoc = new DOMParser().parseFromString(html, "text/html"); // Vytvoří nový DOM
            document.body.innerHTML = newDoc.body.innerHTML; // Přepíše obsah stránky
        })
        .catch(err => console.error("Chyba při načítání stránky:", err));
}