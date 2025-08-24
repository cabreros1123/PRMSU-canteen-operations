<?php
require_once "db.php";

// Get all active and not deleted canteens
$cantines = [];
$sql = "SELECT id, name, owner FROM cantines WHERE active = 0 AND del_status = 0 ORDER BY name ASC";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $cantines[] = $row;
}

// Get bills counts per canteen, per type, per month (for current year)
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$bills = [];
$sql = "SELECT cantine_id, bills_type, 
               COUNT(DISTINCT DATE_FORMAT(date, '%Y-%m')) AS months_paid,
               COUNT(CASE WHEN bills_type=4 THEN 1 END) AS others_count
        FROM bills
        WHERE YEAR(real_date) = ? AND ver_status = 2 AND del_status = 0 AND ver_status != 0
        GROUP BY cantine_id, bills_type";
$stmt = $conn->prepare($sql);
// Bind $year as parameter
$stmt->bind_param("i", $year);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $bills[$row['cantine_id']][$row['bills_type']] = [
        'months_paid' => $row['months_paid'],
        'others_count' => $row['others_count']
    ];
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Canteens Payments Monitoring</title>
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
        .status-pill {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.97rem;
            font-weight: 600;
            background: #e0f7e9;
            color: #1b7e3c;
            margin: 0 2px;
        }
        .status-pill.incomplete {
            background: #ffeaea;
            color: #c0392b;
        }
        .status-pill.other {
            background: #eaf1ff;
            color: #2a3b4c;
        }
        .btn-success {
            background: #43a047;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px #43a04722;
            margin-bottom: 12px;
        }
        .btn-success:hover {
            background: #388e3c;
        }
        .btn-primary.btn-sm {
            background: #1976d2;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 0.97rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px #1976d222;
            margin-left: 6px;
        }
        .btn-primary.btn-sm:hover {
            background: #125ea2;
        }

        .action-btn {
            background: #ffb300;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 0.97rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px #ffb30022;
            margin-right: 6px;
        }
        .action-btn:hover {
            background: #ff8f00;
        }

        @media (max-width: 700px) {
            .container { padding: 12px 2px; }
            thead th, tbody td { font-size: 0.98rem; padding: 8px 4px; }
            h2 { font-size: 1.2rem; }
        }

        @media (max-width: 500px) {
            #missingBillsModal > div {
                max-width: 98vw !important;
                padding: 12px 4px 12px 4px !important;
            }
        }

        .material-icons { font-family: Arial, sans-serif; }
    </style>
</head>
<body>
<?php require_once "sidebar.php"; ?>
<?php require_once "header.php"; ?>
<div class="container py-4">
    <h2>Bills Payment Report for <?= htmlspecialchars($year) ?></h2>
    <form method="get" style="margin-bottom:20px;">
        <label for="year">Select Year:</label>
        <select name="year" id="year" onchange="this.form.submit()">
            <?php
            $currentYear = date('Y');
            $startYear = 2020; // or your earliest year
            $selectedYear = isset($_GET['year']) ? intval($_GET['year']) : $currentYear;
            for ($y = $currentYear; $y >= $startYear; $y--) {
                echo '<option value="'.$y.'"'.($selectedYear == $y ? ' selected' : '').'>'.$y.'</option>';
            }
            ?>
        </select>
    </form>
    <form method="get" action="export_all_canteens_excel.php" style="display:inline;">
        <input type="hidden" name="year" value="<?= htmlspecialchars($year) ?>">
        <button type="submit" class="btn btn-success">Download All Canteens Excel</button>
    </form>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Canteen Name</th>
                    <th>Owner</th>
                    <th>Status Completed</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($cantines)): ?>
                <?php foreach ($cantines as $cantine): 
                    $rental = $bills[$cantine['id']][1]['months_paid'] ?? 0;
                    $electric = $bills[$cantine['id']][2]['months_paid'] ?? 0;
                    $water = $bills[$cantine['id']][3]['months_paid'] ?? 0;
                    $others = $bills[$cantine['id']][4]['others_count'] ?? 0;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($cantine['id']) ?></td>
                        <td><?= htmlspecialchars($cantine['name']) ?></td>
                        <td><?= htmlspecialchars($cantine['owner']) ?></td>
                        <td>
                            <span class="status-pill<?= $rental < 12 ? ' incomplete' : '' ?>">Rental: <?= $rental ?>/12</span>
                            <span class="status-pill<?= $electric < 12 ? ' incomplete' : '' ?>">Electric: <?= $electric ?>/12</span>
                            <span class="status-pill<?= $water < 12 ? ' incomplete' : '' ?>">Water: <?= $water ?>/12</span>
                            <span class="status-pill other">Others: <?= $others ?></span>
                        </td>
                        <td>
                            <button type="button" class="action-btn" onclick="viewCanteenBills(<?= $cantine['id'] ?>)">View</button>
                            <form method="get" action="export_canteen_excel.php" style="display:inline;">
                                <input type="hidden" name="cantine_id" value="<?= $cantine['id'] ?>">
                                <input type="hidden" name="year" value="<?= htmlspecialchars($year) ?>">
                                <button type="submit" class="btn btn-primary btn-sm">Download Excel</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted">No active canteens found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="canteenModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;max-width:900px;width:98vw;max-height:95vh;overflow-y:auto;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,0.18);padding:28px 18px 18px 18px;position:relative;">
        <span onclick="closeCanteenModal()" style="position:absolute;top:10px;right:18px;font-size:1.6rem;cursor:pointer;color:#888;">&times;</span>
        <div id="canteenModalContent"></div>
    </div>
