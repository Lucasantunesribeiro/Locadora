<?php
session_start();
session_unset();
session_destroy();
header('Location: /locadora_de_carros/src/views/login.html');
exit;
?>
