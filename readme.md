
# Processamento de linguagem natural

  Esta classe PHP serve como componente central em um sistema 
  de processamento de linguagem natural, especialmente voltado 
  para aplicações de aprendizado de máquina. 
  
  Durante a inicialização, ela estabelece conexões com o banco de dados 
  para recuperar informações essenciais, como palavras proibidas (stop words) 
  e categorias usadas no treinamento do modelo. 
  
  A classe organiza esses dados para facilitar o uso em algoritmos 
  de classificação de texto e outras operações relacionadas 
  ao processamento de linguagem natural.
  
  É uma peça fundamental para preparar e manipular dados, 
  tornando-se indispensável para análises e predições 
  na área de processamento de linguagem natural.

## Instale com o composer:
```
composer require israel-nogueira/naivebayes

```

## Crie um arquivo "mysql.env":
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=NAIVE_BAYES
DB_USERNAME=root
DB_PASSWORD=
DB_TYPE=mysql
DB_CHAR=
DB_FLOW=
DB_FKEY=
```

## Configure sua base de dados:
```SQL
CREATE DATABASE IF NOT EXISTS NAIVE_BAYES;
```

## Agora crie as tabelas corretamente:
```SQL
CREATE TABLE IF NOT EXISTS `MACHINE_LEARNING` (
`ID` int(11) NOT NULL AUTO_INCREMENT,
`CATEGORIA` varchar(255) NOT NULL,
`TREINOS` int(11) NOT NULL DEFAULT 0,
`PALAVRAS` text NOT NULL,
PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=23;

CREATE TABLE IF NOT EXISTS `BLACK_LIST_WORDS` (
`ID` int(11) NOT NULL AUTO_INCREMENT,
`PALAVRA` varchar(255) NOT NULL,
PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3;

```

## Treinando seu modelo:
Crie um arquivo *index.php* e insira isso:

```php
<?php
	include "vendor\autoload.php";
	use IsraelNogueira\Dotenv\env;
	use IsraelNogueira\naivebayes\naivebayes;
			   
	/*
	|------------------------------------------------
	|  LÊ AS VARIÁVEIS DE CONEXÃO A BASE
	|------------------------------------------------
	*/

		env::install(__DIR__.'/mysql.env');

	/*
	|------------------------------------------------
	|  TREINAMENTO
	|------------------------------------------------
	*/

		$insatisfeito = "insatisfeito";
		$satisfeito = "satisfeito";

		$_TREINAMENTO = new naivebayes();
				
		$_TREINAMENTO->APRENDE("Estou extremamente frustrado com a qualidade deste produto; não faz nada do que promete!",$insatisfeito);
		$_TREINAMENTO->APRENDE("Que desperdício de dinheiro! Este produto é uma completa decepção e não atende às minhas expectativas.",$insatisfeito);
		$_TREINAMENTO->APRENDE("Estou furioso com a falta de durabilidade deste produto. Que desperdício total de recursos.",$insatisfeito);
		$_TREINAMENTO->APRENDE("Este produto é um verdadeiro pesadelo! Não funciona corretamente e me causou muitos problemas.",$insatisfeito);
		$_TREINAMENTO->APRENDE("Estou indignado com o péssimo serviço ao cliente associado a este produto. Nunca mais comprarei nada dessa empresa.",$insatisfeito);
		$_TREINAMENTO->APRENDE("O produto não funcionou conforme o esperado.",$insatisfeito);
		$_TREINAMENTO->APRENDE("O prazo de entrega do produto foi muito longo.",$insatisfeito);
		$_TREINAMENTO->APRENDE("O produto veio com peças faltando.",$insatisfeito);
		$_TREINAMENTO->APRENDE("Não recomendo este produto, pois não cumpre o que promete.",$insatisfeito);
		$_TREINAMENTO->APRENDE("O produto chegou danificado e não consigo entrar em contato com a empresa para resolver o problema.",$insatisfeito);

		$_TREINAMENTO->APRENDE("Este produto superou todas as minhas expectativas! É incrível!",$satisfeito);
		$_TREINAMENTO->APRENDE("Estou impressionado com a qualidade e o desempenho deste produto. Recomendo!",$satisfeito);
		$_TREINAMENTO->APRENDE("Não consigo mais viver sem este produto. Ele facilitou muito a minha vida!",$satisfeito);
		$_TREINAMENTO->APRENDE("Estou completamente apaixonado por este produto. É simplesmente perfeito!",$satisfeito);
		$_TREINAMENTO->APRENDE("Que inovação fantástica! Este produto é uma verdadeira revolução!",$satisfeito);
		$_TREINAMENTO->APRENDE("Estou encantado com a eficácia deste produto. Nunca vi nada igual!",$satisfeito);
		$_TREINAMENTO->APRENDE("Este produto realmente faz a diferença. É uma compra que vale cada centavo!",$satisfeito);
		$_TREINAMENTO->APRENDE("Recebi muitos elogios desde que comecei a usar este produto. É fenomenal!",$satisfeito);
		$_TREINAMENTO->APRENDE("Parabéns à equipe por criar um produto tão excelente! Estou muito satisfeito!",$satisfeito);
		$_TREINAMENTO->APRENDE("Este produto é tudo o que eu precisava e mais um pouco. Simplesmente maravilhoso!",$satisfeito);

		$_TREINAMENTO->FINISH();

?>
```

Como classificar um texto:

```php
<?php

	include "vendor\autoload.php";
	use IsraelNogueira\naivebayes\naivebayes;
		
	$_teste = new naivebayes();
	$result1 = $_teste->CLASSIFICA('Estou tão irritado com esse produto que mal posso expressar minha frustração');
	$result2 = $_teste->CLASSIFICA('Este produto superou todas as minhas expectativas; estou radiante com a minha compra');

?>
