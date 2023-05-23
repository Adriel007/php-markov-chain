<?php
header('Content-Type: application/json; charset=utf-8');
include(dirname(__DIR__) . '/machine-learning/markov-chain.php');
error_reporting(0);

$data;
$brain = [];

$dataSet = json_decode(file_get_contents('../dataset/generator.json'), true);
$dataSet_nb = json_decode(file_get_contents('../dataset/classifier.json'), true);
$dictionary = json_decode(file_get_contents('../dataset/dictionary.json'), true);

$level = !empty($_GET['level']) ? $_GET['level'] : 2;
$levels = !empty($_GET['levels']) ? $_GET['levels'] : '2,4';
$group = !empty($_GET['group']) ? $_GET['group'] : 'amor';
$groups = !empty($_GET['groups']) ? $_GET['groups'] : 'amor,sexismo';
$mix_cut = !empty($_GET['mix_cut']) ? $_GET['mix_cut'] : ',';
$phrase = !empty($_GET['phrase']) ? $_GET['phrase'] : 'Fabio';
$comparation = !empty($_GET['comparation']) ? $_GET['comparation'] : 'Fardo';
$cluster = !empty($_GET['cluster']) ? $_GET['cluster'] : '';
$clusterSeparator = !empty($_GET['clusterSeparator']) ? $_GET['clusterSeparator'] : '';
$clusterK = !empty($_GET['clusterK']) ? $_GET['clusterK'] : 3;
$clusterMaxIter = !empty($_GET['clusterMaxIter']) ? $_GET['clusterMaxIter'] : 100;
$clusterTolerance = !empty($_GET['clusterTolerance']) ? $_GET['clusterTolerance'] : 0.0001;
$model = !empty($_GET['model']) ? $_GET['model'] : 'all';

switch ($model) {
    case 'markov chain':
        $data['markov'] = markov_chain($dictionary, $level, $group);

        break;
}

function markov_chain($dataSet, $level, $key)
{
    if ($dataSet[$key]['peso'] === 0)
        return ['markov' => $dataSet[$key]['dados'][array_rand($dataSet[$key]['dados'])]];
    if ($level === 'auto')
        $level = $dataSet[$key]['peso'];

    $arr = [];
    $mkc = new MarkovChain($level); // 1-2 baixa coesao alta criatividade, 3-4 media coesao media criatividade, 5+ alta coesao baixa criatividade
    $mkc->train($dataSet[$key]['dados']);
    $arr['markov'] = $mkc->generateText(100);
    $arr['score'] = $mkc->scoreGeneratedText($arr['markov']);
    return $arr;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);