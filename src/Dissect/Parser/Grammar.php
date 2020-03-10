<?php

namespace Dissect\Parser;

use LogicException;

/**
 * Represents a context-free grammar.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
class Grammar
{
    /**
     * @var string[]
     */
    public $nonterminals;

    /**
     * The name given to the rule the grammar is augmented with
     * when start() is called.
     * @var string
     */
    const START_RULE_NAME = '$start';

    /**
     * The epsilon symbol signifies an empty production.
     * @var string
     */
    const EPSILON = '$epsilon';

    /**
     * @var Rule[]
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $groupedRules = [];

    /**
     * @var int
     */
    protected $nextRuleNumber = 1;

    /**
     * @var int
     */
    protected $conflictsMode = 9; // SHIFT | OPERATORS

    /**
     * @var string
     */
    protected $currentNonterminal;

    /**
     * @var Rule
     */
    protected $currentRule;

    /**
     * @var array
     */
    protected $operators = [];

    /**
     * @var array
     */
    protected $currentOperators = [];

    /**
     * Signifies that the parser should not resolve any
     * grammar conflicts.
     * @var int
     */
    const NONE = 0;

    /**
     * Signifies that the parser should resolve
     * shift/reduce conflicts by always shifting.
     * @var int
     */
    const SHIFT = 1;

    /**
     * Signifies that the parser should resolve
     * reduce/reduce conflicts by reducing with
     * the longer rule.
     * @var int
     */
    const LONGER_REDUCE = 2;

    /**
     * Signifies that the parser should resolve
     * reduce/reduce conflicts by reducing
     * with the rule that was given earlier in
     * the grammar.
     * @var int
     */
    const EARLIER_REDUCE = 4;

    /**
     * Signifies that the conflicts should be
     * resolved by taking operator precendence
     * into account.
     * @var int
     */
    const OPERATORS = 8;

    /**
     * Signifies that the parser should automatically
     * resolve all grammar conflicts.
     * @var int
     */
    const ALL = 15;

    /**
     * Left operator associativity.
     * @var int
     */
    const LEFT = 0;

    /**
     * Right operator associativity.
     * @var int
     */
    const RIGHT = 1;

    /**
     * The operator is nonassociative.
     * @var int
     */
    const NONASSOC = 2;

    /**
     * Construct the class.
     */
    public function __construct()
    {
        $this->define();
    }

    /**
     * Add a rule.
     *
     * @param  string $nonterminal
     * @return self
     */
    public function __invoke(string $nonterminal): self
    {
        return $this->rule($nonterminal);
    }

    /**
     * Add a rule.
     *
     * @param  string $nonterminal
     * @return $this
     */
    public function rule(string $nonterminal): self
    {
        $this->currentNonterminal = $nonterminal;

        return $this;
    }

    /**
     * Defines an alternative for a grammar rule.
     *
     * @param string[] ...$components The components of the rule.
     *
     * @return Grammar This instance.
     */
    public function is(...$components): self
    {
        $this->currentOperators = [];

        if ($this->currentNonterminal === null) {
            throw new LogicException(
                'You must specify a name of the rule first.'
            );
        }

        $num = $this->nextRuleNumber++;

        $rule = new Rule($num, $this->currentNonterminal, $components);

        $this->rules[$num] = $rule;
        $this->groupedRules[$this->currentNonterminal][] = $rule;
        $this->currentRule = $rule;

        return $this;
    }

    /**
     * Sets the callback for the current rule.
     *
     * @param callable $callback The callback.
     *
     * @return Grammar This instance.
     */
    public function call(callable $callback): self
    {
        if ($this->currentRule === null) {
            throw new LogicException(
                'You must specify a rule first.'
            );
        }

        $this->currentRule->setCallback($callback);

        return $this;
    }

    /**
     * Returns the set of rules of this grammar.
     *
     * @return Rule[] The rules.
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Returns a rule of this grammar.
     *
     * @param  int $number
     * @return Rule
     */
    public function getRule(int $number): Rule
    {
        return $this->rules[$number];
    }

    /**
     * Returns the nonterminal symbols of this grammar.
     *
     * @return string[] The nonterminals.
     */
    public function getNonterminals(): array
    {
        return $this->nonterminals;
    }

    /**
     * Returns rules grouped by nonterminal name.
     *
     * @return array The rules grouped by nonterminal name.
     */
    public function getGroupedRules(): array
    {
        return $this->groupedRules;
    }

    /**
     * Sets a start rule for this grammar.
     *
     * @param string The name of the start rule.
     */
    public function start(string $name): void
    {
        $this->rules[0] = new Rule(0, self::START_RULE_NAME, [$name]);
    }

    /**
     * Returns the augmented start rule. For internal use only.
     *
     * @return Rule The start rule.
     */
    public function getStartRule(): Rule
    {
        if (!isset($this->rules[0])) {
            throw new LogicException("No start rule specified.");
        }

        return $this->rules[0];
    }

    /**
     * Sets the mode of conflict resolution.
     *
     * @param int $mode The bitmask for the mode.
     */
    public function resolve(int $mode): void
    {
        $this->conflictsMode = $mode;
    }

    /**
     * Returns the conflict resolution mode for this grammar.
     *
     * @return int The bitmask of the resolution mode.
     */
    public function getConflictsMode(): int
    {
        return $this->conflictsMode;
    }

    /**
     * Does a nonterminal $name exist in the grammar?
     *
     * @param string $name The name of the nonterminal.
     *
     * @return boolean
     */
    public function hasNonterminal(string $name): bool
    {
        return array_key_exists($name, $this->groupedRules);
    }

    /**
     * Defines a group of operators.
     *
     * @param string[] ...$operators Any number of tokens that serve as the operators.
     *
     * @return Grammar This instance for fluent interface.
     */
    public function operators(...$operators): self
    {
        $this->currentRule = null;

        $this->currentOperators = $operators;

        foreach ($operators as $op) {
            $this->operators[$op] = [
                'prec'  => 1,
                'assoc' => self::LEFT,
            ];
        }

        return $this;
    }

    /**
     * Marks the current group of operators as left-associative.
     *
     * @return Grammar This instance for fluent interface.
     */
    public function left(): Grammar
    {
        return $this->assoc(self::LEFT);
    }

    /**
     * Marks the current group of operators as right-associative.
     *
     * @return Grammar This instance for fluent interface.
     */
    public function right(): Grammar
    {
        return $this->assoc(self::RIGHT);
    }

    /**
     * Marks the current group of operators as nonassociative.
     *
     * @return Grammar This instance for fluent interface.
     */
    public function nonassoc(): Grammar
    {
        return $this->assoc(self::NONASSOC);
    }

    /**
     * Explicitly sets the associatity of the current group of operators.
     *
     * @param int $a One of Grammar::LEFT, Grammar::RIGHT, Grammar::NONASSOC
     *
     * @return Grammar This instance for fluent interface.
     */
    public function assoc(int $a): self
    {
        if ($this->currentOperators === []) {
            throw new LogicException('Define a group of operators first.');
        }

        foreach ($this->currentOperators as $op) {
            $this->operators[$op]['assoc'] = $a;
        }

        return $this;
    }

    /**
     * Sets the precedence (as an integer) of the current group of operators.
     * If no group of operators is being specified, sets the precedence
     * of the currently described rule.
     *
     * @param int $i The precedence as an integer.
     *
     * @return Grammar This instance for fluent interface.
     */
    public function prec(int $i): self
    {
        if ($this->currentOperators === []) {
            if (!$this->currentRule) {
                throw new LogicException('Define a group of operators or a rule first.');
            } else {
                $this->currentRule->setPrecedence($i);
            }
        } else {
            foreach ($this->currentOperators as $op) {
                $this->operators[$op]['prec'] = $i;
            }
        }

        return $this;
    }

    /**
     * Is the passed token an operator?
     *
     * @param string $token The token type.
     *
     * @return boolean
     */
    public function hasOperator(string $token): bool
    {
        return array_key_exists($token, $this->operators);
    }

    public function getOperatorInfo($token)
    {
        return $this->operators[$token];
    }

    /**
     * Define the rules for the grammar.
     *
     * @return void
     */
    protected function define(): void
    {
        //
    }
}
