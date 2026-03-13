<?php

require_once __DIR__ . '/auth.php';
logoutUser();
header('Location: index.php?msg=You have been signed out.');
exit;
