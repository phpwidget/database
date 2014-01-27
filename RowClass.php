<?php
/**
 * This file is part of project created by Martin Krizan
 * @copyright Martin Krizan [MK] (mnohosten@gmail.com)
 * @author MK http://www.martinkrizan.com
 */

namespace Wigex\Database;


class RowClass extends \NotORM_Row {

    function __get($name) {
        $column = $this->result->notORM->structure->getReferencedColumn($name, $this->result->table);
        $referenced = &$this->result->referenced[$name];
        if (!isset($referenced)) {
            $keys = array();
            foreach ($this->result->rows as $row) {
                if ($row[$column] !== null) {
                    $keys[$row[$column]] = null;
                }
            }
            if ($keys) {
                $table = $this->result->notORM->structure->getReferencedTable($name, $this->result->table);
                $this->result->notORM->updateRowClass($table);
                $referenced = new \NotORM_Result($table, $this->result->notORM);
                $referenced->where("$table." . $this->result->notORM->structure->getPrimary($table), array_keys($keys));
            } else {
                $referenced = array();
            }
        }
        if (!isset($referenced[$this[$column]])) { // referenced row may not exist
            return null;
        }
        return $referenced[$this[$column]];
    }


    /**
     * Save entity changes
     * @param null $data
     */
    function save($data = null, $return = FALSE) {
        $id = NULL;
        if(isset($this['id']) && (bool)$this['id']) {
            $this->update($data);
            $id = $this['id'];
        } else {
            $data = iterator_to_array($this) + (array)$data;
            $this->result->insert($data);
            $id = $this->result->insert_id();
        }
        if(!(bool)$id && isset($data['id'])) {
            $id = $data['id'];
        }

        if($return) {
            return $this->result->where('id', $id)->limit(1)->fetch();
        }
    }

    /**
     * Create Instance
     * @param NotORM $notORM
     * @param array $where
     * @return self
     */
    static function getInstance(NotORM $notORM, $where=array()) {
        $table = array_search(get_called_class(), NotORM::$tableMap);
        $result = new \NotORM_Result(
            $notORM->getStructure()
                ->getReferencingTable($table, ''),
            $notORM);
        $reflection = new \ReflectionClass(get_called_class());
        return $reflection->newInstanceArgs(array($where, $result));
    }

    /**
     * @param NotORM $notORM
     * @param array $where
     * @param int $limit
     * @return mixed
     */
    static function load(NotORM $notORM, $where = array(), $limit = 1) {
        $table = self::getTableName();
        $res = $notORM->$table()->where($where);
        if($limit === 1) {
            $res = $res->limit($limit)->fetch();
        } elseif (is_integer($limit)) {
            $res = $res->limit($limit);
        }
        return $res;
    }

    /**
     * Search table from container definition
     * @return string | bool
     */
    static function getTableName() {
        return array_search(get_called_class(), NotORM::$tableMap);
    }

    /**
     * @param array $where
     * @param array | bool $keyVal
     * @param string | bool $order
     * @return array
     */
    function getArray($where = array(), $keyVal = FALSE, $order = FALSE) {
        $table = self::getTableName();
        $res = $this->result->notORM->$table();
        if(count($where)) {
            $res->where($where);
        }
        if($order) {
            $res->order($order);
        }
        $array = array();
        foreach ($res as $item) {
            if((bool)$keyVal) {
                $array[$item[$keyVal[0]]] = $item[$keyVal[1]];
                continue;
            }
            $array[] = $item->__toArray();
        }
        return $array;
    }

    function __toArray() {
        return iterator_to_array($this);
    }

}