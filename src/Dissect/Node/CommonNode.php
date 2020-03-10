<?php

namespace Dissect\Node;

use ArrayIterator;
use RuntimeException;

/**
 * An AST node.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
class CommonNode implements Node
{
    /**
     * @var Node[]
     */
    protected $nodes;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Constructor.
     *
     * @param array $attributes The attributes of this node.
     * @param array $nodes The nodes of this node.
     */
    public function __construct(array $attributes = [], array $nodes = [])
    {
        $this->attributes = $attributes;
        $this->nodes = $nodes;
    }

    /**
     * {@inheritDoc}
     * @return mixed[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * {@inheritDoc}
     */
    public function hasNode(string $key): bool
    {
        return isset($this->nodes[$key]);
    }

    /**
     * {@inheritDoc}
     */
    public function getNode($key): Node
    {
        if (!isset($this->nodes[$key])) {
            throw new RuntimeException(sprintf('No child node "%s" exists.', $key));
        }

        return $this->nodes[$key];
    }

    /**
     * {@inheritDoc}
     */
    public function setNode(string $key, Node $node): void
    {
        $this->nodes[$key] = $node;
    }

    /**
     * {@inheritDoc}
     */
    public function removeNode(string $key): void
    {
        unset($this->nodes[$key]);
    }

    /**
     * {@inheritDoc}
     * @return mixed[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute(string $key)
    {
        if (!isset($this->attributes[$key])) {
            throw new RuntimeException(sprintf('No attribute "%s" exists.', $key));
        }

        return $this->attributes[$key];
    }

    /**
     * {@inheritDoc}
     */
    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function removeAttribute(string $key): void
    {
        unset($this->attributes[$key]);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->nodes);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->nodes);
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate()
    {
        return $this->getAttribute('value');
    }
}
