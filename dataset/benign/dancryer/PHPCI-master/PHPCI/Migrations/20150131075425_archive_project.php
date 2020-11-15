<?php

use Phinx\Migration\AbstractMigration;

class ArchiveProject extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $project = $this->table('project');
        $project->addColumn('archived', 'boolean');
        $project->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $project = $this->table('project');
        $project->removeColumn('archived');
        $project->save();
    }
}