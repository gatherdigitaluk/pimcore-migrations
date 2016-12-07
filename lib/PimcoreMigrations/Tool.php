<?php

namespace PimcoreMigrations;

use Pimcore\Model\Object\ClassDefinition;

class Tool {


    public static function isMigrationsInitialised()
    {
        return \Pimcore\Db::get()->query("SHOW TABLES LIKE 'plugin_pimcore_migrations'")->rowCount() > 0;
    }

    public static function createMigrationsTable()
    {
       return \Pimcore\Db::get()->query("CREATE TABLE `plugin_pimcore_migrations` (
          `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `className` varchar(255) NULL,      
          `filename` varchar(255) NULL,
          `creationDate` bigint(20) NOT NULL,
          `version` bigint(20) NOT NULL
        ) COMMENT='';");
    }

    public static function resetMigrationsTable()
    {
        return \Pimcore\Db::get()->query("TRUNCATE plugin_pimcore_migrations");
    }

    public static function deleteMigrationsTable()
    {
        return \Pimcore\Db::get()->query("DROP TABLE plugin_pimcore_migrations");
    }

    /**
     * Example 12345_migration_test returns true as first part is a number
     * @param $filename
     * @return bool
     */
    public static function isValidMigrationFilename($filename)
    {
        return is_numeric(self::filePathToMigrationVersion($filename));
    }

    /**
     * Example 12345_migration_test returns 12345MigrationTest
     * @param $filePath
     * @return string
     */
    public static function filePathToMigrationClassName($filePath)
    {
        $classParts = explode('_', basename($filePath, '.php'));
        array_walk($classParts, function(&$val) {
            $val = ucfirst($val);
        });

        return implode('', $classParts);
    }

    /**
     * Gets the migration version from the current filename
     * @param $filePath
     * @return mixed
     */
    public static function filePathToMigrationVersion($filePath)
    {
        return end(explode('_', basename($filePath, '.php')));
    }

    public static function rebuildClasses()
    {
        $list = new \Pimcore\Model\Object\ClassDefinition\Listing();
        $list->load();
        foreach ($list->getClasses() as $class) {
            $class->save();
        }

        $list = new \Pimcore\Model\Object\Objectbrick\Definition\Listing();
        $list = $list->load();
        foreach ($list as $brickDefinition) {
            $brickDefinition->save();
        }

        $list = new \Pimcore\Model\Object\Fieldcollection\Definition\Listing();
        $list = $list->load();
        foreach ($list as $fc) {
            $fc->save();
        }

        // clean the cache
        \Pimcore\Cache::clearAll();
    }

    public static function addClassDefinition($className, $id=null)
    {
        $classDef = \Pimcore\Model\Object\ClassDefinition::getByName($className);
        if (!$classDef) {
            return \Pimcore\Db::get()->insert('classes', [
                'name' => $className,
                'id'   => $id
            ]);
        }

        if ($id && $classDef->getId() != $id) {
            throw new \Exception('PimcoreMigrations\FATAL: addClassDefinition ID mismatch on existing class with same name.');
        }

        return null;
    }


}
