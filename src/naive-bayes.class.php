<?php
/**
 * Esta classe PHP serve como componente central em um sistema 
 * de processamento de linguagem natural, especialmente voltado 
 * para aplicações de aprendizado de máquina. 
 * 
 * Durante a inicialização, ela estabelece conexões com o banco de dados 
 * para recuperar informações essenciais, como palavras proibidas (stop words) 
 * e categorias usadas no treinamento do modelo. 
 * 
 * A classe organiza esses dados para facilitar o uso em algoritmos 
 * de classificação de texto e outras operações relacionadas 
 * ao processamento de linguagem natural.
 * 
 * É uma peça fundamental para preparar e manipular dados, 
 * tornando-se indispensável para análises e predições 
 * na área de processamento de linguagem natural.
 *
 * @category   Machine Learning
 * @package    IsraelNogueira\NBClassifier
 * @author     Israel Nogueira
 * @license    MIT
 * @link       https://github.com/israel-nogueira/NBClassifier
 * @version    1.0.0
 */

	namespace IsraelNogueira\NBClassifier\NBClassifier;
	use IsraelNogueira\galaxyDB\galaxyDB;
	class NBClassifier {

			/*
			|-------------------------------------------------
			| PUXAMOS A MEMÓRIA
			|-------------------------------------------------
			|
			| Inicializa instâncias de duas tabelas do banco de dados, 
			| sendo uma responsável por armazenar palavras proibidas (stop words) 
			| e outra por categorias utilizadas em aprendizado de máquina. 
			|
			| As palavras proibidas e as categorias são carregadas durante a inicialização da classe, 
			| preparando os dados necessários para os algoritmos de classificação subsequentes. 
			|
			| Selecionamos as informações relevantes das tabelas 
			| e organiza os dados em estruturas de fácil acesso para a classe.
			|
			|-------------------------------------------------
			*/
				public function __construct() {
					$STOP_WORDS = new galaxyDB();
					$STOP_WORDS->connect();
					$STOP_WORDS->set_table('BLACK_LIST_WORDS');
					$STOP_WORDS->set_colum('PALAVRA');
					$STOP_WORDS->select('BL');
					
					$_CATEGORIAS = new galaxyDB();
					$_CATEGORIAS->connect();
					$_CATEGORIAS->set_table('MACHINE_LEARNING');
					$_CATEGORIAS->select('ML');

					$this->STOP_WORDS 			= ($STOP_WORDS->_num_rows['BL']==0)?[]:array_values($STOP_WORDS->fetch_array('BL'));
					$this->_ARRAY_CAT			= ($_CATEGORIAS->_num_rows['ML']==0)?[]:array_values($_CATEGORIAS->fetch_array('ML'));
					$this->_STOPWORDS			= array();
					$this->_CATEGORIAS_MYSQL	= array();
					$this->_TOTAL_TREINO		= array();

					foreach ($this->STOP_WORDS as  $value) {
						$this->_STOPWORDS[]=$value; 
					}

					foreach ($this->_ARRAY_CAT as  $value) {
						$this->_TOTAL_TREINO[$value['CATEGORIA']]		= $value['TREINOS'];
						$this->_CATEGORIAS_MYSQL[$value['CATEGORIA']]	= ($value['PALAVRAS']=="") ? array() : json_decode(stripcslashes($value['PALAVRAS']),1);
					}
				}
			/*
			|-------------------------------------------------
			| CLASSIFICAMOS OS TEXTOS
			|-------------------------------------------------
			| Após tokenizar o texto, decidimos a qual categoria ele pertence
			| e retorna a probabilidade
			|-------------------------------------------------
			*/
				public function CLASSIFICA($sentence) {
					$keywordsArray 	= $this->tokenize($sentence);
					$CATEGORIA 		= $this->decide($keywordsArray);
					return $CATEGORIA;
				}	
			
			
			/*
			|-------------------------------------------------
			| DECIDE AS PROBABILIDADES
			|-------------------------------------------------
			| Essa função implementa um classificador de texto utilizando o algoritmo Naive Bayes.
			|
			| Ela recebe um array de palavras como entrada e calcula a probabilidade de pertencer 
			| a diversas categorias, classificando as palavras com base nesses cálculos. 
			|
			| O resultado é um array ordenado por probabilidade decrescente, 
			| indicando a chance de pertencer a cada categoria. 
			|
			| Cada elemento do array possui informações sobre a 
			| probabilidade e a categoria associada.
			|-------------------------------------------------
			*/
				private function decide($ARRAY_PALAVRAS) {
					$_TOTAL_FREQUENCIA 	= 0;
					$_TOTAL_TREINO 		= 0;
					foreach ($this->_TOTAL_TREINO as  $value) {
						$_TOTAL_TREINO = $_TOTAL_TREINO + $value;
					}
					$_CLASSIFY =array();
					foreach ($this->_CATEGORIAS_MYSQL as $_CHAVECAT => $_ARRAY_PALAVRAS) {
						$_TOTAL_TREINO_COM_A_CATEGORIA			= $this->_TOTAL_TREINO[$_CHAVECAT];
						$_PROBABILIDADE 						= ($_TOTAL_TREINO==0)	?	0	:	log($_TOTAL_TREINO_COM_A_CATEGORIA / $_TOTAL_TREINO);
							foreach ($ARRAY_PALAVRAS as $_PALAVRA) {
								if(
									count($this->_CATEGORIAS_MYSQL[$_CHAVECAT])>0 && 
									array_key_exists($_PALAVRA, $this->_CATEGORIAS_MYSQL[$_CHAVECAT])
								){
									$_PROBABILIDADE += log(($_TOTAL_TREINO) * ($_TOTAL_TREINO_COM_A_CATEGORIA / count($this->_CATEGORIAS_MYSQL[$_CHAVECAT])));
								}
							}
						$_PROBABILIDADE= ( $_PROBABILIDADE < 0 ) ? ($_PROBABILIDADE * -1) : $_PROBABILIDADE;
						$_CLASSIFY[]=array("PROBABILIDADE"=>number_format($_PROBABILIDADE, 2, '.', ' '),'CATEGORIA'=>$_CHAVECAT);
					}
					usort($_CLASSIFY, function($a, $b) {return floatval($a['PROBABILIDADE']) < floatval($b['PROBABILIDADE']);});
					return $_CLASSIFY;
				}


			/*
			|-------------------------------------------------
			| APRENDIZADO
			|-------------------------------------------------
			|
			| Essa função, tem o propósito de treinar o classificador Naive Bayes 
			| com base em uma frase e sua categoria associada. 
			|
			| Ela atualiza as contagens de palavras por categoria, incrementando a 
			| frequência das palavras na categoria específica. 
			|
			| Se a categoria não existir previamente, a função a cria. 
			|
			| Em seguida, atualiza as contagens de palavras na categoria correspondente.
			|
			|-------------------------------------------------
			*/
			public function APRENDE($_FRASE, $CATEGORIA) {

				if(!array_key_exists($CATEGORIA, $this->_CATEGORIAS_MYSQL)){
					$this->_CATEGORIAS_MYSQL[$CATEGORIA]	= array();
					$this->_TOTAL_TREINO[$CATEGORIA]		= 0;
				}

				$this->_TOTAL_TREINO[$CATEGORIA]		= $this->_TOTAL_TREINO[$CATEGORIA]+1;
				$_CATEGORIA_ARRAY_WORD					= $this->_CATEGORIAS_MYSQL[$CATEGORIA];
				$_ARRAY_PALAVRAS						= array_filter($this->tokenize($_FRASE));
				 
				if(!is_null($_CATEGORIA_ARRAY_WORD)){
					foreach ($_ARRAY_PALAVRAS as $_PALAVRA) {
						if(
							count($_CATEGORIA_ARRAY_WORD)==0 || !array_key_exists($_PALAVRA, $_CATEGORIA_ARRAY_WORD)==true
						){
							$_CATEGORIA_ARRAY_WORD[$_PALAVRA]=1;
						}else{
							$_CATEGORIA_ARRAY_WORD[$_PALAVRA] = $_CATEGORIA_ARRAY_WORD[$_PALAVRA]+1;
						}
						$this->_CATEGORIAS_MYSQL[$CATEGORIA]	= $_CATEGORIA_ARRAY_WORD;
					}
				}
			}			

			/*
			|-------------------------------------------------
			| SALVAMOS O APRENDIZADO
			|-------------------------------------------------
			| Conclui o processo de treinamento do classificador de texto,
			| atualizando ou inserindo dados na tabela "MACHINE_LEARNING" de um banco de dados. 
			| 
			| Ela verifica as categorias existentes, converte as palavras associadas 
			| a cada categoria em formato JSON e as armazena na tabela. 
			|
			| O total de treinos para cada categoria também é atualizado. 
			| 
			| A função utiliza duas instâncias da classe "galaxyDB" para realizar operações de inserção e atualização, 
			| finalizando a transação ao executar as consultas SQL.
			|
			|-------------------------------------------------
			*/

			public function FINISH() {

				$_allCats = new galaxyDB();
				$_allCats->connect();
				$_allCats->set_table('MACHINE_LEARNING');
				$_allCats->set_colum('CATEGORIA');
				$_allCats->select('CAT');
				$ARRAY_MACHINE_LEARNING			= ($_allCats->_num_rows['CAT']==0)?[]:array_values($_allCats->fetch_array('CAT'));
				$_VERIFICA_CAT = array();
				foreach ($ARRAY_MACHINE_LEARNING as $_CATEGORIA){$_VERIFICA_CAT[] = $_CATEGORIA['CATEGORIA']; }
				$INSERT = new galaxyDB();
				$INSERT->connect();

				
				$UPDATE = new galaxyDB();
				$UPDATE->connect();

				foreach ($this->_CATEGORIAS_MYSQL as $_CATEGORIA=>$_PALAVRA){
					$_JSON = addslashes(json_encode($_PALAVRA,JSON_UNESCAPED_UNICODE|JSON_BIGINT_AS_STRING));
					if(isset($_PALAVRA) && count($_PALAVRA)>0 && in_array($_CATEGORIA,$_VERIFICA_CAT)){		 
							$UPDATE->set_table("MACHINE_LEARNING");
							$UPDATE->set_update('TREINOS',$this->_TOTAL_TREINO[$_CATEGORIA]);
							$UPDATE->set_update('PALAVRAS',$_JSON);
							$UPDATE->set_where('CATEGORIA="'.$_CATEGORIA.'"');
							$UPDATE->prepare_update();
					}else{
							$INSERT->set_table("MACHINE_LEARNING");
							$INSERT->set_insert('TREINOS', $this->_TOTAL_TREINO[$_CATEGORIA]);
							$INSERT->set_insert('PALAVRAS',$_JSON);
							$INSERT->set_insert('CATEGORIA',$_CATEGORIA);
							$INSERT->prepare_insert();
					 }
				}
				$INSERT->transaction(function ($ERROR) {print_r($ERROR);});
				$UPDATE->transaction(function ($ERROR) {print_r($ERROR);});
				$INSERT->execQuery();
				$UPDATE->execQuery();
			}

			/*
			|-------------------------------------------------
			| TOKENIZADOR DE TEXTOS
			|-------------------------------------------------
			| 
			| Trata problemas de codificação na string.
			| Converte entidades HTML para caracteres correspondentes.
			| Remove caracteres não alfanuméricos e múltiplos espaços.
			| Converte a string para minúsculas e remove espaços no início e no final.
			| Divide a string em unigrams.
			| Gera n-grams (sequências contíguas de n elementos) a partir dos unigrams.
			| Combina unigrams e n-grams em um array e retorna o resultado.
			|
			|-------------------------------------------------
			*/


			public function tokenize( $string, $stopwords=false , $matriz=2){
				if(	$string !== mb_convert_encoding(mb_convert_encoding($string, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32'))
				$string			= mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string));
				$string			= htmlentities($string, ENT_NOQUOTES, 'UTF-8');
				$string			= preg_replace('`&([a-z]{1,2})(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i', '\1', $string);
				$string			= html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');
				$string			= preg_replace(array('`[^a-z0-9]`i','`[-]+`'), ' ', $string);
				$string			= preg_replace('/( ){2,}/', '$1', $string);
				$string			= strtolower(trim($string));
				$unigrams		= explode(' ',$string);
				$num_unigrams 	= count( $unigrams );
				$ngrams			= array();
				for( $n=2; 	$n<=$matriz; $n++ ) {
					for( $i=0; $i<=$num_unigrams-$n; $i++ ) {
						$key = $i;
						$ngram = array();
						for( $key=$i; $key<$i+$n; $key++ ){
							$ngram[] = $unigrams[$key];
							$ngrams[] = implode( ' ', $ngram );
						}
					}
				}
				$ngrams = array_merge( $unigrams, $ngrams );
				return $ngrams;
			}
	}

?>
