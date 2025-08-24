<?php
include 'db.php';

// Handle Add Canteen
if (isset($_POST['add_cantine'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $owner = $_POST['owner'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stall_no = $_POST['stall_no'];

    // Check for duplicate stall_no
    $stmt = $conn->prepare("SELECT id FROM cantines WHERE stall_no = ? AND del_status = 0");
    $stmt->bind_param("s", $stall_no);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<script>alert('Stall No. $stall_no already exists!');window.history.back();</script>";
        exit();
    }
    $stmt->close();

    $query = "INSERT INTO cantines (name, email, phone, owner, username, password, stall_no, registerDate, del_status, active) 
              VALUES ('$name', '$email', '$phone', '$owner', '$username', '$password', '$stall_no', NOW(), 0, 0)";
    mysqli_query($conn, $query);

    header("Location: cantine.php");
    exit();
}

// Handle Edit Canteen
if (isset($_POST['edit_cantine'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $owner = $_POST['owner'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stall_no = $_POST['stall_no'];

    // Check for duplicate stall_no (exclude current canteen)
    $stmt = $conn->prepare("SELECT id FROM cantines WHERE stall_no = ? AND id != ? AND del_status = 0");
    $stmt->bind_param("si", $stall_no, $id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<script>alert('Stall No. $stall_no already exists!');window.history.back();</script>";
        exit();
    }
    $stmt->close();

    $query = "UPDATE cantines 
              SET name = '$name', email = '$email', phone = '$phone', owner = '$owner', username = '$username', password = '$password', stall_no = '$stall_no' 
              WHERE id = $id";
    mysqli_query($conn, $query);

    header("Location: cantine.php");
    exit();
}

// Handle Delete Canteen (Soft Delete)
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $query = "UPDATE cantines SET del_status = 1 WHERE id = $id";
    mysqli_query($conn, $query);

    header("Location: cantine.php");
    exit();
}

// Handle Activation/Deactivation
if (isset($_GET['toggle_active_id'])) {
    $id = $_GET['toggle_active_id'];
    $currentStatus = $_GET['current_status'];

    // Toggle the active status
    $newStatus = $currentStatus == 0 ? 1 : 0;
    $query = "UPDATE cantines SET active = $newStatus WHERE id = $id";
    mysqli_query($conn, $query);

    header("Location: cantine.php");
    exit();
}

// Fetch All Canteens where del_status = 0
$notif_cantines = [];
$sql = "SELECT * FROM cantines WHERE del_status = 0";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $notif_cantines[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Canteens</title>
    <link rel="stylesheet" href="css/cantine.css">
    <link rel="icon" type="image/png" href="img/icono-negro.png">
</head>
<body>
<?php require_once "sidebar.php"; ?>
<?php require_once "header.php"; ?>

<div class="container">
    <h2>Manage Canteens</h2>

    <!-- Add Canteen Button -->
    <button class="btn-add-cantine" onclick="openAddModal()">Add Canteen</button>

    <!-- Add/Edit Canteen Modal -->
    <div id="cantineModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <h3 id="modalTitle">Add Canteen</h3>
            <br><br>
            <form action="cantine.php" method="POST" class="modal-form">
                <input type="hidden" name="id" id="cantineId">
                <div class="form-group">
                    <label for="cantineName">Name:</label>
                    <input type="text" name="name" id="cantineName" required>
                </div>
                <div class="form-group">
                    <label for="cantineEmail">Email:</label>
                    <input type="email" name="email" id="cantineEmail" required>
                </div>
                <div class="form-group">
                    <label for="cantinePhone">Phone:</label>
                    <input type="text" name="phone" id="cantinePhone" required>
                </div>
                <div class="form-group">
                    <label for="cantineOwner">Owner:</label>
                    <input type="text" name="owner" id="cantineOwner" required>
                </div>
                <div class="form-group">
                    <label for="cantineUsername">Username:</label>
                    <input type="text" name="username" id="cantineUsername" required>
                </div>
                <div class="form-group">
                    <label for="cantinePassword">Password:</label>
                    <input type="password" name="password" id="cantinePassword" required>
                </div>
                <div class="form-group">
                    <label for="cantineStallNo">Stall No.:</label>
                    <input type="text" name="stall_no" id="cantineStallNo" required>
                </div>
                <button type="submit" name="add_cantine" id="modalSubmitButton">Save</button>
            </form>
        </div>
    </div>

    <!-- Canteen Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Password</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Owner</th>
                <th>Last Login</th>
                <th>Register Date</th>
                <th>Status</th> <!-- New column for status -->
                <th>Stall No.</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notif_cantines as $row) : ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td data-label="Name"><?php echo $row['name']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td>
                        <?php 
                        $password = $row['password'];
                        $maskedPassword = strlen($password) > 2 
                            ? str_repeat('*', strlen($password) - 2) . substr($password, -2) 
                            : str_repeat('*', strlen($password));
                        echo $maskedPassword;
                        ?>
                    </td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['phone']; ?></td>
                    <td><?php echo $row['owner']; ?></td>
                    <td><?php echo $row['Last_login']; ?></td>
                    <td><?php echo $row['registerDate']; ?></td>
                    <td>
                        <!-- Status Button -->
                        <a href="cantine.php?toggle_active_id=<?php echo $row['id']; ?>&current_status=<?php echo $row['active']; ?>" 
                           class="btn-status <?php echo $row['active'] == 0 ? 'active' : 'deactive'; ?>" 
                           onclick="return confirm('Are you sure you want to <?php echo $row['active'] == 0 ? 'deactivate' : 'activate'; ?> this canteen?');">
                            <?php echo $row['active'] == 0 ? 'Active' : 'Deactive'; ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($row['stall_no']); ?></td>
                    <td>
                        <button class="btn-edit" onclick="openEditModal(
                            '<?php echo $row['id']; ?>',
                            '<?php echo htmlspecialchars($row['name']); ?>',
                            '<?php echo htmlspecialchars($row['email']); ?>',
                            '<?php echo htmlspecialchars($row['phone']); ?>',
                            '<?php echo htmlspecialchars($row['owner']); ?>',
                            '<?php echo htmlspecialchars($row['username']); ?>',
                            '<?php echo htmlspecialchars($row['password']); ?>',
                            '<?php echo htmlspecialchars($row['stall_no']); ?>'
                        )">Edit</button>
                        <a href="cantine.php?delete_id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to remove this canteen?');">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function openAddModal() {
        document.getElementById('cantineModal').style.display = 'flex';
        document.getElementById('modalTitle').innerText = 'Add Canteen';
        document.getElementById('modalSubmitButton').name = 'add_cantine';
        document.getElementById('cantineId').value = '';
        document.getElementById('cantineName').value = '';
        document.getElementById('cantineEmail').value = '';
        document.getElementById('cantinePhone').value = '';
        document.getElementById('cantineOwner').value = '';
        document.getElementById('cantineUsername').value = '';
        document.getElementById('cantinePassword').value = '';
        document.getElementById('cantineStallNo').value = '';
    }
    function openEditModal(id, name, email, phone, owner, username, password, stall_no) {
        document.getElementById('cantineModal').style.display = 'flex';
        document.getElementById('modalTitle').innerText = 'Edit Canteen';
        document.getElementById('modalSubmitButton').name = 'edit_cantine';
        document.getElementById('cantineId').value = id;
        document.getElementById('cantineName').value = name;
        document.getElementById('cantineEmail').value = email;
        document.getElementById('cantinePhone').value = phone;
        document.getElementById('cantineOwner').value = owner;
        document.getElementById('cantineUsername').value = username;
        document.getElementById('cantinePassword').value = password;
        document.getElementById('cantineStallNo').value = stall_no;
    }
    function closeModal() {
        document.getElementById('cantineModal').style.display = 'none';
    }

    // Close modal on overlay click or ESC key
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('cantineModal');
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === "Escape") closeModal();
        });
    });
</script>

</body>
</html>