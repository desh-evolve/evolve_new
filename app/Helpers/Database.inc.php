<?php

namespace App\Services;

use App\Models\Core\DBError;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\TTDate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Throwable;

class CustomDatabaseService
{
    public function initializeDatabase()
    {
        // Try-catch block to handle database connection
        try {
            // If caching is enabled, set the cache directory (using Laravel's config system)
            if (config('cache.dir') != '') {
                $ADODB_CACHE_DIR = config('cache.dir') . DIRECTORY_SEPARATOR;
            }

            // Set the MySQL session options for ANSI and Read Committed isolation level
            DB::statement("SET SESSION sql_mode='ANSI'");
            DB::statement("SET TRANSACTION ISOLATION LEVEL READ COMMITTED");

            // Set the timezone for MySQL session
            DB::statement("SET time_zone = '" . config('app.timezone') . "'");

            // Create the database connection
            $db = DB::connection();

            // Set timezone for TTDate class
            TTDate::setTimeZone(config('app.timezone'));

            // Debug mode (if applicable)
            if (Debug::getVerbosity() == 11) {
                // Optionally log SQL queries
                DB::enableQueryLog();
            }

            // Return the DB connection (similar to the original)
            return $db;
        } catch (Throwable $e) {
            // Handle any connection errors
            Debug::Text('Error connecting to the database!', __FILE__, __LINE__, __METHOD__, 1);
            throw new DBError($e);
        }
    }
}
