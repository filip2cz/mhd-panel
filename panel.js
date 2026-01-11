function setWindowSizeCookie() {
    document.cookie = "window_height=" + window.innerHeight + "; path=/";
    document.cookie = "window_width=" + window.innerWidth + "; path=/";
}

// Zavoláme při načtení a změně velikosti okna
window.onload = setWindowSizeCookie;
window.onresize = setWindowSizeCookie;

function refreshPage() {
    fetch(window.location.href) // Stáhne aktuální stránku
        .then(response => response.text()) // Převede ji na text
        .then(html => {
            let newDoc = new DOMParser().parseFromString(html, "text/html"); // Vytvoří nový DOM
            document.body.innerHTML = newDoc.body.innerHTML; // Přepíše obsah stránky
        })
        .catch(err => console.error("Chyba při načítání stránky:", err));
}