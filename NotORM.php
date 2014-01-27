<?php
/**
 * This file is part of project created by Martin Krizan
 * @copyright Martin Krizan [MK] (mnohosten@gmail.com)
 * @author MK http://www.martinkrizan.com
 */

namespace Wigex\Database;

include_once dirname(__FILE__) . "/RowClass.php";

class NotORM extends \NotORM {

    static $tableMap = array();
    protected $defaultRowClass;

    function __construct(\PDO $connection, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        parent::__construct($connection, $structure, $cache);
        $this->rowClass = 'Wigex\Database\RowClass';
        $this->defaultRowClass = $this->rowClass;
    }

    function __call($table, array $where) {
        $this->updateRowClass($table);
        return parent::__call($table, $where);
    }

    function updateRowClass($table) {
        if(array_key_exists($table, self::$tableMap)) {
            $this->rowClass = self::$tableMap[$table];
        } else {
            $this->rowClass = $this->defaultRowClass;
        }
    }

    function setTableMap(array $tableMap) {
        self::$tableMap = $tableMap;
    }

    function getStructure() {
        return $this->structure;
    }

    function getDefaultRowClass() {
        return $this->defaultRowClass;
    }

}