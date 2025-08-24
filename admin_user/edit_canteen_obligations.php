<?php
session_start();
require_once 'db.php';

// Fetch canteens
$canteens = $conn->query("SELECT id, name FROM cantines WHERE active=0 AND del_status=0");

// Fetch obligations template
$obligation_templates = [];
$res = $conn->query("SELECT id, obligation FROM obligation_category WHERE del_status=0 ORDER BY date_added ASC");
while ($row = $res->fetch_assoc()) {
    $obligation_templates[] = $row;
}

// Handle form submission (always INSERT, never UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cantine_id'])) {
    $cantine_id = intval($_POST['cantine_id']);
    $obligation_and_status = [];
    foreach ($obligation_templates as $ob) {
        $status = isset($_POST['status'][$ob['id']]) ? $_POST['status'][$ob['id']] : 'Pending';
        $obligation_and_status[] = [
            'id' => $ob['id'],
            'obligation' => $ob['obligation'],
            'status' => $status
        ];
    }
    $json = $conn->real_escape_string(json_encode($obligation_and_status));
    $date_added = date('Y-m-d H:i:s');
    $conn->query("INSERT INTO obligations (cantine_id, obligation_and_status, date_added, del_status) VALUES ($cantine_id, '$json', '$date_added', 0)");
    echo "<script>alert('Inspection saved!');window.location='edit_canteen_obligations.php?cantine_id=$cantine_id';</script>";
    exit;
}

// Get selected canteen
$cantine_id = isset($_GET['cantine_id']) ? intval($_GET['cantine_id']) : 0;

// Add this block BEFORE using $selected_limit
$limits = [3, 5, 10, 20, 50, 100];
$selected_limit = isset($_GET['limit']) && in_array(intval($_GET['limit']), $limits) ? intval($_GET['limit']) : 5;

// Fetch all inspections for this canteen
$inspections = [];
if ($cantine_id) {
    $res = $conn->query("SELECT * FROM obligations WHERE cantine_id=$cantine_id AND del_status=0 ORDER BY date_added DESC LIMIT $selected_limit");
    while ($row = $res->fetch_assoc()) {
        $row['obligation_and_status'] = json_decode($row['obligation_and_status'], true);
        $inspections[] = $row;
    }
}

// Get active inspection for this canteen
$active_inspection = null;
if ($cantine_id) {
    $res = $conn->query("SELECT * FROM obligations WHERE cantine_id=$cantine_id AND status=1 AND del_status=0 LIMIT 1");
    if ($row = $res->fetch_assoc()) {
        $row['obligation_and_status'] = json_decode($row['obligation_and_status'], true);
        $active_inspection = $row;
    } else {
        // No active inspection, create one
        $obligation_and_status = [];
        foreach ($obligation_templates as $ob) {
            $obligation_and_status[] = [
                'id' => $ob['id'],
                'obligation' => $ob['obligation'],
                'status' => 'Pending'
            ];
        }
        $json = $conn->real_escape_string(json_encode($obligation_and_status));
        $date_added = date('Y-m-d H:i:s');
        $conn->query("INSERT INTO obligations (cantine_id, obligation_and_status, date_added, status, del_status) VALUES ($cantine_id, '$json', '$date_added', 1, 0)");
        // Fetch the newly created inspection
        $res = $conn->query("SELECT * FROM obligations WHERE cantine_id=$cantine_id AND status=1 AND del_status=0 LIMIT 1");
        if ($row = $res->fetch_assoc()) {
            $row['obligation_and_status'] = json_decode($row['obligation_and_status'], true);
            $active_inspection = $row;
        }
    }
}

// --- Messenger logic ---
// Save uploads to the main project folder 'uploads'
$selected_canteen = $cantine_id;
if (isset($_POST['send_message'])) {
    $canteen_id = intval($_POST['msg_canteen_id']);
    $message = $conn->real_escape_string($_POST['message']);
    $file_paths = [];
    if (!empty($_FILES['files']['name'][0])) {
        $target_dir = __DIR__ . '/../uploads/';
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        foreach ($_FILES['files']['name'] as $i => $file_name) {
            if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                $file_path = time() . '_' . basename($file_name);
                move_uploaded_file($_FILES['files']['tmp_name'][$i], $target_dir . $file_path);
                $file_paths[] = 'uploads/' . $file_path; // Save as 'uploads/filename.jpg'
            }
        }
    }
    $files_json = $conn->real_escape_string(json_encode($file_paths));
    $conn->query("INSERT INTO canteen_messages (canteen_id, sender, message, image) VALUES ($canteen_id, 'admin', '$message', '$files_json')");
    echo "<script>location.href=location.href;</script>";
    exit;
}

