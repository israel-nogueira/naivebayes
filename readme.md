
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
composer require israel-nogueira/nbclassifier

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
	use IsraelNogueira\NBClassifier\NBClassifier;
			   
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

		$sentimento_1 = "amor";
		$sentimento_2 = "raiva";

		$_TREINAMENTO = new NBClassifier();
		$_TREINAMENTO->APRENDE("Seu texto 1",$sentimento_1);
		$_TREINAMENTO->APRENDE("Seu texto 2",$sentimento_1);
		$_TREINAMENTO->APRENDE("Seu texto 3",$sentimento_1);
		$_TREINAMENTO->APRENDE("Seu texto 4",$sentimento_2);
		$_TREINAMENTO->APRENDE("Seu texto 5",$sentimento_2);
		$_TREINAMENTO->FINISH();

?>
```

Como classificar um texto:

```php
<?php

	include "vendor\autoload.php";
	use IsraelNogueira\NBClassifier\NBClassifier;
		
	$_teste = new NBClassifier();
	$result1 = $_teste->CLASSIFICA('Seu texto 1');
	$result2 = $_teste->CLASSIFICA('Seu texto 2');
	$result3 = $_teste->CLASSIFICA('Seu texto 3');

?>
