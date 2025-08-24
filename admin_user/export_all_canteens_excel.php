<?php
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$year = intval($_GET['year'] ?? date('Y'));

// Fetch all canteens
$cantines = [];
$res = $conn->query("SELECT id, name, owner, stall_no FROM cantines WHERE active=0 AND del_status=0 ORDER BY name ASC");
while ($row = $res->fetch_assoc()) {
    $cantines[] = $row;
}

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

$spreadsheet = new Spreadsheet();

// Place these at the top, before your foreach loop
function styleHeader($sheet, $rowNum) {
    $sheet->getStyle("A$rowNum:F$rowNum")->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '3A6EA5']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    ]);
}
function styleTotal($sheet, $rowNum, $col) {
    $sheet->getStyle("{$col}{$rowNum}")->getFont()->setBold(true);
}

function addTableBorders($sheet, $startRow, $endRow, $endCol = 'F') {
    $sheet->getStyle("A{$startRow}:{$endCol}{$endRow}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
    ]);
}

// ...then your foreach ($cantines as $index => $cantine) {
foreach ($cantines as $index => $cantine) {
    $sheet = $index === 0
        ? $spreadsheet->getActiveSheet()
        : $spreadsheet->createSheet();
    $sheet->setTitle(substr($cantine['name'], 0, 28)); // Sheet name max 31 chars

    $rowNum = 1;
    $sheet->setCellValue("A$rowNum", "NAME: {$cantine['owner']}, STALL #{$cantine['stall_no']}");
    $sheet->mergeCells("A$rowNum:F$rowNum");
    $sheet->getStyle("A$rowNum")->getFont()->setBold(true);
    $rowNum += 2;

    // MONTHLY RENTAL
    $sheet->setCellValue("A$rowNum", "MONTHLY RENTAL");
    $sheet->mergeCells("A$rowNum:F$rowNum");
    styleHeader($sheet, $rowNum);
    $rowNum++;

    // Before writing the table header
    $tableStartRow = $rowNum;

    // Write table header
    $sheet->fromArray(['#','BILL MONTH','BUSINESS NAME','MONTHLY RENTAL PAYMENT','OR NO.','PAYMENT DATE'], null, "A$rowNum");
    styleHeader($sheet, $rowNum);
    $rowNum++;

    $rental = getBills($conn, $cantine['id'], 1, $year);
    $totalRental = 0;
    foreach ($months as $m => $month) {
        $sheet->fromArray([
            $m,
            $month,
            $cantine['name'],
            isset($rental[$m]) ? '₱'.number_format($rental[$m]['payment'],2) : '',
            isset($rental[$m]) ? $rental[$m]['or_no'] : '',
            isset($rental[$m]) ? $rental[$m]['real_date'] : ''
        ], null, "A$rowNum");
        if (isset($rental[$m])) $totalRental += $rental[$m]['payment'];
        $rowNum++;
    }
    $sheet->setCellValue("C$rowNum", "TOTAL");
    $sheet->setCellValue("D$rowNum", '₱'.number_format($totalRental,2));
    styleTotal($sheet, $rowNum, "C");
    styleTotal($sheet, $rowNum, "D");
    // After writing the TOTAL row
    addTableBorders($sheet, $tableStartRow, $rowNum - 1);
    $rowNum += 2;

    // MONTHLY ELECTRIC
    $sheet->setCellValue("A$rowNum", "MONTHLY ELECTRIC");
    $sheet->mergeCells("A$rowNum:F$rowNum");
    styleHeader($sheet, $rowNum);
    $rowNum++;

    $sheet->fromArray(['#','BILL MONTH','BUSINESS NAME','PAYMENT TOTAL','OR NO.','PAYMENT DATE'], null, "A$rowNum");
    styleHeader($sheet, $rowNum);
    $rowNum++;

    $electric = getBills($conn, $cantine['id'], 2, $year);
    $totalElectric = 0;
    foreach ($months as $m => $month) {
        $sheet->fromArray([
            $m,
            $month,
            $cantine['name'],
            isset($electric[$m]) ? '₱'.number_format($electric[$m]['payment'],2) : '',
            isset($electric[$m]) ? $electric[$m]['or_no'] : '',
            isset($electric[$m]) ? $electric[$m]['real_date'] : ''
        ], null, "A$rowNum");
        if (isset($electric[$m])) $totalElectric += $electric[$m]['payment'];
        $rowNum++;
    }
    $sheet->setCellValue("C$rowNum", "TOTAL");
    $sheet->setCellValue("D$rowNum", '₱'.number_format($totalElectric,2));
    styleTotal($sheet, $rowNum, "C");
    styleTotal($sheet, $rowNum, "D");
    addTableBorders($sheet, $rowNum-($rowNum-1-count($months)), $rowNum-1);
    $rowNum += 2;

    // MONTHLY WATER
    $sheet->setCellValue("A$rowNum", "MONTHLY WATER");
    $sheet->mergeCells("A$rowNum:F$rowNum");
    styleHeader($sheet, $rowNum);
    $rowNum++;

    $sheet->fromArray(['#','BILL MONTH','BUSINESS NAME','PAYMENT TOTAL','OR NO.','PAYMENT DATE'], null, "A$rowNum");
    styleHeader($sheet, $rowNum);
    $rowNum++;

    $water = getBills($conn, $cantine['id'], 3, $year);
    $totalWater = 0;
    foreach ($months as $m => $month) {
        $sheet->fromArray([
            $m,
            $month,
            $cantine['name'],
            isset($water[$m]) ? '₱'.number_format($water[$m]['payment'],2) : '',
            isset($water[$m]) ? $water[$m]['or_no'] : '',
            isset($water[$m]) ? $water[$m]['real_date'] : ''
        ], null, "A$rowNum");
        if (isset($water[$m])) $totalWater += $water[$m]['payment'];
        $rowNum++;
    }
    $sheet->setCellValue("C$rowNum", "TOTAL");
    $sheet->setCellValue("D$rowNum", '₱'.number_format($totalWater,2));
    styleTotal($sheet, $rowNum, "C");
    styleTotal($sheet, $rowNum, "D");
    addTableBorders($sheet, $rowNum-($rowNum-1-count($months)), $rowNum-1);
    $rowNum += 2;

    // OVERALL TOTAL
    $overallTotal = $totalRental + $totalElectric + $totalWater;
    $sheet->setCellValue("C$rowNum", "OVERALL TOTAL");
    $sheet->setCellValue("D$rowNum", '₱'.number_format($overallTotal,2));
    styleTotal($sheet, $rowNum, "C");
    styleTotal($sheet, $rowNum, "D");
    $rowNum += 2;

    // Prepared by
    $sheet->setCellValue("C".($rowNum+2), "Prepared by:");
    $sheet->setCellValue("C".($rowNum+3), "JOSEPH J. JULIANO");
    $sheet->getStyle("C".($rowNum+3))->getFont()->setBold(true);

    // Auto-size columns
    foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="all_canteens_report_'.$year.'.xlsx"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;