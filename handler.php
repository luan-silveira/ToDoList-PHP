<?php

function gravarLog($dados)
{
    $filename = 'log.log';
    return file_put_contents($filename, $dados . "\n", FILE_APPEND);
}

header('Content-Type: application/json');
set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) {
    $error = "$errstr at line $errline at file $errfile";
    throw new ErrorException($error, 0, 1, $errfile, $errline);
}, E_ALL & ~E_NOTICE);

set_exception_handler(function ($e) {
    echo json_encode([
        'erro' => true,
        'mensagem' => $e->getMessage(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});

try {
    $json = json_decode(file_get_contents('php://input'), true) ?: [];
    $request = array_merge($_REQUEST, $json);
    if (isset($request['f']) && !empty($request['f'])) {
        $f = $request['f'];
        unset($request['f']);

        if (!function_exists($f)) {
            $p = implode($request, ', ');
            throw new Exception("A função '$f($p)' não existe.");
        }

        gravarLog('JSON: ' . file_get_contents('php://input') . "\n");
        gravarLog("Request: \n");
        gravarLog(print_r($request, true) . "\n\n");

        echo $f($request);
    } else {
        throw new Exception('Função não informada');
    }
} catch (Exception $e) {
    echo json_encode([
        'erro' => true,
        'mensagem' => $e->getMessage(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
