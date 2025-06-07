<?php

namespace console\controllers;

use yii\console\controllers\MigrateController as BaseMigrateController;

/**
 * Extends the base MigrateController to provide custom migration functionality
 */
class MigrateController extends BaseMigrateController
{
    /**
     * @inheritdoc
     */
    public $migrationTable = '{{%migration}}';

    /**
     * @inheritdoc
     */
    public $migrationPath = '@console/migrations';

    /**
     * @inheritdoc
     */
    public $templateFile = '@yii/views/migration.php';
}