// --- Announcement logic ---
if (isset($_POST['send_announcement'])) {
    $announcement = $conn->real_escape_string($_POST['announcement_message']);
    $file_paths = [];
    if (!empty($_FILES['announcement_files']['name'][0])) {
        $target_dir = __DIR__ . '/../uploads/';
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        foreach ($_FILES['announcement_files']['name'] as $i => $file_name) {
            if ($_FILES['announcement_files']['error'][$i] === UPLOAD_ERR_OK) {
                $file_path = time() . '_' . basename($file_name);
                move_uploaded_file($_FILES['announcement_files']['tmp_name'][$i], $target_dir . $file_path);
                $file_paths[] = 'uploads/' . $file_path;
            }
        }
    }
    $files_json = $conn->real_escape_string(json_encode($file_paths));
    // Send to all active, not deleted canteens
    $canteen_query = $conn->query("SELECT id FROM cantines WHERE active=0 AND del_status=0");
    while ($row = $canteen_query->fetch_assoc()) {
        $cid = intval($row['id']);
        $conn->query("INSERT INTO canteen_messages (canteen_id, sender, message, image, is_announcement) VALUES ($cid, 'admin', '$announcement', '$files_json', 1)");
    }
    echo "<script>alert('Announcement sent to all canteens!');location.href=location.href;</script>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add/View Canteen Obligations</title>
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <style>
        body {
    background: #f5f7fa;
    font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
}
h2, h3 {
    color: #1976d2;
    font-weight: 700;
}
.btn, button, select {
    border-radius: 12px !important;
    font-size: 1.05em;
    transition: background 0.2s, box-shadow 0.2s;
}
.btn-primary, .btn-primary:active {
    background: linear-gradient(90deg, #1976d2 60%, #42a5f5 100%);
    color: #fff;
    border: none;
    box-shadow: 0 2px 8px #1976d220;
}
.btn-secondary {
    background: #ececec;
    color: #1976d2;
    border: 1px solid #1976d2;
}
.btn-warning {
    background: #ffb300;
    color: #fff;
    border: none;
}
.btn:active, .btn-primary:active, .btn-secondary:active, .btn-warning:active {
    filter: brightness(0.95);
}
table {
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 2px 12px #1976d210;
}
th, td {
    border: none;
    padding: 12px 14px;
    font-size: 1.05em;
}
th {
    background: #e3f2fd;
    color: #1976d2;
    font-weight: 600;
}
.status-group {
    display: flex;
    gap: 8px;
    justify-content: center;
}
.status-option {
    background: #f1f8e9;
    color: #388e3c;
    padding: 6px 16px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 500;
    border: 2px solid transparent;
    transition: background 0.2s, color 0.2s, border 0.2s, box-shadow 0.2s;
    box-shadow: 0 1px 4px #0001;
    position: relative;
    min-width: 90px;
    text-align: center;
}
.status-option.selected {
    border: 2.5px solid #1976d2;
    box-shadow: 0 2px 8px #1976d230;
    font-weight: 700;
    z-index: 1;
}
.status-option.selected[data-status="Complied"] {
    background: #43a047;
    color: #555f68ff;
    border-color: #43a047;
}
.status-option.selected[data-status="Pending"] {
    background: #fbc02d;
    color: #555f68ff;
    border-color: #fbc02d;
}
.status-option.selected[data-status="Not Complied"] {
    background: #d32f2f;
    color: #555f68ff;
    border-color: #d32f2f;
}
.status-option[data-status="Pending"] {
    background: #fffde7;
    color: #fbc02d;
    border: 2px solid #ffe082;
}
.status-option[data-status="Not Complied"] {
    background: #ffebee;
    color: #d32f2f;
    border: 2px solid #ffcdd2;
}
.status-option[data-status="Complied"] {
    background: #e8f5e9;
    color: #43a047;
    border: 2px solid #c8e6c9;
}
.status-option.selected::after {
    content: "✓";
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.1em;
    font-weight: bold;
    color: green;
    opacity: 0.8;
    pointer-events: none;
}
.canteen-box, .messenger-box {
    background: linear-gradient(135deg, #f7fafc 60%, #e3f2fd 100%);
    border-radius: 18px;
    box-shadow: 0 4px 24px #1976d220, 0 1.5px 4px #1976d210;
    padding: 22px 18px 18px 18px;
    margin-bottom: 18px;
    min-height: 180px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: box-shadow 0.2s, transform 0.2s;
    cursor: pointer;
    position: relative;
}
.canteen-box:hover {
    box-shadow: 0 8px 32px #1976d240, 0 2px 8px #1976d220;
    transform: translateY(-2px) scale(1.02);
}
button, .btn {
    margin: 0 4px;
}
.past-inspection-btns {
    display: flex;
    gap: 8px;
    margin-top: 8px;
    justify-content: flex-end;
}
.btn-back {
    background: linear-gradient(90deg, #1976d2 60%, #42a5f5 100%);
    color: #fff !important;
    border: none;
    border-radius: 12px;
    padding: 8px 22px 8px 16px;
    font-weight: 600;
    font-size: 1.08em;
    box-shadow: 0 2px 8px #1976d220;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: background 0.2s, box-shadow 0.2s;
    text-decoration: none;
}
.btn-back:hover {
    background: linear-gradient(90deg, #1565c0 60%, #1976d2 100%);
    box-shadow: 0 4px 16px #1976d240;
}

.announcement-form .messenger-row {
    display: flex;
    align-items: center;
    background: #f7fafc;
    border-radius: 16px;
    box-shadow: 0 2px 8px #1976d210;
    padding: 6px 10px;
    gap: 8px;
}
.announcement-form .messenger-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 1.08em;
    padding: 8px 10px;
    outline: none;
}
.announcement-form .messenger-file-label {
    cursor: pointer;
    color: #1976d2;
    font-size: 1.5em;
    margin-right: 4px;
    display: flex;
    align-items: center;
}
.btn-announcement {
    background: linear-gradient(90deg, #ffb300 60%, #ffd54f 100%);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5em;
    box-shadow: 0 2px 8px #ffb30030;
    margin-left: 4px;
    transition: background 0.2s, box-shadow 0.2s;
}
.btn-announcement:hover {
    background: linear-gradient(90deg, #ffa000 60%, #ffb300 100%);
    box-shadow: 0 4px 16px #ffb30050;
}
@media (max-width: 600px) {
    .canteen-box, .messenger-box {
        padding: 14px 8px;
        min-height: 120px;
    }
    th, td {
        padding: 8px 6px;
    }
}

/* Only style emoji rating inside the rating modal */
#ratingDashboardModal .emoji-rating-grid {
    display: grid;
    grid-template-columns: 140px repeat(5, 1fr);
    gap: 8px 6px;
    align-items: center;
    margin-bottom: 12px;
}
#ratingDashboardModal .emoji-label {
    text-align: center;
    font-size: 1em;
    color: #888;
    font-weight: 500;
    padding-bottom: 2px;
}
#ratingDashboardModal .emoji-row-label {
    font-weight: 600;
    color: #1976d2;
    text-align: right;
    padding-right: 8px;
    font-size: 1.05em;
}
#ratingDashboardModal .emoji-rating {
    display: flex;
    justify-content: center;
    gap: 40px;
    font-size: 2em;
    cursor: pointer;
    grid-column: span 5;
}
#ratingDashboardModal .emoji-rating span {
    transition: transform 0.1s, filter 0.1s;
    filter: grayscale(0.5) brightness(0.85);
    opacity: 0.7;
    cursor: pointer;
    user-select: none;
}
#ratingDashboardModal .emoji-rating span.selected {
    transform: scale(1.2);
    filter: none;
    opacity: 1;
    text-shadow: 0 2px 8px #1976d250;
}
#ratingDashboardModal .emoji-rating span:hover,
#ratingDashboardModal .emoji-rating span:hover ~ span {
    filter: grayscale(0.2) brightness(1.1);
    opacity: 1;
}

