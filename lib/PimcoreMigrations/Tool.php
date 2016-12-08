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
        try {
            $list = new \Pimcore\Model\Object\ClassDefinition\Listing();
            $list->load();
            foreach ($list->getClasses() as $class) {
                $class->save();
            }
        } catch(\Exception $e) {
            throw new \Exception('Could not rebuild Classes: ' . $e->getMessage());
        }

        try {
            $list = new \Pimcore\Model\Object\Objectbrick\Definition\Listing();
            $list = $list->load();
            foreach ($list as $brickDefinition) {
                $brickDefinition->save();
            }
        } catch(\Exception $e) {
            throw new \Exception('Could not rebuild Bricks: ' . $e->getMessage());
        }

        try {
            $list = new \Pimcore\Model\Object\Fieldcollection\Definition\Listing();
            $list = $list->load();
            foreach ($list as $fc) {
                $fc->save();
            }
        } catch(\Exception $e) {
            throw new \Exception('Could not rebuild FieldCollections: ' . $e->getMessage());
        }

        // clean the cache
        \Pimcore\Cache::clearAll();
    }

    public static function addClassDefinition($className, $id=null)
    {
        // check the classdefinition exists in php
        if (!file_exists(PIMCORE_CLASS_DIRECTORY . '/definition_' . $className . '.php')) {
            throw new \Exception($className . ' definition file does not exist!');
        }

        // query if the class exists in the DB first
        if ($id) {
            $existing = \Pimcore\Db::get()->fetchRow('SELECT * FROM `classes` WHERE name = ? OR id = ?', [$className, $id]);
        } else {
            $existing = \Pimcore\Db::get()->fetchRow('SELECT * FROM `classes` WHERE name = ?', [$className]);
        }

        if (!$existing['id']) {
            $db = \Pimcore\Db::get();

            $db->insert('classes', [
                'name' => $className,
                'id'   => $id
            ]);

        }

        if ($existing && ($existing['id'] != $id || $existing['name'] != $className)) {
            throw new \Exception('PimcoreMigrations\FATAL: addClassDefinition ID/name mismatch on existing class with same id/name.');
        }

        return null;
    }


}
