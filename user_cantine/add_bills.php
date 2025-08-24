<?php
session_start();
require_once "db.php"; // Adjust path if needed

if (!isset($_SESSION['id_cantine'])) {
    die('Error: You are not authorized to view this page. Please log in.');
}

$id_cantine = $_SESSION['id_cantine'];
$selected_canteen = isset($id_cantine) ? intval($id_cantine) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_message'])) {
        // Messenger logic for canteen user
        $selected_canteen = $id_cantine;
        $canteen_id = $selected_canteen;
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
        $conn->query("INSERT INTO canteen_messages (canteen_id, sender, message, image) VALUES ($canteen_id, 'canteen', '$message', '$files_json')");
        echo "<script>location.href=location.href;</script>";
        exit;
    }

    // 2. Check for duplicate month in rental, electric, water
    if (!empty($_POST['bill_date']) && !empty($_POST['bill_type'])) {
        foreach ($_POST['bill_date'] as $i => $date) {
            $type = $_POST['bill_type'][$i];
            if (in_array($type, [1, 2, 3])) { // rental, electric, water
                $month = date('m', strtotime($date));
                $year = date('Y', strtotime($date));
                $stmt = $conn->prepare("SELECT ver_status FROM bills WHERE cantine_id=? AND bills_type=? AND MONTH(date)=? AND YEAR(date)=?");
                $stmt->bind_param("iiii", $id_cantine, $type, $month, $year);
                $stmt->execute();
                $stmt->store_result();
                $has_active = false;
                $stmt->bind_result($ver_status);
                while ($stmt->fetch()) {
                    if ($ver_status != 0) { // Only block if there is a non-declined bill
                        $has_active = true;
                        break;
                    }
                }
                $stmt->close();
                if ($has_active) {
                    $typeName = ($type == 1 ? 'Rental' : ($type == 2 ? 'Electric' : 'Water'));
                    echo "<script>alert('You already have a $typeName bill for $year-$month!');window.history.back();</script>";
                    exit;
                }
            }
        }
    }

    // 1. Handle image upload
    $imgPath = null;
    if (isset($_FILES['official_receipt_img']) && $_FILES['official_receipt_img']['error'] === UPLOAD_ERR_OK) {
        $imgName = uniqid() . '_' . basename($_FILES['official_receipt_img']['name']);
        $targetDir = __DIR__ . '/../views/or_img/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $targetFile = $targetDir . $imgName;
        if (move_uploaded_file($_FILES['official_receipt_img']['tmp_name'], $targetFile)) {
            $imgPath = 'views/or_img/' . $imgName;
        }
    }

    // 2. Get Official Receipt No.
    $or_no = $_POST['official_receipt_number'];

    // 3. Save to bills_img table
    $category_no = null;
    if ($imgPath) {
        $stmt = $conn->prepare("INSERT INTO bills_img (img, or_no, cantine_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $imgPath, $or_no, $id_cantine);
        $stmt->execute();
        $category_no = $conn->insert_id; // Get the inserted bills_img.id
        $stmt->close();
    }

    // 4. Save each bill row to bills table
    if (!empty($_POST['bill_date']) && !empty($_POST['bill_amount']) && !empty($_POST['bill_type'])) {
        $otherNames = isset($_POST['name_other']) ? $_POST['name_other'] : [];
        $otherIndex = 0;
        foreach ($_POST['bill_date'] as $i => $date) {
            $payment = $_POST['bill_amount'][$i];
            $type = $_POST['bill_type'][$i];
            $nameOther = null;
            if ($type == 4) {
                $nameOther = $otherNames[$otherIndex] ?? null;
                $otherIndex++;
            }
            // For rental, electric, water: convert month to YYYY-MM-01
            if (in_array($type, [1, 2, 3])) {
                // $date is in format YYYY-MM
                $date = $date . '-01';
            }
            if ($date && $payment) {
                if ($type == 4) {
                    $stmt = $conn->prepare("INSERT INTO bills (or_no, date, payment, bills_type, cantine_id, name_other, category_no) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssdsisi", $or_no, $date, $payment, $type, $id_cantine, $nameOther, $category_no);
                } else {
                    $stmt = $conn->prepare("INSERT INTO bills (or_no, date, payment, bills_type, cantine_id, category_no) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssdsii", $or_no, $date, $payment, $type, $id_cantine, $category_no);
                }
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Send message to admin about the new bill payment
    if ($imgPath) {
        $msg_text = "Payment Bill Submitted. Official Receipt No.: $or_no";
        $msg_files = json_encode([$imgPath]);
        $canteen_id = $id_cantine;
        $conn->query("INSERT INTO canteen_messages (canteen_id, sender, message, image, is_payment_bill) VALUES ($canteen_id, 'canteen', '$msg_text', '$msg_files', 1)");
    }

    echo "<script>alert('Bills saved successfully!');window.location='add_bills.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Bills</title>
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .center-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        form {
            width: 100%;
            max-width: 400px;
        }
        .row-align {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 10px 12px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            gap: 10px;
        }
        .row-label {
            flex: 1;
            font-weight: 600;
            font-size: 1.25rem;
        }
        .row-input {
            flex: 2;
            min-width: 0;
        }
        .plus-btn {
            width: 40px;
            height: 40px;
            font-size: 1.5rem;
            padding: 0;
            margin-left: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .close-btn {
            position: absolute;
            top: 8px;
            right: 12px;
            font-size: 1.2rem;
            color: #888;
            cursor: pointer;
        }
        .table-wrapper {
            position: relative;
        }
        .total-label {
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 20px;
        }
        .card {
            width: 100%;
        }
        .card-body {
            padding-left: 24px;
            padding-right: 24px;
        }
        .form-control, .input-group {
            width: 100%;
        }
        #imgPreviewContainer {
            display: none;
            text-align: center;
        }
        #imgPreview {
            max-width: 100%;
            max-height: 180px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        @media (max-width: 600px) {
            .center-container {
                align-items: flex-start;
                padding: 10px;
            }
            form {
                max-width: 100%;
            }
            .row-align {
                flex-direction: column;
                align-items: stretch;
                padding: 10px 6px;
            }
            .row-label, .row-input {
                font-size: 1.1rem;
            }
            .plus-btn {
                width: 100%;
                margin-left: 0;
                margin-top: 8px;
            }
            .card-body {
                padding-left: 8px;
                padding-right: 8px;
            }
        }

    /* Messenger styles moved to messenger.php */
    </style>
</head>
<body>
    <?php require_once "header.php"; ?>
<div class="center-container">
    <div style="display: flex; gap: 32px; align-items: flex-start; justify-content: center;">
        <div style="flex: 1; max-width: 500px;">
            <!-- Bills Form -->
            <form method="POST" enctype="multipart/form-data">
                <!-- Official Receipt IMG row -->
                <div class="row-align">
                    <label class="row-label" for="officialReceiptImg">Official Receipt IMG</label>
                    <input type="file" id="officialReceiptImg" name="official_receipt_img" accept="image/*" style="display:none;" required onchange="previewImage(event)">
                    <button type="button" class="btn btn-outline-primary plus-btn" onclick="document.getElementById('officialReceiptImg').click()">+</button>
                </div>
                <div id="imgPreviewContainer">
                    <img id="imgPreview" src="">
                </div>
                <div id="imgWarning" style="display:none;color:#dc3545;font-weight:600;margin-bottom:10px;">
                    Please select an Official Receipt image before saving!
                </div>
                <!-- Official Receipt Number row -->
                <div class="row-align">
                    <label class="row-label" for="officialReceiptNumber">Official Receipt No.</label>
                    <input type="text" class="form-control row-input" id="officialReceiptNumber" name="official_receipt_number" placeholder="Official Receipt Number" required>
                </div>
                <!-- Rental row -->
                <div class="row-align btn-container">
                    <span class="row-label">RENTAL</span>
                    <button type="button" class="btn btn-outline-secondary plus-btn" onclick="addBillTable('rentalTable', this)">+</button>
                </div>
                <!-- Electric row -->
                <div class="row-align btn-container">
                    <span class="row-label">ELECTRIC</span>
                    <button type="button" class="btn btn-outline-secondary plus-btn" onclick="addBillTable('electricTable', this)">+</button>
                </div>
                <!-- Water row -->
                <div class="row-align btn-container">
                    <span class="row-label">WATER</span>
                    <button type="button" class="btn btn-outline-secondary plus-btn" onclick="addBillTable('waterTable', this)">+</button>
                </div>
                <!-- Others row -->
                <div class="row-align btn-container">
                    <span class="row-label">OTHERS</span>
                    <button type="button" class="btn btn-outline-secondary plus-btn" onclick="addBillTable('othersTable', this)">+</button>
                </div>
                <div class="total-label">total: ₱0.00</div>
                <button type="submit" class="btn btn-success mt-3 w-100">SAVE!</button>
            </form>
        </div>
    <!-- Messenger moved to messenger.php -->
    </div>
</div>

<!-- Rental Table Template -->
<div id="rentalTable" style="display:none;">
    <div class="card mb-2 table-wrapper inserted-table">
        <span class="close-btn" onclick="closeTable(this)">&times;</span>
        <div class="card-header">RENTAL</div>
        <div class="card-body">
            <div class="row g-2 align-items-end mb-2">
                <div class="col-5">
                    <label class="form-label mb-1">Date</label>
                    <input type="month" class="form-control bill-date" name="bill_date[]" required>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-1" onclick="setToday(this)">Today</button>
                </div>
                <div class="col-5">
                    <label class="form-label mb-1">Amount Payment</label>
                    <div class="input-group">
                        <input type="number" class="form-control bill-amount" min="0" step="0.01" name="bill_amount[]" oninput="updateTotal()" required>
                    </div>
                </div>
                <input type="hidden" name="bill_type[]" value="1">
            </div>
        </div>
    </div>
</div>
<!-- Electric Table Template -->
<div id="electricTable" style="display:none;">
    <div class="card mb-2 table-wrapper inserted-table">
        <span class="close-btn" onclick="closeTable(this)">&times;</span>
        <div class="card-header">ELECTRIC</div>
        <div class="card-body">
            <div class="row g-2 align-items-end mb-2">
                <div class="col-5">
                    <label class="form-label mb-1">Date</label>
                    <input type="month" class="form-control bill-date" name="bill_date[]" required>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-1" onclick="setToday(this)">Today</button>
                </div>
                <div class="col-5">
                    <label class="form-label mb-1">Amount Payment</label>
                    <div class="input-group">
                        <input type="number" class="form-control bill-amount" min="0" step="0.01" name="bill_amount[]" oninput="updateTotal()" required>
                    </div>
                </div>
                <input type="hidden" name="bill_type[]" value="2">
            </div>
        </div>
    </div>
</div>
<!-- Water Table Template -->
<div id="waterTable" style="display:none;">
    <div class="card mb-2 table-wrapper inserted-table">
        <span class="close-btn" onclick="closeTable(this)">&times;</span>
        <div class="card-header">WATER</div>
        <div class="card-body">
            <div class="row g-2 align-items-end mb-2">
                <div class="col-5">
                    <label class="form-label mb-1">Date</label>
                    <input type="month" class="form-control bill-date" name="bill_date[]" required>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-1" onclick="setToday(this)">Today</button>
                </div>
                <div class="col-5">
                    <label class="form-label mb-1">Amount Payment</label>
                    <div class="input-group">
                        <input type="number" class="form-control bill-amount" min="0" step="0.01" name="bill_amount[]" oninput="updateTotal()" required>
                    </div>
                </div>
                <input type="hidden" name="bill_type[]" value="3">
            </div>
        </div>
    </div>
</div>
<!-- Others Table Template -->
<div id="othersTable" style="display:none;">
    <div class="card mb-2 table-wrapper inserted-table">
        <span class="close-btn" onclick="closeTable(this)">&times;</span>
        <div class="card-header">OTHERS</div>
        <div class="card-body">
            <div class="row g-2 align-items-end mb-2">
                <div class="col-12 mb-2">
                    <label class="form-label mb-1">Name of Bill</label>
                    <input type="text" class="form-control bill-other-name" name="name_other[]" placeholder="Enter bill name" required>
                </div>
                <div class="col-5">
                    <label class="form-label mb-1">Date</label>
                    <input type="date" class="form-control bill-date" name="bill_date[]" required>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-1" onclick="setToday(this)">Today</button>
                </div>
                <div class="col-5">
                    <label class="form-label mb-1">Amount Payment</label>
                    <div class="input-group">
                        <input type="number" class="form-control bill-amount" min="0" step="0.01" name="bill_amount[]" oninput="updateTotal()" required>
                    </div>
                </div>
                <input type="hidden" name="bill_type[]" value="4">
            </div>
        </div>
    </div>
</div>
<!-- Messenger Image Modal -->
<div id="messengerImgModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;background:rgba(0,0,0,0.7);align-items:center;justify-content:center;">
    <img id="messengerImgModalImg" src="" style="max-width:90vw;max-height:90vh;border-radius:12px;box-shadow:0 2px 16px #0008;">
</div>
<script>
function showMessengerImgModal(src) {
    var modal = document.getElementById('messengerImgModal');
    var modalImg = document.getElementById('messengerImgModalImg');
    modalImg.src = src;
    modal.style.display = 'flex';
}
document.getElementById('messengerImgModal').onclick = function() {
    this.style.display = 'none';
}
</script>
<script>
function addBillTable(tableId, btn) {
    var table = document.getElementById(tableId).cloneNode(true);
    table.style.display = 'block';
    table.classList.add('inserted-table');
    table.removeAttribute('id');
    // Set data-bill-type for restoration
    if (tableId === 'rentalTable') table.setAttribute('data-bill-type', 'rental');
    if (tableId === 'electricTable') table.setAttribute('data-bill-type', 'electric');
    if (tableId === 'waterTable') table.setAttribute('data-bill-type', 'water');
    if (tableId === 'othersTable') table.setAttribute('data-bill-type', 'others');
    btn.parentNode.parentNode.insertBefore(table, btn.parentNode.nextSibling);
    // Disable used months for rental/electric/water
    if (['rentalTable','electricTable','waterTable'].includes(tableId)) {
        let type = tableId === 'rentalTable' ? 1 : (tableId === 'electricTable' ? 2 : 3);
        let input = table.querySelector('.bill-date');
        disableUsedMonths(input, type);
    }
    updateTotal();
    saveFormToSession();
}
function closeTable(closeBtn) {
    closeBtn.closest('.inserted-table').remove();
    updateTotal();
    saveFormToSession(); // Save after removing
}
function setToday(btn) {
    var input = btn.closest('.row').querySelector('.bill-date');
    if (input.type === "month") {
        var now = new Date();
        var month = (now.getMonth() + 1).toString().padStart(2, '0');
        input.value = now.getFullYear() + '-' + month;
    } else {
        input.valueAsDate = new Date();
    }
}
function updateTotal() {
    let total = 0;
    document.querySelectorAll('.bill-amount').forEach(input => {
        if (input.closest('.inserted-table')) {
            total += parseFloat(input.value) || 0;
        }
    });
    document.querySelector('.total-label').textContent = 'total: ₱' + total.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
}
// Save image preview as DataURL in sessionStorage
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('imgPreview');
    const container = document.getElementById('imgPreviewContainer');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
            // Save DataURL to sessionStorage
            let data = sessionStorage.getItem(BILL_FORM_KEY);
            data = data ? JSON.parse(data) : {};
            data['imgPreviewDataURL'] = e.target.result;
            sessionStorage.setItem(BILL_FORM_KEY, JSON.stringify(data));
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '';
        container.style.display = 'none';
        // Remove DataURL from sessionStorage
        let data = sessionStorage.getItem(BILL_FORM_KEY);
        data = data ? JSON.parse(data) : {};
        delete data['imgPreviewDataURL'];
        sessionStorage.setItem(BILL_FORM_KEY, JSON.stringify(data));
    }
}

// Key for sessionStorage
const BILL_FORM_KEY = 'canteen_bill_form';

// Save form data to sessionStorage
function saveFormToSession() {
    const form = document.querySelector('form');
    let data = {};
    // Save all single-value fields
    form.querySelectorAll('input:not([name^="bill_"]), select:not([name^="bill_"]), textarea:not([name^="bill_"])').forEach(input => {
        if (input.type === 'file') return;
        if (input.type === 'checkbox' || input.type === 'radio') {
            data[input.name] = input.checked;
        } else {
            data[input.name] = input.value;
        }
    });
    // Save all bill tables as array of objects
    data['bill_tables'] = [];
    document.querySelectorAll('.inserted-table').forEach(table => {
        let bill = {};
        bill.type = table.getAttribute('data-bill-type');
        bill.date = table.querySelector('.bill-date') ? table.querySelector('.bill-date').value : '';
        bill.amount = table.querySelector('.bill-amount') ? table.querySelector('.bill-amount').value : '';
        bill.name_other = table.querySelector('.bill-other-name') ? table.querySelector('.bill-other-name').value : '';
        data['bill_tables'].push(bill);
    });
    // Save image preview
    if (document.getElementById('imgPreview').src) {
        data['imgPreviewDataURL'] = document.getElementById('imgPreview').src;
    }
    sessionStorage.setItem(BILL_FORM_KEY, JSON.stringify(data));
}

</script>

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
<script>
document.addEventListener('DOMContentLoaded', function() {
    var msgBox = document.querySelector('.messenger-messages');
    if (msgBox) {
        msgBox.scrollTop = msgBox.scrollHeight;
    }
});
</script>
<script>
const orInput = document.getElementById('officialReceiptNumber');
const saveBtn = document.querySelector('form button[type="submit"]');

orInput.addEventListener('input', function() {
    const orNo = orInput.value.trim();
    if (!orNo) {
        orInput.classList.remove('is-invalid');
        saveBtn.disabled = false;
        return;
    }
    fetch('check_or_no.php?or_no=' + encodeURIComponent(orNo))
        .then(res => res.json())
        .then(data => {
            if (data.exists) {
                orInput.classList.add('is-invalid');
                saveBtn.disabled = true;
            } else {
                orInput.classList.remove('is-invalid');
                saveBtn.disabled = false;
            }
        });
});
</script>
<style>
.is-invalid {
    border-color: #dc3545 !important;
    background-color: #f8d7da !important;
}
</style>
<script>
function disableUsedMonths(input, type) {
    fetch('get_used_months.php?type=' + type)
        .then(res => res.json())
        .then(months => {
            input.addEventListener('input', function() {
                if (months.includes(this.value)) {
                    this.setCustomValidity('This month is already used.');
                    this.reportValidity();
                } else {
                    this.setCustomValidity('');
                }
            });
            // Prevent selecting a used month immediately
            input.addEventListener('change', function() {
                if (months.includes(this.value)) {
                    this.value = '';
                    this.setCustomValidity('This month is already used.');
                    this.reportValidity();
                } else {
                    this.setCustomValidity('');
                }
            });
        });
}
</script>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    var imgInput = document.getElementById('officialReceiptImg');
    if (!imgInput.files || imgInput.files.length === 0) {
        alert('You need to input an image to save.');
        imgInput.focus();
        e.preventDefault();
        return false;
    }
});
</script>
</body>
</html>
<?php
if (!empty($file_paths)) {
    foreach ($file_paths as $fp) {
        echo "<!-- Uploaded: $fp -->";
        echo "<!-- Full path: " . realpath(__DIR__ . '/../admin_user/' . $fp) . " -->";
    }
}
?>