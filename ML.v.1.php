<?php
/*
 * Teste com o PHP-ML
 * -----------------------------------------
 * Para o blog: ciencia de dados
 * @author Thiago Serra F Carvalho (C073835)
 * Teste com a base petr4.csv
 * -----------------------------------------
 *
*/

require_once 'vendor/autoload.php';

$start_time = microtime(TRUE);

use Phpml\Dataset\CsvDataset;
use Phpml\Dataset\ArrayDataset;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Metric\Accuracy;

class Prever
{
    protected $previsao;

    public function __construct($algo)
    {
        $this->previsao = new $algo();
        //$this->previsao->setVarPath('C:\tmp');
    }

    public function predict($samples)
    {
        return $this->previsao->predict($samples);
    }

    public function train($samples, $labels)
    {
        $this->previsao->train($samples, $labels);
    }
}

$treinamento = array();

// lendo o arquivo - 4 colunas - 3 de feature
$dataset = new CsvDataset("petr4.csv", 3, true);

//separando a 2 coluna - preco do ativo
foreach ($dataset->getSamples() as $amostra)
{
    $treinamento[] = $amostra;
}

//isolando as "respostas" - preço de fechamento
$respostasTreino = new ArrayDataset($treinamento, $dataset->getTargets());

//gerando uma aleatoriedade na ordem dos dados
//para poder propciar ordens diferentes dos eventos
//podendo testar varias combinacoes de resultados
$conjuntoAleatorio = new StratifiedRandomSplit($respostasTreino, 0.1);

//gerando o conjunto de teste
$conjuntoTeste = $conjuntoAleatorio->getTrainSamples();

//gerando as respostas (preco de venda) dos dados que estao
//no conjunto de teste
$respostasTeste = $conjuntoAleatorio->getTrainLabels();


//gerando as mostrar para usar na predicao
$conjuntoPrevisao = $conjuntoAleatorio->getTestSamples();

//gerando respostas das amostrar para testar a
//acuracidade do algoritmo
$previsaoRespostas = $conjuntoAleatorio->getTestLabels();

echo "------------------------------------------------------------ (C073835) --\n";
echo "------------------------------------------------------------ PHP-ML -----\n";
echo "------------------------------------------------------------ v.1 --------\n";
echo "--> Conjunto de Treino: " . count($conjuntoTeste) ." linhas.\n";
echo "--> Conjunto para Previsão: " . count($conjuntoPrevisao) ." linhas.\n";
echo "--> Conjunto Total: " . count($treinamento) ." linhas.\n";
echo "-------------------------------------------------------------------------\n";

//Instanciando o objeto e fazendo os testes!!
$algoritmos = array( "Phpml\Classification\NaiveBayes",
                    "Phpml\Classification\KNearestNeighbors",
                    "Phpml\Classification\SVC",
                    "Phpml\Regression\SVR",
                    "Phpml\Regression\LeastSquares"
                );

foreach ($algoritmos as $key => $value)
{
    echo ">> Algoritimo $value ...\n";
    $classificador = new Prever($value);
    $classificador->train($conjuntoTeste, $respostasTeste);
    $predictedLabels = $classificador->predict($conjuntoPrevisao);
    $score = Accuracy::score($previsaoRespostas, $predictedLabels)*100;
    $score = number_format($score, 2, ',', '.');
    echo "** Acurácia : " .   $score  . "%\n";
}


$end_time = microtime(TRUE);
$time_taken = ($end_time - $start_time) * 1000;
$time_taken = round($time_taken, 0);
$time_taken = $time_taken / 1000;
echo "Tempo em $time_taken segundos.\n";
echo "-------------------------------------------------------------------------\n";
?>