#fixedMessengerBox {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    width: 370px;
    max-width: 95vw;
    box-shadow: 0 4px 24px #1976d220, 0 1.5px 4px #1976d210;
    background: linear-gradient(135deg, #f7fafc 60%, #e3f2fd 100%);
    border-radius: 18px;
    padding: 22px 18px 18px 18px;
    min-height: 180px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
#fixedMessengerBox.collapsed {
    height: 48px !important;
    min-height: 0 !important;
    width: 320px;
    max-width: 95vw;
    padding: 8px 18px !important;
    overflow: hidden;
    transition: height 0.2s, min-height 0.2s, padding 0.2s;
}
#fixedMessengerBox.collapsed #messengerContent {
    display: none;
}
#fixedMessengerBox.collapsed #messengerToggleBtn {
    top: 8px;
    right: 8px;
}
@media (max-width: 600px) {
    #fixedMessengerBox {
        right: 4px;
        left: 4px;
        width: auto;
        bottom: 4px;
        padding: 10px 6px;
        min-height: 120px;
    }
}
    </style>
    <link rel="stylesheet" href="css/edit_canteen_obligations.css" />
</head>
<body style="padding: 24px;">
<?php require_once "sidebar.php"; ?>
<?php require_once "header.php"; ?>
<div>
    <h2>Add/View Canteen Obligations</h2>
    <br>
    <a href="food_safety.php" class="btn btn-back" style="margin-bottom:16px;">
    <span class="material-symbols-rounded" style="vertical-align:middle;font-size:1.2em;margin-right:4px;">arrow_back</span>
    Back to Food Safety
