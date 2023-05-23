<?php
header('Content-Type: application/json; charset=utf-8');
include(dirname(__DIR__) . '/machine-learning/markov-chain.php');
error_reporting(0);

if (isset($_FILES['file'])) {
    $tmp = $_FILES['file']['tmp_name'];
    $content = file_get_contents($tmp);
    $array = explode('\n', $content);
    markov_chain($arr, 3);
} else {
    echo 'Nenhum arquivo foi enviado.';
}

$level = !empty($_GET['level']) ? $_GET['level'] : 2;
$group = !empty($_GET['group']) ? $_GET['group'] : 'love';

markov_chain($dataset, $level, $group);

function markov_chain($dataSet, $level)
{
    $arr = [];
    $mkc = new MarkovChain($level); // 1-2 low cohesion high creativity, 3-4 medium cohesion medium creativity, 5+ high cohesion low creativity
    $mkc->train($dataSet);
    $arr['markov'] = $mkc->generateText(100);
    $arr['score'] = $mkc->scoreGeneratedText($arr['markov']);
    echo json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}