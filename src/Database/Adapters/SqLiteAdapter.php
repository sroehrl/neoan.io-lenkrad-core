<?php

namespace Neoan\Database\Adapters;

use Neoan\Database\Adapter;
use Neoan\Database\NeoanSQLTranslator;
use Neoan\Helper\DateHelper;
use PDO;
use PDOStatement;

class SqLiteAdapter implements Adapter
{
    private PDO $db;
    private NeoanSQLTranslator $translator;
    private array $rawSubstitutions;
    private array $callFunctions = ['orderBy'=>[],'limit'=>[]];

    public function __construct($credentials = ['location' => __DIR__ . '/database.db'])
    {
        $this->db = new PDO('sqlite:' . $credentials['location']);
        $this->translator = new NeoanSQLTranslator();
    }

    public function easy(string $selectorString, array $conditions = [], mixed $extra = null): array
    {
        $selectorArray = $this->translator->parseEasy($selectorString);
        $sql = "SELECT " . implode(', ', $selectorArray) . " FROM " . $this->translator->tableName;
        if (!empty($conditions)) {
            $this->translator->parseWhere($conditions);
            $sql .= " WHERE " . $this->translator->whereString;
        }
        $this->rawSubstitutions = array_values($conditions);
        $this->addCallFunctions($extra);
        $result = $this->execute($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
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

    private function execute($sql): PDOStatement
    {
        foreach ($this->callFunctions as $key => $set){
            if(!empty($set)){
                $sql .= $this->{$key}($set);
            }
        }
        // reset
        $this->callFunctions = ['orderBy'=>[],'limit'=>[]];
        $exec = $this->db->prepare($sql);
        if (empty($this->rawSubstitutions)) {
            $exec->execute();
        } else {
            $exec->execute($this->rawSubstitutions);
        }
        return $exec;
    }

    public function insert($table, array $content)
    {
        $this->translator->setTableName($table);
        $sql = $this->translator->generateInsert($content);
        $this->rawSubstitutions = $this->translator->statementParameter;
        $this->execute($sql);
        return $this->db->lastInsertId();
    }

    public function delete($table, string $id, bool $hard = false): array|bool|int
    {
        if ($hard) {
            return $this->raw('DELETE FROM ' . $table . ' WHERE id = {{id}}', ['id' => $id]);
        }
        $now = new DateHelper();
        return $this->update($table, ['deletedAt' => (string)$now], ['id' => $id]);
    }

    public function raw(string $sql, array $conditions, mixed $extra = null): bool|array
    {
        $this->rawSubstitutions = [];
        $cleanedSql = preg_replace_callback('/{{([a-z.]+)}}/', function ($matches) use ($conditions) {
            $this->rawSubstitutions[] = $conditions[$matches[1]];
            return '?';
        }, $sql);
        $result = $this->execute($cleanedSql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($table, array $values, array $where): int
    {
        $this->translator->setTableName($table);
        $sql = $this->translator->generateUpdate($values, $where);
        $this->rawSubstitutions = $this->translator->statementParameter;
        $result = $this->execute($sql);
        return $result->rowCount();
    }
}