<?php


use Phinx\Migration\AbstractMigration;

class DummyTable extends AbstractMigration
{
    public function change()
    {
        // column `id` is automatically created as a primary key
        $this->table('table')
            ->addColumn('name', 'string', [
                'limit' => 255
            ])
            ->addColumn('email', 'string')
            ->addColumn('age', 'integer')
            ->addIndex(['email'], ['unique' => true])
            ->create();
    }
}
