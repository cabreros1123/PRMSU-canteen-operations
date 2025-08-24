<?php
require_once "db.php";
require_once "sidebar.php";
require_once "header.php";
// Fetch all canteens
$cantines = $conn->query("SELECT id, name, owner FROM cantines WHERE active=0 AND del_status=0 ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Overall Reports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f6f8fa;
            margin: 0;
            padding: 0;
        }
        h2, h3 {
            color: #1976d2;
            margin-top: 24px;
        }
        form {
            max-width: 1100px;
            margin: 24px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.07);
            padding: 24px 12px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
            background: #fff;
            font-size: 1rem;
        }
        .report-table th, .report-table td {
            border: 1px solid #e3e3e3;
            padding: 10px 8px;
            text-align: left;
        }
        .report-table th {
            background: #e3f2fd;
            color: #1976d2;
            font-weight: 600;
        }
        .report-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .select-all {
            margin-right: 8px;
        }
        .download-btn {
            background: #1976d2;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 12px 28px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1.1rem;
            margin-top: 12px;
            transition: background 0.2s;
        }
        .download-btn:hover {
            background: #125ea2;
        }
        button[type="button"] {
            background: #fff;
            color: #1976d2;
            border: 1px solid #1976d2;
            border-radius: 5px;
            padding: 6px 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        button[type="button"]:hover {
            background: #1976d2;
            color: #fff;
        }
        @media (max-width: 900px) {
            form {
                padding: 10px 2vw;
            }
            .report-table th, .report-table td {
                padding: 8px 4px;
                font-size: 0.98rem;
            }
        }
        @media (max-width: 600px) {
            form {
                padding: 4px 0;
            }
            .report-table, .report-table thead, .report-table tbody, .report-table th, .report-table td, .report-table tr {
                display: block;
                width: 100%;
            }
            .report-table thead {
                display: none;
            }
            .report-table tr {
                margin-bottom: 18px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 1px 4px rgba(0,0,0,0.04);
                border: 1px solid #e3e3e3;
                padding: 8px 0;
            }
            .report-table td {
                border: none;
                position: relative;
                padding-left: 48%;
                min-height: 32px;
                font-size: 1rem;
            }
            .report-table td:before {
                position: absolute;
                left: 12px;
                top: 8px;
                width: 45%;
                white-space: nowrap;
                font-weight: 600;
                color: #1976d2;
                content: attr(data-label);
            }
        }
        #reportModal .modal-content {
            background: #fff;
            max-width: 700px;
            width: 98vw;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 10px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
            padding: 24px 8px 16px 8px;
            position: relative;
        }
        #reportModal .close-modal {
            position: absolute;
            top: 10px;
            right: 18px;
            font-size: 1.6rem;
            cursor: pointer;
            color: #888;
        }
        .hide-exit-btn .save-btn[style*="background:#888"] {
            display: none !important;
        }
    </style>
</head>
<body>
<h2>Overall Reports</h2>
<form id="overallReportForm" method="post" action="download_selected_reports.php">
    <!-- Table 1: Food Safety Ratings -->
    <h3>Food Safety Ratings</h3>
    <table class="report-table">
        <thead>
            <tr>
                <th><input type="checkbox" class="select-all" onclick="toggleAll(this, 'gmp[]')"></th>
                <th>Canteen</th>
                <th>Date</th>
                <th>Note</th>
                <th>GMP Grade</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cantines as $canteen): ?>
            <?php
            // Fetch latest GMP group for this canteen
            $gmp = $conn->query("SELECT group_code, note, date FROM food_safety_category_code WHERE group_code IN (SELECT DISTINCT group_code FROM food_safety_ratings WHERE cantine_id={$canteen['id']}) ORDER BY date DESC LIMIT 1")->fetch_assoc();
            if ($gmp):
                // Calculate grade (reuse your logic from fetch_gmp_ratings.php)
                $ratings = $conn->query("SELECT * FROM food_safety_ratings WHERE cantine_id={$canteen['id']} AND group_code='{$gmp['group_code']}'");
                $section_count = $ratings->num_rows;
                $total_grade = 0;
                while ($s = $ratings->fetch_assoc()) {
                    $section_grade = ($s['rating'] >= 1 && $s['rating'] <= 5) ? ($s['rating'] * 20) : 0;
                    $total_grade += $section_grade * 0.1;
                }
                $max_grade = $section_count * 10;
                $final_grade = $section_count > 0 ? round(($total_grade / $max_grade) * 100, 1) : 0;
            ?>
            <tr>
                <td data-label="Select"><input type="checkbox" name="gmp[]" value="<?= $canteen['id'] ?>|<?= $gmp['group_code'] ?>"></td>
                <td data-label="Canteen"><?= htmlspecialchars($canteen['name']) ?></td>
                <td data-label="Date"><?= htmlspecialchars($gmp['date']) ?></td>
                <td data-label="Note"><?= htmlspecialchars($gmp['note']) ?></td>
                <td data-label="GMP Grade"><?= $final_grade ?>%</td>
                <td data-label="Action">
                    <button type="button" onclick="openReportModal('fetch_gmp_ratings.php?cantine_id=<?= $canteen['id'] ?>')">View</button>
                </td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Table 2: Obligation Reports -->
    <h3>Obligation Reports</h3>
    <table class="report-table">
        <thead>
            <tr>
                <th><input type="checkbox" class="select-all" onclick="toggleAll(this, 'obligation[]')"></th>
                <th>Canteen</th>
                <th>Date</th>
                <th>Complied</th>
                <!-- Removed Action column -->
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cantines as $canteen): ?>
            <?php
            // Fetch latest obligation for this canteen
            $ob = $conn->query("SELECT id, date_added, obligation_and_status FROM obligations WHERE cantine_id={$canteen['id']} AND del_status=0 ORDER BY date_added DESC LIMIT 1")->fetch_assoc();
            if ($ob):
                $ob_data = json_decode($ob['obligation_and_status'], true);
                $complied = 0;
                foreach ($ob_data as $item) if ($item['status'] === 'Complied') $complied++;
            ?>
            <tr>
                <td><input type="checkbox" name="obligation[]" value="<?= $ob['id'] ?>"></td>
                <td><?= htmlspecialchars($canteen['name']) ?></td>
                <td><?= htmlspecialchars($ob['date_added']) ?></td>
                <td><?= $complied ?>/<?= count($ob_data) ?></td>
                <!-- Removed Action column -->
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Table 3: Verified Bills Payments -->
    <h3>Verified Bills Payments</h3>
    <table class="report-table">
        <thead>
            <tr>
                <th><input type="checkbox" class="select-all" onclick="toggleAll(this, 'bills[]')"></th>
                <th>Canteen</th>
                <th>Status Completed</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cantines as $canteen): ?>
            <?php
            // Fetch bills summary (reuse your logic from bills_payments.php)
            $year = date('Y');
            $bills = $conn->query("SELECT bills_type, COUNT(DISTINCT DATE_FORMAT(date, '%Y-%m')) AS months_paid FROM bills WHERE cantine_id={$canteen['id']} AND YEAR(real_date) = $year AND ver_status = 2 AND del_status = 0 GROUP BY bills_type");
            $status = [];
            while ($b = $bills->fetch_assoc()) {
                $status[$b['bills_type']] = $b['months_paid'];
            }
            ?>
            <tr>
                <td><input type="checkbox" name="bills[]" value="<?= $canteen['id'] ?>"></td>
                <td><?= htmlspecialchars($canteen['name']) ?></td>
                <td>
                    Rental: <?= $status[1] ?? 0 ?>/12,
                    Electric: <?= $status[2] ?? 0 ?>/12,
                    Water: <?= $status[3] ?? 0 ?>/12
                </td>
                <td><button type="button" onclick="openReportModal('view_canteen_bills.php?cantine_id=<?= $canteen['id'] ?>')">View</button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <button type="submit" class="download-btn">Download Selected Reports</button>
