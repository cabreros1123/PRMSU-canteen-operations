<?php
require_once "db.php";
if(isset($_POST['category_no'], $_POST['status'])) {
    $category_no = intval($_POST['category_no']);
    $status = intval($_POST['status']);

    // Get the or_no for this category_no
    $stmt = $conn->prepare("SELECT or_no FROM bills_img WHERE id = ?");
    $stmt->bind_param("i", $category_no);
    $stmt->execute();
    $stmt->bind_result($or_no);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Bill not found']);
        exit;
    }
    $stmt->close();

    // Get current status of this bill
    $stmt = $conn->prepare("SELECT ver_status FROM bills WHERE category_no = ?");
    $stmt->bind_param("i", $category_no);
    $stmt->execute();
    $stmt->bind_result($current_status);
    $stmt->fetch();
    $stmt->close();

    // If current bill is Declined and trying to change status
    if ($current_status == 0 && $status != 0) {
        // Check if any other bill with same or_no is Verified or Pending
        $stmt = $conn->prepare("SELECT COUNT(*) FROM bills WHERE or_no = ? AND category_no != ? AND ver_status IN (1,2)");
        $stmt->bind_param("si", $or_no, $category_no);
        $stmt->execute();
        $stmt->bind_result($active_count);
        $stmt->fetch();
        $stmt->close();

        if ($active_count > 0) {
            echo json_encode(['success' => false, 'error' => 'Cannot change status: another bill with same Official Receipt No. is verified or pending.']);
            exit;
        }
    }

    // Otherwise, allow update
    $result = $conn->query("UPDATE bills SET ver_status=$status WHERE category_no=$category_no");
    echo json_encode(['success' => $result ? true : false]);
} else {
    echo json_encode(['success' => false]);
}