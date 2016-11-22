#Pimcore Migrations Plugin

## Introduction
Pimcore already does a lot of migration work for developers, all class definition changes and database schema changes can
be easily ported between development, staging and production environments. However, the following use-cases for database
changes are not easily portable between Pimcore environments: 
 - Custom persistant model schemas.
 - The addition of new class definitions does not happen automatically happen in deployment.
 - Changes to website settings (stored in the database) do not happen in any deployment helpers.
 - Alterations to document editables, i.e. changing a textarea to wysiwyg creates invalid references to editables.
 - A rename of a ClassDefinition field removes the old column, then adds a new one, rather than actually renaming.

### What this plugin provides
This plugin provides a simple mechanism for implementing version controllable database changes between pimcore environments,
in a lightweight convention similar to packages such as Phinx or Doctrine Migrations.

## Installation
Install using composer:

```
composer require gatherdigitaluk/pimcore-migrations
```

## Configuration

Configuration is intended to be as simple as possible.
1. After install ensure that the extensionis listed as follows in your extensions.php file
```php
<?php
  return [
      "PimcoreMigrations" => TRUE
  ];
?>
```
2. Migrations should be placed in your /website/var/plugins/PimcoreMigrations/migrations folder.

3. The plugin will automatically install a new table into the pimcore environment upon detecting a migrations folder when the console app is executed.

## Usage

### Extend the AbstractMigration Class

1. Each migration should extend the class PimcoreMigrations\Model\AbstractMigration.
2. It can be named however you wish, although the last part of the name should be a numeric value separated by an underscore (_).
For example:
    - some_table_changes_1000.php
    - new_custom_persistent_class_1001.php
    - anotherdifferentlynamedclass_1002.php
3. Each migration class should have a similarly named classname following the same pattern as Pimcore, example:
    - SomeTableChanges1000
    - NewCustomPersistentClass1001
    - Anotherdifferentlynamedclass1002
4. If we created the classes above we would have the following versions (represented by an integer)
    - 1000
    - 1001
    - 1002

### Implement the required class methods
Each Migration should contain both an up() and down() method. UP being the default operation, and DOWN being to roll
back changes. Here is an example:

```php
<?php

class MyMigrationClass1000 extends \PimcoreMigrations\Model\AbstractMigration 
{

    public function up()
    {
        \Pimcore\Db::get()->query("CREATE table `test`");
    }
    
    public function down()
    {
        \Pimcore\Db::get()->query("DROP table `test`");    
    }

}


```

### Run the console app
Migrations can be run through the console application made available in the Pimcore\Cli. Running as a plugin ensures that 
all migrations are runnning in the Pimcore environment. There are 3 commands available and can be run as follows:

1. Check Status
```
php console.php deployment:migrations:status
```

2. Migrate up
```
php console.php deployment:migrations:up
or
php console.php deployment:migrations:up --fromVersion 1000 --toVersion 1001
```

3. Migrate down 
```
php console.php deployment:migrations:down
or
php console.php deployment:migrations:down --fromVersion 1000 --toVersion 1001
```

