<?php

namespace Dissect\Lexer;

/**
 * A common contract for tokens.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
interface Token
{
    /**
     * Returns the token type.
     *
     * @return string The token type.
     */
    public function getType(): string;

    /**
     * Returns the token value.
     *
     * @return string|null The token value.
     */
    public function getValue(): ?string;

    /**
     * Returns the line on which the token was found.
     *
     * @return int The line.
     */
    public function getLine(): int;
}
