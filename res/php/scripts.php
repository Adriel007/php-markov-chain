<?php
header('Content-Type: application/json; charset=utf-8');
mb_internal_encoding('UTF-8');
include(dirname(__DIR__) . '/machine-learning/markov-chain.php');
error_reporting(0);

if (isset($_FILES['file'])) {
    $tmp = $_FILES['file']['tmp_name'];
    $content = file_get_contents($tmp);
    $currentEncoding = mb_detect_encoding($content, 'UTF-8, ISO-8859-1');
    $content = mb_convert_encoding($content, 'UTF-8', $currentEncoding);
    $array = explode('@separatorphp@', $content);
    array_pop($array);
    $result = [];

    for ($c = 0; $c < 5; $c++)
        $result[] = mb_convert_encoding(markov_chain($array, 2)['markov'], 'UTF-8', 'UTF-8');

    $jsonResult = json_encode(['result' => $result], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

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
    $arr['markov'] = $mkc->generateText(10);
    $arr['score'] = $mkc->scoreGeneratedText($arr['markov']);
    return $arr;
}