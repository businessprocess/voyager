<?php

namespace TCG\Voyager\Database\Schema;

class SchemaException extends \Exception
{
    public static function tableDoesNotExist($tableName)
    {
        return new self(sprintf('There is no table with name "%s" in the schema.', $tableName));
    }

    public static function tableAlreadyExists($tableName)
    {
        return new self(sprintf('The table with name "%s" already exists.', $tableName));
    }
}
