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
$currentMonth = date('n');
$currentYear = $year;

// --- Warning Notifier Logic ---
$warning_rows = [];
foreach ($cantines as $cantine) {
    $missing = [];
    foreach (['Rental'=>1, 'Electric'=>2, 'Water'=>3] as $label => $type) {
        // Only check months before the current month
        for ($m = 1; $m < $currentMonth; $m++) {
            $sql = "SELECT id FROM bills WHERE cantine_id=? AND bills_type=? AND YEAR(date)=? AND MONTH(date)=? AND ver_status=2 AND del_status=0";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiii", $cantine['id'], $type, $currentYear, $m);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows == 0) {
                $missing[] = [
                    'type' => $label,
                    'month' => date('F', mktime(0,0,0,$m,1)),
                    'month_num' => $m
                ];
            }
            $stmt->close();
        }
    }
    if (!empty($missing)) {
        // Group by type for easier modal rendering
        $grouped = ['Rental'=>[], 'Electric'=>[], 'Water'=>[]];
        foreach ($missing as $item) {
            $grouped[$item['type']][] = $item['month'];
        }
        $warning_rows[] = [
            'canteen' => $cantine['name'],
            'owner' => $cantine['owner'],
            'missing' => $grouped, // grouped by type
            'missing_count' => count($missing)
        ];
    }
}

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