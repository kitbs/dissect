<?php

namespace Dissect\Parser\LALR1\Analysis\Exception;

use Dissect\Parser\LALR1\Analysis\Automaton;
use LogicException;

/**
 * A base class for exception thrown when encountering
 * inadequate states during parse table construction.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
class ConflictException extends LogicException
{
    /**
     * @var int
     */
    protected $state;
    /**
     * @var Automaton
     */
    protected $automaton;

    public function __construct($message, $state, Automaton $automaton)
    {
        parent::__construct($message);

        $this->state = $state;
        $this->automaton = $automaton;
    }

    /**
     * Returns the number of the inadequate state.
     *
     * @return int
     */
    public function getStateNumber(): int
    {
        return $this->state;
    }

    /**
     * Returns the faulty automaton.
     *
     * @return Automaton
     */
    public function getAutomaton(): Automaton
    {
        return $this->automaton;
    }
}