</a>
    <br><br>
    <?php
$current_year = date('Y');
$years = [];
for ($i = 0; $i < 10; $i++) {
    $years[] = $current_year - $i;
}
sort($years); // Optional: sort ascending, remove if you want descending
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : $current_year;
?>

    <?php if (!$active_inspection): ?>
        <!-- Show form to create new inspection if no active inspection -->
        <form method="post">
            <input type="hidden" name="cantine_id" value="<?= $cantine_id ?>">
            <button type="button" id="complyAllBtn">Comply All</button>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Obligation</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($obligation_templates as $ob): ?>
                    <tr>
                        <td><?= $ob['id'] ?></td>
                        <td><?= htmlspecialchars($ob['obligation']) ?></td>
                        <td>
                            <div class="status-group">
                                <div class="status-option" data-status="Complied">
                                    <span class="status-check" data-status="Complied">&#10003;</span>
                                    Complied
                                </div>
                                <div class="status-option" data-status="Pending">
                                    <span class="status-check" data-status="Pending">&#10003;</span>
                                    Pending
                                </div>
                                <div class="status-option" data-status="Not Complied">
                                    <span class="status-check" data-status="Not Complied">&#10003;</span>
                                    Not Complied
                                </div>
                                <input type="hidden" name="status[<?= $ob['id'] ?>]" value="Pending">
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 12px;">
    <button type="submit" class="btn btn-primary">Save Inspection</button>
</div>
        </form>
    <?php else: ?>
        <!-- Show active inspection table if exists -->
        <h3>Active Inspection (Edit in real time)</h3>
        <button type="button" id="complyAllBtn">Comply All</button>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Obligation</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($active_inspection['obligation_and_status'] as $item): ?>
                <tr class="obligation-row" data-obligation-id="<?= $item['id'] ?>">
                    <td><?= $item['id'] ?></td>
                    <td><?= htmlspecialchars($item['obligation']) ?></td>
                    <td>
                        <div class="status-group">
                            <div class="status-option<?= $item['status']=='Complied'?' selected':'' ?>" data-status="Complied">Complied</div>
                            <div class="status-option<?= $item['status']=='Pending'?' selected':'' ?>" data-status="Pending">Pending</div>
                            <div class="status-option<?= $item['status']=='Not Complied'?' selected':'' ?>" data-status="Not Complied">Not Complied</div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="past-inspection-btns">
    <button type="button" class="btn btn-primary" onclick="finishInspection(<?= $active_inspection['id'] ?>)">Finish Inspection</button>
</div>
    <?php endif; ?>
    <div style="display: flex; align-items: center; gap: 18px; margin-top:32px; margin-bottom: 18px;">
    <h3 style="margin:0;">Past Inspections</h3>
    <form method="get" style="margin:0; display: flex; align-items: center; gap: 8px;">
        <input type="hidden" name="cantine_id" value="<?= $cantine_id ?>">
        <label for="limitSelect" style="font-weight:normal;">Show:</label>
        <select name="limit" id="limitSelect" onchange="this.form.submit()" style="padding: 6px 10px; border-radius: 8px; border: 1px solid #bbb;">
            <?php
            $limits = [3, 5, 10, 20, 50, 100];
            $selected_limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
            foreach ($limits as $l) {
                echo "<option value=\"$l\" ".($selected_limit==$l?'selected':'').">$l</option>";
            }
            ?>
        </select>
    </form>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
    <form method="get" style="margin:0;">
    <input type="hidden" name="cantine_id" value="<?= $cantine_id ?>">
    <label for="yearSelect"><strong>Year:</strong></label>
    <select name="year" id="yearSelect" onchange="this.form.submit()">
        <?php foreach ($years as $y): ?>
            <option value="<?= $y ?>" <?= $selected_year == $y ? 'selected' : '' ?>><?= $y ?></option>
        <?php endforeach; ?>
    </select>
