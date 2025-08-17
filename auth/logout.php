<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php"); // vì đang ở auth/, login.php nằm cùng thư mục
exit();
?>
