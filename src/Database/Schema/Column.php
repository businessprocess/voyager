<?php

namespace TCG\Voyager\Database\Schema;

use Doctrine\DBAL\Schema\Column as DoctrineColumn;
use Doctrine\DBAL\Types\Type as DoctrineType;
use TCG\Voyager\Database\Types\Type;

abstract class Column
{
    public static function make(array $column, ?string $tableName = null)
    {
        $name = Identifier::validate($column['name'], 'Column');
        $type = $column['type'];
        $type = ($type instanceof DoctrineType) ? $type : DoctrineType::getType(trim($type['name']));
        if ($type instanceof Type) {
            $type->tableName = $tableName;
        }

        // DBAL 3 silently dropped defaults the platform does not support, DBAL 4 sends them to the database
        if (Type::isDefaultDisabled(Type::lookupName($type))) {
            unset($column['default']);
        }

        return new DoctrineColumn($name, $type, static::castOptions($column));
    }

    /**
     * DBAL 4 rejects unknown column options and is strict about their types,
     * while the options here come straight from the (JSON) request.
     *
     * @return array
     */
    protected static function castOptions(array $column)
    {
        $casts = [
            'length'           => 'int',
            'precision'        => 'int',
            'scale'            => 'int',
            'unsigned'         => 'bool',
            'fixed'            => 'bool',
            'notnull'          => 'bool',
            'autoincrement'    => 'bool',
            'default'          => null,
            'columnDefinition' => 'string',
            'comment'          => 'string',
        ];

        $options = [];
        foreach (array_intersect_key($column, $casts) as $option => $value) {
            $cast = $casts[$option];

            if ($cast === 'int') {
                if ($value === null || $value === '') {
                    // scale is not nullable
                    if ($option !== 'scale') {
                        $options[$option] = null;
                    }
                    continue;
                }
                $value = (int) $value;
            } elseif ($cast === 'bool') {
                $value = (bool) $value;
            } elseif ($cast === 'string') {
                if ($value === null && $option === 'columnDefinition') {
                    $options[$option] = null;
                    continue;
                }
                $value = (string) $value;
            }

            $options[$option] = $value;
        }

        return $options;
    }

    /**
     * @return array
     */
    public static function toArray(DoctrineColumn $column)
    {
        $columnArr = $column->toArray(true);
        unset($columnArr['typeName'], $columnArr['values']);
        $columnArr['type'] = Type::toArray($column->getType());
        $columnArr['oldName'] = $columnArr['name'];
        $columnArr['null'] = $columnArr['notnull'] ? 'NO' : 'YES';
        $columnArr['extra'] = static::getExtra($column);
        $columnArr['composite'] = false;

        return $columnArr;
    }

    /**
     * @return string
     */
    protected static function getExtra(DoctrineColumn $column)
    {
        $extra = '';

        $extra .= $column->getAutoincrement() ? 'auto_increment' : '';
        // todo: Add Extra stuff like mysql 'onUpdate' etc...

        return $extra;
    }
}
