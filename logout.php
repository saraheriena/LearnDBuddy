<?php
session_start();

// buang semua session
session_unset();
session_destroy();

// balik ke login page
header("Location: index.php");
exit;
?>
