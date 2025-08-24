<?php
require_once "db.php";

$cantine_id = intval($_GET['cantine_id'] ?? 0);
$year = intval($_GET['year'] ?? date('Y'));

// Fetch canteen info
$stmt = $conn->prepare("SELECT name, owner, stall_no FROM cantines WHERE id=?");
$stmt->bind_param("i", $cantine_id);
$stmt->execute();
$cantine = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch bills for each type (Rental, Electric, Water, Others)
function getBills($conn, $cantine_id, $type, $year) {
    $sql = "SELECT MONTH(date) as m, payment, or_no, real_date FROM bills WHERE cantine_id=? AND bills_type=? AND YEAR(real_date)=? AND ver_status=2 AND del_status=0 AND ver_status != 0 ORDER BY real_date";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $cantine_id, $type, $year);
    $stmt->execute();
    $res = $stmt->get_result();
    $bills = [];
    while ($row = $res->fetch_assoc()) {
        $bills[intval($row['m'])] = $row;
    }
    $stmt->close();
    return $bills;
}
function getOtherBills($conn, $cantine_id, $year) {
    $sql = "SELECT name_other, payment, or_no, real_date FROM bills WHERE cantine_id=? AND bills_type=4 AND YEAR(real_date)=? AND ver_status=2 AND del_status=0 ORDER BY real_date";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cantine_id, $year);
    $stmt->execute();
    $res = $stmt->get_result();
    $bills = [];
    while ($row = $res->fetch_assoc()) {
        $bills[] = $row;
    }
    $stmt->close();
    return $bills;
}

$months = [
    1=>'JANUARY',2=>'FEBRUARY',3=>'MARCH',4=>'APRIL',5=>'MAY',6=>'JUNE',
    7=>'JULY',8=>'AUGUST',9=>'SEPTEMBER',10=>'OCTOBER',11=>'NOVEMBER',12=>'DECEMBER'
];

$rental = getBills($conn, $cantine_id, 1, $year);
$electric = getBills($conn, $cantine_id, 2, $year);
$water = getBills($conn, $cantine_id, 3, $year);
$others = getOtherBills($conn, $cantine_id, $year);


// Set headers for Excel download
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"canteen_report_{$year}.xls\"");
// Output UTF-8 BOM for Excel compatibility with special characters
echo "\xEF\xBB\xBF";

function renderBillTable($title, $cantine, $bills, $months, $col_payment) {
    $total = 0;
    echo "<tr><td colspan='6' style='background:#3a6ea5;color:#fff;font-weight:600;'>$title</td></tr>";
    echo "<tr>
        <th>#</th>
        <th>BILL MONTH</th>
        <th>BUSINESS NAME</th>
        <th>$col_payment</th>
        <th>OR NO.</th>
        <th>PAYMENT DATE</th>
    </tr>";
    $i = 1;
    foreach ($months as $m => $month) {
        echo "<tr>";
        echo "<td>$i</td>";
        echo "<td>$month</td>";
        echo "<td>".htmlspecialchars($cantine['name'])."</td>";
        if (isset($bills[$m])) {
            $total += $bills[$m]['payment'];
            echo "<td>₱".number_format($bills[$m]['payment'],2)."</td>";
            echo "<td>".htmlspecialchars($bills[$m]['or_no'])."</td>";
            echo "<td>".htmlspecialchars($bills[$m]['real_date'])."</td>";
        } else {
            echo "<td></td><td></td><td></td>";
        }
        echo "</tr>";
        $i++;
    }
    echo "<tr style='font-weight:bold;'><td colspan='3'>TOTAL</td><td>₱".number_format($total,2)."</td><td colspan='2'></td></tr>";
}
?>
<table border="1">
    <tr><td colspan="6" style="font-weight:bold;">NAME: <?= htmlspecialchars($cantine['owner']) ?>, STALL #<?= htmlspecialchars($cantine['stall_no']) ?></td></tr>
    <?php renderBillTable('MONTHLY RENTAL', $cantine, $rental, $months, 'MONTHLY RENTAL PAYMENT'); ?>
    <?php renderBillTable('MONTHLY ELECTRIC', $cantine, $electric, $months, 'PAYMENT TOTAL'); ?>
    <?php renderBillTable('MONTHLY WATER', $cantine, $water, $months, 'PAYMENT TOTAL'); ?>
    <tr><td colspan="6" style="background:#3a6ea5;color:#fff;font-weight:600;">OTHERS</td></tr>
    <tr>
        <th>#</th>
        <th>NAME</th>
        <th>PAYMENT TOTAL</th>
        <th>OR NO.</th>
        <th>PAYMENT DATE</th>
        <th></th>
    </tr>
    <?php $i=1; $total=0; foreach($others as $row): $total+=$row['payment']; ?>
    <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($row['name_other']) ?></td>
        <td>₱<?= number_format($row['payment'],2) ?></td>
        <td><?= htmlspecialchars($row['or_no']) ?></td>
        <td><?= htmlspecialchars($row['real_date']) ?></td>
        <td></td>
    </tr>
    <?php endforeach; ?>
    <tr style="font-weight:bold;"><td colspan="2">TOTAL</td><td>₱<?= number_format($total,2) ?></td><td colspan="3"></td></tr>
</table>