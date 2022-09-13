<?php

class ParentClass
{
    const CONSTANT = "ParentClass constant\n";

    public function message()
    {
        echo "ParentClass message()\n";
    }

    public function returnSelf(): self
    {
        return (new self);
    }

    public function returnStatic(): static
    {
        return (new static);
    }

    public function echoSelfConst()
    {
        echo self::CONSTANT;
    }

    public function echoStaticConst()
    {
        echo static::CONSTANT;
    }

    public static function returnStaticSelf(): self
    {
        return (new self);
    }

    public static function returnStaticStatic(): static
    {
        return (new static);
    }
}

class A extends ParentClass
{
    const CONSTANT = "A constant\n";

    public function message()
    {
        echo "A message()\n";
    }
}

A::returnStaticSelf()->message();
A::returnStaticStatic()->message();

(new A)->returnSelf()->message();
(new A)->returnStatic()->message();

(new A)->echoSelfConst();
(new A)->echoStaticConst();