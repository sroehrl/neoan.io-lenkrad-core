<?php

namespace Neoan\Database;

use Exception;
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

    private PDO $db;

    private NeoanSQLTranslator $translator;

    public function __construct($credentials = [])
    {
        $this->credentials = array_merge($this->credentials, $credentials);
        $this->db = new PDO("mysql:host={$this->credentials['host']};port={$this->credentials['port']};dbname={$this->credentials['database']}", $this->credentials['user'], $this->credentials['password']);
        $this->translator = new NeoanSQLTranslator();
    }

    public function raw(string $sql, array $conditions, mixed $extra)
    {
        // TODO: Implement raw() method.
    }

    public function easy(string $selectorString, array $conditions = [], mixed $extra = null)
    {
        $select = $this->translator->parseEasy($selectorString);
        $sql = "SELECT " . implode(', ', $select) . " FROM " . $this->translator->tableName;
        if (!empty($conditions)) {
            $this->translator->parseWhere($conditions);
            $sql .= " WHERE " . $this->translator->whereString;
        }
        // TODO: GENERIC Event for SQL
        $this->typeMatch($conditions, $this->describeTable($this->translator->tableName));
        $this->addCallFunctions($extra);
        $result = $this->execute($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($table, array $content)
    {
        // TODO: Implement insert() method.
    }

    public function update($table, array $values, array $where)
    {
        // TODO: Implement update() method.
    }

    public function delete($table, string $id, bool $hard = false)
    {
        // TODO: Implement delete() method.
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
        // TODO: Implement addCallFunctions() method.
    }
    private function execute($sql) : PDOStatement
    {
        $exec = $this->db->prepare($sql);
        if (empty($this->rawSubstitutions)) {
            $exec->execute();
        } else {
            $exec->execute($this->rawSubstitutions);
        }
        return $exec;
    }
}