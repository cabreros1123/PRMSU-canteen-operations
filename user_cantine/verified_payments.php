<?php
session_start();
if (!isset($_SESSION['id_cantine'])) {
    die('Error: You are not authorized to view this page. Please log in.');
}
require_once "../admin_user/db.php";
$id_cantine = (int)$_SESSION['id_cantine'];
// Fetch only bills for the logged-in canteen
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
WHERE bills.cantine_id = $id_cantine
  AND bills.ver_status != 0
GROUP BY bills_img.id, bills_img.or_no, bills_img.img, bills_img.real_date, cantines.owner
ORDER BY bills_img.real_date DESC
";
$result = $conn->query($sql);

// --- Find missing bills for this canteen ---
$today = new DateTime();
$currentMonth = (int)$today->format('n');
$currentYear = (int)$today->format('Y');

// Only check months in the current year, up to last month
$monthsToCheck = [];
for ($m = 1; $m < $currentMonth; $m++) { // from January to last month
    $dt = DateTime::createFromFormat('Y-n', "$currentYear-$m");
    $monthsToCheck[] = [
        'year' => $currentYear,
        'month' => $m,
        'label' => $dt->format('F Y'),
        'ym' => $dt->format('Y-m')
    ];
}

// Bill types to check
$billTypes = [
    1 => 'Rental',
    2 => 'Electric',
    3 => 'Water'
];

