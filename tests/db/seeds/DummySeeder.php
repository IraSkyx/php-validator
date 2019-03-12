<?php


use Phinx\Seed\AbstractSeed;

class DummySeeder extends AbstractSeed
{
    public function run()
    {
        //$this->table('table')->truncate();

        $this->insert('table', [
            [
                'name' => 'joe',
                'email' => 'joe@doe.fr',
                'age' => 20
            ],
            [
                'name' => 'john',
                'email' => 'john@doe.fr',
                'age' => 30
            ]
        ]);
    }
}
