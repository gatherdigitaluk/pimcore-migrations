<?php

namespace PimcoreMigrations\Migration;

use PimcoreMigrations\Factory;
use PimcoreMigrations\Tool;
use PimcoreMigrations\Model\AbstractMigration;

class Manager {


    /**
     * @var $migrations AbstractMigration[]
     */
    protected $migrations;

    protected $mode;

    /**
     * @var $output \Symfony\Component\Console\Output\OutputInterface;
     */
    protected $output;

    public function __construct($output=null)
    {
        $this->output = $output;

        $this->setMode(MigrationInterface::UP);
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @return AbstractMigration[]
     */
    public function getMigrations()
    {
        if (!$this->migrations) {
            $this->loadMigrations();
        }

        return $this->migrations;
    }

    /**
     * @param $migrations
     */
    public function setMigrations($migrations)
    {
        $this->migrations = $migrations;
    }

    /**
     * Runs this migration
     */
    public function migrate()
    {

        $this->output->writeln(sprintf('%d Migration files are present', count($this->migrations)));

        foreach($this->getMigrations() as $migration) {
            /**
             * @var $migration AbstractMigration
             */

            if($migration->getSkip()) {
                continue;
            }

            if ($this->getMode() === MigrationInterface::UP) {
                if (!$migration->hasBeenApplied()) {
                    $migration->run(MigrationInterface::UP);
                    $migration->save();
                } else {
                    $migration->setSkip(true);
                }
            } else if ($this->getMode() === MigrationInterface::DOWN) {

                if (!$migration->hasBeenApplied()) {
                    $migration->run(MigrationInterface::DOWN);
                    $migration->delete();
                } else {
                    $migration->setSkip(true);
                }

            }

            if ($migration->wasSuccessful()) {
                $this->output->writeln(sprintf('Migration "%s" was successful version now "%s"',
                    $migration->getClassName(),
                    $migration->getVersion()
                ));
            } else if ($migration->getSkip()) {
                $this->output->writeln(sprintf('Migration "%s" was skipped',
                    $migration->getClassName(),
                    $migration->getVersion()
                ));
            } else {

                $this->output->writeln(sprintf('Migration "%s" failed (reason: "%s")',
                    $migration->getClassName(),
                    $migration->getError()
                ));

                throw new \Exception('Error Migrating ' . $this->getMode() . '!');
            }

        }
    }

    protected function loadMigrations()
    {
        $phpFiles = glob(Factory::getInstance()->getMigrationsPath() . DIRECTORY_SEPARATOR . '*.php');
        $migrations = [];

        foreach ($phpFiles as $filePath) {

            $basename = basename($filePath, '.php');

            if (Tool::isValidMigrationFilename($basename)) {

                $migration = Factory::getInstance()->createMigrationClassInstance($filePath);

                $version = $this->determineMigrationVersion($migration);
                $migration->setVersion($version);

                // else then grab an instance of this migration and add it to the migrations list
                $migrations[$version] = $migration;

            } else {
                throw new \Exception($basename . ' is not a valid filename for migrations');
            }
        }
        ksort($migrations);

        $this->setMigrations($migrations);
    }

    protected function determineMigrationVersion(AbstractMigration $migration)
    {
        $parts = explode('_', $migration->getFilename());

        return (int) end($parts);
    }



}
