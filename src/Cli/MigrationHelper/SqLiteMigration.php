<?php

namespace Neoan\Cli\MigrationHelper;

use Exception;
use Neoan\Database\Database;

class SqLiteMigration
{
    private ModelInterpreter $interpreter;


    private array $existingTable;
    public string $sql = '';
    public string $backupSql = '';
    public string $interimTableName;

    /**
     * @throws Exception
     */
    function __construct(ModelInterpreter $modelInterpreter, string $backup = null)
    {
        $this->interpreter = $modelInterpreter;
        $this->interimTableName = $this->interpreter->getTableName() . date('_d_m_H_i');
        $this->getExistingTable();
        if($backup){
            $this->writeBackupCopy($backup);
        }
        $this->updateTable();
    }

    function getFieldSql($field): string
    {

        return "`{$field['name']}` {$this->getType($field)}";
    }
    function getType(array $property): string
    {
        $type = match ($property['type']) {
            'int' => 'INTEGER',
            'float' => 'REAL',
            default => 'TEXT',
        };
        foreach ($property['attributes'] as $attribute) {
            if($attribute['name'] === "Neoan\\Model\\Attributes\\Type"){
                $instance = $attribute['instance'];
                $type .= ($instance->default ? " DEFAULT {$instance->default} " : '');
            }
        }
        return $type;
    }
    function getExistingTable(): void
    {
        try{
            $result = Database::raw("SELECT sql FROM sqlite_master WHERE name = '{$this->interpreter->getTableName()}'",[]);
            if(!empty($result)){
                preg_match('/\(([^)]+)/', $result[0]['sql'], $matches);
                $result = explode(',',$matches[1]);
            }


            foreach ($result as $field) {
                $row = explode(' ', trim($field));

                $this->existingTable[$row[0]] = [
                    'name' => $row[0],
                    'type' => $row[1],
                    'key' => $row[2] ?? null
                ];
            }

        } catch (Exception $e){}

    }
    function updateTable():void
    {
        $doubleDown = [];
        $sql = "CREATE TABLE `{$this->interimTableName}`(";
        foreach ($this->interpreter->filteredProperties() as $i => $property) {
            $sql .= ($i > 0 ?  ",\n" : "\n") . $this->getFieldSql($property);
            $key = $this->addKey($property);
            $sql .= ' ' . $key;
            if($key === 'UNIQUE' && !$this->existingTablePropertyIsUnique($property['name'])){
                $doubleDown[$property['name']] = $property['name'] . '_non_unique';
                $secureProperty = [
                    'name' => $property['name'] . '_non_unique',
                    'type' => $property['type'],
                    'key'  => null,
                    'attributes' => []
                ];
                $sql .= ",\n" . $this->getFieldSql($secureProperty);
            }
        }
        $this->sql .= $sql . ");\n";
        // copy old data?
        if(isset($this->existingTable)){
            $fields = [];
            foreach($this->existingTable as $existing){
                $fields[] = $existing['name'] . (isset($doubleDown[$existing['name']]) ? ' as ' . $doubleDown[$existing['name']] : '');
            }

            $this->sql .= "INSERT INTO `{$this->interimTableName}`";
            $this->sql .= " SELECT " . implode(', ', $fields) . " FROM `{$this->interpreter->getTableName()}`;\n";
            // try to copy
            foreach ($doubleDown as $key => $value){
                $this->sql .= "UPDATE `{$this->interimTableName}` SET `$value` = `$key`;\n";
                $this->sql .= "ALTER TABLE `{$this->interimTableName}` DROP COLUMN `$value`;\n";
            }
            $this->sql .= "DROP TABLE `{$this->interpreter->getTableName()}`;\n";





        }
        $this->sql .= "ALTER TABLE `{$this->interimTableName}` RENAME TO `{$this->interpreter->getTableName()}`;\n";

    }
    function sqlAsSingleCommands(): array
    {
        return array_values(explode(';', $this->sql));
    }
    private function existingTablePropertyIsUnique(string $propertyName):bool
    {
        return isset($this->existingTable[$propertyName]) && $this->existingTable[$propertyName]['key'] === 'UNIQUE';
    }
    function addKey(array $property): string
    {
        $name = $property['name'];
        if($this->interpreter->getPrimaryField()['name'] === $name){
            return 'PRIMARY KEY';
        }
        if($this->interpreter->isUnique($property)){
            return 'UNIQUE';
        }
        return '';
    }

    /**
     * @throws Exception
     */
    private function writeBackupCopy(string $destination): void
    {
        if(!isset($this->existingTable)) {
            throw new Exception("Failed: Cannot create backup copy of non-existing table");
        }
        $this->backupSql = "CREATE TABLE `$destination` as SELECT * FROM `{$this->interpreter->getTableName()}`;";
    }
}