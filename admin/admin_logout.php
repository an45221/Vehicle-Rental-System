<?php
session_cache_limiter('private_no_expire');
session_start();
session_destroy();
header("Location: admin_login.php");
exit();
?>