</form>
</div>
</div>
    <?php
    $total_obligations = count($obligation_templates);
    if ($inspections): ?>
    <div style="display: flex; gap: 32px; align-items: flex-start;">
        <div style="flex: 1;">
            <div style="max-width:700px;">
            <?php foreach ($inspections as $idx => $insp): ?>
                <?php
                    // Skip active inspection (status == 1)
                    if (isset($insp['status']) && $insp['status'] == 1) continue;
                    if (date('Y', strtotime($insp['date_added'])) != $selected_year) continue;
                    $complied = 0;
                    $pending = 0;
                    $not_complied = 0;
                    $complied_tooltip = "";
                    $pending_tooltip = "";
                    $not_complied_tooltip = "";
                    foreach ($insp['obligation_and_status'] as $item) {
                        if ($item['status'] === 'Complied') {
                            $complied++;
                            $complied_tooltip .= "• {$item['obligation']}\n";
                        }
                        if ($item['status'] === 'Pending') {
                            $pending++;
                            $pending_tooltip .= "• {$item['obligation']}\n";
                        }
                        if ($item['status'] === 'Not Complied') {
                            $not_complied++;
                            $not_complied_tooltip .= "• {$item['obligation']}\n";
                        }
                    }
                    $color = '#d32f2f'; // red
                    if ($complied == $total_obligations) {
                        $color = '#388e3c'; // green
                    } elseif ($complied > 2) {
                        $color = '#fbc02d'; // yellow
                    }
                ?>
                <div style="margin-bottom:8px;">
                    <button type="button"
                        onclick="toggleInspection('insp<?= $idx ?>')"
                        style="width:100%;text-align:left;display:flex;justify-content:space-between;align-items:center;padding:10px 14px;font-size:1em;border:1px solid #aaa;background:#f8f8f8;cursor:pointer;border-radius:4px;position:relative;">
                        <span>
    <?= date('F j, Y - g:ia', strtotime($insp['date_added'])) ?>
</span>
                        <span class="tooltip" style="margin-left:10px; color:<?= $color ?>; font-weight:bold; font-size:1.1em;">
                            Complied <?= $complied ?>/<?= $total_obligations ?>
                            <span class="tooltiptext"><?= $complied_tooltip ? "Complied:\n" . trim($complied_tooltip) : "No complied obligations" ?></span>
                        </span>
                        <?php if ($pending > 0): ?>
                            <span class="tooltip" style="margin-left:10px; color:#fbc02d; font-weight:bold;">
                                Pending: <?= $pending ?>
                                <span class="tooltiptext"><?= $pending_tooltip ? "Pending:\n" . trim($pending_tooltip) : "No pending obligations" ?></span>
                            </span>
                        <?php endif; ?>
                        <?php if ($not_complied > 0): ?>
                            <span class="tooltip" style="margin-left:10px; color:#d32f2f; font-weight:bold;">
                                Not Complied: <?= $not_complied ?>
                                <span class="tooltiptext"><?= $not_complied_tooltip ? "Not Complied:\n" . trim($not_complied_tooltip) : "No not complied obligations" ?></span>
                            </span>
                        <?php endif; ?>
                        <span id="icon-insp<?= $idx ?>" style="margin-left:10px;">&#9660;</span>
                        <span 
                            title="Print Report"
                            style="margin-left:auto; margin-right:0; cursor:pointer; font-size:1.3em; color:#1976d2;"
                            onclick="event.stopPropagation(); window.open('print_obligation_report.php?inspection_id=<?= $insp['id'] ?>', '_blank')">
                            🖨️
                        </span>
                        <span 
    title="Edit Rating"
    style="margin-left:8px; cursor:pointer; font-size:1.3em; color:#ff9800;"
    onclick="event.stopPropagation(); editRating(<?= $insp['id'] ?>)">
    <span class="material-symbols-rounded" style="vertical-align:middle;">star_rate</span>
