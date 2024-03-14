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

	namespace IsraelNogueira\naivebayes;
	use IsraelNogueira\galaxyDB\galaxyDB;
	use Wamania\Snowball\StemmerFactory;
	use Exception;
	class naivebayes {

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

					$_STOP_WORDS = new galaxyDB();
					$_STOP_WORDS->connect();
					$_STOP_WORDS->table("BLACK_LIST_WORDS");
					$_STOP_WORDS->prepare_select('PALAVRA');
					$_STOP_WORDS->transaction(function($error) {die(print_r($error));});
					$_STOP_WORDS->execQuery(function($success) {/*die(print_r($success));*/});
					$STOP_WORDS = $_STOP_WORDS->fetch_array('PALAVRA');

					$_CATEGORIAS = new galaxyDB();
					$_CATEGORIAS->connect();
					$_CATEGORIAS->table("MACHINE_LEARNING");
					$_CATEGORIAS->prepare_select('CAT');
					$_CATEGORIAS->transaction(function($error) {die(print_r($error));});
					$_CATEGORIAS->execQuery(function($success) {/*die(print_r($success));*/});
					$CATEGORIAS = $_CATEGORIAS->fetch_array('CAT');


					$this->_STOPWORDS			= array("o", "a", "os", "as", "um", "uma", "uns", "umas", "de", "do", "da", "dos", "das", "em", "no", "na", "nos", "nas", "por", "para", "com", "como", "em", "entre", "sobre", "sob", "ante", "após", "até", "depois", "durante", "desde", "perante", "sem", "fora", "dentro", "além", "atrás", "adiante", "ao", "aos", "à", "às", "àquele", "àquela", "àqueles", "àquelas", "aquele", "aquela", "aqueles", "aquelas", "este", "esta", "estes", "estas", "isto", "isso", "aquele", "aquela", "aqueles", "aquelas", "isso", "isto", "assim", "ali", "aqui", "lá", "aí", "cá", "lá", "ali", "aqui", "lá", "cá", "aí", "acolá", "adentro", "afinal", "afora", "algures", "ambos", "anteontem", "antigamente", "aparentemente", "apenas", "assaz", "assim", "atualmente", "bem", "breve", "cá", "cedo", "certamente", "certo", "coisa", "comigo", "comum", "conforme", "conforme", "consigo", "constante", "corrente", "daí", "dele", "depois", "desse", "deste", "detalhe", "dois", "domingo", "embora", "enquanto", "entanto", "então", "entretanto", "enquanto", "eram", "essa", "esse", "esta", "este", "essas", "esses", "estas", "estes", "estou", "exatamente", "felizmente", "fim", "finalmente", "frente", "gente", "gostei", "gosto", "hoje", "hora", "horas", "lhe", "logo", "longe", "lugar", "mais", "mal", "máximo", "médio", "meio", "menos", "meses", "mesmo", "mês", "muita", "muito", "nada", "nenhum", "nenhuma", "nós", "nossa", "nosso", "novamente", "novo", "ontem", "outra", "outras", "outro", "outros", "pela", "pelas", "pelo", "pelos", "penúltimo", "perto", "pouca", "pouco", "próprio", "quase", "quem", "quinta", "segunda", "seja", "sejam", "semelhante", "semelhantes", "senão", "ser", "será", "serão", "seria", "seriam", "sexta", "sobre", "sobretudo", "sábado", "talvez", "tanto", "terça", "toda", "todas", "todo", "todos", "tomara", "tão", "têm", "um", "uma", "umas", "uns", "vem", "veja", "vez", "vindo", "vinte", "você", "vocês", "vossa", "vosso");
					$this->STOP_WORDS 			= ($_STOP_WORDS->_num_rows==0)?[]:array_values($STOP_WORDS);
					$this->_ARRAY_CAT			= ($_CATEGORIAS->_num_rows==0)?[]:array_values($CATEGORIAS);
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
					$_TOTAL_TREINO = 0;
					foreach ($this->_TOTAL_TREINO as $value) {
						$_TOTAL_TREINO += $value;
					}
					$_CLASSIFY = array();
					foreach ($this->_CATEGORIAS_MYSQL as $_CHAVECAT => $_ARRAY_PALAVRAS) {
						$_TOTAL_TREINO_COM_A_CATEGORIA = $this->_TOTAL_TREINO[$_CHAVECAT];
						$_PROBABILIDADE = ($_TOTAL_TREINO == 0) ? 0 : log($_TOTAL_TREINO_COM_A_CATEGORIA / $_TOTAL_TREINO);
						foreach ($ARRAY_PALAVRAS as $_PALAVRA) {
							if (
								count($this->_CATEGORIAS_MYSQL[$_CHAVECAT]) > 0 &&
								array_key_exists($_PALAVRA, $this->_CATEGORIAS_MYSQL[$_CHAVECAT])
							) {
								$_PROBABILIDADE += log(($_TOTAL_TREINO) * ($_TOTAL_TREINO_COM_A_CATEGORIA / count($this->_CATEGORIAS_MYSQL[$_CHAVECAT])));
							}
						}
						$_PROBABILIDADE = ($_PROBABILIDADE < 0) ? ($_PROBABILIDADE * -1) : $_PROBABILIDADE;
						$_CLASSIFY[] = array('CATEGORIA' => $_CHAVECAT, "PROBABILIDADE" => number_format($_PROBABILIDADE, 2, '.', ' '));
					}
					usort($_CLASSIFY, function ($a, $b) {
						if (floatval($a['PROBABILIDADE']) < floatval($b['PROBABILIDADE'])) {
							return -1;
						} elseif (floatval($a['PROBABILIDADE']) > floatval($b['PROBABILIDADE'])) {
							return 1;
						} else {
							return 0;
						}
					});
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

				if (!isset($this->_CATEGORIAS_MYSQL[$CATEGORIA])) {
					$this->_CATEGORIAS_MYSQL[$CATEGORIA] = [];
					$this->_TOTAL_TREINO[$CATEGORIA] = 0;
				}
				
				$this->_TOTAL_TREINO[$CATEGORIA]++;
				$_ARRAY_PALAVRAS = array_filter($this->tokenize($_FRASE));

				foreach ($_ARRAY_PALAVRAS as $_PALAVRA) {
					if (!isset($this->_CATEGORIAS_MYSQL[$CATEGORIA][$_PALAVRA])) {
						$this->_CATEGORIAS_MYSQL[$CATEGORIA][$_PALAVRA] = 1;
					} else {
						$this->_CATEGORIAS_MYSQL[$CATEGORIA][$_PALAVRA]++;
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

				$_CATEGORIAS = new galaxyDB();
				$_CATEGORIAS->connect();
				$_CATEGORIAS->table("MACHINE_LEARNING");
				$_CATEGORIAS->colum("CATEGORIA");
				$_CATEGORIAS->prepare_select('CAT');
				$_CATEGORIAS->transaction(function($error) {die(print_r($error));});
				$_CATEGORIAS->execQuery(function($success) {});
				$CATEGORIAS 			= $_CATEGORIAS->fetch_array('CAT');
				$CAT_CADASTRADAS = [];
				foreach ($CATEGORIAS as $_CATEGORIA) {
					$CAT_CADASTRADAS[] =$_CATEGORIA['CATEGORIA'];
				}

				$PROCESS = new galaxyDB();
				$PROCESS->connect();
				$key = 0;
				foreach ($this->_CATEGORIAS_MYSQL as $_CATEGORIA => $_PALAVRA) {
					 $_JSON = addslashes(json_encode($_PALAVRA, JSON_UNESCAPED_UNICODE | JSON_BIGINT_AS_STRING));
					 if (isset($_PALAVRA) && count($_PALAVRA) > 0 && in_array($_CATEGORIA,$CAT_CADASTRADAS)) {
						$PROCESS->table("MACHINE_LEARNING");
						$PROCESS->set_update('TREINOS', $this->_TOTAL_TREINO[$_CATEGORIA]);
						$PROCESS->set_update('PALAVRAS', $_JSON);
						$PROCESS->where('CATEGORIA="' . $_CATEGORIA . '"');
						$PROCESS->prepare_update('key_'.$key);
					} else {
						$PROCESS->table("MACHINE_LEARNING");
						$PROCESS->set_insert('TREINOS', $this->_TOTAL_TREINO[$_CATEGORIA]);
						$PROCESS->set_insert('PALAVRAS', $_JSON);
						$PROCESS->set_insert('CATEGORIA', $_CATEGORIA);
						$PROCESS->prepare_insert('key_'.$key);
					}
					$key++;
				}
				$PROCESS->transaction(function ($ERROR) {throw new Exception($ERROR, 1);});
				$PROCESS->execQuery();
			}

			/*
			|-------------------------------------------------
			| LEMATIZADOR DE TEXTOS
			|-------------------------------------------------
			| 
			| Normalização de texto são técnicas de pré-processamento de texto que ajudam 
			| a reduzir as palavras a uma forma mais básica ou normalizada, 
			| facilitando a análise e a comparação de textos
			|
			|-------------------------------------------------
			*/

				public function lematize($text=""){
					$stemmer = StemmerFactory::create('pt');
					return $stemmer->stem($text);
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


			public function tokenize( $string, $stopwords=[] , $matriz=2){
				if(	$string !== mb_convert_encoding(mb_convert_encoding($string, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32'))
				$string			= mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string));
				$string			= htmlentities($string, ENT_NOQUOTES, 'UTF-8');
				$string			= preg_replace('`&([a-z]{1,2})(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i', '\1', $string);
				$string			= html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');
				$string			= preg_replace(array('`[^a-z0-9]`i','`[-]+`'), ' ', $string);
				$string			= preg_replace('/( ){2,}/', '$1', $string);	
				$string			= strtolower(trim($string));
				$string			= str_replace($stopwords,'',$string);
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
