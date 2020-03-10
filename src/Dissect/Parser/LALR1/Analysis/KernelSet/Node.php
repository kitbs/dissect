<?php

namespace Dissect\Parser\LALR1\Analysis\KernelSet;

class Node
{
    /**
     * @var mixed[]
     */
    public $kernel;

    /**
     * @var int
     */
    public $number;

    /**
     * @var null
     */
    public $left = null;

    /**
     * @var null
     */
    public $right = null;

    public function __construct(array $hashedKernel, int $number)
    {
        $this->kernel = $hashedKernel;
        $this->number = $number;
    }
}
