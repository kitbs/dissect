<?php

namespace Dissect\Parser\LALR1;

use Dissect\Parser\Exception\UnexpectedTokenException;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    protected $lexer;
    protected $parser;

    public function setUp(): void
    {
        $this->lexer = new ArithLexer();
        $this->parser = new Parser(new ArithGrammar());
    }

    /**
     * @test
     */
    public function parserShouldProcessTheTokenStreamAndUseGrammarCallbacksForReductions()
    {
        $this->assertEquals(-2, $this->lexAndParse('-1 - 1'));

        $this->assertEquals(11664, $this->lexAndParse('6 ** (1 + 1) ** 2 * (5 + 4)'));

        $this->assertEquals(-4, $this->lexAndParse('3 - 5 - 2'));

        $this->assertEquals(262144, $this->lexAndParse('4 ** 3 ** 2'));
    }

    /**
     * @test
     */
    public function parserShouldProcessTokenStreamWithMultipleArgs()
    {
        $this->assertEquals(5, $this->lexAndParse('Add(1, 2, 2)'));
    }

    /**
     * @test
     */
    public function parserShouldProcessTokenStreamWithNoArgs()
    {
        $this->assertEquals(0, $this->lexAndParse('Add()'));
    }

    /**
     * @test
     */
    public function parserShouldThrowAnExceptionOnInvalidInput()
    {
        try {
            $this->lexAndParse('6 ** 5 3');
            $this->fail('Expected an UnexpectedTokenException.');
        } catch (UnexpectedTokenException $e) {
            $this->assertEquals('INT', $e->getToken()->getType());
            $this->assertEquals(array('$eof', '+', '-', '*', '/', '**', ')', ','), $e->getExpected());
            $this->assertEquals(<<<EOT
Unexpected 3 (INT) at line 1.

Expected one of \$eof, +, -, *, /, **, ), ,.
EOT
            , $e->getMessage());
        }
    }

    protected function lexAndParse(string $expression)
    {
        return $this->parser->parse($this->lexer->lex($expression));
    }
}
