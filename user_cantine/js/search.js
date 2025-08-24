document.getElementById('searchInput').addEventListener('input', function () {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#productTable tbody tr');

    rows.forEach(row => {
        const id = row.cells[0].textContent.toLowerCase();
        const category = row.cells[1].textContent.toLowerCase();
        const description = row.cells[2].textContent.toLowerCase();

        if (id.includes(filter) || category.includes(filter) || description.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});