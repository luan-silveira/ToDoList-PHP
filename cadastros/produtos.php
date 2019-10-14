<?php

include_once '/conexao.php';

if (isset($_REQUEST['f'])){
	$f = $_REQUEST['f'];
	unset($_REQUEST['f']);
	$json = (array) json_decode(file_get_contents('php://input'));
	$f(array_merge($_REQUEST, $json));
	
}

function store($request){
	global $conexao;

	$query = "INSERT INTO produtos (codigo, descricao, preco) VALUES ({$request['codigo']}, {$request['descricao']}, {$request['preco']})";
	$res = mysqli_query($conexao, $query);

	return json_encode(['sucesso' => true]);
}