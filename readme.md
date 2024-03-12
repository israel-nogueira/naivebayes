
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


Como treinar o seu modelo:

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
