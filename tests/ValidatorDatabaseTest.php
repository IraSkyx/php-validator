<?php

namespace Aubind97\Tests;

use Aubind97\Validator;

class ValidatorDatabaseTest extends DatabaseTestCase
{
    /**
     * @var array params test array
     */
    private $params = [
        'email-1' => 'joe@doe.fr',
        'email-2' => 'joe@joedoe.fr',
        'email-3' => 'john@doe.fr',
        'number' => 2
    ];

    // Test exists validation
    public function testKeyExists()
    {
        $validator = ($this->getValidator())
            ->exists('email-1', 'email', "'table'", $this->pdo); // Need to surround table by '' for SQLite

        $this->assertTrue($validator->isValid());

        $validator
            ->exists('email-2', 'email', "'table'", $this->pdo);

        $errors = $validator->getErrors();

        $this->assertFalse($validator->isValid());
    }

    // Test unique validation
    public function testUnique()
    {
        $validator = ($this->getValidator())
            ->unique('email-1', 'email', "'table'", $this->pdo, true)
            ->unique('email-2', 'email', "'table'", $this->pdo, true)
            ->unique('email-2', 'email', "'table'", $this->pdo)
            ->unique('email-3', 'email', "'table'", $this->pdo, 2);

        $this->assertTrue($validator->isValid());

        $validator
            ->unique('email-1', 'email', "'table'", $this->pdo)
            ->unique('email-3', 'email', "'table'", $this->pdo, 1);

        $this->assertCount(2, $validator->getErrors());
    }

    /**
     * Return a validator with the test params
     *
     * @return Validator
     */
    private function getValidator() : Validator
    {
        return new Validator($this->params);
    }

}
