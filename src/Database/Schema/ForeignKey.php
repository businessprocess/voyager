<?php

namespace TCG\Voyager\Database\Schema;

use Doctrine\DBAL\Schema\ForeignKeyConstraint as DoctrineForeignKey;

abstract class ForeignKey
{
    public static function make(array $foreignKey, $localTable = null)
    {
        $localTable = $foreignKey['localTable'] ?? $localTable;

        $localColumns = $foreignKey['localColumns'];
        $foreignTable = $foreignKey['foreignTable'];
        $foreignColumns = $foreignKey['foreignColumns'];
        $options = $foreignKey['options'] ?? [];

        // Set the name
        $name = isset($foreignKey['name']) ? trim($foreignKey['name']) : '';
        if (empty($name)) {
            $name = Index::createName($localColumns, 'foreign', $localTable);
        } else {
            $name = Identifier::validate($name, 'Foreign Key');
        }

        return new DoctrineForeignKey(
            $localColumns,
            $foreignTable,
            $foreignColumns,
            $name,
            $options
        );
    }

    /**
     * @return array
     */
    public static function toArray(DoctrineForeignKey $fk, $localTable = null)
    {
        return [
            'name'           => $fk->getName(),
            'localTable'     => $localTable,
            'localColumns'   => $fk->getLocalColumns(),
            'foreignTable'   => $fk->getForeignTableName(),
            'foreignColumns' => $fk->getForeignColumns(),
            'options'        => $fk->getOptions(),
        ];
    }
}
