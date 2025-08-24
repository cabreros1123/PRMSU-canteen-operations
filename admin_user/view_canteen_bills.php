<?php
require_once "db.php";
$cantine_id = intval($_GET['cantine_id'] ?? 0);
if (!$cantine_id) exit;

// Get canteen info
$stmt = $conn->prepare("SELECT name, owner, stall_no FROM cantines WHERE id=?");
$stmt->bind_param("i", $cantine_id);
$stmt->execute();
$cantine = $stmt->get_result()->fetch_assoc();
$stmt->close();

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$months = [
    1=>'JANUARY',2=>'FEBRUARY',3=>'MARCH',4=>'APRIL',5=>'MAY',6=>'JUNE',
    7=>'JULY',8=>'AUGUST',9=>'SEPTEMBER',10=>'OCTOBER',11=>'NOVEMBER',12=>'DECEMBER'
];

// Helper to get bills by type
function getBills($conn, $cantine_id, $type, $year) {
    $sql = "SELECT MONTH(date) as m, payment, or_no, date, real_date FROM bills WHERE cantine_id=? AND bills_type=? AND YEAR(real_date)=? AND ver_status=2 ORDER BY real_date";
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

// Helper for others
function getOtherBills($conn, $cantine_id, $year) {
    $sql = "SELECT name_other, payment, or_no, real_date FROM bills WHERE cantine_id=? AND bills_type=4 AND YEAR(date)=? AND ver_status=2 ORDER BY date";
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

$rental = getBills($conn, $cantine_id, 1, $year);
$electric = getBills($conn, $cantine_id, 2, $year);
$water = getBills($conn, $cantine_id, 3, $year);
$others = getOtherBills($conn, $cantine_id, $year);

function renderBillTable($title, $cantine, $bills, $months, $col_payment, $col_or, $col_date) {
    $total = 0;
    ob_start();
    ?>
    <div style="margin-bottom:24px;">
        <div style="background:#3a6ea5;color:#fff;font-weight:600;padding:6px 12px;border-radius:6px 6px 0 0;"><?= $title ?></div>
        <table style="width:100%;border-collapse:collapse;">
            <tr style="background:#e9eef6;">
                <th style="padding:4px 8px;">#</th>
                <th style="padding:4px 8px;">BILL MONTH</th>
                <th style="padding:4px 8px;">BUSINESS NAME</th>
                <th style="padding:4px 8px;"><?= $col_payment ?></th>
                <th style="padding:4px 8px;">OR NO.</th>
                <th style="padding:4px 8px;"><?= $col_date ?></th>
            </tr>
            <?php foreach ($months as $i=>$m): ?>
                <tr>
                    <td style="padding:4px 8px;text-align:center;"><?= $i ?></td>
                    <td style="padding:4px 8px;"><?= $m ?></td>
                    <td style="padding:4px 8px;"><?= htmlspecialchars($cantine['name']) ?></td>
                    <td style="padding:4px 8px;text-align:right;">
                        <?php if(isset($bills[$i])): $total += $bills[$i]['payment']; ?>
                            ₱ <?= number_format($bills[$i]['payment'],2) ?>
                        <?php endif; ?>
                    </td>
                    <td style="padding:4px 8px;text-align:center;">
                        <?= isset($bills[$i]) ? htmlspecialchars($bills[$i]['or_no']) : '' ?>
                    </td>
                    <td style="padding:4px 8px;text-align:center;">
                        <?= isset($bills[$i]['real_date']) ? htmlspecialchars($bills[$i]['real_date']) : '' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr style="background:#f7fafd;font-weight:600;">
                <td colspan="3" style="text-align:right;padding:4px 8px;">TOTAL</td>
                <td style="padding:4px 8px;text-align:right;">₱ <?= number_format($total,2) ?></td>
                <td colspan="2"></td>
            </tr>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
?>
<div style="font-weight:600;font-size:1.1rem;margin-bottom:8px;">
    NAME: <?= htmlspecialchars($cantine['owner']) ?>, STALL #<?= htmlspecialchars($cantine['stall_no']) ?>
</div>
<?= renderBillTable('MONTHLY RENTAL', $cantine, $rental, $months, 'MONTHLY RENTAL PAYMENT', 'OR NO.', 'PAYMENT DATE') ?>
<?= renderBillTable('MONTHLY ELECTRIC', $cantine, $electric, $months, 'PAYMENT TOTAL', 'OR NO.', 'PAYMENT DATE') ?>
<?= renderBillTable('MONTHLY WATER', $cantine, $water, $months, 'PAYMENT TOTAL', 'OR NO.', 'PAYMENT DATE') ?>

<div style="background:#3a6ea5;color:#fff;font-weight:600;padding:6px 12px;border-radius:6px 6px 0 0;margin-bottom:0;">OTHERS</div>
<table style="width:100%;border-collapse:collapse;">
    <tr style="background:#e9eef6;">
        <th style="padding:4px 8px;">#</th>
        <th style="padding:4px 8px;">NAME</th>
        <th style="padding:4px 8px;">PAYMENT TOTAL</th>
        <th style="padding:4px 8px;">OR NO.</th>
        <th style="padding:4px 8px;">PAYMENT DATE</th>
    </tr>
    <?php $i=1; $total=0; foreach($others as $row): $total+=$row['payment']; ?>
    <tr>
        <td style="padding:4px 8px;text-align:center;"><?= $i++ ?></td>
        <td style="padding:4px 8px;"><?= htmlspecialchars($row['name_other']) ?></td>
        <td style="padding:4px 8px;text-align:right;">₱ <?= number_format($row['payment'],2) ?></td>
        <td style="padding:4px 8px;text-align:center;"><?= htmlspecialchars($row['or_no']) ?></td>
        <td style="padding:4px 8px;text-align:center;"><?= htmlspecialchars($row['real_date']) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="background:#f7fafd;font-weight:600;">
        <td colspan="2" style="text-align:right;padding:4px 8px;">TOTAL</td>
        <td style="padding:4px 8px;text-align:right;">₱ <?= number_format($total,2) ?></td>
        <td colspan="2"></td>
    </tr>
</table>