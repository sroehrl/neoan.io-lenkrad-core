<?php

namespace Neoan\Database\Adapters;

use Exception;
use Neoan\Database\Adapter;
use Neoan\Database\NeoanSQLTranslator;
use Neoan\Event\Event;
use PDO;
use PDOStatement;
use Test\Mocks\MockPDO;
use Test\Mocks\MockStm;

class MySQLAdapter implements Adapter
{
    private array $credentials = [
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'neoan',
        'port' => 3306

    ];
    private array $rawSubstitutions;
    private array $conditions;

    private array $callFunctions = [];

    private PDO|MockPDO $db;

    private NeoanSQLTranslator $translator;

    public function __construct($credentials = [], MockPDO|bool $debug = false)
    {
        $this->credentials = array_merge($this->credentials, $credentials);
        if(!$debug){
            $this->db = new PDO("mysql:host={$this->credentials['host']};port={$this->credentials['port']};dbname={$this->credentials['database']}", $this->credentials['user'], $this->credentials['password']);
        } else {
            $this->db = $debug;
        }

        $this->translator = new NeoanSQLTranslator();
    }

    public function raw(string $sql, array $conditions, mixed $extra = null): MockStm|PDOStatement
    {
        $sql = preg_replace_callback('/{\{([a-z.]+)\}\}/i', function($matches) use ($conditions, &$conditionArray){
            $this->rawSubstitutions[] = $conditions[$matches[1]];
            return ':' . $matches[1];
        }, $sql);
        return $this->execute($sql);

    }

