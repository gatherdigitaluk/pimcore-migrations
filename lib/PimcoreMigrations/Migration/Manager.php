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

    /**
     * @var string $mode
     */
    protected $mode;

    /**
     * @var int $fromVersion
     */
    protected $fromVersion;

    /**
     * @var int $toVersion
     */
    protected $toVersion;

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

        if ($this->migrations) {
            $this->sortMigrations();
        }
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
     * @return int
     */
    public function getFromVersion()
    {
        return $this->fromVersion;
    }

    /**
     * @param int $fromVersion
     */
    public function setFromVersion($fromVersion)
    {
        $this->fromVersion = (int) $fromVersion;
    }

    /**
     * @return int
     */
    public function getToVersion()
    {
        return $this->toVersion;
    }

    /**
     * @param int $toVersion
     */
    public function setToVersion($toVersion)
    {
        $this->toVersion = (int) $toVersion;
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
        $migrations = $this->getMigrations();

        $this->output->writeln(sprintf('%d Migration files are present', count($migrations)));

        foreach($migrations as $version=>$migration) {
            /**
             * @var $migration AbstractMigration
             */

            if($this->versionInRange($version)) {
                if ($this->getMode() === MigrationInterface::UP) {
                    if (!$migration->hasBeenApplied()) {
                        $migration->run(MigrationInterface::UP);
                        $migration->save();
                    } else {
                        $migration->setSkip(true);
                    }
                } else {
                    // down
                    if ($migration->hasBeenApplied()) {
                        $migration->run(MigrationInterface::DOWN);
                        $migration->delete();
                    } else {
                        $migration->setSkip(true);
                    }
                }
            } else {
                $this->output->writeln(sprintf('Migration version "%d" not in range', $migration->getVersion()));
                continue;
            }

            if ($migration->wasSuccessful()) {
                $this->output->writeln(sprintf('Migration "%s" was successful version now "%d"',
                    $migration->getClassName(),
                    $migration->getVersion()
                ));
            } else if ($migration->getSkip()) {
                $this->output->writeln(sprintf('Migration "%s" was skipped', $migration->getClassName()));
            } else {

                $this->output->writeln(sprintf('Migration "%s" failed (reason: "%s")',
                    $migration->getClassName(),
                    $migration->getError()
                ));

                throw new \Exception('Error Migrating ' . $this->getMode() . '!');
            }

        }
    }

    protected function versionInRange($version)
    {
        if ($this->getMode() === MigrationInterface::UP) {
            if ($this->getFromVersion() && $version < $this->getFromVersion()) {
                return false;
            }
            if ($this->getToVersion() && $version > $this->getToVersion()) {
                return false;
            }
        } else {
            if ($this->getFromVersion() && $version > $this->getFromVersion()) {
                return false;
            }
            if ($this->getToVersion() && $version < $this->getToVersion()) {
                return false;
            }
        }

        return true;
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

        $this->setMigrations($migrations);
        $this->sortMigrations();
    }

    protected function determineMigrationVersion(AbstractMigration $migration)
    {
        $parts = explode('_', $migration->getFilename());

        return (int) end($parts);
    }

    protected function sortMigrations()
    {
        if ($this->getMode() === MigrationInterface::UP) {
            ksort($this->migrations);
        } else {
            krsort($this->migrations);
        }
    }



}
