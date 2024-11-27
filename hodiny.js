function aktualizujHodiny() {
    const hodinyElement = document.getElementById('hodiny');
    const aktualniCas = new Date();
    const hodiny = String(aktualniCas.getHours()).padStart(2, '0');
    const minuty = String(aktualniCas.getMinutes()).padStart(2, '0');
    const vteriny = String(aktualniCas.getSeconds()).padStart(2, '0');
    
    hodinyElement.textContent = `${hodiny}:${minuty}:${vteriny}`;
}

// Aktualizace času každou sekundu
setInterval(aktualizujHodiny, 1000);

// Zavolání funkce hned při načtení stránky
aktualizujHodiny();