<?php

require 'core/Table.php';

class Simplex
{

    /**
     * Target function
     *
     * @var array
     */
    protected $targetFunc;

    /**
     * Limitations
     *
     * @var array
     */
    protected $limitations;

    /**
     * Limitations brought to the canonical form
     *
     * @var array
     */
    protected $limitationsCanonical;

    protected $mathSymbols = [['<=', 1], ['>=', -1], ['=', 0]];

    protected $table;

    protected $basis = [];

    public function __construct(array $targetFunc, array $limitations)
    {
        $this->targetFunc = $targetFunc;
        $this->limitations = $limitations;
    }

    public function run()
    {

        if(end($this->targetFunc) === 'min')
            $this->reverseTargetFunc();


        $this->limitationsCanonical = $this->canonicalForm();

        $test = $this->checkingForTheUnitMatrix();

        if (sizeof($test) > 0)
            $this->addAnArtificialBasis($test);

        $this->createTable();

        $this->table->renderTable();

        $result = [];

        do {
            $result = $this->decision();

            if ($result['status'] === 'recalculation') {
                $isImpossible = $this->recalculation($result['delta']);

                if(!($isImpossible)) {
                    $this->table->renderTable();
                    $result['status'] === 'impossible';
                    break;
                }

                $this->table->renderTable();
            } else {
                break;
            }
        } while (1);

        if ($result['status'] === 'decided') {
            echo '<p>Найден оптимальный план</p>';
            echo '<p>Ответ: ' . $result['answer'] . '</p>';
        } else
            echo '<p>Оптимальный план найти невозможно</p>';

        // debug($this->limitationsCanonical);
    }

    /**
     * Defines the mathematical symbol in the limitation and returns its index
     *
     * @param array $limitation
     * @return int
     */
    protected function whatMathSymbol(array $limitation)
    {
        foreach ($limitation as $value) {
            foreach ($this->mathSymbols as $symbolMath) {
                if ($value === $symbolMath[0])
                    return $symbolMath[1];
            }
        }

        return -2;
    }

    /**
     * Returns the index of the sign
     *
     * @param string $limitation
     * @return int
     */
    protected function symbolIndex(string $symbol)
    {
        foreach ($this->mathSymbols as $symbolMath) {
            if ($symbol === $symbolMath[0])
                return $symbolMath[1];
        }

        return -2;
    }

    /**
     * Brings the limitation to the canonical form
     *
     * @return array
     */
    protected function canonicalForm()
    {
        $limitationsCanonical = $this->limitations;


        /**
         * Check if B is a negative number then change the signs
         */
        for ($i = 0; $i < sizeof($limitationsCanonical); $i++) {
            if (end($limitationsCanonical[$i]) <= 0) {
                for ($j = 0; $j < sizeof($limitationsCanonical[$i]); $j++) {
                    if (!is_string($limitationsCanonical[$i][$j])) {
                        $limitationsCanonical[$i][$j] *= -1;
                    } else {
                        if ($this->symbolIndex($limitationsCanonical[$i][$j]) === -1)
                            $limitationsCanonical[$i][$j] = '<=';
                        else
                            $limitationsCanonical[$i][$j] = '>=';
                    }
                }
            }
        }

        /**
         * DOC
         */
        for ($i = 0; $i < sizeof($limitationsCanonical); $i++) {
            for ($j = 0; $j < sizeof($limitationsCanonical[$i]); $j++) {

                if ($this->symbolIndex($limitationsCanonical[$i][$j]) === 1 || $this->symbolIndex($limitationsCanonical[$i][$j]) === -1) {

                    if ($this->symbolIndex($limitationsCanonical[$i][$j]) === 1)
                        array_splice($limitationsCanonical[$i], $j, 0, array(1));
                    else
                        array_splice($limitationsCanonical[$i], $j, 0, array(-1));

                    $limitationsCanonical[$i][$j + 1] = '=';

                    array_splice($this->targetFunc, $j, 0, array(0));

                    for ($z = 0; $z < sizeof($limitationsCanonical); $z++) {
                        if ($z != $i)
                            array_splice($limitationsCanonical[$z], $j, 0, array(0));
                    }
                }
            }
        }

        return $limitationsCanonical;
    }

    /**
     * Checks the limitations on the unit matrix, 
     * if there is no unit matrix in the limitations, 
     * the function returns a list of limitations where you need to make an artificial basis
     *
     * @return array
     */
    protected function checkingForTheUnitMatrix()
    {

        $limitationIndex = range(1, (sizeof($this->limitationsCanonical)));

        $unitColumn = false;

        for ($i = 0; $i < sizeof($this->targetFunc) - 1; $i++) {

            $numberOfZeros = 0;
            $possibleLimitation = -1;

            for ($j = 0; $j < sizeof($this->limitationsCanonical); $j++) {

                if ($this->limitationsCanonical[$j][$i] === 1) {
                    $unitColumn = true;

                    $possibleLimitation = $j;

                    continue;
                }

                if ($this->limitationsCanonical[$j][$i] === 0) {
                    $numberOfZeros++;

                    continue;
                } else
                    $unitColumn = false;
            }

            if ($unitColumn === true && $numberOfZeros === sizeof($this->limitationsCanonical) - 1) {
                array_push($this->basis, $i);
                unset($limitationIndex[$possibleLimitation]);
            }
        }

        return $limitationIndex;
    }

