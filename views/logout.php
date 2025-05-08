<?php
// views/logout.php
session_destroy();
header('Location: ?page=home');
exit;
?>