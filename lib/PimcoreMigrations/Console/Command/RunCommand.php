<?php

/**
 * RunCommand
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace PimcoreMigrations\Console\Command;

use PimcoreMigrations\Factory;
use PimcoreMigrations\Migration\MigrationInterface;
use Pimcore\Console\AbstractCommand;
use PimcoreMigrations\Model\AbstractMigration;
use PimcoreMigrations\Tool;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('deployment:migrations:run')
            ->addArgument('file', InputArgument::REQUIRED, 'The migration filename (no path)')
            ->addArgument('mode', InputArgument::REQUIRED, 'The migration direction mode [up|down]')
            ->setDescription('Run a single migration file (Migration Manager runs this internally)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $factory = Factory::getInstance();
        $migrationFullPath = $factory->getMigrationsPath() . DIRECTORY_SEPARATOR . $input->getArgument('file');
        $mode = $input->getArgument('mode');

        if ($mode !== MigrationInterface::UP && $mode !== MigrationInterface::DOWN) {
            throw new \Exception('Invalid migration command');
        }

        $migration = Factory::getInstance()->createMigrationClassInstance($migrationFullPath);
        $migration->init();

        //up
        if ($mode === MigrationInterface::UP) {
            if (!$migration->hasBeenApplied()) {
                if ($migration->getRebuildClassesBefore()) {
                    // rebuild the class definitions
                    Tool::rebuildClasses();
                }
                $migration->run($mode);
            } else {
                $migration->setSkip(true);
            }
        } else {
            // down
            if ($migration->hasBeenApplied()) {
                $migration->run($mode);
                $migration->delete();
            } else {
                $migration->setSkip(true);
            }
        }

        if ($migration->wasSuccessful()) {
            $migration->save();
            return 0;
        } else if ($migration->getSkip()) {
            return 2;
        }

        $this->output->writeln(sprintf('Migration "%s" failed (reason: "%s")',
            $migration->getClassName(),
            $migration->getError()
        ));

        return 1; //error
    }
}