    /**
     * Undocumented function
     *
     * @param array $limitationsIndexes
     * @return void
     */
    protected function addAnArtificialBasis(array $limitationsIndexes)
    {
        foreach ($limitationsIndexes as $key) {

            for ($j = 0; $j < sizeof($this->limitationsCanonical[$key - 1]); $j++) {

                if ($this->symbolIndex($this->limitationsCanonical[$key - 1][$j]) === 0) {

                    array_splice($this->limitationsCanonical[$key - 1], $j, 0, array(1));
                    array_push($this->basis, $j);

                    if (end($this->targetFunc) === 'max') {
                        array_splice($this->targetFunc, $j, 0, array(BIG_NEGATIVE_NUMBER));
                        break;
                    } else {
                        array_splice($this->targetFunc, $j, 0, array(BIG_NUMBER));
                        break;
                    }
                }
            }


            for ($i = 0; $i < sizeof($this->limitationsCanonical); $i++) {
                for ($j = 0; $j < sizeof($this->limitationsCanonical[$i]); $j++) {

                    if ($this->symbolIndex($this->limitationsCanonical[$i][$j]) === 0) {

                        if ($i != intval($key - 1)) {
                            array_splice($this->limitationsCanonical[$i], $j, 0, array(0));
                            break;
                        }
                    }
                }
            }
        }

        // debug($this->targetFunc);
    }

    protected function createTable()
    {
        $this->table = new Table($this->targetFunc, $this->limitationsCanonical, $this->basis);
    }

    protected function decision()
    {

        // status: recalculation, decided, impossible
        $result = [
            "status" => ''
        ];

        $delta = [];
        $tmpDelta = 0;

        // Считает дельту
        for ($i = 0; $i < $this->table->getRowsTable(); $i++) {

            for ($j = 0; $j < $this->table->getCallsTable(); $j++) {
                $tmpDelta += $this->table->getTargetFuncVariables()[$this->table->getBasis()[$j]] * $this->table->getTable()[$j][$i];
            }

            $tmpDelta -= $this->table->getTargetFuncVariables()[$i];

            array_push($delta, $tmpDelta);

            $tmpDelta = 0;
        }

        for ($j = 0; $j < $this->table->getCallsTable(); $j++) {
            $tmpDelta += $this->table->getTargetFuncVariables()[$this->table->getBasis()[$j]] * $this->table->getB()[$j];
        }

        array_push($delta, $tmpDelta);
        $tmpDelta = 0;

        // Проверяем если в дельте есть отрицательное число, то ставим статус пересчет
        foreach ($delta as $value) {
            if ($value == end($delta))
                continue;

            if ($value < 0) {
                $result['status'] = 'recalculation';
                $result += ['delta' => $delta];

                return $result;
            }
        }


        $answer = 0;

        for ($i = 0; $i < $this->table->getCallsTable(); $i++) {
            $answer += $this->table->getTargetFuncVariables()[$this->table->getBasis()[$i]] * $this->table->getB()[$i];
        }

        $result['status'] = 'decided';
        $result['answer'] = $answer;
        return $result;
    }

    protected function reverseTargetFunc()
    {
        foreach($this->targetFunc as $key => $value)
        {
            if(end($this->targetFunc) === $value)
                $this->targetFunc[$key] = 'max';
            else
                $this->targetFunc[$key] = $value * (-1);
        }
    }

    /**
     * Recalculating a simplex table
     *
     * @param array $delta
     * @return void
     */
    protected function recalculation(array $delta)
    {

        $newX = 0;
        $replacementX = 0;

        $replacementXMatrix = [];

        $table = $this->table->getTable();

        $B = $this->table->getB();


        // Ищем в дельте самое большое отрицательное число
        $min = 1000000;
        foreach ($delta as $key => $value) {

            if ($value == end($delta)) {
                continue;
            } else {
                if ($value < $min) {
                    $newX = $key;
                    $min = $value;
                }
            }
        }


        // Ищем иксы для замены
        for ($i = 0; $i < $this->table->getCallsTable(); $i++) {

            if ($this->table->getTable()[$i][$newX] <= 0) {
                array_push($replacementXMatrix, '-');
                continue;
            } else
                array_push($replacementXMatrix, $this->table->getB()[$i] / $this->table->getTable()[$i][$newX]);
        }

        $impossible = true;
        // Проверяем мозможно ли пересчитать таблицу
        foreach ($replacementXMatrix as $key => $value) {
            if ($value !== '-')
            {
                $impossible = false;
                break;
            }
        }

        if($impossible)
            return false;

        $min = 1000000;
        foreach ($replacementXMatrix as $key => $value) {
            if ($value === '-')
                continue;

            if ($value < $min) {
                $replacementX = $key;
                $min = $value;
            }
        }

        // Перерасчет таблицы
        $this->basis[$replacementX] = $newX;
        $tmp = $table[$replacementX][$newX];

        for ($i = 0; $i < $this->table->getRowsTable(); $i++) {
            $table[$replacementX][$i] /= $tmp;
        }

        $B[$replacementX] /= $tmp;

        for ($i = 0; $i < $this->table->getCallsTable(); $i++) {
            $tmpRow = [];

            if ($replacementX === $i)
                continue;


            $tmp = $table[$i][$newX];

            for ($j = 0; $j < $this->table->getRowsTable(); $j++) {
                array_push($tmpRow, $table[$replacementX][$j] * - ($tmp));
            }

            for ($j = 0; $j < $this->table->getRowsTable(); $j++) {
                $table[$i][$j] += $tmpRow[$j];
            }

            $B[$i] += - ($tmp) * $B[$replacementX];

            unset($tmpRow);
        }


        $this->table->updateTable($table, $this->basis, $B);

        return true;
    }
}