$missingBills = [];
$pendingBills = [];
foreach ($billTypes as $typeId => $typeName) {
    $missing = [];
    $pending = [];
    foreach ($monthsToCheck as $m) {
        $ym = $m['ym'];
        $stmt = $conn->prepare("SELECT ver_status FROM bills WHERE cantine_id=? AND bills_type=? AND DATE_FORMAT(date, '%Y-%m')=? AND ver_status != 0");
        $stmt->bind_param("iis", $id_cantine, $typeId, $ym);
        $stmt->execute();
        $stmt->bind_result($ver_status);
        $found = false;
        while ($stmt->fetch()) {
            $found = true;
            if ($ver_status == 1) { // Pending
                $pending[] = $m['label'];
            }
        }
        $stmt->close();
        if (!$found) {
            $missing[] = $m['label'];
        }
    }
    if ($missing) $missingBills[$typeName] = $missing;
    if ($pending) $pendingBills[$typeName] = $pending;
}
$totalMissing = 0;
foreach ($missingBills as $months) {
    $totalMissing += count($months);
}
$isDanger = $totalMissing >= 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <title>Verified Payments</title>
    <style>
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
            outline: none;
            position: relative;
            cursor: default;
        }
        .status-btn.declined { background: #fbeaea; color: #e74c3c; }
        .status-btn.pending { background: #fcf8e3; color: #b7950b; }
        .status-btn.verified { background: #eafaf1; color: #27ae60; }
        .status-btn.active.declined {
            background: #ff3b30 !important;
            color: #fff !important;
            font-weight: bold;
            box-shadow: 0 0 0 2px #ff3b3040;
        }
        .status-btn.active.pending {
            background: #ffd600 !important;
            color: #222 !important;
            font-weight: bold;
            box-shadow: 0 0 0 2px #ffd60040;
        }
        .status-btn.active.verified {
            background: #00c853 !important;
            color: #fff !important;
            font-weight: bold;
            box-shadow: 0 0 0 2px #00c85340;
        }
        @media (max-width: 700px) {
            .container { padding: 12px 2px; }
            thead th, tbody td { font-size: 0.98rem; padding: 8px 4px; }
            h2 { font-size: 1.2rem; }
        }
    </style>
</head>
<?php require_once "header.php"; ?>
<body>

<div class="container py-4">
    <h2>Verified Payments</h2>
        <!-- View Verified Bills Button -->
    <div style="margin-bottom:18px;">
        <button type="button" class="btn btn-primary" onclick="viewMyVerifiedBills()">View Verified Bills</button>
    </div>

    <!-- Modal for Viewing Bills -->
    <div id="myBillsModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#fff;max-width:900px;width:98vw;max-height:95vh;overflow-y:auto;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,0.18);padding:28px 18px 18px 18px;position:relative;">
            <span onclick="closeMyBillsModal()" style="position:absolute;top:10px;right:18px;font-size:1.6rem;cursor:pointer;color:#888;">&times;</span>
            <div id="myBillsModalContent"></div>
        </div>
    </div>
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
                    <tr>
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
                        <!-- Status column with all buttons (read-only, no onclick) -->
                        <td>
                            <button class="status-btn declined <?= ($row['ver_status']==0 ? 'active' : '') ?>" disabled>Declined</button>
                            <button class="status-btn pending <?= ($row['ver_status']==1 ? 'active' : '') ?>" disabled>Pending</button>
                            <button class="status-btn verified <?= ($row['ver_status']==2 ? 'active' : '') ?>" disabled>Verified</button>
                        </td>
                        <td>
                            <button type="button" class="action-btn" style="background:#3498db;margin-right:4px;" 
                                onclick="viewBillDetails('<?= htmlspecialchars($row['category_no']) ?>')">View</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="10" class="text-center text-muted">No records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (!empty($missingBills) || !empty($pendingBills)): ?>
    <div style="max-width:900px;margin:32px auto 0 auto;
        padding:18px 16px;
        background:<?= $isDanger ? '#ffebee' : '#fffbe7' ?>;
        border:1.5px solid <?= $isDanger ? '#e57373' : '#ffe082' ?>;
        border-radius:10px;
        box-shadow:0 2px 8px <?= $isDanger ? '#e5737333' : '#ffe08233' ?>;">
        <div style="font-size:1.15em;font-weight:600;
            color:<?= $isDanger ? '#c62828' : '#b7950b' ?>;margin-bottom:10px;">
            <span style="margin-right:8px;">
                <?= $isDanger ? '&#10071;' : '&#9888;' ?>
            </span>
            <span>
                <?= $isDanger ? 'Urgent: You have missing bills for the following months!' : 'Warning: You have missing or pending bills for the following months!' ?>
            </span>
        </div>
        <?php foreach ($missingBills as $type => $months): ?>
            <div style="margin-bottom:8px;">
                <b><?= htmlspecialchars($type) ?>:</b>
                <span style="color:<?= $isDanger ? '#c62828' : '#c0392b' ?>;">
                    <?= implode(', ', array_map('htmlspecialchars', $months)) ?>
                </span>
            </div>
        <?php endforeach; ?>
        <?php foreach ($pendingBills as $type => $months): ?>
            <div style="margin-bottom:8px;">
                <b><?= htmlspecialchars($type) ?>:</b>
                <span style="color:#b7950b;background:#fffde7;padding:2px 6px;border-radius:4px;">
                    <?= implode(', ', array_map('htmlspecialchars', $months)) ?> (Pending)
                </span>
            </div>
        <?php endforeach; ?>
        <a href="add_bills.php" style="display:inline-block;margin-top:12px;padding:8px 22px;
            background:<?= $isDanger ? '#c62828' : '#1976d2' ?>;
            color:#fff;border-radius:6px;text-decoration:none;font-weight:600;
            box-shadow:0 2px 8px <?= $isDanger ? '#c6282833' : '#1976d233' ?>;">
            Add Bills
        </a>
    </div>
    <?php endif; ?>


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
<script>
function closeBillModal() {
    document.getElementById('billModal').style.display = 'none';
}
function viewBillDetails(category_no) {
    fetch('../admin_user/view_bill_details.php?category_no=' + encodeURIComponent(category_no))
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
function viewMyVerifiedBills() {
    const year = new Date().getFullYear(); // Or let user select year if you want
    fetch('../admin_user/view_canteen_bills.php?cantine_id=<?= $id_cantine ?>&year=' + year)
        .then(res => res.text())
        .then(html => {
            document.getElementById('myBillsModalContent').innerHTML = html;
            document.getElementById('myBillsModal').style.display = 'flex';
        });
}
function closeMyBillsModal() {
    document.getElementById('myBillsModal').style.display = 'none';
}
// Helper functions
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
</script>
</body>
</html>
<?php
// Debug: Uncomment to check the count
// echo "<pre>Total missing: $totalMissing\n"; print_r($missingBills); echo "</pre>";
?>
