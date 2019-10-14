<?php

require_once 'conexao.php';
require_once 'handler.php';

function getPendencias()
{
    global $conexao;
    $st = $conexao->query('SELECT * FROM pendencias');

    $pendencias = [];

    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $pendencias[] = $row;
    }

    return json_encode($pendencias);
}

function insertPendencia($request)
{
    return insertUpdatePendencia($request);
}

function updatePendencia($request)
{
    return insertUpdatePendencia($request, true);
}

function insertUpdatePendencia($request, $update = false)
{
    global $conexao;

    if (!isset($request['id']) || empty($request['id'])) {
        throw new Exception('ID obrigatório!');
    }

    $id = $request['id'];
    $query = $conexao->query("SELECT COUNT(*) FROM pendencias WHERE id = $id");
    $existeRegistro = ($query->fetchColumn() > 0);

    if ($update && !$existeRegistro) {
        throw new Exception("Não há registro no banco de dados com o ID $id.");
    } elseif (!$update && $existeRegistro) {
        throw new Exception("Já existe um registro com o ID $id.");
    }

    $titulo = isset($request['titulo']) ? $request['titulo'] : null;
    $descricao = isset($request['descricao']) ? $request['descricao'] : null;
    $dataHora = isset($request['data_hora']) ? $request['data_hora'] : date('Y-m-d H:i:s', strtotime('now'));
    $dataLembrete = isset($request['data_lembrete']) ? $request['data_lembrete'] : null;

    $sql = $update
        ? "UPDATE pendencias SET titulo = coalesce(?, '<Nota Sem Título>'), descricao = ?, data_hora = ?, data_lembrete = ? WHERE id = $id"
        : "INSERT INTO pendencias (id, titulo, descricao, data_hora, data_lembrete) VALUES ($id, coalesce(?, '<Nota Sem Título>'), ?, ?, ?)";

    $st = $conexao->prepare($sql);
    if ($st->execute([$titulo, $descricao, $dataHora, $dataLembrete])) {
        return json_encode(['sucesso' => 'true', 'mensagem' => 'Registro '.($update ? 'atualizado' : 'adicionado').' com sucesso.']);
    } else {
        throw new Exception('Erro ao '.($update ? 'atualizar' : 'adicionar').' pendência: '.$st->errorInfo()[2]);
    }
}

function sincronizarPendencias($request)
{
    global $conexao;
    $listaPendencias = [];

    $pendencias = isset($request['pendencias']) ? $request['pendencias'] : [];

    foreach ($pendencias as $pendencia) {
        if (!isset($pendencia['id']) || empty($pendencia['id'])) {
            throw new Exception('Um ou mais registros não têm ID');
        }

        $id = $pendencia['id'];
        $sync = isset($pendencia['sync']) ? $pendencia['sync'] : false;
        $idsDeletar = [];
        $query = $conexao->query("SELECT * FROM pendencias WHERE id=$id");
        if ($query && $row = $query->fetch(PDO::FETCH_ASSOC)) {
            if (isset($pendencia['delete']) && $pendencia['delete']) {
                $idsDeletar[] = $id;
                continue;
            }

            //Se a pendência estiver marcada como sincronizada, ela será substituída com os dados do servidor;
            //caso contrário, insere a pendência no servidor e marca como sincronizada;
            if ($sync) {
                $pendencia = $row;
            } else {
                insertUpdatePendencia($pendencia, true);
            }
            $pendencia['sync'] = true;
            $listaPendencias[] = $pendencia;
        } else {
            if (!$sync) {
                insertUpdatePendencia($pendencia);
                $pendencia['sync'] = true;
                $listaPendencias[] = $pendencia;
            } else {
                $idsDeletar[] = $id;
            }
        }
    }

    if (count($idsDeletar) > 0) {
        $conexao->exec('DELETE FROM pendencias where id in ('.implode(',', $idsDeletar).')');
    }

    $pendencias = json_decode(getPendencias(), true) ?: [];

    foreach ($pendencias as $i => $pendencia) {
        $pendencia['sync'] = true;
        $pendencias[$i] = $pendencia;
    }    

    return json_encode($pendencias);
}
