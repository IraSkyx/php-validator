<?php
namespace Aubind97\Tests;

use Aubind97\ValidatorError;
use PHPUnit\Framework\TestCase;

class ValidatorErrorTest extends TestCase
{
    // Test to get a message
    public function testString()
    {
        $error = new ValidatorError('email', 'email', []);

        $this->assertEquals('email must be a valid email', (string)$error);
    }
}