</div>

<!-- Warning Notifier Table -->
<?php if (count($warning_rows)): ?>
<div class="container" style="background:#fffbe6;border:2px solid #ffe082;margin-bottom:24px;">
    <h3 style="color:#c0392b;">⚠️ Warning: Unpaid Bills</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Canteen Name</th>
                    <th>Owner</th>
                    <th>Missing Bills</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($warning_rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['canteen']) ?></td>
                    <td><?= htmlspecialchars($row['owner']) ?></td>
                    <td>
                        <button 
                            class="btn btn-primary btn-sm"
                            onclick='showMissingBillsModal(
                                <?= json_encode($row['canteen']) ?>, 
                                <?= json_encode($row['missing']) ?>
                            )'
                            style="position:relative;"
                        >
                            View Missing Bills
                            <?php if ($row['missing_count'] > 0): ?>
                                <span style="
                                    position:absolute;
                                    top:2px; right:2px;
                                    background:#e53935;
                                    color:#fff;
                                    border-radius:50%;
                                    font-size:0.85em;
                                    padding:2px 7px;
                                    font-weight:bold;
                                    box-shadow:0 1px 4px #e5393533;
                                " title="<?= $row['missing_count'] ?> missing bills"><?= $row['missing_count'] ?></span>
                            <?php endif; ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Missing Bills Modal -->
<div id="missingBillsModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:10000;align-items:center;justify-content:center;">
    <div style="
        background:#fff;
        width:95vw;
        max-width:400px;
        max-height:80vh;
        overflow-y:auto;
        border-radius:10px;
        box-shadow:0 8px 32px rgba(0,0,0,0.18);
        padding:24px 12px 18px 12px;
        position:relative;
        margin:0 auto;
        ">
        <span onclick="closeMissingBillsModal()" style="position:absolute;top:10px;right:18px;font-size:1.6rem;cursor:pointer;color:#888;">&times;</span>
        <div id="missingBillsModalContent"></div>
    </div>
</div>

<!-- Notification Mini Box -->
<?php
// Prepare notification data: Only notify if a canteen has 2 or more missing bills in the SAME type
$notified_cantines = [];
foreach ($warning_rows as $row) {
    $notify_types = [];
    foreach (['Rental', 'Electric', 'Water'] as $type) {
        if (isset($row['missing'][$type]) && count($row['missing'][$type]) >= 2) {
            $notify_types[] = [
                'type' => $type,
                'count' => count($row['missing'][$type]),
                'months' => $row['missing'][$type]
            ];
        }
    }
    if (count($notify_types) > 0) {
        $notified_cantines[] = [
            'canteen' => $row['canteen'],
            'owner' => $row['owner'],
            'notify_types' => $notify_types
        ];
    }
}
$notif_count = count($notified_cantines);
?>
<div id="notifBellBox" style="position:fixed;top:32px;right:38px;z-index:11000;">
    <div style="position:relative;">
        <button onclick="toggleNotifDropdown()" style="background:#fff;border:none;box-shadow:0 2px 8px #0001;border-radius:50%;width:48px;height:48px;cursor:pointer;outline:none;position:relative;">
            <span style="font-size:2rem;color:#1976d2;" class="material-icons">&#128276;</span>
            <?php if ($notif_count > 0): ?>
                <span id="notifBadge" style="
                    position:absolute;top:7px;right:7px;
                    background:#e53935;color:#fff;
                    border-radius:50%;font-size:0.95em;
                    padding:2px 7px;font-weight:bold;
                    box-shadow:0 1px 4px #e5393533;
                "><?= $notif_count ?></span>
            <?php endif; ?>
        </button>
        <div id="notifDropdown" style="
            display:none;
            position:absolute;
            top:56px;
            right:0;
            width:320px;
            max-width:95vw;
            max-height:70vh;
            overflow-y:auto;
            background:#fff;
            border-radius:10px;
            box-shadow:0 8px 32px rgba(0,0,0,0.18);
            padding:0 0 8px 0;
            z-index:11001;
        ">
            <div style="padding:14px 18px;border-bottom:1px solid #eee;font-weight:bold;color:#1976d2;">
                Notifications
            </div>
            <?php if ($notif_count == 0): ?>
                <div style="padding:18px;text-align:center;color:#888;">No urgent missing bills.</div>
            <?php else: ?>
                <?php foreach ($notified_cantines as $row): ?>
                    <div style="padding:12px 18px;border-bottom:1px solid #f3f3f3;">
                        <div style="font-weight:600;"><?= htmlspecialchars($row['canteen']) ?></div>
                        <div style="font-size:0.97em;color:#555;">Owner: <?= htmlspecialchars($row['owner']) ?></div>
                        <?php foreach ($row['notify_types'] as $nt): ?>
                            <div style="margin:6px 0 0 0;">
                                <span style="background:#e53935;color:#fff;border-radius:8px;padding:2px 8px;font-size:0.95em;font-weight:600;">
                                    <?= $nt['count'] ?> missing <?= htmlspecialchars($nt['type']) ?> bills
                                </span>
                            </div>
                        <?php endforeach; ?>
                        <?php
                        // Build missing array by type for the modal
                        $missing_for_modal = ['Rental'=>[], 'Electric'=>[], 'Water'=>[]];
                        foreach ($row['notify_types'] as $nt) {
                            $missing_for_modal[$nt['type']] = $nt['months'];
                        }
                        ?>
                        <button 
                            class="btn btn-primary btn-sm"
                            style="margin-top:8px;"
                            onclick='showMissingBillsModal(
                                <?= json_encode($row['canteen']) ?>, 
                                <?= json_encode($missing_for_modal) ?>
                            )'
                        >View Details</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Prepare notification message for JS
