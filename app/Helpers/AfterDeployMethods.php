<?php

namespace App\Helpers;

class AfterDeployMethods
{
    // Symfony
    const ASSETIC_DUMP = 'asseticDump';
    const CREATE_CACHE_DIR = 'createCacheDirectory';
    const NORMALIZE_ASSET_TIMESTAMPS = 'normalizeAssetTimestamps';
    const CACHE_WARMUP = 'cacheWarmup';
    const DOCTRINE_MIGRATE = 'doctrineMigrate';
    const CLEAR_CONTROLLERS = 'clearControllers';

    // Laravel
    const DISABLE_MAINTENANCE = 'disableMaintenance';
    const ENABLE_MAINTENANCE = 'enableMaintenance';
    const MIGRATE = 'migrate';
    const ROLLBACK_MIGRATIONS = 'rollbackMigrations';
    const MIGRATION_STATUS = 'migrationStatus';
    const SEED_DATABASE = 'seedDatabase';
    const CLEAR_CACHE = 'clearCache';
    const CONFIG_CACHE = 'configCache';
    const ROUTE_CACHE = 'routeCache';
    const PUBLIC_STORAGE_SYMLINK = 'createSymlinkToPublicStorage';

    // Descriptions
    public static $methodDescriptions = [
        self::ASSETIC_DUMP => 'Run assetic:dump',
        self::CREATE_CACHE_DIR => 'Create cache directory',
        self::NORMALIZE_ASSET_TIMESTAMPS => 'Normalize asset timestamps',
        self::CACHE_WARMUP => 'Warm up cache',
        self::DOCTRINE_MIGRATE => 'Run doctrine DB migrations',
        self::CLEAR_CONTROLLERS => 'Clear development specific files',
        self::DISABLE_MAINTENANCE => 'Disable maintenance mode',
        self::ENABLE_MAINTENANCE => 'Enable maintenance mode',
        self::MIGRATE => 'Run database migrations',
        self::ROLLBACK_MIGRATIONS => 'Rollback database migrations',
        self::MIGRATION_STATUS => 'Show migration status',
        self::SEED_DATABASE => 'Seed database with sample data',
        self::CLEAR_CACHE => 'Clear cache',
        self::CONFIG_CACHE => 'Configure cache',
        self::ROUTE_CACHE => 'Create route cache file',
        self::PUBLIC_STORAGE_SYMLINK => 'Create symlink to public storage'
    ];
}