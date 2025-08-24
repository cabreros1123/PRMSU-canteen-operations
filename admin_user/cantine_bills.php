<?php
require_once "db.php"; // Adjust path if needed

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['or_no'])) {
    $or_no = $conn->real_escape_string($_POST['or_no']);
    $status = intval($_POST['status']);
    // Only update ver_status for all bills with the same or_no
    $conn->query("UPDATE bills SET ver_status=$status WHERE or_no='$or_no'");
}

// Fetch all bills_img records with owner and total bill
$sql = "
SELECT 
    bills_img.id AS category_no, 
    bills_img.or_no, 
    bills_img.img, 
    bills_img.real_date,
    cantines.owner AS owner_name,
    SUM(bills.payment) AS total_bill,
    MAX(bills.ver_status) AS ver_status
FROM bills_img
LEFT JOIN bills ON bills_img.id = bills.category_no
LEFT JOIN cantines ON bills.cantine_id = cantines.id
GROUP BY bills_img.id, bills_img.or_no, bills_img.img, bills_img.real_date, cantines.owner
ORDER BY bills_img.real_date DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Canteen Bills Ledger</title>
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <style>
        body {
            background: #f4f6fb;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
            margin-top: 32px;
            padding: 32px 24px;
        }
        h2 {
            font-weight: 700;
            color: #2a3b4c;
            margin-bottom: 28px;
            letter-spacing: 1px;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
        }
        thead th {
            background: #e9eef6;
            color: #2a3b4c;
            font-weight: 600;
            padding: 14px 10px;
            border-bottom: 2px solid #d1d9e6;
            text-align: center;
            font-size: 1.08rem;
        }
        tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid #f0f2f7;
            text-align: center;
            font-size: 1.01rem;
            vertical-align: middle;
        }
        tbody tr:nth-child(odd) {
            background: #f7fafd;
        }
        .table-img {
            max-width: 80px;
            max-height: 60px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid #e0e6ed;
            background: #fff;
        }
        .action-btn {
            padding: 4px 16px;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            background: #e74c3c;
            color: #fff;
            cursor: pointer;
            transition: background 0.15s;
        }
        .action-btn:hover {
            background: #c0392b;
        }
        .text-muted {
            color: #a0a4aa;
            font-style: italic;
        }
        .status-btn {
            padding: 6px 18px;
            border: none;
            border-radius: 6px;
            margin: 0 2px;
            font-weight: 600;
            background: #f0f0f0;
            color: #444;
            cursor: pointer;
            transition: background 0.18s, color 0.18s, box-shadow 0.18s, transform 0.12s;
            outline: none;
            position: relative;
        }
        .status-btn.declined { background: #fbeaea; color: #e74c3c; }
        .status-btn.pending { background: #fcf8e3; color: #b7950b; }
        .status-btn.verified { background: #eafaf1; color: #27ae60; }
        .status-btn.declined.active, .status-btn.declined:focus { background: #e74c3c; color: #fff; }
        .status-btn.pending.active, .status-btn.pending:focus { background: #f1c40f; color: #222; }
        .status-btn.verified.active, .status-btn.verified:focus { background: #27ae60; color: #fff; }
        .status-btn:active {
            transform: scale(0.95);
            box-shadow: 0 0 0 2px #1976d220;
        }
        @media (max-width: 700px) {
            .container { padding: 12px 2px; }
            thead th, tbody td { font-size: 0.98rem; padding: 8px 4px; }
            h2 { font-size: 1.2rem; }
        }
    </style>
</head>
<body>
<?php require_once "sidebar.php"; ?>
<?php require_once "header.php"; ?>
<div class="container py-4">
    <h2>Canteen Bills Ledger</h2>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Official Receipt No.</th>
                    <th>Owner Name</th>
                    <th>Image</th>
                    <th>Date Added</th>
                    <th>Total Bill</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr id="row-<?= htmlspecialchars($row['category_no']) ?>">
                        <td><?= htmlspecialchars($row['category_no']) ?></td>
                        <td><?= htmlspecialchars($row['or_no']) ?></td>
                        <td><?= htmlspecialchars($row['owner_name'] ?? 'N/A') ?></td>
                        <td>
                            <?php if (!empty($row['img'])): ?>
                                <a href="../<?= htmlspecialchars($row['img']) ?>" target="_blank">
                                    <img src="../<?= htmlspecialchars($row['img']) ?>" class="table-img" alt="Receipt">
                                </a>
                            <?php else: ?>
                                <span class="text-muted">No Image</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['real_date']) ?></td>
                        <td>₱<?= number_format($row['total_bill'] ?? 0, 2) ?></td>
                        <!-- Status column with all buttons -->
                        <td>
                            <button class="status-btn declined <?= ($row['ver_status']==0 ? 'active' : '') ?>"
                                onclick="updateStatus('<?= htmlspecialchars($row['category_no']) ?>',0,this)">Declined</button>
                            <button class="status-btn pending <?= ($row['ver_status']==1 ? 'active' : '') ?>"
                                onclick="updateStatus('<?= htmlspecialchars($row['category_no']) ?>',1,this)">Pending</button>
                            <button class="status-btn verified <?= ($row['ver_status']==2 ? 'active' : '') ?>"
                                onclick="updateStatus('<?= htmlspecialchars($row['category_no']) ?>',2,this)">Verified</button>
                        </td>
                        <td>
                            <button type="button" class="action-btn" style="background:#3498db;margin-right:4px;" 
                                onclick="viewBillDetails('<?= htmlspecialchars($row['category_no']) ?>')">View</button>
                            <form method="POST" action="delete_bill_img.php" style="display:inline;" onsubmit="return confirm('Delete this record?');">
                                <input type="hidden" name="id" value="<?= $row['category_no'] ?>">
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="10" class="text-center text-muted">No records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="billModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;max-width:700px;width:95vw;min-height:340px;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,0.18);display:flex;overflow:hidden;position:relative;">
        <span onclick="closeBillModal()" style="position:absolute;top:10px;right:18px;font-size:1.6rem;cursor:pointer;color:#888;">&times;</span>
        <div id="modalImgWrap" style="flex:1;min-width:180px;max-width:260px;background:#f7fafd;display:flex;align-items:center;justify-content:center;">
            <a id="modalImgLink" href="#" target="_blank">
                <img id="modalImg" src="" alt="Receipt" style="max-width:95%;max-height:320px;border-radius:8px;">
            </a>
        </div>
        <div style="flex:2;padding:28px 20px 18px 24px;">
            <div style="font-size:1.1rem;font-weight:600;margin-bottom:8px;">Official Receipt No.: <span id="modalOrNo"></span></div>
            <div style="margin-bottom:8px;">Canteen Name: <span id="modalCantine"></span></div>
            <div style="margin-bottom:8px;">Date: <span id="modalDate"></span></div>
            <div style="margin-bottom:12px;">
                <div style="font-weight:600;">Nature of Collection:</div>
                <div id="modalNature"></div>
            </div>
            <div style="font-weight:600;">Total: ₱<span id="modalTotal"></span></div>
        </div>
    </div>
</div>
<div id="declinedModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:99999;align-items:center;justify-content:center;">
    <div style="background:#fff;max-width:340px;width:94vw;padding:32px 18px 18px 18px;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,0.18);text-align:center;position:relative;">
        <div style="font-size:1.15em;font-weight:600;color:#e74c3c;margin-bottom:18px;">
            You just declined a payment.
        </div>
        <button onclick="closeDeclinedModal()" style="padding:8px 28px;background:#e74c3c;color:#fff;border:none;border-radius:6px;font-weight:600;font-size:1em;cursor:pointer;">OK</button>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gather all rows by or_no
    const rows = Array.from(document.querySelectorAll('tr[id^="row-"]'));
    const orNoMap = {};
    rows.forEach(row => {
        const orNo = row.querySelector('td:nth-child(2)').textContent.trim();
        const statusBtns = row.querySelectorAll('.status-btn');
        const isDeclined = statusBtns[0].classList.contains('active');
        if (!orNoMap[orNo]) orNoMap[orNo] = [];
        orNoMap[orNo].push({row, isDeclined, statusBtns});
    });
    // For each or_no, if any is Declined and any is Verified/Pending, disable status buttons for Declined
    Object.values(orNoMap).forEach(group => {
        const hasActive = group.some(item => !item.isDeclined);
        group.forEach(item => {
            if (item.isDeclined && hasActive) {
                item.statusBtns.forEach(btn => btn.disabled = true);
            }
        });
    });
});
function closeBillModal() {
    document.getElementById('billModal').style.display = 'none';
}
function closeDeclinedModal() {
    document.getElementById('declinedModal').style.display = 'none';
    // Proceed with status update after OK
    if (window._pendingDecline) {
        const {or_no, status, btn} = window._pendingDecline;
        sendStatusUpdate(or_no, status, btn);
        window._pendingDecline = null;
    }
}
function monthName(dateStr) {
    // dateStr is "YYYY-MM-DD"
    const months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
    let parts = dateStr.split('-');
    if (parts.length >= 2) {
        let monthIdx = parseInt(parts[1], 10) - 1;
        return months[monthIdx] || '';
    }
    return '';
}
function fullDate(dateStr) {
    let d = new Date(dateStr);
    if (!isNaN(d)) {
        return d.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
    }
    return dateStr;
}

function viewBillDetails(category_no) {
    fetch('view_bill_details.php?category_no=' + encodeURIComponent(category_no))
        .then(res => res.json())
        .then (data => {
            document.getElementById('modalImg').src = '../' + (data.img || '');
            document.getElementById('modalImgLink').href = '../' + (data.img || '');
            document.getElementById('modalOrNo').textContent = data.or_no || '';
            document.getElementById('modalCantine').textContent = data.cantine_name || '';
            document.getElementById('modalDate').textContent = data.real_date || '';
            // Nature of Collection
            let nature = '';
            if (data.bills && data.bills.length) {
                nature = '<ul style="margin:0 0 0 16px;padding:0;">';
                data.bills.forEach(bill => {
                    let typeText = '';
                    let dateText = '';
                    switch (parseInt(bill.bills_type)) {
                        case 1:
                            typeText = 'RENTAL';
                            dateText = bill.date ? ` (${monthName(bill.date)} Payment)` : '';
                            break;
                        case 2:
                            typeText = 'ELECTRIC';
                            dateText = bill.date ? ` (${monthName(bill.date)} Payment)` : '';
                            break;
                        case 3:
                            typeText = 'WATER';
                            dateText = bill.date ? ` (${monthName(bill.date)} Payment)` : '';
                            break;
                        case 4:
                            typeText = bill.name_other || '';
                            dateText = bill.date ? ` (${fullDate(bill.date)})` : '';
                            break;
                        default:
                            typeText = 'UNKNOWN';
                            dateText = '';
                    }
                    nature += `<li>${typeText}: ₱${parseFloat(bill.payment).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}${dateText}</li>`;
                });
                nature += '</ul>';
            } else {
                nature = '<span class="text-muted">No bills found.</span>';
            }
            document.getElementById('modalNature').innerHTML = nature;
            // Total
            document.getElementById('modalTotal').textContent = data.total || '0.00';
            document.getElementById('billModal').style.display = 'flex';
        });
}
function updateStatus(or_no, status, btn) {
    // First, check if status change is allowed
    fetch('can_update_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'category_no=' + encodeURIComponent(or_no) + '&status=' + encodeURIComponent(status)
    })
    .then(res => res.json())
    .then(data => {
        if (!data.can_update) {
            alert('Cannot verify or set to pending: another bill with same Official Receipt No. is declined while others are pending/verified.');
            return;
        }
        // If Declined, show modal
        if (status === 0) {
            window._pendingDecline = {or_no, status, btn};
            document.getElementById('declinedModal').style.display = 'flex';
            return;
        }
        sendStatusUpdate(or_no, status, btn);
    });
}

function sendStatusUpdate(or_no, status, btn) {
    btn.disabled = true;
    let origText = btn.textContent;
    btn.textContent = 'Updating...';
    fetch('update_bill_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'category_no=' + encodeURIComponent(or_no) + '&status=' + encodeURIComponent(status)
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = origText;
        if(data.success) {
            document.querySelectorAll('[id^="row-"]').forEach(row => {
                if (row.id === 'row-' + or_no) {
                    row.querySelectorAll('.status-btn').forEach((button, idx) => {
                        button.classList.toggle('active', idx === status);
                        button.classList.remove('declined', 'pending', 'verified');
                        if (idx === 0) button.classList.add('declined');
                        else if (idx === 1) button.classList.add('pending');
                        else if (idx === 2) button.classList.add('verified');
                    });
                }
            });
        } else {
            alert('Failed to update status!');
        }
    });
}
</script>
</body>
</html>