<?php

namespace PimcoreMigrations\Model\AbstractMigration;

use \Pimcore\Model\Dao\AbstractDao;

class Dao extends AbstractDao
{

    /**
     * @var string $tableName
     */
    protected $tableName = 'plugin_pimcore_migrations';

    public function save()
    {

        $vars           = get_object_vars($this->model);
        $buffer         = [];
        $validColumns   = $this->getValidTableColumns($this->tableName);

        if(count($vars)) {
            foreach ($vars as $k => $v) {

                if (!in_array($k, $validColumns)) {
                    continue;
                }

                if ($k == 'id') {
                    continue;
                }


                $getter = "get" . ucfirst($k);

                if (!is_callable([$this->model, $getter])) {
                    continue;
                }

                $value = $this->model->$getter();

                if (is_bool($value)) {
                    $value = (int) $value;
                }

                $buffer[$k] = $value;

            }
        }

        if ($this->model->getId() !== null) {
            $where = ['id = ?' => $this->model->getId()];
            $result = $this->db->update($this->tableName, $buffer, $where);
            return;
        }

        $this->db->insert($this->tableName, $buffer);
        $this->model->setId($this->db->lastInsertId());

        return;
    }

    public function delete()
    {
        $this->db->delete($this->tableName, $this->db->quoteInto("id = ?", $this->model->getId()));
    }

    /**
     * @param integer $id
     * @throws \Exception
     */
    public function getById($id)
    {

        if ($id === null) {
            throw new \Exception('getById requirements not met');
        }

        $this->model->setId($id);

        $data = $this->db->fetchRow(
            "SELECT * FROM {$this->tableName} WHERE id = ?",
            [$this->model->getId()]
        );

        if (!$data["id"]) {
            throw new \Exception('No Migration was found with the given id');
        }

        $this->assignVariablesToModel($data);
    }

    /**
     * @param string $className
     * @throws \Exception
     */
    public function getByClassName($className)
    {
        if ($className === null) {
            throw new \Exception('getById requirements not met');
        }

        $this->model->setClassName($className);

        $data = $this->db->fetchRow(
            "SELECT * FROM {$this->tableName} WHERE className = ?",
            [$this->model->getClassName()]
        );

        if (!$data["id"]) {
            throw new \Exception('No Migration was found with the given className');
        }

        $this->assignVariablesToModel($data);
    }


}
