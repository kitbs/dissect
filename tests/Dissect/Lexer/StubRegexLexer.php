<?php

namespace Dissect\Lexer;

use RuntimeException;

class StubRegexLexer extends RegexLexer
{
    protected $operators = array('+', '-');

    protected function getCatchablePatterns(): array
    {
        return array('[1-9][0-9]*');
    }

    protected function getNonCatchablePatterns(): array
    {
        return array('\s+');
    }

    protected function getType(string &$value): string
    {
        if (is_numeric($value)) {
            $value = (int)$value;

            return 'INT';
        } elseif (in_array($value, $this->operators)) {
            return $value;
        } else {
            throw new RuntimeException(sprintf('Invalid token "%s"', $value));
        }
    }
}
