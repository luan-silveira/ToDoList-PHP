<?php

error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: application/json');

if (!isset($_POST['numero'])){
	echo json_encode(['erro' => 'Número não informado']);
	exit();
}

require_once 'extenso.php';

$numero = $_POST['numero'];

echo json_encode(['resultado' => extenso($numero)]);