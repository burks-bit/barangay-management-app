<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Base service providing shared exception-safety helpers.
 *
 * Every service method that writes to the database should run its work
 * through one of these helpers so that:
 *  - multi-step writes are wrapped in a DB transaction (automatic rollback
 *    when anything inside fails),
 *  - unexpected failures are logged with context,
 *  - the original exception is rethrown so the framework renders the
 *    appropriate error response.
 */
abstract class Service
{
    /**
     * Run a callback inside a DB transaction with error logging.
     *
     * The transaction is rolled back automatically if the callback throws.
     * Deadlocks are retried up to three times before giving up.
     */
    protected function transaction(Closure $callback, string $context)
    {
        try {
            return DB::transaction($callback, 3);
        } catch (Throwable $exception) {
            Log::error($context, ['exception' => $exception]);

            throw $exception;
        }
    }

    /**
     * Run a callback with error logging, without wrapping it in a
     * transaction (useful for single-statement writes or side effects).
     */
    protected function attempt(Closure $callback, string $context)
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            Log::error($context, ['exception' => $exception]);

            throw $exception;
        }
    }
}