
# Categorização de texto com Naive Bayes 

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

## Crie na raiz um arquivo ".env":
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
	use IsraelNogueira\naivebayes\naivebayes;
	
	/*
	|------------------------------------------------
	|  TREINAMENTO
	|------------------------------------------------
	*/

		$_NERVOSO   = "NERVOSO";
		$_FELIZ     = "FELIZ";

		$_TREINAMENTO = new naivebayes();

		$_TREINAMENTO->APRENDE("Estou extremamente frustrado com a qualidade deste produto!", $_NERVOSO);
		$_TREINAMENTO->APRENDE("Este produto não atende às minhas expectativas.", $_NERVOSO);
		$_TREINAMENTO->APRENDE("Estou furioso com a falta de durabilidade deste produto.", $_NERVOSO);
		$_TREINAMENTO->APRENDE("Este produto é um verdadeiro pesadelo!.", $_NERVOSO);

		$_TREINAMENTO->APRENDE("Este produto realmente faz a diferença. É uma compra que vale cada centavo!", $_FELIZ);
		$_TREINAMENTO->APRENDE("Recebi muitos elogios desde que comecei a usar este produto. É fenomenal!", $_FELIZ);
		$_TREINAMENTO->APRENDE("Parabéns à equipe por criar um produto tão excelente! Estou muito satisfeito!", $_FELIZ);
		$_TREINAMENTO->APRENDE("Este produto é tudo o que eu precisava e mais um pouco. Simplesmente maravilhoso!", $_FELIZ);

		$_TREINAMENTO->FINISH();

?>
```

Como classificar um texto:

```php
<?php

	include "vendor\autoload.php";
	use IsraelNogueira\naivebayes\naivebayes;
		
	$_CLASSIFICA = new naivebayes();
	$RESULT = $_CLASSIFICA->CLASSIFICA('Vocês estão de parabéns! Esse produto é excelente! Estou muito satisfeito');
	
	print_r($RESULT);


?>
