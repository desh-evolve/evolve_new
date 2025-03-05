<?php

namespace App\Models\Core;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class DBError extends Exception
{
    public function __construct(Exception $e)
    {
        // Rollback any database transaction
        DB::rollBack();

        // Log database error
        Log::error('Database Error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        // Redirect to a maintenance page
        exit($this->handleRedirect('DBError'));
    }

    private function handleRedirect(string $errorType)
    {
        return Redirect::to(route('maintenance'))->with('exception', $errorType)->send();
    }
}

class GeneralError extends Exception
{
    public function __construct(string $message)
    {
        // Rollback any database transaction
        DB::rollBack();

        // Log the error
        Log::error('General Error: ' . $message, [
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString(),
        ]);

        // Output error message (for debugging in development)
        if (app()->environment('local')) {
            die($this->formatExceptionMessage($message));
        }

        // Redirect to a maintenance page
        exit($this->handleRedirect('GeneralError'));
    }

    private function formatExceptionMessage(string $message): string
    {
        return "
        <div style='border: 1px solid red; padding: 10px; font-family: Arial;'>
            <h3 style='color: red;'>EXCEPTION!</h3>
            <p><strong>Error Message:</strong> {$message}</p>
            <p><strong>Error Code:</strong> {$this->getCode()}</p>
            <p><strong>File:</strong> {$this->getFile()}</p>
            <p><strong>Line:</strong> {$this->getLine()}</p>
        </div>";
    }

    private function handleRedirect(string $errorType)
    {
        return Redirect::to(route('maintenance'))->with('exception', $errorType)->send();
    }
}

?>
