# Produktová dokumentace - MHD Panel

**MHD Panel** je webová aplikace určená pro informační obrazovky (kiosky), která v reálném čase zobrazuje odjezdy hromadné dopravy, aktuální počasí a další užitečné informace. Je navržena pro nepřetržitý provoz na veřejných místech, ve firmách nebo v domácnostech.

## Hlavní funkce

### 1. Odjezdy hromadné dopravy
*   **Reálná data:** Zobrazuje aktuální odjezdy spojů včetně zpoždění.
*   **Podpora systémů:** Integruje data z PID (Pražská integrovaná doprava - Golemio API) a MPVNet.
*   **Přehlednost:** Zobrazuje číslo linky, cílovou stanici, čas odjezdu a případné zpoždění v minutách.
*   **Automatické přizpůsobení:** Počet zobrazených řádků se automaticky vypočítá podle velikosti obrazovky, aby nedocházelo k nutnosti posouvání (scrolling).

### 2. Informace o počasí
*   Zobrazuje aktuální venkovní teplotu.
*   Využívá více zdrojů dat (Open-Meteo, Meteo-Počasí) s automatickým přepnutím v případě výpadku jednoho z nich.
*   Kliknutím na teplotu lze zobrazit aktuálně použitý zdroj dat.

### 3. Interaktivní mapa
*   Volitelná funkce, která umožňuje uživatelům zobrazit mapu okolí (např. pro orientaci na zastávce).
*   Mapa se otevírá v celoobrazovkovém režimu.
*   **Automatický návrat:** Pokud uživatel mapu opustí, systém se po 5 minutách nečinnosti automaticky vrátí zpět na tabuli s odjezdy.

### 4. Pátrání po osobách (Policejní výstrahy)
*   Systém je napojen na RSS kanál Policie ČR.
*   V případě vyhlášení pátrání po pohřešované osobě se ve spodní části obrazovky zobrazí fotografie a upozornění.
*   Tato funkce pomáhá šířit důležité informace veřejnosti.

### 5. Režim Kiosku
*   Aplikace je optimalizována pro tmavý režim (Dark Mode) pro úsporu energie a lepší čitelnost v noci.
*   Stránka se automaticky obnovuje v nastaveném intervalu.

## Uživatelská příručka

### Běžný provoz
Panel funguje zcela automaticky. Uživatel vidí seznam nejbližších spojů seřazený podle času odjezdu.

*   **Čas:** V pravém dolním rohu běží aktuální čas.
*   **Teplota:** V levém dolním rohu je aktuální teplota.
*   **Mapa:** Pokud je povolena, tlačítko "Mapa" v dolní části obrazovky otevře mapové podklady.

### Interakce
*   **Kliknutí na obrazovku:** Kliknutí kamkoliv do prostoru odjezdů vyvolá okamžité obnovení dat (refresh).
*   **Kliknutí na teplotu:** Zobrazí dialogové okno s názvem služby, která poskytuje data o počasí.

## Administrace a Nastavení

Aplikace obsahuje administrační rozhraní pro snadnou konfiguraci bez nutnosti zásahu do kódu.

**Přístup do administrace:**
Administrace je dostupná na adrese `http://vas-server/config/`. Přístup je chráněn uživatelským jménem a heslem.

**Možnosti nastavení:**
*   **Název zastávky:** Text, který se zobrazuje v záhlaví stránky (např. "Odjezdy z Náměstí Míru").
*   **Zdroj dat (URL):** Nastavení API endpointu pro konkrétní zastávku (Golemio/MPVNet).
*   **API Klíč:** Vstup pro bezpečnostní klíč (pokud je vyžadován poskytovatelem dat).
*   **Interval obnovení:** Čas v sekundách, po kterém se stránka automaticky znovu načte (doporučeno 10-60 s).
*   **Mapa:** Zapnutí/vypnutí tlačítka mapy a nastavení URL adresy mapy.
*   **Pátrání:** Povolení nebo zakázání modulu pro zobrazování pohřešovaných osob.

## Technické požadavky

Pro provoz panelu je zapotřebí:
1.  **Hardware:** Počítač, Raspberry Pi nebo tablet s připojením k internetu a displejem.
2.  **Software:** Webový prohlížeč (doporučen Chromium/Chrome) spuštěný v režimu Kiosk (`--kiosk`).
3.  **Server:** Webový server (Apache/Nginx) s podporou PHP a přístupem k internetu (pro stahování dat z API).