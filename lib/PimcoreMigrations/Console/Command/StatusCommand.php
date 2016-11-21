<?php

/**
 * StatusCommand
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
use PimcoreMigrations\Tool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('deployment:migrations:status')
            ->setDescription('Check migration status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $initialised = Tool::isMigrationsInitialised();
        $status = $initialised ? 'installed and ready' : 'not installed yet';

        $output->writeln('PimcoreMigrations is ' . $status);

        if ($initialised) {
            $manager = Factory::getInstance()->getMigrationManager();
            $manager->setOutput($output);
            $totalMigrations = count($manager->getMigrations());

            $output->writeln($totalMigrations . ' Migrations are loaded into the application');
        }

    }
}
