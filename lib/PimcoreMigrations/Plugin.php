<?php

/**
 * Plugin
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace PimcoreMigrations;

use Pimcore\API\Plugin as PluginLib;
use PimcoreMigrations\Console\Command\DownCommand;
use PimcoreMigrations\Console\Command\RunCommand;
use PimcoreMigrations\Console\Command\StatusCommand;
use PimcoreMigrations\Console\Command\UpCommand;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface
{

    use \Pimcore\Console\ConsoleCommandPluginTrait;

    /**
     * @throws \Zend_EventManager_Exception_InvalidArgumentException
     */
    public function init()
    {
        parent::init();

        if(!$this->isInstalled()) {
            return;
        }
        
        if (defined('PIMCORE_CONSOLE') && PIMCORE_CONSOLE === true) {
            $this->initConsoleCommands();
        }
    }


    /**
     * @return bool
     */
    public static function install()
    {
        $path = Factory::getInstance()->getMigrationsPath();
        mkdir($path);

        if (!is_dir($path)) {
            throw new \Exception('Could not install Pimcore Migrations');
        }

        return 'Pimcore Migrations installed sucessfully!';
    }

    public static function uninstall()
    {
        Tool::deleteMigrationsTable();

        return 'Pimcore Migrations Uninstalled Successfully!';
    }

    public static function isInstalled()
    {
        if (is_dir(Factory::getInstance()->getMigrationsPath())) {
            return true;
        }

        return false;
    }

    public function getConsoleCommands()
    {
        return [
            new StatusCommand(),
            new UpCommand(),
            new DownCommand(),
            new RunCommand()
        ];
    }

}
