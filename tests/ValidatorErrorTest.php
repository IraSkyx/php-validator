<?php
namespace Aubind97\Tests;

use Aubind97\ValidatorError;
use PHPUnit\Framework\TestCase;

class ValidatorErrorTest extends TestCase
{
    // Test to get a message
    public function testString()
    {
        $error = new ValidatorError('demo', 'fake', ['p1', 'p2']);

        $property = (new \ReflectionClass($error))->getProperty('messages');
        $property->setAccessible(true);
        $property->setValue($error, ['fake' => 'problem %2$s %3$s']);

        $this->assertEquals('problem p1 p2', (string)$error);
    }
}
