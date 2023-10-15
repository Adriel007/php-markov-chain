<?php
class MarkovChain
{
    private $order;
    public $transitionMatrix;

    public function __construct($order)
    {
        $this->order = $order;
        $this->emissionMatrix = array();
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
        $str = mb_strtolower($str, 'UTF-8');

        // Remover acentos
        $str = preg_replace('/\p{M}/u', '', $str);

        // Remover emojis
        $str = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $str);

        // Remover links
        $str = preg_replace('/https?:\/\/\S+/', '', $str);

        // Remover menções com "#" ou "@"
        $str = preg_replace('/[#@]\S+/', '', $str);

        // Remover pontuações
        $str = preg_replace('/[^\w\s]/u', '', $str);

        return $str;
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

    public function saveModel($filename)
    {
        $modelData = serialize($this);
        file_put_contents($filename, $modelData);
    }

    public static function loadModel($filename)
    {
        $modelData = file_get_contents($filename);
        return unserialize($modelData);
    }

    public function trainBatch($dataset, $batchSize = 10000, $saveModelFile)
    {
        $totalItems = count($dataset);
        $numBatches = ceil($totalItems / $batchSize);

        for ($batch = 0; $batch < $numBatches; $batch++) {
            $start = $batch * $batchSize;
            $currentDataset = array_slice($dataset, $start, $batchSize);

            $this->train($currentDataset);

            $this->saveModel($saveModelFile);

            unset($currentDataset);
            gc_collect_cycles();

        }
    }
}