<?php

declare(strict_types=1);


namespace Noem\Framework;


use ErrorException;

class ErrorHandler
{
    public static function init(callable $exceptionHandler, callable ...$exceptionHandlers)
    {
        /**
         * First param is not variadic, so we can loudly fail if no handlers are passed at all
         */
        array_unshift($exceptionHandlers, $exceptionHandler);
        set_exception_handler(function (\Throwable $e) use ($exceptionHandlers) {
            array_walk($exceptionHandlers, fn($c) => $c($e));
            exit;
        });
        /**
         * @throws ErrorException
         */
        $handleError = function (
            int $errno,
            string $errstr,
            string $errfile,
            int $errline,
        ) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        };
        set_error_handler($handleError);

        register_shutdown_function(function () use ($handleError) {
            $error = error_get_last();

            if ($error === null) {
                return;
            }
            if (!self::isFatal($error['type'])) {
                return;
            }
            $handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        });
    }

    private static function isFatal($level): bool
    {
        $errors = E_ERROR;
        $errors |= E_PARSE;
        $errors |= E_CORE_ERROR;
        $errors |= E_CORE_WARNING;
        $errors |= E_COMPILE_ERROR;
        $errors |= E_COMPILE_WARNING;
        return ($level & $errors) > 0;
    }
}
