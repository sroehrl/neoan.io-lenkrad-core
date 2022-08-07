<?php

namespace Neoan\Model\Migration;

use Exception;
use Neoan\Database\Database;

class MySqlMigration
{
    private array $declaration;
    private array $existingTable;
    public string $sql = '';
    public string $backupSql = '';
    function __construct(string $model, string $backup = null)
    {
        $this->declaration = $model::declare();
        $this->initTable();
        $this->getExistingTable();
        if($backup){
            $this->writeBackupCopy($backup);
        }
        $this->updateTable();
    }
    function getTableName(): string
    {
        return array_key_first($this->declaration);
    }

    /**
     * @throws Exception
     */
    function getPrimaryField(): ?array
    {
        $primaryField = null;
        foreach ($this->declaration[$this->getTableName()] as $property) {
            $primaryField = $property['isPrimary'] ? $property : $primaryField;
        }
        if(!$primaryField){
            throw new Exception('No Primary key set in Model!');
        }
        return $primaryField;
    }
    function initTable():void
    {
        $primaryField = $this->getPrimaryField();
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (\n";
        $sql .= $this->getFieldSql($primaryField);
        $sql .= ",\nPRIMARY KEY ({$primaryField['name']})\n);\n";
        $this->sql .= $sql;
    }
    function getFieldSql($field): string
    {
        $sql = "`{$field['name']}` {$this->getSQLType($field)}";
        $sql .= !$field['nullable'] ? ' NOT NULL ' : ' ';
        $sql .= ($field['isPrimary'] &&  $field['type'] === 'int') ? "AUTO_INCREMENT" : "";
        return $sql;
    }
    function getExistingTable(): void
    {
        try{
            $result = Database::raw("DESCRIBE `{$this->getTableName()}`",[]);
            foreach ($result as $field) {
                $this->existingTable[$field['Field']] = $field;
            }
        } catch (Exception $e){}

    }

    /**
     * @throws Exception
     */
    function writeBackupCopy(string $name): void
    {
        if(!isset($this->existingTable)) {
            throw new Exception("Failed: Cannot create backup copy of non-existing table");
        }
        $this->backupSql = "CREATE TABLE `$name` SELECT * FROM `{$this->getTableName()}`;";
    }
    function updateTable():void
    {
        $sql = '';
        foreach ($this->filteredProperties() as $i => $property) {

            $keyword = isset($this->existingTable[$property['name']]) || $property['isPrimary'] ? 'MODIFY' : 'ADD';
            $sql .= "ALTER TABLE `{$this->getTableName()}` $keyword {$this->getFieldSql($property)};\n";
            $sql .= $this->createUniqueConstraint($property);
        }
        $this->sql .= $sql;
    }
    function getSQLType(array $property): string
    {
        $type = match ($property['type']) {
            'int' => 'int(11)',
            'string' => 'varchar(255)',
            'float' => 'decimal(10,3)'
        };
        foreach ($property['attributes'] as $attribute) {
            if($attribute['name'] === "Neoan\\Model\\Attributes\\Type"){
                $instance = $attribute['instance'];
                $type = strtoupper($instance->propertyType);
                $type .= ($instance->propertyLength ? "({$instance->propertyLength})" : '');
                $type .= ($instance->default ? " DEFAULT {$instance->default} " : '');
            }
        }
        return $type;
    }
    function createUniqueConstraint(array $property): string
    {
        $sql = '';
        $name = $property['name'];
        $existingIsUnique = isset($this->existingTable[$name]) && str_starts_with(strtolower($this->existingTable[$name]['Key']),'uni');
        $newIsUnique = $this->isUnique($property);
        if($existingIsUnique && !$newIsUnique) {
            // remove
            $sql .= "ALTER TABLE `{$this->getTableName()}` DROP $name;\n";
        }
        if(!$existingIsUnique && $newIsUnique) {
            // add
            $sql .= "ALTER TABLE `{$this->getTableName()}` ADD UNIQUE ($name);\n";
        }
        return $sql;
    }
    public function sqlAsSingleCommands(): array
    {
        return array_values(explode(';', $this->sql));
    }
    private function isUnique(array $property): bool
    {
        foreach ($property['attributes'] as $attribute) {
            if($attribute['name'] === "Neoan\\Model\\Attributes\\IsUnique"){
                return true;
            }
        }
        return false;
    }
    private function filteredProperties():array
    {
        return array_filter($this->declaration[$this->getTableName()], function(array $property){
            foreach ($property['attributes'] as $attribute){
                if($attribute['type'] === \Neoan\Enums\AttributeType::ATTACH){
                    return false;
                }
            }
            return true;
        });
    }
}