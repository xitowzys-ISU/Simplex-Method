<?php

class Table
{

    protected $rowsTable;

    protected $callsTable;

    protected $targetFuncVariables = [];

    protected $table = [];

    protected $basis = [];

    protected $B = [];

    protected $delta = [];

    public function __construct(array $targetFunc, array $limitations, array $basis)
    {
        $this->createTable($targetFunc, $limitations, $basis);
    }

    public function getTable() {
        return $this->table;
    }

    public function getRowsTable()
    {
        return $this->rowsTable;
    }

    public function getCallsTable(){
        return $this->callsTable;
    }

    public function getB() {
        return $this->B;
    }

    public function getDelta() {
        return $this->delta;
    }

    public function getTargetFuncVariables() {
        return $this->targetFuncVariables;
    }

    public function getBasis() {
        return $this->basis;
    }

    protected function createTable(array $targetFunc, array $limitations, array $basis)
    {

        $this->rowsTable = sizeof($targetFunc) - 1;

        $this->callsTable = sizeof($limitations);

        for ($i = 0; $i < $this->callsTable; $i++) {
            array_push($this->B, end($limitations[$i]));

            array_push($this->table, array());

            for ($j = 0; $j < $this->rowsTable; $j++) {
                array_push($this->table[$i], $limitations[$i][$j]);
            }
        }

        for ($i = 0; $i < $this->rowsTable; $i++) {
            array_push($this->targetFuncVariables, $targetFunc[$i]);
        }

        $this->basis = $basis;

        // debug($this->B);
    }

    public function updateTable(array $table, array $basis, array $B) {
        unset($this->table);
        unset($this->basis);
        unset($this->B);

        $this->table = $table;
        $this->basis = $basis;
        $this->B = $B;
    }

    public function renderTable() {
        echo '<table border="1">';

        echo '<tr>';
        echo '<td></td><td></td>';
        for ($i = 0; $i < $this->rowsTable; $i++) {
            echo '<td>' . $this->targetFuncVariables[$i] . '</td>';
        }
        echo '</tr>';

        echo '<tr>';
        echo '<td>С базис</td>';
        echo '<td>Базис</td>';
        for ($i = 0; $i < $this->rowsTable; $i++) {
            echo '<td>x' . ($i + 1) . '</td>';
        }

        echo '<td>B</td>';

        echo '</tr>';

        for ($i = 0; $i < $this->callsTable; $i++) {
            echo '<tr>';
            // debug($this->table);
            echo '<td>'. ($this->targetFuncVariables[$this->basis[$i]]) .'</td>';
            echo '<td>x'. ($this->basis[$i] + 1) .'</td>';
            for ($j = 0; $j < $this->rowsTable; $j++) {
                echo '<td>' . $this->table[$i][$j] . '</td>';
            }

            echo '<td>'. $this->B[$i] .'</td>';
            echo '</tr>';
        }

        echo '</table><br />';
    }
}
