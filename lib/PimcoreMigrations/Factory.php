<?php

namespace PimcoreMigrations;

use PimcoreMigrations\Model\AbstractMigration;

final class Factory
{

    private static $instance;

    /**
     * @var Migration\Manager $migrationManager
     */
    private $migrationManager;


    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Factory();
            self::$instance->init();
        }

        return self::$instance;
    }


    private function init()
    {
        if (!Tool::isMigrationsInitialised()) {
            Tool::createMigrationsTable();
        }
    }


    /**
     * @return \PimcoreMigrations\Migration\Manager
     */
    public function getMigrationManager()
    {
        if ($this->migrationManager === null) {
            $this->migrationManager = new Migration\Manager();
        }

        return $this->migrationManager;
    }

    public function getMigrationsPath()
    {
        return PIMCORE_WEBSITE_VAR . '/plugins/PimcoreMigrations/migrations';
    }

    public function createMigrationClassInstance($filePath)
    {
        $className = Tool::filePathToMigrationClassName($filePath);

        require_once $filePath;

        if (!\Pimcore\Tool::classExists($className)) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find class "%s" in file "%s"',
                $className,
                $filePath
            ));
        }

        /**
         * @var AbstractMigration $migration
         */
        $migration = new $className();

        if (!$migration instanceof AbstractMigration) {
            throw new \Exception(sprintf(
                'Migration class "%s" is not a valid AbstractMigration',
                $className
            ));
        }

        $migration->setFilename(basename($filePath));
        $migration->setClassName($className);
        $migration->setVersion(Tool::filePathToMigrationVersion($filePath));

        return $migration;
    }






}
