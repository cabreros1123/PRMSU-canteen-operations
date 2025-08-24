<?php
require_once "db.php";

// Fetch all canteens
$canteens = $conn->query("SELECT * FROM cantines ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Canteen Report</title>
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <style>
        body { font-family: Arial, sans-serif; }
        .canteen-card {
            background: linear-gradient(135deg, #f7fafc 60%, #e3f2fd 100%);
            border-radius: 18px;
            box-shadow: 0 4px 24px #1976d220, 0 1.5px 4px #1976d210;
            padding: 22px 18px 18px 18px;
            min-width: 260px;
            max-width: 320px;
            flex: 1 1 260px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: relative;
            margin-bottom: 18px;
        }
        .canteen-card.selected {
            border: 2.5px solid #1976d2;
            box-shadow: 0 0 0 4px #1976d220, 0 4px 24px #1976d220, 0 1.5px 4px #1976d210;
            background: linear-gradient(135deg, #e3f2fd 80%, #f7fafc 100%);
            transition: box-shadow 0.2s, border 0.2s, background 0.2s;
        }
        .canteen-title {
            font-size:1.18em;
            font-weight:bold;
            color:#1976d2;
            margin-bottom:2px;
        }
        .canteen-info {
            font-size:1em;
            color:#444;
        }
        .action-btn {
            background:#27ae60;
            color:#fff;
            border:none;
            border-radius:6px;
            padding:6px 16px;
            cursor:pointer;
            margin-top:10px;
            width:80px;
        }
        .canteen-list {
            display:flex;
            flex-wrap:wrap;
            gap:18px 14px;
            margin-bottom:24px;
        }
        .app-input {
            padding: 7px 12px;
            border-radius: 6px;
            border: 1px solid #bdbdbd;
            font-size: 1em;
            margin-bottom: 6px;
        }
        .messages-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 12px #1976d220;
        }
        .messages-table th, .messages-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }
        .messages-table th {
            background: #1976d2;
            color: #fff;
            font-weight: 600;
        }
        .messages-table tr:last-child td {
            border-bottom: none;
        }
        .messages-table tr:hover td {
            background: #e3f2fd;
        }
        #messages-container h3 {
            margin-top: 0;
            color: #1976d2;
        }
    </style>
