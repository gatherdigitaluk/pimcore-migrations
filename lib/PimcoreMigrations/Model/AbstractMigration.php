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
     * @var float $version
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
     * Returns the version number of the migration
     *
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }


    public function setVersion($version)
    {
        $this->version = $version;
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


    protected function init()
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
            $this->getDao()->getByClassName($this->getClassName());
        } catch(\Exception $w) {
            return false;
        }

        return false;
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
        try {
            $this->$action();
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

}
