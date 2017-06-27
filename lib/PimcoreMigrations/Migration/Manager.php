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
                $this->output->write('Starting Migration ' . $migration->getVersion());
                $result = $this->runInSeparateProcess($this->getConsoleCommandPath($migration->getFilename()));
            } else {
                $this->output->writeln(sprintf('Migration version "%d" not in range', $migration->getVersion()));
                continue;
            }

            if ($result[0] == 0) {
                $this->output->writeln(' - <info>Success!</info>');
                // success!
            } else if ($result[0] == 2) {
                $this->output->writeln(' - <comment>Skipped!</comment>');
                // skipped!
            } else {
                $this->output->writeln(' - <error>Migration Failed :-(</error>');
                $this->output->writeln('<comment>' . implode('\n', $result[1]) . '</comment>');
                throw new \Exception('Migration Failure, exit code ' . $result);
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
                $migration->init();

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

    protected function getConsoleCommandPath($migrationFile)
    {
        $cmdPath = PIMCORE_PATH . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'console.php';
        $env = \Pimcore\Config::getEnvironment();
        if ($env) {
            $cmdPath .= '--environment=' . $env;
        }

        return $cmdPath . ' deployment:migrations:run "'.$migrationFile.'" ' . $this->getMode();
    }

    protected function runInSeparateProcess($command)
    {
        $ll = exec($command, $output, $code);

        return [$code,$output];
    }



}
