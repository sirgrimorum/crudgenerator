<?php

namespace Sirgrimorum\CrudGenerator;

class DynamicCompare
{
    private $op1;
    private $op2;
    private $op3;
    private $c;

    function __construct($op1, $op2, $c, $op3 = null, $conCase = false)
    {
        if (!$conCase && is_string($op1)) {
            $this->op1 = strtolower($op1);
        } else {
            $this->op1 = $op1;
        }
        if (!$conCase && is_string($op2)) {
            $this->op2 = strtolower($op2);
        } else {
            $this->op2 = $op2;
        }
        if (!$conCase && is_string($op3)) {
            $this->op3 = strtolower($op3);
        } else {
            $this->op3 = $op3;
        }
        $this->c = $c;
    }

    public function es()
    {
        $meth = [
            '==' => 'igual',
            '!=' => 'diferente',
            '<' => 'menor_que',
            '<=' => 'menor_que_inclusive',
            '>' => 'mayor_que',
            '>=' => 'mayor_que_inclusive',
            "set" => 'existe',
            "notset" => 'no_existe',
            "contiene" => 'contiene',
            "nocontiene" => 'no_contiene',
            "entre" => 'entre',
            "entreexcl" => 'entre_excluyente',
            "noentre" => 'no_entre',
            "noentreincl" => 'no_entre_incluyente'
        ];
        if (isset($meth[$this->c]) && $method = $meth[$this->c]) {
            return $this->$method();
        } elseif ($this->c == "else") {
            return true;
        }
        return null;
    }

    private function entre()
    {
        return $this->op1 >= $this->op2 && $this->op1 <= $this->op3;
    }

    private function noentre()
    {
        return $this->op1 < $this->op2 && $this->op1 > $this->op3;
    }

    private function entre_excluyente()
    {
        return $this->op1 > $this->op2 && $this->op1 < $this->op3;
    }

    private function no_entre_incluyente()
    {
        return $this->op1 <= $this->op2 && $this->op1 >= $this->op3;
    }

    private function igual()
    {
        return $this->op1 == $this->op2;
    }

    private function diferente()
    {
        return $this->op1 !== $this->op2;
    }

    private function menor_que()
    {
        return $this->op1 < $this->op2;
    }

    private function menor_que_inclusive()
    {
        return $this->op1 <= $this->op2;
    }

    private function mayor_que()
    {
        return $this->op1 > $this->op2;
    }

    private function mayor_que_inclusive()
    {
        return $this->op1 >= $this->op2;
    }

    private function existe()
    {
        return isset($this->op1);
    }

    private function no_existe()
    {
        return !isset($this->op1);
    }

    private function contiene()
    {
        return str_contains($this->op1 ?? "", $this->op2);
    }
    private function no_contiene()
    {
        return !str_contains($this->op1 ?? "", $this->op2);
    }
}