</span>
                    </button>
                    <div id="insp<?= $idx ?>" style="display:none;padding:0 10px 10px 10px;">
                        <table style="margin-top:10px;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Obligation</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($insp['obligation_and_status'] as $item): ?>
                                <tr>
                                    <td><?= $item['id'] ?></td>
                                    <td><?= htmlspecialchars($item['obligation']) ?></td>
                                    <td><?= htmlspecialchars($item['status']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
        <div style="flex: 0 0 420px;">
            <h3>Messenger to Canteens</h3>
            <form method="post" enctype="multipart/form-data" class="announcement-form" style="margin-bottom:18px;">
    <div class="messenger-row">
        <label class="messenger-file-label" title="Attach files">
            <span class="material-symbols-rounded">attach_file</span>
            <input type="file" name="announcement_files[]" class="messenger-file-input" multiple style="display:none;">
        </label>
        <input type="text" name="announcement_message" class="messenger-input" placeholder="Type your announcement..." required>
        <button type="submit" name="send_announcement" class="btn btn-announcement" title="Send Announcement">
            <span class="material-symbols-rounded">campaign</span>
        </button>
    </div>
</form>
            <div class="messenger-box" id="fixedMessengerBox">
                <h3 style="margin-bottom:12px;">Messenger to Canteen</h3>
    <button id="messengerToggleBtn" style="position:absolute;top:8px;right:8px;background:none;border:none;font-size:1.5em;cursor:pointer;z-index:10000;" title="Collapse/Expand Messenger">
        <span id="messengerToggleIcon" class="material-symbols-rounded">expand_more</span>
    </button>
    <div id="messengerContent"> 
        <div class="messenger-messages" id="messengerMessages"></div>
        <form id="messengerForm" enctype="multipart/form-data" class="messenger-form-row">
            <input type="hidden" name="msg_canteen_id" value="<?= $selected_canteen ?>">
            <input type="hidden" name="send_message" value="1">
            <input type="text" name="message" class="messenger-input" placeholder="Type your message..." required>
            <label class="messenger-file-label" title="Attach files">
                <span class="material-symbols-rounded">attach_file</span>
                <input type="file" name="files[]" class="messenger-file-input" multiple>
            </label>
            <button type="submit" class="messenger-send-btn" title="Send">
                <span class="material-symbols-rounded">send</span>
            </button>
        </form>
    </div>
</div>
<?php else: ?>
    <p>No inspections yet for this canteen.</p>
<?php endif; ?>
</div>
<!-- Add this modal HTML just before </body> -->
<div id="messenger-img-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.7);z-index:9999;align-items:center;justify-content:center;">
    <span onclick="closeMessengerImgModal()" style="position:absolute;top:24px;right:36px;font-size:2em;color:#fff;cursor:pointer;">&times;</span>
    <img id="messenger-img-modal-img" src="" style="max-width:90vw;max-height:90vh;border-radius:12px;box-shadow:0 2px 16px #0008;">
</div>

<!-- Rating Dashboard Modal -->
<div id="ratingDashboardModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;padding:32px 24px;border-radius:16px;max-width:600px;width:90vw;box-shadow:0 2px 16px #0008;position:relative;">
    <span onclick="closeRatingDashboard()" style="position:absolute;top:18px;right:24px;font-size:2em;color:#888;cursor:pointer;">&times;</span>
    <h3 style="margin-bottom:18px;">Rate This Inspection</h3>
    <form id="ratingForm">
  <div class="emoji-rating-grid">
    <!-- Label row -->
    <div class="emoji-label"></div>
    <div class="emoji-label">Poor</div>
    <div class="emoji-label">Below Average</div>
    <div class="emoji-label">Average</div>
    <div class="emoji-label">Good</div>
    <div class="emoji-label">Impressive</div>
    <!-- Food Quality row -->
    <div class="emoji-row-label">Food Quality:</div>
    <span class="emoji-rating" data-field="food_quality"></span>
    <!-- Food Safety row -->
    <div class="emoji-row-label">Food Safety:</div>
    <span class="emoji-rating" data-field="food_safety"></span>
    <!-- Hygiene row -->
    <div class="emoji-row-label">Hygiene:</div>
    <span class="emoji-rating" data-field="hygiene"></span>
    <!-- Service Quality row -->
    <div class="emoji-row-label">Service Quality:</div>
    <span class="emoji-rating" data-field="service_quality"></span>
  </div>
  <input type="hidden" name="inspection_id" id="ratingInspectionId" value="">
  <button type="submit" class="btn btn-primary" style="margin-top:18px;width:100%;">Save Rating</button>
</form>
  </div>
</div>

<script>
function showMessengerImgModal(src) {
    var modal = document.getElementById('messenger-img-modal');
    var img = document.getElementById('messenger-img-modal-img');
    img.src = src;
    modal.style.display = 'flex';
}
function closeMessengerImgModal() {
    document.getElementById('messenger-img-modal').style.display = 'none';
}
document.getElementById('messenger-img-modal').addEventListener('click', function(e){
    if(e.target === this) closeMessengerImgModal();
});
</script>
<script>
let selectedCanteen = <?= json_encode($selected_canteen) ?>;

function renderMessengerMessages(messages) {
    let html = '';
    messages.forEach(msg => {
        const is_admin = msg.sender === 'admin';
        html += `<div class="messenger-message${is_admin ? ' admin' : ''}">
            <div class="messenger-bubble">
                ${msg.is_announcement == 1 ? '<span class="material-symbols-rounded" style="vertical-align:middle;color:#fbc02d;font-size:1.5em;margin-right:6px;">campaign</span>' : ''}
                ${msg.message ? msg.message.replace(/\n/g, '<br>') : ''}
                ${msg.image ? (() => {
                    let files = [];
                    try { files = JSON.parse(msg.image); } catch(e) {}
                    if (Array.isArray(files) && files.length) {
                        let fhtml = '<div class="messenger-files">';
                        files.forEach(file => {
                            const ext = file.split('.').pop().toLowerCase();
                            if (['jpg','jpeg','png','gif','bmp','webp'].includes(ext)) {
                                let imgPath = (file.startsWith('uploads/') || file.startsWith('views/or_img/')) ? '/POS-PHP/' + file : file;
                                fhtml += `<img src="${imgPath}" alt="img" style="cursor:pointer;" onclick="showMessengerImgModal(this.src)">`;
                            } else {
                                fhtml += `<a href="${file}" target="_blank">${file.split('/').pop()}</a>`;
                            }
                        });
                        fhtml += '</div>';
                        return fhtml;
                    }
                    return '';
                })() : ''}
            </div>
            <div class="messenger-meta">
                ${is_admin ? 'You' : (msg.name || '')} &bull; ${msg.date_sent ? new Date(msg.date_sent.replace(' ', 'T')).toLocaleString('en-US', {month:'short', day:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}) : ''}
            </div>
        </div>`;
    });
    document.getElementById('messengerMessages').innerHTML = html;
    // Auto-scroll to bottom
    document.getElementById('messengerMessages').scrollTop = document.getElementById('messengerMessages').scrollHeight;
}

function fetchMessengerMessages() {
    if (!selectedCanteen) return;
    fetch('fetch_canteen_messages.php?canteen_id=' + selectedCanteen)
        .then(res => res.json())
        .then(data => renderMessengerMessages(data));
}

// Initial fetch
fetchMessengerMessages();
// Poll every 5 seconds
setInterval(fetchMessengerMessages, 5000);
</script>
<script>
function setStatus(row, status) {
    // Remove selected from all
    row.querySelectorAll('.status-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    // Set selected
    var selected = row.querySelector('.status-option[data-status="' + status + '"]');
    if (selected) selected.classList.add('selected');
    // Set hidden input value if exists (for form)
    var hidden = row.querySelector('input[type="hidden"]');
    if (hidden) hidden.value = status;
}

function complyAll() {
    var obligations = [];
    document.querySelectorAll('.obligation-row[data-obligation-id]').forEach(row => {
        var obligationId = row.getAttribute('data-obligation-id');
        obligations.push({ id: obligationId, status: 'Complied' });
        // Update UI
        row.querySelectorAll('.status-option').forEach(opt => opt.classList.remove('selected'));
        var selected = row.querySelector('.status-option[data-status="Complied"]');
        if (selected) selected.classList.add('selected');
    });

    if (obligations.length > 0) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_obligation_status.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('inspection_id=<?= isset($active_inspection['id']) ? $active_inspection['id'] : 0 ?>&obligations=' + encodeURIComponent(JSON.stringify(obligations)));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('complyAllBtn');
    if (btn) btn.onclick = complyAll;
});
document.querySelectorAll('.obligation-row .status-option').forEach(function(opt){
    opt.addEventListener('click', function(){
        var row = opt.closest('.obligation-row');
        var obligationId = row.getAttribute('data-obligation-id');
        var status = opt.getAttribute('data-status');
        // Visual feedback
        row.querySelectorAll('.status-option').forEach(o=>o.classList.remove('selected'));
        opt.classList.add('selected');
        // AJAX update
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_obligation_status.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('inspection_id=<?= isset($active_inspection['id']) ? $active_inspection['id'] : 0 ?>&obligations=' + encodeURIComponent(JSON.stringify([{id: obligationId, status: status}])));
    });
});

function finishInspection(inspectionId) {
    customConfirm('Finish this inspection?', function(result) {
    if(result) {
        // User clicked OK
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'finish_inspection.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            // Show rating dashboard after finishing
            showRatingDashboard(inspectionId);
        };
        xhr.send('inspection_id='+inspectionId);
    } else {
        // User clicked Cancel
    }
});
}

