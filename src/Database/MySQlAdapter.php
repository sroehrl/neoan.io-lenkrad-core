<?php

namespace Neoan\Database;

use Exception;
use Neoan\Event\Event;
use Neoan\Helper\DateHelper;
use PDO;
use PDOStatement;

class MySQlAdapter implements Adapter
{
    private array $credentials = [
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'neoan',
        'port' => 3306

    ];
    private array $rawSubstitutions;

    private array $callFunctions = [];

    private PDO $db;

    private NeoanSQLTranslator $translator;

    public function __construct($credentials = [])
    {
        $this->credentials = array_merge($this->credentials, $credentials);
        $this->db = new PDO("mysql:host={$this->credentials['host']};port={$this->credentials['port']};dbname={$this->credentials['database']}", $this->credentials['user'], $this->credentials['password']);
        $this->translator = new NeoanSQLTranslator();
    }

    public function raw(string $sql, array $conditions, mixed $extra = null)
    {
        $sql = preg_replace_callback('/{\{([a-z.]+)\}\}/i', function($matches) use ($conditions, &$conditionArray){
            $this->rawSubstitutions[] = $conditions[$matches[1]];
            return '?';
        }, $sql);
        $result = $this->execute($sql);

        die();
    }

    public function easy(string $selectorString, array $conditions = [], mixed $extra = null): array
    {
        $select = $this->translator->parseEasy($selectorString);
        $sql = "SELECT " . implode(', ', $select) . " FROM " . $this->translator->tableName;
        if (!empty($conditions)) {
            $this->translator->parseWhere($conditions);
            $sql .= " WHERE " . $this->translator->whereString;
        }
        $this->typeMatch($conditions, $this->describeTable($this->translator->tableName));
        $this->addCallFunctions($extra);
        $result = $this->execute($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($table, array $content): int
    {
        $columns = implode(', ', array_keys($content));
        $backtickedColumns = $this->translator->addBackticks($columns);
        $sql = "INSERT INTO `{$table}` ($backtickedColumns) VALUES(";
        $tableDefinition = $this->describeTable($table);
        foreach ($content as $key => $value) {
            if(array_key_exists($key, $tableDefinition)) {
                switch (preg_replace('/\([0-9]+\)/','',$tableDefinition[$key]['type'])) {
                    case 'int':
                    case 'tinyint':
                        $sql .= '?, ';
                        $this->rawSubstitutions[] = (int)$value;
                        break;
                    case 'bool':
                    case 'boolean':
                        $this->rawSubstitutions[] = (bool)$value;
                        $sql .= '?, ';
                        break;
                    case 'datetime':
                    case 'date':
                    case 'year':
                        if($value === '.'){
                            $sql .= 'NOW(), ';
                        } else {
                            $sql .= '?, ';
                            $this->rawSubstitutions[] = trim((string) $value);
                        }
                        break;
                    case 'decimal':
                        $sql .= '?, ';
                        $this->rawSubstitutions[] = (float) $value;
                        break;
                    default:
                        $sql .= '?, ';
                        $this->rawSubstitutions[] = $value;
                };
            }
        }
        $sql = rtrim($sql, ', ') . ')';
        $exec = $this->execute($sql);
        if($exec->errorCode()) {
            throw new Exception($exec->errorInfo()[2]);
        }
        return $this->db->lastInsertId();
    }

    public function update($table, array $values, array $where)
    {
        $tableDefinition = $this->describeTable($table);
        $this->typeMatch($values, $tableDefinition);
        $this->typeMatch($where, $tableDefinition);
        $sql = "UPDATE `{$table}` SET ";
        foreach ($values as $key => $value) {
            if(array_key_exists($key, $tableDefinition)) {
                switch (preg_replace('/\([0-9]+\)/','',$tableDefinition[$key]['type'])) {
                    case 'datetime':
                    case 'date':
                    case 'year':
                        if($value === '.'){
                            $sql .= $this->translator->addBackticks($key) . ' = NOW(), ';
                            unset($this->rawSubstitutions[':' . $key]);
                        } else {
                            $sql .= $this->translator->addBackticks($key) . " = :$key, ";
                        }
                        break;
                    default:
                        $sql .= $sql .= $this->translator->addBackticks($key) . " = :$key, ";
                };
            }
        }
        $sql = rtrim($sql, ', ') . ' WHERE ';
        $this->translator->parseWhere($where);
        $sql .= $this->translator->whereString;
        $result = $this->execute($sql);
        if($result->errorCode()) {
            throw new Exception($result->errorInfo()[2]);
        }
        return $result->rowCount();

    }

    public function delete($table, string $id, bool $hard = false)
    {
        $targetTable = $this->describeTable($table);
        if ($hard || !array_key_exists('deletedAt', $targetTable)) {
            return $this->raw('DELETE FROM ' . $this->translator->addBackticks($table) . ' WHERE id = {{id}}', ['id' => $id]);
        }
        return $this->update($table, ['deletedAt' => '.'], ['id' => $id]);
    }

    public function describeTable($table): array
    {
        $query = $this->db->query('DESCRIBE ' . $table);
        $description = $query->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($description as $field) {
            $result[$field['Field']] = [
                'type' => $field['Type'],
                'null' => $field['Null'] === 'YES'
            ];
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    private function typeMatch(array $conditions, array $tableFields): void
    {
        foreach ($conditions as $key => $value) {
            if(!array_key_exists($key, $tableFields)) {
                throw new Exception('Field ' . $key . ' does not exist in table');
            }
            // nullable?
            if($tableFields[$key]['null'] === false && $value === null) {
                $value = false;
            }
            match (preg_replace('/\([0-9]+\)/','',$tableFields[$key]['type'])) {
                'int', 'tinyint' => $this->rawSubstitutions[':' . $key] = (int) $value,
                'bool', 'boolean' => $this->rawSubstitutions[':' . $key] = (bool) $value,
                'datetime', 'date', 'year' => $this->rawSubstitutions[':' . $key] = trim((string) $value),
                'decimal' => $this->rawSubstitutions[':' . $key] = (float) $value,
                default => $this->rawSubstitutions[':' . $key] = $value
            };
        }
    }

    private function addCallFunctions(?array $extra): void
    {
        foreach ($this->callFunctions as $key => $set){
            if(isset($extra[$key])){
                $this->callFunctions[$key] = $extra[$key];
            }
        }
    }
    private function orderBy($set): string
    {
        return " ORDER BY $set[0] $set[1]";
    }
    private function limit($set): string
    {
        return " LIMIT $set[0], $set[1]";
    }
    private function execute($sql) : PDOStatement
    {
        foreach ($this->callFunctions as $key => $set){
            if(!empty($set)){
                $sql .= $this->{$key}($set);
            }
        }

        Event::dispatch('MySQLAdapter.execute', ['sql' => $sql, 'substitutions' => $this->rawSubstitutions]);
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