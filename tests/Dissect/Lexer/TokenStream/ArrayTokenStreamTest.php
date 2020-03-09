<?php

namespace Dissect\Lexer\TokenStream;

use Dissect\Lexer\CommonToken;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class ArrayTokenStreamTest extends TestCase
{
    protected $stream;

    public function setUp(): void
    {
        $this->stream = new ArrayTokenStream(array(
            new CommonToken('INT', '6', 1, 1),
            new CommonToken('PLUS', '+', 1, 3),
            new CommonToken('INT', '5', 1, 5),
            new CommonToken('MINUS', '-', 1, 7),
            new CommonToken('INT', '3', 1, 9),
        ));
    }

    /**
     * @test
     */
    public function theCursorShouldBeOnFirstTokenByDefault()
    {
        $this->assertEquals('6', $this->stream->getCurrentToken()->getValue());
    }

    /**
     * @test
     */
    public function getPositionShouldReturnCurrentPosition()
    {
        $this->stream->seek(2);
        $this->stream->next();

        $this->assertEquals(3, $this->stream->getPosition());
    }

    /**
     * @test
     */
    public function lookAheadShouldReturnTheCorrectToken()
    {
        $this->assertEquals('5', $this->stream->lookAhead(2)->getValue());
    }

    /**
     * @test
     * @expectedException OutOfBoundsException
     */
    public function lookAheadShouldThrowAnExceptionWhenInvalid()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Invalid look-ahead.');

        $this->stream->lookAhead(15);
    }

    /**
     * @test
     */
    public function getShouldReturnATokenByAbsolutePosition()
    {
        $this->assertEquals('3', $this->stream->get(4)->getValue());
    }

    /**
     * @test
     * @expectedException OutOfBoundsException
     */
    public function getShouldThrowAnExceptionWhenInvalid()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Invalid index.');

        $this->stream->get(15);
    }

    /**
     * @test
     */
    public function moveShouldMoveTheCursorByToAnAbsolutePosition()
    {
        $this->stream->move(2);
        $this->assertEquals('5', $this->stream->getCurrentToken()->getValue());
    }

    /**
     * @test
     * @expectedException OutOfBoundsException
     */
    public function moveShouldThrowAnExceptionWhenInvalid()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Invalid index to move to.');

        $this->stream->move(15);
    }

    /**
     * @test
     */
    public function seekShouldMoveTheCursorByRelativeOffset()
    {
        $this->stream->seek(4);
        $this->assertEquals('3', $this->stream->getCurrentToken()->getValue());
    }

    /**
     * @test
     * @expectedException OutOfBoundsException
     */
    public function seekShouldThrowAnExceptionWhenInvalid()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Invalid seek.');

        $this->stream->seek(15);
    }

    /**
     * @test
     */
    public function nextShouldMoveTheCursorOneTokenAhead()
    {
        $this->stream->next();
        $this->assertEquals('PLUS', $this->stream->getCurrentToken()->getType());

        $this->stream->next();
        $this->assertEquals('5', $this->stream->getCurrentToken()->getValue());
    }

    /**
     * @test
     * @expectedException OutOfBoundsException
     */
    public function nextShouldThrowAnExceptionWhenAtTheEndOfTheStream()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Attempting to move beyond the end of the stream.');

        $this->stream->seek(4);
        $this->stream->next();
    }
}
