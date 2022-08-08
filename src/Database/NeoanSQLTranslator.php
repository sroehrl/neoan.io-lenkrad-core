<?php
namespace Neoan\Database;
class NeoanSQLTranslator
{
    public string $tableName;
    public string $whereString;
    public string $setString;
    public array $statementParameter = [];
    function __construct(string $tableName = '')
    {
        $this->tableName = $tableName;
    }
    function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }
    function generateUpdate($setArray, $whereArray): string
    {
        $this->parseSet($setArray);

        $sql = "UPDATE `{$this->tableName}` SET " . $this->setString;
        if(!empty($whereArray)){
            $this->parseWhere($whereArray);
            $sql .= " WHERE " . $this->whereString;
        }
        $this->statementParameter = [...array_values($setArray), ...array_values($whereArray)];
        return $sql;
    }
    function generateInsert($setArray): string
    {
        $keys = array_keys($setArray);
        $this->statementParameter = array_values($setArray);
        $columns = $this->addBackticks(implode(', ', $keys));
        $questionMarks = array_fill(0, count($keys),'?');
        $values = implode(', ', $questionMarks);
        return "INSERT INTO `{$this->tableName}` ($columns) VALUES($values)";
    }

    function parseWhere(array &$whereArray): void
    {
        $sql = '';
        if(!empty($whereArray)){
            $sql = $this->parseArray($whereArray, ' AND ');
        }

        $this->whereString = $sql;
    }
    function parseSet(array &$setArray):void
    {
        $this->setString = $this->parseArray($setArray, ', ', '=');
    }
    function parseEasy(string $selectorString): array
    {
        preg_match('/[a-z_]+/i', $selectorString, $matches);
        $this->setTableName($matches[0]);
        return explode(' ', $selectorString);
    }
    /*
     * Utilities
     * */
    private function parseArray(array &$array, string $separator = ', ', string $nullBinder = 'IS'): string
    {
        $sql = '';
        $i = 0;
        foreach($array as $key => $value) {
            $sql .= $i > 0 ? $separator : '';
            $sql .= $this->addBackticks($key);
            if($value === null){
                $sql .= " $nullBinder NULL";
                unset($array[$key]);
            } else {
                $sql .= ' = ?';
            }
            $i++;
        }
        return $sql;
    }
    private function addBackticks(string $string): string
    {
        return preg_replace('/[a-z_]+/i','`$0`', $string);
    }

}