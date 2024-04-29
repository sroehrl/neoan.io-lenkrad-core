<?php

namespace Neoan\Database;

use Exception;
use \UnhandledMatchError;
use function PHPUnit\Framework\isInstanceOf;

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

    function generateUpdate($setArray, $whereArray): string
    {
        $this->parseSet($setArray);

        $sql = "UPDATE `{$this->tableName}` SET " . $this->setString;
        if (!empty($whereArray)) {
            $this->parseWhere($whereArray);
            $sql .= " WHERE " . $this->whereString;
        }
        $this->statementParameter = [...array_values($setArray), ...array_values($whereArray)];
        return $sql;
    }

    function parseSet(array &$setArray): void
    {
        $this->setString = $this->parseArray($setArray, ', ', '=');
    }

    private function parseArray(array &$array, string $separator = ', ', string $nullBinder = 'IS'): string
    {
        $sql = '';
        $i = 0;
        foreach ($array as $key => $value) {
            if(is_array($value)){
                $sql .= ($i > 0 ? $separator : '') . ' (' . $this->parseArray($value, ' OR ', $nullBinder) . ')';
                unset($array[$key]);
                foreach ($value as $k => $v) {
                    $array[$k] = $v;
                }

                $i++;
                continue;
            }
            if(is_numeric($key) && preg_match('/^\^|!/', $value, $matches)){
                unset($array[$key]);
                $key = preg_replace_callback('/\\' . $matches[0] . '/i',function($hit) use ($matches, &$value){
                    $value = Selectandi::matchHit($hit[0]);
                    return '';
                }, $value);

            }
            $sql .= $i > 0 ? $separator : '';
            $sql .= $this->addBackticks($key);
            if ($value === null) {
                $sql .= " $nullBinder NULL";
                unset($array[$key]);
            } elseif ($value instanceof Selectandi ) {
                $sql .= ' ' . $value->value;

            } elseif($value === '.') {
                $sql .= ' = NOW()';
            } else {
                try{
                    $operandi = Operandi::matchValue($value);
                    $array[$key] = mb_substr($value, 1);
                    $sql .= $operandi->setNamedParameter($key);
                } catch (UnhandledMatchError | Exception $e){
                    $sql .= ' = :' . $key;
                }

            }
            $i++;
        }
        return $sql;
    }

    public function addBackticks(string $string): string
    {
        return preg_replace('/[a-z_]+/i', '`$0`', $string);
    }

    function parseWhere(array &$whereArray): void
    {
        $sql = '';
        if (!empty($whereArray)) {

            $sql = $this->parseArray($whereArray, ' AND ');
        }

        $this->whereString = $sql;
    }

    function generateInsert($setArray): string
    {
        $keys = array_keys($setArray);
        $this->statementParameter = array_values($setArray);
        $columns = $this->addBackticks(implode(', ', $keys));
        $questionMarks = array_fill(0, count($keys), '?');
        $values = implode(', ', $questionMarks);
        return "INSERT INTO `{$this->tableName}` ($columns) VALUES($values)";
    }

    /*
     * Utilities
     * */

    function parseEasy(string $selectorString): array
    {
        preg_match('/[a-z_]+/i', $selectorString, $matches);
        $this->setTableName($matches[0]);
        $selectorArray = explode(' ', $selectorString);
        $addOns = [];
        foreach ($selectorArray as $i => $value) {
            $addOns[$i] = '';
            if(preg_match('/:([a-z]+)/i', $value, $matches)){
                $addOns[$i] = ' as ' . $matches[1];
                $selectorArray[$i] = str_replace($matches[0], '', $value);
            }
        }
        foreach ($selectorArray as $i => $value) {
            $selectorArray[$i] = $this->addBackticks($value) . $addOns[$i];
        }

        return $selectorArray;
    }

    function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

}