<?php

namespace Dissect\Node;

use RuntimeException;

/**
 * An AST node.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
class CommonNode implements Node
{
    /**
     * @var \Dissect\Node\Node[]
     */
    public $children;
    /**
     * @var array
     */
    protected $nodes = [];

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Constructor.
     *
     * @param array $attributes The attributes of this node.
     * @param array $children The children of this node.
     * @param mixed[] $attributes
     * @param mixed[] $nodes
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
    public function getNode($key): \Dissect\Node\Node
    {
        if (!isset($this->children[$key])) {
            throw new RuntimeException(sprintf('No child node "%s" exists.', $key));
        }

        return $this->nodes[$key];
    }

    /**
     * {@inheritDoc}
     */
    public function setNode(string $key, Node $child): void
    {
        $this->children[$key] = $child;
    }

    /**
     * {@inheritDoc}
     */
    public function removeNode(string $key): void
    {
        unset($this->children[$key]);
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

    public function count(): int
    {
        return is_countable($this->children) ? count($this->children) : 0;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->children);
    }
}
