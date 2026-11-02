<?php

namespace TCG\Voyager\Database\Platforms;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Illuminate\Support\Collection;

abstract class Platform
{
    // abstract public static function getTypes(Collection $typeMapping);

    // abstract public static function registerCustomTypeOptions();

    /**
     * DBAL 4 removed AbstractPlatform::getName(), so we derive the old names from the class.
     */
    public static function getPlatformName(AbstractPlatform $platform)
    {
        return match (true) {
            $platform instanceof AbstractMySQLPlatform => 'mysql',
            $platform instanceof PostgreSQLPlatform    => 'postgresql',
            $platform instanceof SQLitePlatform        => 'sqlite',
            default                                    => strtolower(str_replace('Platform', '', class_basename($platform))),
        };
    }

    public static function getPlatform($platformName)
    {
        $platform = __NAMESPACE__.'\\'.ucfirst($platformName);

        if (!class_exists($platform)) {
            throw new \Exception("Platform {$platformName} doesn't exist");
        }

        return $platform;
    }

    public static function getPlatformTypes($platformName, Collection $typeMapping)
    {
        $platform = static::getPlatform($platformName);

        return $platform::getTypes($typeMapping);
    }

    public static function registerPlatformCustomTypeOptions($platformName)
    {
        $platform = static::getPlatform($platformName);

        return $platform::registerCustomTypeOptions();
    }
}
