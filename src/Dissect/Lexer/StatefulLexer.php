<?php

namespace Dissect\Lexer;

use Dissect\Lexer\Recognizer\RegexRecognizer;
use Dissect\Lexer\Recognizer\SimpleRecognizer;
use Dissect\Util\Util;
use LogicException;

/**
 * The StatefulLexer works like SimpleLexer,
 * but internally keeps notion of current lexer state.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
class StatefulLexer extends AbstractLexer
{
    protected $states = array();
    protected $stateStack = array();
    protected $stateBeingBuilt = null;
    protected $typeBeingBuilt = null;

    /**
     * Signifies that no action should be taken on encountering a token.
     * @var int
     */
    const NO_ACTION = 0;

    /**
     * Indicates that a state should be popped of the state stack on
     * encountering a token.
     * @var int
     */
    const POP_STATE = 1;

    /**
     * Adds a new token definition. If given only one argument,
     * it assumes that the token type and recognized value are
     * identical.
     *
     * @param string $type The token type.
     * @param string $value The value to be recognized.
     *
     * @return SimpleLexer This instance for fluent interface.
     */
    public function token(string $type, string $value = null): self
    {
        if ($this->stateBeingBuilt === null) {
            throw new LogicException("Define a lexer state first.");
        }

        if ($value === null) {
            $value = $type;
        }

        $this->states[$this->stateBeingBuilt]['recognizers'][$type] =
            new SimpleRecognizer($value);

        $this->states[$this->stateBeingBuilt]['actions'][$type] = self::NO_ACTION;

        $this->typeBeingBuilt = $type;

        return $this;
    }

    /**
     * Adds a new regex token definition.
     *
     * @param string $type The token type.
     * @param string $regex The regular expression used to match the token.
     *
     * @return SimpleLexer This instance for fluent interface.
     */
    public function regex(string $type, string $regex): self
    {
        if ($this->stateBeingBuilt === null) {
            throw new LogicException("Define a lexer state first.");
        }

        $this->states[$this->stateBeingBuilt]['recognizers'][$type] =
            new RegexRecognizer($regex);

        $this->states[$this->stateBeingBuilt]['actions'][$type] = self::NO_ACTION;

        $this->typeBeingBuilt = $type;

        return $this;
    }

    /**
     * Marks the token types given as arguments to be skipped.
     *
     * @param mixed $type ,... Unlimited number of token types.
     *
     * @return SimpleLexer This instance for fluent interface.
     */
    public function skip(): self
    {
        if ($this->stateBeingBuilt === null) {
            throw new LogicException("Define a lexer state first.");
        }

        $this->states[$this->stateBeingBuilt]['skip_tokens'] = func_get_args();

        return $this;
    }

    /**
     * Registers a new lexer state.
     *
     * @param string $state The new state name.
     *
     * @return SimpleLexer This instance for fluent interface.
     */
    public function state(string $state): self
    {
        $this->stateBeingBuilt = $state;

        $this->states[$state] = array(
            'recognizers' => array(),
            'actions' => array(),
            'skip_tokens' => array(),
        );

        return $this;
    }

    /**
     * Sets the starting state for the lexer.
     *
     * @param string $state The name of the starting state.
     *
     * @return SimpleLexer This instance for fluent interface.
     */
    public function start(string $state): self
    {
        $this->stateStack[] = $state;

        return $this;
    }

    /**
     * Sets an action for the token type that is currently being built.
     *
     * @param mixed $action The action to take.
     *
     * @return SimpleLexer This instance for fluent interface.
     */
    public function action($action): self
    {
        if ($this->stateBeingBuilt === null || $this->typeBeingBuilt === null) {
            throw new LogicException("Define a lexer state and type first.");
        }

        $this->states[$this->stateBeingBuilt]['actions'][$this->typeBeingBuilt] = $action;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function shouldSkipToken(Token $token): bool
    {
        $state = $this->states[$this->stateStack[count($this->stateStack) - 1]];

        return in_array($token->getType(), $state['skip_tokens']);
    }

    /**
     * {@inheritDoc}
     */
    protected function extractToken(string $string): ?Token
    {
        $v = null;
        if (empty($this->stateStack)) {
            throw new LogicException("You must set a starting state before lexing.");
        }

        $value = $action = null;
        $type = $action = null;
        $state = $this->states[$this->stateStack[count($this->stateStack) - 1]];

        foreach ($state['recognizers'] as $t => $recognizer) {
            if ($recognizer->match($string, $v) && ($value === null || Util::stringLength($v) > Util::stringLength($value))) {
                $value = $v;
                $type = $t;
                $action = $state['actions'][$type];
            }
        }

        if ($type !== null) {
            if (is_string($action)) { // enter new state
                $this->stateStack[] = $action;
            } elseif ($action === self::POP_STATE) {
                array_pop($this->stateStack);
            }

            return new CommonToken($type, $value, $this->getCurrentLine());
        }

        return null;
    }
}
