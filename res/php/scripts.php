<?php
header('Content-Type: application/json; charset=utf-8');
mb_internal_encoding('UTF-8');
include(dirname(__DIR__) . '/machine-learning/markov-chain.php');
error_reporting(0);

if (isset($_FILES['file'])) {
    $level = $_POST['level'] | 2;
    $tmp = $_FILES['file']['tmp_name'];
    $content = file_get_contents($tmp);
    $array = explode('@separatorphp@', $content);
    array_pop($array);

    $jsonResult = json_encode(['result' => markov_chain($array, $level)['markov']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($jsonResult === false)
        echo json_encode(['error' => json_last_error_msg()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    else
        echo $jsonResult;
} else
    echo json_encode(['error' => 'Nenhum arquivo foi enviado'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

function markov_chain($dataset, $level)
{
    $arr = [];
    $mkc = new MarkovChain($level); // 1-2 low cohesion high creativity, 3-4 medium cohesion medium creativity, 5+ high cohesion low creativity
    $mkc->train($dataset);
    $arr['markov'] = $mkc->generateText(200);
    $arr['score'] = $mkc->scoreGeneratedText($arr['markov']);
    return $arr;
}