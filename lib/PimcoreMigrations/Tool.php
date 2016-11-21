<?php

namespace PimcoreMigrations;

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


}