<?php

namespace Neoan\Database;

use Neoan\Helper\DateHelper;
use PDO;

class SqLiteAdapter implements Adapter
{
    private PDO $db;
    private NeoanSQLTranslator $translator;
    private array $rawSubstitutions;

    public function __construct($credentials = ['location' => __DIR__ . '/database.db'])
    {
        $this->db = new PDO('sqlite:' . $credentials['location']);
        $this->translator = new NeoanSQLTranslator();
    }
    private function execute($sql): \PDOStatement
    {
        $exec = $this->db->prepare($sql);
        if(empty($this->rawSubstitutions)){
            $exec->execute();
        } else {
            $exec->execute($this->rawSubstitutions);
        }
        return $exec;
    }


    public function raw(string $sql, array $conditions, mixed $extra = null): bool|array
    {
        $this->rawSubstitutions = [];
        $cleanedSql = preg_replace_callback('/{{([a-z.]+)}}/', function($matches) use($conditions){
            $this->rawSubstitutions[] = $conditions[$matches[1]];
            return '?';
        }, $sql);
        $result = $this->execute($cleanedSql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function easy(string $selectorString, array $conditions = [], mixed $extra = null): array
    {
        $selectorArray = $this->translator->parseEasy($selectorString);
        $sql = "SELECT " . implode(', ', $selectorArray) . " FROM ".$this->translator->tableName;
        if(!empty($conditions)){
            $this->translator->parseWhere($conditions);
            $sql .= " WHERE " . $this->translator->whereString;
        }
        $this->rawSubstitutions = array_values($conditions);
        $result = $this->execute($sql, array_values($conditions));
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($table, array $content)
    {
        $this->translator->setTableName($table);
        $sql = $this->translator->generateInsert($content);
        $this->rawSubstitutions = $this->translator->statementParameter;
        $this->execute($sql);
        return $this->db->lastInsertId();
    }

    public function update($table, array $values, array $where): int
    {
        $this->translator->setTableName($table);
        $sql = $this->translator->generateUpdate($values, $where);
        $this->rawSubstitutions = $this->translator->statementParameter;
        $result = $this->execute($sql);
        return $result->rowCount();
    }

    public function delete($table, string $id, bool $hard = false): array|bool|int
    {
        if($hard){
            return $this->raw('DELETE FROM ' . $table . ' WHERE id = {{id}}',['id'=>$id]);
        }
        $now = new DateHelper();
        return $this->update($table, ['deletedAt' => (string) $now],['id'=> $id]);
    }
}