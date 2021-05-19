<?php

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

    public function __construct(array $targetFunc, array $limitations)
    {
        $this->targetFunc = $targetFunc;
        $this->limitations = $limitations;
    }

    public function run()
    {
        $this->limitationsCanonical = $this->canonicalForm();

        $test = $this->checkingForTheUnitMatrix();

        if (sizeof($test) > 0)
            $this->addAnArtificialBasis($test);


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

                // TODO: A negative number must be inserted when the sign >= is used
                if ($this->symbolIndex($limitationsCanonical[$i][$j]) === 1) {
                    $limitationsCanonical[$i][$j] = '=';

                    array_splice($limitationsCanonical[$i], $j, 0, array(1));

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

            if ($unitColumn === true && $numberOfZeros === sizeof($this->limitationsCanonical) - 1)

                unset($limitationIndex[$possibleLimitation]);
        }

        return $limitationIndex;
    }


    // /**
    //  * Set a variable to a limitation
    //  *
    //  * @return void
    //  */
    // protected function setVariable($symbolIndex, $variableValue, $variableValueTargetFunc){
    //     for ($i = 0; $i < sizeof($this->limitationsCanonical); $i++) {
    //         for ($j = 0; $j < sizeof($this->limitationsCanonical[$i]); $j++) {
    //             if ($this->symbolIndex($this->limitationsCanonical[$i][$j]) === $symbolIndex) {

    //                 array_splice($this->limitationsCanonical[$i], $j, 0, array($variableValue));

    //                 array_splice($this->targetFunc, $j, 0, array($variableValueTargetFunc));

    //                 for ($z = 0; $z < sizeof($this->limitationsCanonical); $z++) {
    //                     if ($z != $i)
    //                         array_splice($this->limitationsCanonical[$z], $j, 0, array(0));
    //                 }
    //             }
    //         }
    //     }
    // }

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

        debug($this->limitationsCanonical);
    }
}