function toggleInspection(id) {
    var el = document.getElementById(id);
    var icon = document.getElementById('icon-' + id);
    if (el.style.display === "none" || el.style.display === "") {
        el.style.display = "block";
        if (icon) icon.innerHTML = "&#9650;"; // up arrow
    } else {
        el.style.display = "none";
        if (icon) icon.innerHTML = "&#9660;"; // down arrow
    }
}

// --- Rating Dashboard logic ---
document.querySelectorAll('.open-rating-dashboard').forEach(btn => {
    btn.addEventListener('click', function() {
        var inspectionId = this.getAttribute('data-inspection-id');
        // Fetch existing rating via AJAX and show dashboard
        editRating(inspectionId);
    });
});

const emojiFaces = ["😠","🙁","😐","🙂","😁"]; // 1-5

function renderEmojiRating(container, value) {
    container.innerHTML = '';
    for (let i = 1; i <= 5; i++) {
        const span = document.createElement('span');
        span.textContent = emojiFaces[i-1];
        span.dataset.value = i;
        if (i === value) span.classList.add('selected');
        span.onclick = function() {
            // Always use parentNode to get the correct .emoji-rating container
            const parent = this.parentNode;
            parent.querySelectorAll('span').forEach(s=>s.classList.remove('selected'));
            this.classList.add('selected');
            parent.dataset.selected = i;
            // Auto-save if editing past inspection
            if (parent.dataset.edit && parent.dataset.inspectionId) {
                saveRatingField(parent.dataset.inspectionId, parent.dataset.field, i);
            }
        };
        container.appendChild(span);
    }
}
function showRatingDashboard(inspectionId, existing) {
    document.getElementById('ratingDashboardModal').style.display = 'flex';
    document.getElementById('ratingInspectionId').value = inspectionId;
    var saveBtn = document.querySelector('#ratingForm button[type="submit"]');
    var closeBtn = document.querySelector('#ratingDashboardModal > div > span[onclick="closeRatingDashboard()"]');
    if (existing) {
        saveBtn.style.display = 'none';
        if (closeBtn) closeBtn.style.display = '';
    } else {
        saveBtn.style.display = '';
        if (closeBtn) closeBtn.style.display = 'none';
    }
    document.querySelectorAll('.emoji-rating').forEach(el=>{
        // Set data-field if not present
        if (!el.dataset.field) {
            if (el.previousElementSibling && el.previousElementSibling.tagName === 'LABEL') {
                el.dataset.field = el.previousElementSibling.textContent.trim().toLowerCase().replace(/\s+/g, '_');
            }
        }
        el.dataset.selected = existing && existing[el.dataset.field] ? existing[el.dataset.field] : 3;
        // Set edit mode attributes BEFORE rendering
        if (existing) {
            el.dataset.edit = "1";
            el.dataset.inspectionId = inspectionId;
        } else {
            delete el.dataset.edit;
            delete el.dataset.inspectionId;
        }
        renderEmojiRating(el, Number(el.dataset.selected));
    });
}
document.getElementById('ratingForm').onsubmit = function(e) {
    e.preventDefault();
    const inspection_id = document.getElementById('ratingInspectionId').value;
    const data = {
        inspection_id,
        food_quality: document.querySelector('.emoji-rating[data-field="food_quality"]').dataset.selected,
        food_safety: document.querySelector('.emoji-rating[data-field="food_safety"]').dataset.selected,
        hygiene: document.querySelector('.emoji-rating[data-field="hygiene"]').dataset.selected,
        service_quality: document.querySelector('.emoji-rating[data-field="service_quality"]').dataset.selected
    };
    fetch('save_inspection_rating.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(data)
    }).then(r=>r.json()).then(res=>{
        if(res.success) {
            alert('Rating saved!');
            document.getElementById('ratingDashboardModal').style.display = 'none';
            location.reload();
        }
    });
};
function saveRatingField(inspectionId, field, value) {
    fetch('save_inspection_rating.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({inspection_id:inspectionId, [field]:value, autosave:1})
    });
}

