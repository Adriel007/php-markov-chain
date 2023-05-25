<?php

class MarkovChain
{
    private $order;
    public $transitionMatrix;

    public function __construct($order)
    {
        $this->order = $order;
        $this->transitionMatrix = array();
    }

    public function train($dataset)
    {
        $aux = [];
        foreach ($dataset as $phrase) {
            array_push($aux, $this->clean($phrase));
        }
        $dataset = $aux;

        $sentences = array();
        foreach ($dataset as $phrase) {
            $sentences[] = explode(' ', $phrase);
        }

        for ($i = 0; $i < count($sentences); $i++) {
            for ($j = $this->order; $j < count($sentences[$i]); $j++) {
                $currentState = implode(' ', array_slice($sentences[$i], $j - $this->order, $this->order));
                $nextState = $sentences[$i][$j];

                if (!isset($this->transitionMatrix[$currentState])) {
                    $this->transitionMatrix[$currentState] = array();
                }

                if (!isset($this->transitionMatrix[$currentState][$nextState])) {
                    $this->transitionMatrix[$currentState][$nextState] = 0;
                }

                $this->transitionMatrix[$currentState][$nextState]++;
            }
        }

        foreach ($this->transitionMatrix as &$row) {
            $sum = array_sum($row);
            foreach ($row as &$count) {
                $count = ($count + 1) / ($sum + count($row));
            }
        }

        $learningRate = 0.01; // Taxa de aprendizado
        $numIterations = 100; // Número de iterações do Gradiente Descendente

        for ($iteration = 0; $iteration < $numIterations; $iteration++) {
            $gradient = array(); // Gradiente dos pesos

            foreach ($this->transitionMatrix as $currentState => $nextStates) {
                foreach ($nextStates as $nextState => $prob) {
                    $predictedProb = $this->calculateTransitionScore($currentState, $nextState);
                    $expectedProb = $prob; // Probabilidade esperada

                    // Gradiente Descendente: calcula a derivada do erro em relação ao peso
                    $gradient[$currentState][$nextState] = ($predictedProb - $expectedProb) * $predictedProb * (1 - $predictedProb);
                }
            }

            // Atualiza os pesos usando o Gradiente Descendente
            foreach ($this->transitionMatrix as $currentState => &$nextStates) {
                foreach ($nextStates as $nextState => &$prob) {
                    $prob -= $learningRate * $gradient[$currentState][$nextState];
                }
            }
        }

        $this->wittenBellSmooth();
    }

    public function generateText($length)
    {
        $currentState = array_rand($this->transitionMatrix);

        $text = '';
        for ($i = 0; $i < $length; $i++) {
            $text .= $currentState . ' ';
            if (array_key_exists($currentState, $this->transitionMatrix)) {
                $nextStates = $this->transitionMatrix[$currentState];
                $cumulativeProb = 0;
                $rand = mt_rand() / mt_getrandmax();
                foreach ($nextStates as $nextState => $prob) {
                    $cumulativeProb += $prob;
                    if ($rand <= $cumulativeProb) {
                        $currentState = substr($currentState, strpos($currentState, ' ') + 1) . ' ' . $nextState;
                        break;
                    }
                }
            }
        }

        return $this->finalPolishing($text);
    }

    private function finalPolishing($str)
    {
        $arr = explode(' ', $str);
        $arr = array_unique($arr, SORT_REGULAR);
        $str = '';

        foreach ($arr as $value)
            $str .= $value . ' ';

        return $this->capitalizeText(trim($str));
    }

    private function clean($str)
    {
        $str = strtolower($str);
        $str = strtr($str, ['á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a', 'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I', 'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o', 'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O', 'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U', 'ñ' => 'n', 'Ñ' => 'N']);
        return $str;
    }

    // Método para calcular a pontuação de um estado de transição
    private function calculateTransitionScore($currentState, $nextState)
    {
        if (isset($this->transitionMatrix[$currentState][$nextState])) {
            return $this->transitionMatrix[$currentState][$nextState];
        } else {
            // Retornar uma pontuação baixa caso a transição não exista na matriz de transição
            return 0.01;
        }
    }

    // Método para pontuar a qualidade de um texto gerado
    public function scoreGeneratedText($text)
    {
        $text = $this->clean($text);
        $words = explode(' ', $text);
        $numWords = count($words);
        $numRealisticTransitions = 0;

        for ($i = 0; $i < $numWords - 1; $i++) {
            $currentState = $words[$i];
            $nextState = $words[$i + 1];
            $transitionScore = $this->calculateTransitionScore($currentState, $nextState);

            if ($transitionScore > 0) {
                $numRealisticTransitions++;
            }
        }

        // Calcula a porcentagem de transições realistas em relação ao total de transições
        $realismScore = ($numRealisticTransitions / ($numWords - 1)) * 100;

        return round($realismScore, 2); // Retorna o nível de realismo como uma porcentagem com duas casas decimais
    }

    private function capitalizeText($text)
    {
        $text = strtolower($text); // converter para minúsculas
        $sentences = preg_split('/(\.|\?|\!)/', $text); // dividir em frases

        $capitalizedText = '';
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (!empty($sentence)) {
                $sentence = ucfirst($sentence); // capitalizar primeira letra da frase
                $capitalizedText .= $sentence . ' ';
            }
        }

        return rtrim($capitalizedText);
    }

    public function wittenBellSmooth()
    {
        foreach ($this->transitionMatrix as &$nextStates) {
            $totalCounts = array_sum($nextStates);
            $numNextStates = count($nextStates);

            $seen = array();
            foreach ($nextStates as $nextState => $count) {
                $seen[$nextState] = true;
            }

            $unseenCount = 0;
            for ($i = 0; $i < $numNextStates; $i++) {
                $nextState = 'UNKNOWN_' . $i;
                if (!isset($seen[$nextState])) {
                    $unseenCount++;
                }
            }

            $gamma = $unseenCount / ($totalCounts + $unseenCount);

            $denominator = 0;
            foreach ($nextStates as &$count) {
                $count = ($count + $gamma) / ($totalCounts + $unseenCount);
                $denominator += $count;
            }

            foreach ($nextStates as &$count) {
                $count /= $denominator;
            }
        }
    }

}