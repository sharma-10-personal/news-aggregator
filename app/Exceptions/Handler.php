<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;
use Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthenticationException::class,
        // You can add other exceptions that should not be reported here
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        // Log exception details
        Log::error('Exception caught: ' . $exception->getMessage(), [
            'exception' => $exception,
        ]);

        // If needed, send exception details to an external service (e.g., Sentry)
        // Sentry\captureException($exception);

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        // If the request expects a JSON response (API)
        if ($request->expectsJson()) {
            // Handle specific exception types
            if ($exception instanceof AuthenticationException) {
                return $this->unauthenticated($request, $exception);
            }

            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The requested resource was not found.'
                ], 404);
            }

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed.',
                    'errors' => $exception->errors()
                ], 422);
            }

            if ($exception instanceof HttpException) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage()
                ], $exception->getStatusCode());
            }

            // Catch any other unhandled exceptions
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }

        // For non-API requests (standard web responses)
        if ($exception instanceof AuthenticationException) {
            return redirect()->route('login');
        }

        // Render a generic error page for others (404, 500, etc.)
        return parent::render($request, $exception);
    }

    /**
     * Handle an unauthenticated exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    public function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        return redirect()->route('login');
    }
}
