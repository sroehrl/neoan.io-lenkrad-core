<?php

namespace Neoan\Cli\MigrationHelper;

use Exception;
use Neoan\Database\Database;

class MySqlMigration
{
    private ModelInterpreter $interpreter;


    private array $existingTable;
    public string $sql = '';
    public string $backupSql = '';

    /**
     * @throws Exception
     */
    function __construct(ModelInterpreter $modelInterpreter, string $backup = null)
    {
        $this->interpreter = $modelInterpreter;

        // ensure table existence
        $this->initTable();

        // parse and normalize existing table
        $this->getExistingTable();

        if($backup){
            $this->writeBackupCopy($backup);
        }
        $this->updateTable();
    }


    /**
     * @throws Exception
     */
    public function initTable():void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->interpreter->getTableName()}` (\n";
        $sql .= $this->getFieldSql($this->interpreter->getPrimaryField());
        $sql .= ",\nPRIMARY KEY ({$this->interpreter->getPrimaryField()['name']})\n);\n";
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
            $result = Database::raw("DESCRIBE `{$this->interpreter->getTableName()}`",[]);
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
        $this->backupSql = "CREATE TABLE `$name` SELECT * FROM `{$this->interpreter->getTableName()}`;";
    }
    function updateTable():void
    {
        $sql = '';
        foreach ($this->interpreter->filteredProperties() as $i => $property) {

            $keyword = isset($this->existingTable[$property['name']]) || $property['isPrimary'] ? 'MODIFY' : 'ADD';
            $sql .= "ALTER TABLE `{$this->interpreter->getTableName()}` $keyword {$this->getFieldSql($property)};\n";
            $sql .= $this->createUniqueConstraint($property);
        }
        $this->sql .= $sql;
    }
    function getSQLType(array $property): string
    {
        $type = match ($property['type']) {
            'int' => 'int(11)',
            'float' => 'decimal(10,3)',
            'Neoan\Helper\DateHelper' => 'datetime',
            default => 'varchar(255)',
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
        if($existingIsUnique && !$this->interpreter->isUnique($property)) {
            // remove
            $sql .= "ALTER TABLE `{$this->interpreter->getTableName()}` DROP $name;\n";
        }
        if(!$existingIsUnique && $this->interpreter->isUnique($property)) {
            // add
            $sql .= "ALTER TABLE `{$this->interpreter->getTableName()}` ADD UNIQUE ($name);\n";
        }
        return $sql;
    }
    public function sqlAsSingleCommands(): array
    {
        return array_values(explode(';', $this->sql));
    }
}