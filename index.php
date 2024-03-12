<?php

include "vendor\autoload.php";
use IsraelNogueira\NBClassifier\NBClassifier;


$cat_1 = "amor";
$cat_2 = "raiva";

$_teste = new NBClassifier();

$_teste->APRENDE("Nossa capacidade de amar é limitada, e o amor infinito; este é o drama",$cat_1);
$_teste->APRENDE("Não sei amar pela metade, não sei viver de mentiras, não sei voar com os pés no chão",$cat_1);
$_teste->APRENDE("Quero que todos os dias do ano, todos os dias da vida, de meia em meia hora, de cinco em cinco minutos me digas: eu te amo",$cat_1);
$_teste->APRENDE("Não importa a distância que nos separa, se há um céu que nos une",$cat_1);
$_teste->APRENDE("O mundo é grande e cabe nesta janela sobre o mar. O mar é grande e cabe na cama e no colchão de amar. O amor é grande e cabe no breve espaço de beijar",$cat_1);
$_teste->FINISH();

print_r($_teste->CLASSIFICA('Nossa capacidade de amar é limitada, e o amor infinito; este é o drama'));

/*
	Array
	(
		[PROBABILIDADE] => 16.75
		[CATEGORIA] => amor
		[PROBABILIDADE] => 0.12
		[CATEGORIA] => raiva
	)
 */


?>
