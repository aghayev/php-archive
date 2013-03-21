<?php

require_once ( '../config/bootstrap.php');

$controller = router::getController();
$controller->execute();



