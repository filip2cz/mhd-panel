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