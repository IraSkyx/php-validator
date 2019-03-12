<?php

namespace Aubind97\Tests;

use PDO;
use Phinx\Config\Config;
use Aubind97\ValidatorError;
use Phinx\Migration\Manager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class DatabaseTestCase extends TestCase
{
    /**
     * @var \PDO database connection
     */
    protected $pdo;

    /**
     * Create and seed a table for each tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $configArray = require('phinx.php');
        $configArray['environments']['test'] = [
            'adapter' => 'sqlite',
            'connection' => $pdo
        ];

        $config = new Config($configArray);
        $manager = new Manager($config, new StringInput(' '), new NullOutput());
        $manager->migrate('test');
        $manager->seed('test');
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        $this->pdo = $pdo;
    }

    /**
     * Return the validator error message
     *
     * @param string $key param you check
     * @param string $rule message name
     * @param array $array argument to complete the error message
     * @return ValidatorError
     */
    protected function getErrorMessage(string $key, string $rule, array $array = []) : ValidatorError
    {
        return new ValidatorError($key, $rule, $array);
    }
}
