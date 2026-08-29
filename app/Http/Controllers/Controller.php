<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class Controller
{
    /**
     * Run a mutating service action inside an exception guard.
     *
     * Expected application errors (validation failures, missing records,
     * explicit HTTP aborts) are rethrown so the framework answers with the
     * correct status code. Unexpected failures are logged and surfaced to
     * the user as a friendly error flash instead of a raw 500 screen.
     */
    protected function handle(\Closure $mutate, \Closure $success, string $context)
    {
        try {
            $result = $mutate();

            return $success($result);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (ModelNotFoundException | NotFoundHttpException $exception) {
            throw $exception;
        } catch (HttpException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error($context, ['exception' => $exception]);

            return back()->with('error', 'Something went wrong while processing your request. Please try again.');
        }
    }
}
