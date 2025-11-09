<?php

//Iniciar sesión
session_start();

//destruir todas las variables de la sesión
session_destroy();

//Redirigir al login
header('Location: ../../Landing/login.html');
exit();

?>