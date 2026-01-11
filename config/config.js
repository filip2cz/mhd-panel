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