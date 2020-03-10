<?php

namespace Dissect\Parser;

use Dissect\Node\Node;
use Closure;

/**
 * Represents a rule in a context-free grammar.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
class Rule
{
    /**
     * @var int
     */
    protected $number;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string[]
     */
    protected $components = [];

    /**
     * @var Closure
     */
    protected $callback = null;

    /**
     * @var string
     */
    protected $node = null;

    /**
     * @var string
     */
    protected $method = null;

    /**
     * @var int|null
     */
    protected $precedence = null;

    /**
     * Keep selected arguments.
     *
     * @var int[]
     */
    protected $keep = [];

    /**
     * Constructor.
     *
     * @param int $number The number of the rule in the grammar.
     * @param string $name The name (lhs) of the rule ("A" in "A -> a b c")
     * @param string[] $components The components of this rule.
     */
    public function __construct(int $number, string $name, array $components)
    {
        $this->number = $number;
        $this->name = $name;
        $this->components = $components;
    }

    /**
     * Returns the number of this rule.
     *
     * @return int The number of this rule.
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * Returns the name of this rule.
     *
     * @return string The name of this rule.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the components of this rule.
     *
     * @return string[] The components of this rule.
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * Returns a component at index $index or null
     * if index is out of range.
     *
     * @param int $index The index.
     *
     * @return string The component at index $index.
     */
    public function getComponent(int $index): ?string
    {
        if (!isset($this->components[$index])) {
            return null;
        }

        return $this->components[$index];
    }

    /**
     * Sets the callback (the semantic value) of the rule.
     *
     * @param Closure $callback The callback.
     * @return $this
     */
    public function setCallback(Closure $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Get the callback of the rule.
     *
     * @return Closure|null
     */
    public function getCallback(): ?Closure
    {
        return $this->callback;
    }

    /**
     * Sets the return node of the rule.
     *
     * @param string|Node $node The node class.
     * @return $this
     */
    public function setNode($node): self
    {
        if ($node instanceof Node) {
            $node = get_class($node);
        }

        $this->node = $node;

        return $this;
    }

    /**
     * Get the node of the rule.
     *
     * @return string|null
     */
    public function getNode(): ?string
    {
        return $this->node;
    }

    /**
     * Sets the return node of the rule.
     *
     * @param string|null $method The node method.
     * @return $this
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get the node method of the rule.
     *
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Get the precedence of the rule.
     *
     * @return int
     */
    public function getPrecedence(): ?int
    {
        return $this->precedence;
    }

    /**
     * Set the precedence of the rule.
     *
     * @param  int $i
     * @return $this
     */
    public function setPrecedence(int $i): self
    {
        $this->precedence = $i;

        return $this;
    }

    /**
     * Get the arguments to keep for the rule.
     *
     * @return int[]
     */
    public function getKeep(): array
    {
        return $this->keep;
    }

    /**
     * Set the arguments to keep for the rule.
     *
     * @param  int[] $keep
     * @return $this
     */
    public function setKeep(array $keep): self
    {
        $this->keep = $keep;

        return $this;
    }
}