// Edit rating function
function editRating(inspectionId) {
    // Fetch existing rating via AJAX
    fetch('get_inspection_rating.php?id='+inspectionId)
        .then(r=>r.json())
        .then(data=>{
            showRatingDashboard(inspectionId, data);
        });
}
function closeRatingDashboard() {
    document.getElementById('ratingDashboardModal').style.display = 'none';
}
</script>
<!-- Add this to your <head> if not already present -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var msgBox = document.querySelector('.messenger-messages');
    if (msgBox) {
        msgBox.scrollTop = msgBox.scrollHeight;
    }
});
</script>
</html>
<?php require_once "custom_confirm.php"; ?>
<script>
document.getElementById('messengerToggleBtn').onclick = function() {
    var box = document.getElementById('fixedMessengerBox');
    var icon = document.getElementById('messengerToggleIcon');
    box.classList.toggle('collapsed');
    if (box.classList.contains('collapsed')) {
        icon.textContent = 'expand_less'; // Show up arrow
    } else {
        icon.textContent = 'expand_more'; // Show down arrow
    }
};
document.getElementById('messengerForm').onsubmit = function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch('edit_canteen_obligations.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(() => {
        form.message.value = '';
        form.files.value = '';
        fetchMessengerMessages(); // Refresh messages immediately
        setTimeout(() => {
            const msgBox = document.getElementById('messengerMessages');
            if (msgBox) msgBox.scrollTop = msgBox.scrollHeight;
        }, 100);
    });
};
</script>