    public function easy(string $selectorString, array $conditions = [], mixed $extra = null): array
    {
        $this->conditions = $conditions;
        $select = $this->translator->parseEasy($selectorString);

        $description = $this->describeTable($this->translator->tableName);

        $sql = "SELECT " . implode(', ', $select) . " FROM " .  $this->translator->addBackticks($this->translator->tableName);
        if (!empty($conditions)) {
            $this->normalizeConditions($this->translator->tableName);

            $where = $this->parseConditions($description, $this->conditions, ' AND ', 'LIKE');
            $sql .= " WHERE " . $where;
        }


        $this->typeMatch($this->conditions, $description);

        $this->addCallFunctions($extra);
        $result = $this->execute($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($table, array $content): int
    {
        $tableDefinition = $this->describeTable($table);

        $this->conditions = $content;
        $this->normalizeConditions($table);
        $this->typeMatch($this->conditions, $tableDefinition);
        $values = $this->parseConditions($tableDefinition, $this->conditions, ', ');

        $sql = "INSERT INTO `{$table}` SET ";
        $sql .= $values;

        $result = $this->execute($sql);
        if($result->errorCode() && $result->rowCount() === 0) {
            $error = $result->errorInfo();
            throw new Exception($error[2], $error[1]);
        }

        return $this->db->lastInsertId();
    }

    public function update($table, array $values, array $where): int
    {

        $tableDefinition = $this->describeTable($table);

        // where
        $this->conditions = $where;
        $this->normalizeConditions($table);
        $this->typeMatch($this->conditions, $tableDefinition);
        $where = $this->parseConditions($tableDefinition, $this->conditions, ' AND ', 'LIKE');

        // SET
        $this->conditions = $values;
        $this->normalizeConditions($table);
        $this->typeMatch($this->conditions, $tableDefinition);
        $values = $this->parseConditions($tableDefinition, $this->conditions, ', ');

        $sql = "UPDATE `{$table}` SET ";
        $sql .= $values;
        $sql = rtrim($sql, ', ') . ' WHERE ';
        $sql .= $where;
        $result = $this->execute($sql);
        if($result->errorCode() && $result->rowCount() === 0) {
            $error = $result->errorInfo();
            throw new Exception($error[2], $error[1]);
        }
        return $result->rowCount();

    }

    public function delete($table, string $id, bool $hard = false): int
    {
        $targetTable = $this->describeTable($table);
        if ($hard || !array_key_exists('deletedAt', $targetTable)) {
            $call = $this->raw('DELETE FROM ' . $this->translator->addBackticks($table) . ' WHERE id = {{id}}', ['id' => $id]);
            return $call->rowCount();
        }
        return $this->update($table, ['deletedAt' => '.'], ['id' => $id]);
    }

    public function describeTable($table): array
    {
        $query = $this->db->query('DESCRIBE ' . $table);
        $description = $query->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($description as $field) {
            $result[$table . '.' .$field['Field']] = [
                'type' => $field['Type'],
                'null' => $field['Null'] === 'YES'
            ];
        }
        return $result;
    }

    private function parseConditions(array $description, array $array, string $separator = ', ', string $equalizer = '='): string
    {
        $sql = '';
        $i = 0;
        foreach ($array as $key => $value) {
            if(is_array($value)){
                $isSequential = array_keys($value) === range(0, count($value) - 1);
                $sql .= ($i > 0 ? $separator : '') . ' (' . $this->parseConditions($description, $value, $isSequential ? ' OR ' : ' AND ', $equalizer) . ')';
                foreach ($value as $k => $v) {
                    $array[$k] = $v;
                }

                $i++;
                continue;
            }
            $pureKey = preg_replace('/_\d+$/','', $key);

            $definition = $description[$pureKey];
            $sql .= $i > 0 ? $separator : '';
            $sql .= match ($definition['type']) {
                'datetime', 'date', 'year' => $this->dateHandler($key, $value),
                default => $this->translator->addBackticks($pureKey) . " {$equalizer} :{$this->underscoreKey($key)} "
            };
            $i++;
        }
        return $sql;
    }
    private function dateHandler(string $key, string|null $value) :string
    {
        $pureKey = preg_replace('/_\d+$/','', $key);
        if(is_null($value)) {
            $sql = $this->translator->addBackticks($pureKey) . " = NULL";
            unset($this->conditions[$key]);
            return $sql;
        }
        $sql = '';
        $position = strcspn( $value , '0123456789');

        switch(trim(substr($value, 0, $position))) {
            case '.':
                $sql = $this->translator->addBackticks($pureKey) . ' = NOW() ';
                unset($this->conditions[$key]);
                break;
            case '^':
                $sql = $this->translator->addBackticks($pureKey) . ' IS NULL ';
                unset($this->conditions[$key]);
                break;
            case '!':
                $sql = $this->translator->addBackticks($pureKey) . ' IS NOT NULL ';
                unset($this->conditions[$key]);
                break;
            case '>':
            case '>=':
            case '<':
            case '<=':
                $sql = ' ' . $this->translator->addBackticks($pureKey) . trim(substr($value, 0, $position)) . " :{$this->underscoreKey($key)} ";
                break;
            default:
                $sql = $this->translator->addBackticks($pureKey) . " = :{$this->underscoreKey($key)} ";

        }
        return $sql;
    }

    private function normalizeConditions(string $defaultTable): void
    {
        $normalized = [];
        $i = 0;
        foreach ($this->conditions as $key => $value) {
            if(is_array($value)){
                $passDown = [];
                foreach ($value as $int => $v) {
                    $passDown[$int] = [];
                    foreach ($v as $k => $vv) {
                        $passDown[$int][$k] = $this->normalizeCondition($k, $vv, $defaultTable, $i);
                        $i++;
                    }

                }

                $normalized[] = [...$passDown];

                continue;
            }
            $normalized = array_merge($normalized, $this->normalizeCondition($key, $value, $defaultTable, $i));
            $i++;


        }
        $this->conditions = $normalized;
    }

    private function normalizeCondition($key, $value, $defaultTable, $runner = 0): array
    {
        $return = [];
        // short declaration?
        if(is_numeric($key)){
            $position = strcspn( $value , '!^');
            $key = substr($value, $position + 1);
            $value = substr($value, 0, $position + 1);
        }
        if(!str_contains($key, $defaultTable .'.')) {
            $key = $defaultTable . '.' . $key;
        }
        $return[$key . '_' . $runner] = $value;
        return $return;
    }
    private function underscoreKey(string $key): string
    {
        return str_replace('.', '_', $key);
    }

    /**
     * @throws Exception
     */
    private function typeMatch(array $conditions, array $tableFields): void
    {
        $i = 0;
        foreach ($conditions as $key => $value) {

            if(is_array($value)){
                $this->typeMatch($value, $tableFields);
                continue;
            }
            $pureKey = preg_replace('/_\d+$/','',$key);

            if(!array_key_exists($pureKey, $tableFields)) {
                throw new Exception('Field ' . $pureKey . ' does not exist in table');
            }
            // nullable?
            if($tableFields[$pureKey]['null'] === false && $value === null) {
                $value = false;
            } elseif(is_null($value)) {
                $this->rawSubstitutions[':' . $this->underscoreKey($key)] = null;
                continue;
            }
            // enum?
            if ($value instanceof \BackedEnum) {
                $value = $value->value;
            }
            match (preg_replace('/\([0-9]+\)/','',$tableFields[$pureKey]['type'])) {
                'int', 'tinyint' => $this->rawSubstitutions[':' . $this->underscoreKey($key)] = (int) $value,
                'bool', 'boolean' => $this->rawSubstitutions[':' . $this->underscoreKey($key)] = (bool) $value,
                'datetime', 'date', 'year' => $this->rawSubstitutions[':' . $this->underscoreKey($key)] = preg_replace('/[^0-9-\s:]/','',trim((string) $value)),
                'decimal' => $this->rawSubstitutions[':' . $this->underscoreKey($key)] = (float) $value,
                default => $this->rawSubstitutions[':' . $this->underscoreKey($key)] = (string) $value
            };
        }
    }

    private function addCallFunctions(?array $extra): void
    {
        foreach (['orderBy', 'limit'] as $key){
            if(isset($extra[$key])){
                $this->callFunctions[$key] = $extra[$key];
            }
        }
    }
    private function orderBy($set): string
    {
        return " ORDER BY {$this->translator->addBackticks($set[0])} $set[1]";
    }
    private function limit($set): string
    {
        return " LIMIT $set[0], $set[1]";
    }
    private function execute($sql) : PDOStatement|MockStm
    {
        foreach ($this->callFunctions as $key => $set){
            if(!empty($set)){
                $sql .= $this->{$key}($set);
            }
        }

        Event::dispatch('MySQLAdapter.execute', ['sql' => $sql, 'substitutions' => $this->rawSubstitutions ?? []]);
        $exec = $this->db->prepare($sql);
        if (empty($this->rawSubstitutions)) {
            $exec->execute();
        } else {
            $exec->execute($this->rawSubstitutions);
        }
        // reset
        $this->callFunctions = ['orderBy'=>[],'limit'=>[]];
        $this->rawSubstitutions = [];
        return $exec;
    }
}