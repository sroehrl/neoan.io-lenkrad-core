<?php

namespace Neoan\Tests\Mocks;

use PDO;

class DatabaseTestAdapter implements \Neoan\Database\Adapter
{
    private PDO $db;

    public function __construct($credentials = [])
    {
        $this->db =  new PDO('sqlite:'.__DIR__.'/database.db');
    }

    private function removeVars(string $sqlString):string
    {
        return preg_replace('/{{[a-z.]}}/i','?', $sqlString);
    }
    private function parseConditions(array $conditions = [], $mode = 'update'):array
    {
        match($mode){
            'select' => $sql = ' WHERE ',
            'update',
            'insert' => $sql = ''
        };
        $params = [];
        $columns = '';
        if(!empty($conditions)){
            $sql .= '';
            $i = 0;
            foreach ($conditions as $key => $condition){
                $sql .= ($i>0? ', ': '') . "$key = ? ";
                $columns .= ($i>0? ', ': '') . "$key";

                $params[] = $key;
                $i++;
            }
        }
        return [
            'conditionSql' => $sql,
            'params' => $params,
            'columns' => $columns
        ];
    }
    private function stripCondition(array $allowedKeys = [], array $conditionsArray = []):array
    {
        $params = [];
        foreach ($allowedKeys as $allowed){

            if(isset($conditionsArray[$allowed])){
                $params[] = $conditionsArray[$allowed];
            }
        }
        return $params;
    }

    /**
     * @inheritDoc
     */
    public function raw(string $sql, array $conditions, mixed $extra = null)
    {
        $exec = $this->db->prepare($this->removeVars($sql));
        if(empty($conditions)){
            $exec->execute();
        } else {
            $exec->execute(array_values($conditions));
        }
        return $exec->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @inheritDoc
     */
    public function easy(string $selectorString, array $conditions = [], mixed $extra = null)
    {
        $selections = explode(' ', $selectorString);
        $sql = 'SELECT ';
        $from = [];
        foreach ($selections as $i => $selection) {
            preg_match('/([^.]+)\.([^$]+)/',$selection, $matches);
            if(!in_array($matches[1], $from)){
                $from[] = $matches[1];
            }
            $sql .= ($i>0? ', ': '') . "`$matches[1]`.`$matches[2]`";
        }
        $sql .= ' FROM ' . $from[0];
        [
            'conditionSql' => $conditionSql,
            'params' => $allowedParams
        ] = $this->parseConditions($conditions, 'select');
        return $this->raw($sql . $conditionSql, $this->stripCondition($allowedParams, $conditions));

    }

    /**
     * @inheritDoc
     */
    public function insert($table, array $content)
    {
        $sql = "INSERT INTO `$table` (";
        [
            'params' => $allowedParams,
            'columns' => $columns
        ] = $this->parseConditions($content, 'insert');
        $question = array_fill(0, count($content),'?');

        $sql .= $columns . ') VALUES (' . implode(', ', $question) . ')';

        $this->raw($sql, $this->stripCondition($allowedParams, $content));
        return $this->db->lastInsertId();

    }

    /**
     * @inheritDoc
     */
    public function update($table, array $values, array $where)
    {
        $sql = "UPDATE `$table` SET ";
        [
            'params' => $allowedParams,
            'conditionSql' => $conditionSql
        ] = $this->parseConditions($values, 'update');
        $sql .= $conditionSql;
        $setParams = $this->stripCondition($allowedParams, $values);
        [
            'params' => $allowedParams,
            'conditionSql' => $conditionSql
        ] = $this->parseConditions($where, 'select');
        $sql .= $conditionSql;
        $whereParams = $this->stripCondition($allowedParams, $values);
        return $this->raw($sql, array_merge($setParams,$whereParams));
    }

    /**
     * @inheritDoc
     */
    public function delete($table, string $id, bool $hard = false)
    {
        // TODO: Implement delete() method.
    }
}