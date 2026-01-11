function logout() {
    document.cookie = "account=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC";
    document.cookie = "passwd=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC";
    location.reload();
}

function addWeatherSource() {
    const container = document.getElementById('weatherSourcesContainer');
    const div = document.createElement('div');
    div.style.cssText = 'display: flex; gap: 5px; margin-bottom: 5px;';
    div.innerHTML = '<input type="text" name="weatherSources[]" class="fullWidthInput" placeholder="Add URL" style="flex-grow: 1;">' +
        '<button type="button" onclick="moveUp(this)">↑</button>' +
        '<button type="button" onclick="moveDown(this)">↓</button>' +
        '<button type="button" onclick="this.parentElement.remove()">Remove</button>';
    container.appendChild(div);
}

function moveUp(btn) {
    const row = btn.parentElement;
    const prev = row.previousElementSibling;
    if (prev) {
        row.parentElement.insertBefore(row, prev);
    }
}

function moveDown(btn) {
    const row = btn.parentElement;
    const next = row.nextElementSibling;
    if (next) {
        row.parentElement.insertBefore(next, row);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("form").forEach(form => {
        form.addEventListener("submit", function (e) {
            const inputs = form.querySelectorAll("input");
            for (let input of inputs) {
                // Validace refreshTime
                if (input.name === 'refreshTime' && (input.value === "" || isNaN(input.value) || Number(input.value) < 1)) {
                    alert("Čas obnovení musí být číslo větší nebo rovno 1.");
                    input.focus();
                    e.preventDefault();
                    return;
                }
                // Ochrana proti XSS (zakázání < a >)
                if (input.type === 'text' && /[<>]/.test(input.value)) {
                    alert("Vstup nesmí obsahovat znaky < nebo >.");
                    input.focus();
                    e.preventDefault();
                    return;
                }
            }
        });
    });
});

document.getElementById('passwordForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const oldPwd = document.getElementById('oldPassword');
    const newPwd = document.getElementById('newPassword');
    const confirmPwd = document.getElementById('confirmPassword');

    async function sha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    if (oldPwd.value) oldPwd.value = await sha256(oldPwd.value);
    if (newPwd.value) newPwd.value = await sha256(newPwd.value);
    if (confirmPwd.value) confirmPwd.value = await sha256(confirmPwd.value);

    this.submit();
});

function validateMapSettings() {
    const enableMap = document.getElementById('enableMap');
    const mapUrl = document.getElementById('mapUrl');

    if (enableMap.checked && mapUrl.value.trim() === "") {
        alert("URL mapy nesmí být prázdná, pokud je mapa povolena.");
        return false;
    }
    return true;
}