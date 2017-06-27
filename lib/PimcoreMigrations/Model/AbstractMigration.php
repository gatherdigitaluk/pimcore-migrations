<?php

namespace PimcoreMigrations\Model;

use PimcoreMigrations\Migration\MigrationInterface;
use Pimcore\Model\AbstractModel;

class AbstractMigration extends AbstractModel implements MigrationInterface
{

    /**
     * @var int $id
     */
    public $id;

    /**
     * @var string $className
     */
    public $className;

    /**
     * @var int $creationDate
     */
    public $creationDate;

    /**
     * @var string $filename
     */
    public $filename;

    /**
     * @var int $version
     */
    public $version;

    /**
     * @var bool $successful
     */
    private $successful;

    /**
     * @var string $error
     */
    private $error;

    /**
     * @var bool $skip
     */
    private $skip;

    /**
     * @var $rebuildClassesBefore bool
     */
    private $rebuildClassesBefore;

    /**
     * @var $rebuildClassesAfter bool
     */
    private $rebuildClassesAfter;


    /**
     * Returns the version number of the migration
     *
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param $version
     */
    public function setVersion($version)
    {
        $this->version = (int) $version;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AbstractMigration
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     * @return AbstractMigration
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * @return int
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param int $creationDate
     * @return AbstractMigration
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = (int) $creationDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return AbstractMigration
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }


    public function init()
    {

    }

    public function up()
    {

    }

    public function down()
    {

    }

    /**
     * @return bool
     */
    public function hasBeenApplied()
    {
        // if its from the database then yes
        if ($this->getId()) {
            return true;
        }

        // else do a check
        try {
            $this->getDao()->getByVersion($this->getVersion());
        } catch(\Exception $w) {
            return false;
        }

        return (bool) $this->getId();
    }

    /**
     * @param $className
     * @return bool|\PimcoreMigrations\Model\AbstractMigration
     */
    public static function getByClassName($className)
    {
        $obj = new self;

        try {
            $obj->getDao()->getByClassName($className);
        } catch(\Exception $w) {
            return false;
        }

        return $obj;
    }

    /**
     * @param $version
     * @return bool|\PimcoreMigrations\Model\AbstractMigration
     */
    public static function getByVersion($version)
    {
        $obj = new self;

        try {
            $obj->getDao()->getByVersion($version);
        } catch(\Exception $w) {
            return false;
        }

        return $obj;
    }

    public function save()
    {
        if (!$this->getId()) {
            $this->setCreationDate(time());
        }

        if (!$this->getVersion()) {
            throw new \Exception('Migration cannot be saved without a version');
        }

        $this->getDao()->save();
    }

    public function delete()
    {
        return $this->getDao()->delete();
    }

    /**
     * @param string $action
     * @return void
     */
    public function run($action)
    {
        \Pimcore::collectGarbage();
        
        try {

            $this->$action();

            if ($this->getRebuildClassesAfter()) {
                \PimcoreMigrations\Tool::rebuildClasses();
            }

            $this->successful = true;
            $this->error = null;
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    /**
     * @return bool
     */
    public function wasSuccessful()
    {
        return $this->successful;
    }

    public function setSkip($bool)
    {
        $this->skip = (bool) $bool;
    }

    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return boolean
     */
    public function getRebuildClassesBefore()
    {
        return $this->rebuildClassesBefore;
    }

    /**
     * @param boolean $rebuildClassesBefore
     */
    public function setRebuildClassesBefore($rebuildClassesBefore)
    {
        $this->rebuildClassesBefore = $rebuildClassesBefore;
    }

    /**
     * @return boolean
     */
    public function getRebuildClassesAfter()
    {
        return $this->rebuildClassesAfter;
    }

    /**
     * @param boolean $rebuildClassesAfter
     */
    public function setRebuildClassesAfter($rebuildClassesAfter)
    {
        $this->rebuildClassesAfter = $rebuildClassesAfter;
    }



}
