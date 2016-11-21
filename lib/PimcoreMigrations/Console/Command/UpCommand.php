<?php

/**
 * UpCommand
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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('deployment:migrations:up')
            ->setDescription('Migrate your project UP');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = Factory::getInstance()->getMigrationManager();
        $manager->setOutput($output);
        $manager->setMode(MigrationInterface::UP);
        $manager->migrate();
    }
}
