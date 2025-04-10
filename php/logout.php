<?php
session_start();
session_destroy();
header("Location: employee-login.html");
exit();
?>