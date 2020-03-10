<?php

namespace Dissect\Parser\LALR1;

use Dissect\Parser\Grammar;
use Dissect\Lexer\TokenStream\TokenStream;
use Dissect\Parser\Exception\UnexpectedTokenException;
use Dissect\Parser\LALR1\Analysis\Analyzer;
use Dissect\Parser as P;

/**
 * A LR parser.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
class Parser implements P\Parser
{
    /**
     * @var Grammar
     */
    protected $grammar;

    /**
     * @var array
     */
    protected $parseTable = [];

    /**
     * Constructor.
     *
     * @param Grammar $grammar The grammar.
     * @param array $parseTable If given, the parser doesn't have to analyze the grammar.
     */
    public function __construct(Grammar $grammar, array $parseTable = null)
    {
        $this->grammar = $grammar;

        if ($parseTable) {
            $this->parseTable = $parseTable;
        } else {
            $analyzer = new Analyzer();
            $this->parseTable = $analyzer->analyze($grammar)->getParseTable();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function parse(TokenStream $stream)
    {
        $stateStack = [$currentState = 0];
        $args = [];

        foreach ($stream as $token) {
            while (true) {
                $type = $token->getType();

                if (!isset($this->parseTable['action'][$currentState][$type])) {
                    // unexpected token

                    throw new UnexpectedTokenException(
                        $token,
                        array_keys($this->parseTable['action'][$currentState])
                    );
                }

                $action = $this->parseTable['action'][$currentState][$type];

                if ($action > 0) {
                    // shift

                    $args[] = $token;
                    $stateStack[] = $action;
                    $currentState = $action;

                    break;
                } elseif ($action < 0) {
                    // reduce
                    $rule = $this->grammar->getRule(-$action);
                    $popCount = count($rule->getComponents());

                    $newArgs = $args;
                    $keep = $rule->getKeep();

                    if ($popCount > 0) {
                        array_splice($stateStack, -$popCount);
                        $newArgs = array_splice($args, -$popCount);

                        if (count($keep)) {
                            $i = 0;
                            $newArgs = array_filter($newArgs, function () use ($keep, &$i) {
                                $i++;
                                return in_array($i, $keep);
                            });
                        }
                    }

                    if ($method = $rule->getMethod()) {
                        $node = array_shift($newArgs);
                        $args[] = $node->$method(...$newArgs);
                    } elseif ($node = $rule->getNode()) {
                        $args[] = new $node(...$newArgs);
                    } elseif ($callback = $rule->getCallback()) {
                        $args[] = $callback(...$newArgs);
                    } else {
                        $args[] = $newArgs[0];
                    }

                    $state = $stateStack[count($stateStack) - 1];
                    $stateStack[] = $this->parseTable['goto']
                        [$state][$rule->getName()];
                    $currentState = $this->parseTable['goto']
                        [$state][$rule->getName()];
                } else {
                    // accept

                    return $args[0];
                }
            }
        }
    }
}