</form>

<!-- Modal for viewing reports -->
<div id="reportModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:10000;align-items:center;justify-content:center;">
    <div style="background:#fff;max-width:700px;width:95vw;max-height:90vh;overflow-y:auto;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,0.18);padding:24px 16px 16px 16px;position:relative;">
        <span onclick="closeReportModal()" style="position:absolute;top:10px;right:18px;font-size:1.6rem;cursor:pointer;color:#888;">&times;</span>
        <div id="reportModalContent"></div>
    </div>
</div>

<script>
function toggleAll(source, name) {
    document.querySelectorAll('input[name="'+name+'"]').forEach(cb => cb.checked = source.checked);
}

function openReportModal(url) {
    var modal = document.getElementById('reportModal');
    var content = document.getElementById('reportModalContent');
    content.innerHTML = '<div style="text-align:center;padding:40px 0;">Loading...</div>';
    modal.style.display = 'flex';

    // Remove the class first
    modal.classList.remove('hide-exit-btn');

    fetch(url)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
            // If it's the food safety modal, hide the exit button
            if (url.startsWith('fetch_gmp_ratings.php')) {
                modal.classList.add('hide-exit-btn');
            }
        })
        .catch(() => {
            content.innerHTML = '<div style="color:red;">Failed to load report.</div>';
        });
}

function closeReportModal() {
    document.getElementById('reportModal').style.display = 'none';
}

function toggleGmpGroup(groupId) {
    var content = document.getElementById(groupId);
    var arrow = document.getElementById('arrow-' + groupId);
    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        if (arrow) arrow.innerHTML = '&#9650;'; // up arrow
    } else {
        content.style.display = 'none';
        if (arrow) arrow.innerHTML = '&#9660;'; // down arrow
    }
}

function printGmpReport(groupId, groupCode, date) {
    // Clone the group content so we can modify it
    var groupContentElem = document.getElementById(groupId).cloneNode(true);
    // Remove all elements with class 'no-print'
    groupContentElem.querySelectorAll('.no-print').forEach(function(el) {
        el.parentNode.removeChild(el);
    });
    var groupContent = groupContentElem.innerHTML;

    var contractImg = ''; // You can set your contract image path here if needed
    var contractText = `<div style="margin-bottom:18px;">
        <b>1. TERM OF THE CONTRACT</b><br>
        This contract shall be for a term of one (1) year commencing on January, 2025 subject to an Annual Performance Appraisal to be conducted by the OWNER up to December, 2025.<br>
        Sixty (60) days before the expiration of the (1) year period of this contract, the CANTEEN OPERATOR shall notify the OWNER of his/her intention to continue or not the operation of the canteen.
    </div>`;    
    var summary = `<div style="margin-bottom:18px;">
        <b>GMP Rating for Food Safety</b><br>
        Group Code: ${groupCode}<br>
        Date: ${date}
    </div>`;
    var printWindow = window.open('', '', 'width=900,height=700');
    printWindow.document.write('<html><head><title>GMP Report</title>');
    printWindow.document.write('<style>body{font-family:sans-serif;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:8px;} th{background:#1976d2;color:#fff;} img.view-img{width:2cm;height:2cm;object-fit:cover;border:1px solid #888;} @media print {.no-print{display:none!important;}}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(contractImg);
    printWindow.document.write(contractText);
    printWindow.document.write(summary);
    printWindow.document.write(groupContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}
</script>
</body>
</html>