</head>
<body>
    <?php require_once "sidebar.php"; ?>
    <?php require_once "header.php"; ?>
    <h2>Messages Canteen Reports</h2>
    <div class="canteen-list">
    <?php
    mysqli_data_seek($canteens, 0);
    while($c = $canteens->fetch_assoc()): ?>
        <div class="canteen-card" id="canteen-card-<?= $c['id'] ?>">
            <div class="canteen-title"><?= htmlspecialchars($c['name']) ?></div>
            <div class="canteen-info">
                <span><strong>Stall No:</strong> <?= htmlspecialchars($c['stall_no']) ?></span><br>
                <span><strong>Owner:</strong> <?= htmlspecialchars($c['owner']) ?></span><br>
                <span><strong>Email:</strong> <?= htmlspecialchars($c['email']) ?></span><br>
                <span><strong>Phone:</strong> <?= htmlspecialchars($c['phone']) ?></span>
            </div>
            <button class="action-btn" onclick="viewCanteen(<?= $c['id'] ?>)">View</button>
        </div>
    <?php endwhile; ?>
    </div>
    <div id="messages-controls" style="display:none; margin-bottom:18px;">
        <input type="text" id="searchInput" placeholder="Search conversations..." class="app-input" style="margin-right:10px;">
        <label for="dateFrom">From:</label>
        <input type="date" id="dateFrom" class="app-input" style="margin-right:10px;">
        <label for="dateTo">To:</label>
        <input type="date" id="dateTo" class="app-input" style="margin-right:10px;">
        <select id="rowsPerPage" class="app-input" style="margin-right:10px;">
            <option value="10">10 rows</option>
            <option value="20">20 rows</option>
            <option value="50">50 rows</option>
            <option value="100">100 rows</option>
            <option value="500">500 rows</option>
            <option value="1000">1000 rows</option>
            <option value="999999">All</option>
        </select>
        <span id="pagination-controls"></span>
    </div>
    <div id="messages-container"></div>
    <script>
    let allMessages = [];
    let currentCanteenId = null;
    let rowsPerPage = 10;

    function formatDate(dateStr) {
        const d = new Date(dateStr.replace(' ', 'T'));
        const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true };
        return d.toLocaleString('en-US', options);
    }

    function highlightCanteenCard(id) {
        document.querySelectorAll('.canteen-card').forEach(card => card.classList.remove('selected'));
        const selected = document.getElementById('canteen-card-' + id);
        if (selected) selected.classList.add('selected');
    }

    function viewCanteen(id) {
        currentCanteenId = id;
        highlightCanteenCard(id);
        fetch('fetch_canteen_messages.php?canteen_id=' + id)
            .then(res => res.json())
            .then(data => {
                allMessages = data;
                document.getElementById('messages-controls').style.display = 'block';
                renderMessages();
            });
    }

    function renderMessages(page = 1) {
        let html = '<h3>Messages</h3>';
        let filtered = allMessages;

        // Search filter
        const search = document.getElementById('searchInput').value.toLowerCase();
        if (search) {
            filtered = filtered.filter(msg =>
                (msg.sender && msg.sender.toLowerCase().includes(search)) ||
                (msg.message && msg.message.toLowerCase().includes(search))
            );
        }

        // Date filter
        const from = document.getElementById('dateFrom').value;
        const to = document.getElementById('dateTo').value;
        if (from) {
            filtered = filtered.filter(msg => msg.date_sent >= from);
        }
        if (to) {
            filtered = filtered.filter(msg => msg.date_sent <= to + ' 23:59:59');
        }

        // Pagination
        const rowsPerPage = parseInt(document.getElementById('rowsPerPage').value) || 10;
        const totalRows = filtered.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginated = filtered.slice(start, end);

        if (filtered.length === 0) {
            html += '<p>No messages found for this canteen.</p>';
        } else {
            html += `<table class="messages-table">
                <tr>
                    <th>ID</th>
                    <th>Sender</th>
                    <th>Message</th>
                    <th>Attachment</th>
                    <th>Date Sent</th>
                </tr>`;
            paginated.forEach(msg => {
                let sender = msg.sender;
                if (msg.is_announcement == "1" || msg.is_announcement == 1) {
                    sender = "admin-(announcement)";
                }
                let attachment = "No Image or File";
                if (msg.image && msg.image !== "NULL" && msg.image !== "") {
                    const imgExt = /\.(jpg|jpeg|png|gif)$/i;
                    const fileExt = /\.(pdf|doc|docx|xls|xlsx|zip|rar)$/i;
                    if (imgExt.test(msg.image)) {
                        attachment = "With Image";
                    } else if (fileExt.test(msg.image)) {
                        attachment = "With File";
                    } else {
                        attachment = "With File";
                    }
                }
                html += `<tr>
                    <td>${msg.id}</td>
                    <td>${sender}</td>
                    <td>${msg.message}</td>
                    <td>${attachment}</td>
                    <td>${formatDate(msg.date_sent)}</td>
                </tr>`;
            });
            html += '</table>';
        }
        document.getElementById('messages-container').innerHTML = html;

        // Pagination controls
        let pagHtml = '';
        if (totalPages > 1) {
            pagHtml += `<button ${page === 1 ? 'disabled' : ''} onclick="renderMessages(${page-1})">Prev</button>`;
            pagHtml += ` Page ${page} of ${totalPages} `;
            pagHtml += `<button ${page === totalPages ? 'disabled' : ''} onclick="renderMessages(${page+1})">Next</button>`;
        }
        document.getElementById('pagination-controls').innerHTML = pagHtml + 
            `<button id="printTableBtn" class="app-input" style="float:right;">Print</button>`;

        document.getElementById('printTableBtn').onclick = function() {
            // Get canteen info from the card
            const canteenCard = document.querySelector(`.canteen-card button[onclick="viewCanteen(${currentCanteenId})"]`).parentElement;
            const canteenName = canteenCard.querySelector('.canteen-title').textContent;
            const canteenInfoLines = Array.from(canteenCard.querySelectorAll('.canteen-info span')).map(span => span.textContent);
            const canteenInfo = canteenInfoLines.join('<br>');

            // Get current date/time
            const printDate = new Date();
            const printDateStr = printDate.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });

            // Filter messages (same as in renderMessages)
            let filtered = allMessages;
            const search = document.getElementById('searchInput').value.toLowerCase();
            if (search) {
                filtered = filtered.filter(msg =>
                    (msg.sender && msg.sender.toLowerCase().includes(search)) ||
                    (msg.message && msg.message.toLowerCase().includes(search))
                );
            }
            const from = document.getElementById('dateFrom').value;
            const to = document.getElementById('dateTo').value;
            if (from) {
                filtered = filtered.filter(msg => msg.date_sent >= from);
            }
            if (to) {
                filtered = filtered.filter(msg => msg.date_sent <= to + ' 23:59:59');
            }

            // Build table with ALL filtered rows
            let tableHtml = `<table class="messages-table">
                <tr>
                    <th>ID</th>
                    <th>Sender</th>
                    <th>Message</th>
                    <th>Attachment</th>
                    <th>Date Sent</th>
                </tr>`;
            filtered.forEach(msg => {
                let sender = msg.sender;
                if (msg.is_announcement == "1" || msg.is_announcement == 1) {
                    sender = "admin-(announcement)";
                }
                let attachment = "No Image or File";
                if (msg.image && msg.image !== "NULL" && msg.image !== "") {
                    const imgExt = /\.(jpg|jpeg|png|gif)$/i;
                    const fileExt = /\.(pdf|doc|docx|xls|xlsx|zip|rar)$/i;
                    if (imgExt.test(msg.image)) {
                        attachment = "With Image";
                    } else if (fileExt.test(msg.image)) {
                        attachment = "With File";
                    } else {
                        attachment = "With File";
                    }
                }
                tableHtml += `<tr>
                    <td>${msg.id}</td>
                    <td>${sender}</td>
                    <td>${msg.message}</td>
                    <td>${attachment}</td>
                    <td>${formatDate(msg.date_sent)}</td>
                </tr>`;
            });
            tableHtml += '</table>';

            // Print window
            const printWindow = window.open('', '', 'height=600,width=900');
            printWindow.document.write('<html><head><title>Print Messages</title>');
            printWindow.document.write('<style>body{font-family:Arial;} h2{color:#1976d2;} .canteen-print-info{margin-bottom:14px;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:8px;} th{background:#1976d2;color:#fff;}</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<h2>Messages Report</h2>');
            printWindow.document.write('<div class="canteen-print-info">');
            printWindow.document.write('<strong>Canteen:</strong> ' + canteenName + '<br>');
            printWindow.document.write(canteenInfo + '<br>');
            printWindow.document.write('<strong>Printed on:</strong> ' + printDateStr);
            printWindow.document.write('</div>');
            printWindow.document.write(tableHtml);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        };

        window.scrollTo({ top: document.getElementById('messages-container').offsetTop, behavior: 'smooth' });
    }

    // Update event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('dateFrom').setAttribute('max', today);
        document.getElementById('dateTo').setAttribute('max', today);

        document.getElementById('searchInput').addEventListener('input', () => renderMessages(1));
        document.getElementById('dateFrom').addEventListener('change', () => renderMessages(1));
        document.getElementById('dateTo').addEventListener('change', () => renderMessages(1));
        document.getElementById('rowsPerPage').addEventListener('change', () => renderMessages(1));
    });
    </script>
</body>
</html>