$notif_messages = [];
foreach ($notified_cantines as $row) {
    $msg = $row['canteen'] . ': ';
    $types = [];
    foreach ($row['notify_types'] as $nt) {
        $types[] = $nt['count'] . ' ' . $nt['type'];
    }
    $msg .= implode(', ', $types);
    $notif_messages[] = $msg;
}
$notif_message_str = implode("\n", $notif_messages);
?>

<script>
function closeCanteenModal() {
    document.getElementById('canteenModal').style.display = 'none';
}
function viewCanteenBills(cantine_id) {
    const year = document.getElementById('year').value; // get selected year from dropdown
    fetch('view_canteen_bills.php?cantine_id=' + cantine_id + '&year=' + year)
        .then(res => res.text())
        .then(html => {
            document.getElementById('canteenModalContent').innerHTML = html;
            document.getElementById('canteenModal').style.display = 'flex';
        });
}

function showMissingBillsModal(canteen, missing) {
    let html = `<h3 style="margin-top:0;">Missing Bills for <span style="color:#1976d2;">${canteen}</span></h3>`;
    let hasAny = false;
    ['Rental', 'Electric', 'Water'].forEach(type => {
        if (missing[type] && missing[type].length > 0) {
            hasAny = true;
            html += `<div style="margin-top:18px;margin-bottom:6px;font-weight:bold;color:#1976d2;">For ${type}:</div>`;
            html += `<ul style="margin:0 0 0 18px;padding:0;color:#c0392b;">`;
            missing[type].forEach(month => {
                html += `<li>${month}</li>`;
            });
            html += `</ul>`;
        }
    });
    if (!hasAny) {
        html += `<div style="margin-top:12px;font-size:1.1em;color:#43a047;">No missing bills!</div>`;
    }
    document.getElementById('missingBillsModalContent').innerHTML = html;
    document.getElementById('missingBillsModal').style.display = 'flex';
}
function closeMissingBillsModal() {
    document.getElementById('missingBillsModal').style.display = 'none';
}

function toggleNotifDropdown() {
    var dd = document.getElementById('notifDropdown');
    dd.style.display = (dd.style.display === 'block') ? 'none' : 'block';
    // Optional: close when clicking outside
    if (dd.style.display === 'block') {
        setTimeout(() => {
            document.addEventListener('click', notifOutsideClick);
        }, 10);
    }
}
function notifOutsideClick(e) {
    if (!document.getElementById('notifBellBox').contains(e.target)) {
        document.getElementById('notifDropdown').style.display = 'none';
        document.removeEventListener('click', notifOutsideClick);
    }
}

// Close notification dropdown if clicked outside
window.onclick = function(event) {
    const dropdown = document.getElementById('notifDropdown');
    if (!event.target.matches('#notifBellBox *')) {
        dropdown.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Only notify if there are missing bills
    var notifCount = <?= json_encode($notif_count) ?>;
    if (notifCount > 0 && "Notification" in window) {
        if (Notification.permission === "granted") {
            sendBillsNotification();
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(function(permission) {
                if (permission === "granted") {
                    sendBillsNotification();
                }
            });
        }
    }

});
</script>
</body>
</html>