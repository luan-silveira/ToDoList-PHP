<?php

const UNIDADES = [1 => 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove'];
const UNIDADES_DEZ = [1 => 'onze', 'doze', 'treze', 'quatorze', 'quinze', 'dezesseis', 'dezessete', 'dezoito', 'dezenove'];
const DEZENAS = [1 => 'dez', 'vinte', 'trinta', 'quarenta', 'cinquenta', 'sessenta', 'setenta', 'oitenta', 'noventa'];
const CENTENAS = [1 => 'cento', 'duzentos', 'trezentos', 'quatrocentos', 'quinhentos', 'seiscentos', 'setecentos', 'oitocentos', 'novecentos'];
const CLASSES = [1 => 'mil', 'mi', 'bi', 'tri', 'quatri', 'quinti'];

//tipos
const UNIDADE = 0;
const DEZENA = 1;
const CENTENA = 2;

/**
 * Função utilizada para obter o valor de um número por extenso
 * 
 * @author Luan Christian Nascimento da Silveira
 */
function extenso($numero){
    if (!$numero) {
        return 'zero';
    }

    $extenso = '';
    if ($numero < 0) {
        $extenso .= 'menos ';
        $numero = abs($numero);
    }

    //Agrupa os dígitos em grupos de 3 de acordo com a posição de separação dos milhares
    $grupoMilhares = explode(',', number_format($numero));
    $tamanho = count($grupoMilhares);
    
    foreach ($grupoMilhares as $idx => $n) {
        $n = intval($n);
        if ($n == 0) continue;

        /**
         * Posição do milhar de acordo com o grupo:
         * 
         * 0 - Unidades/Dezenas/Centenas
         * 1 - milhares
         * 2 - milhões
         * 3 - bilhões
         * 4 - trilhões
         * ...
         */
        $posicaoMilhar = $tamanho - $idx - 1;

        $i = 0;
        $isUnidade10 = false;
        $recebeE = ($idx > 0 && ($n < 100 || $n % 100 == 0)) ;
        if ($recebeE) $extenso .= 'e ';

        if ($posicaoMilhar == 1 && $n == 1){
            $extenso .= CLASSES[1] . ' ';
            continue;
        }

        $nAux = $n;
        while ($n > 0) {
            $expoente10 = strlen($n) - 1;
            $digito = substr($n, 0, 1);
            $tipo = ($expoente10 % 3);
            $vlRelativo = $digito * (10 ** $expoente10);
            $n -= $vlRelativo;

            $valor = '';
            $recebeE = ($i > 0);

            if ($digito > 0) {
                if (CENTENA == $tipo) {
                    $valor = (1 == $digito && 0 == $n ? 'cem' : CENTENAS[$digito]);
                    $recebeE = $recebeE && (0 == $n);
                } elseif (DEZENA == $tipo) {
                    $isUnidade10 = (1 == $digito);
                    if ($isUnidade10) {
                        if (0 == $n) {
                            $valor = DEZENAS[1];
                        } else {
                            $recebeE = false;
                        }
                    } else {
                        $valor = DEZENAS[$digito];
                    }
                } else {
                    if ($isUnidade10) {
                        $valor = UNIDADES_DEZ[$digito];
                        $isUnidade10 = false;
                        $recebeE = ($i > 1);
                    } else {
                        $valor = UNIDADES[$digito];
                    }
                }

                if ($valor) $extenso .= ($recebeE ? 'e ' : '')."$valor ";
            }
            ++$i;
        }

        if ($posicaoMilhar > 0){
            $extenso .= CLASSES[$posicaoMilhar];
            if ($posicaoMilhar > 1) $extenso .= 'lh' . ($nAux > 1 ? 'ões' : 'ão');
            $extenso .= ' ';
        }
    }

    return trim($extenso);
}