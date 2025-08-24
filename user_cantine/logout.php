<?php
session_start();

// Destroy the session
session_unset();
session_destroy();

// Redirect to the login page
header("Location: ../cantine_login.php");
exit();
?>

<script>
localStorage.removeItem('notifiedProducts');
</script>