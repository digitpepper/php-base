<?php

declare(strict_types = 1);

namespace DP;

class ErrorAndException
{
	/**
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errline
	 * @param array <string, array<string|int, mixed>> $errcontext
	 * @return bool
	 * @throws \ErrorException
	 */
	public static function error_handler(int $errno, string $errstr, string $errfile, int $errline, array $errcontext = []): bool
	{
		if (!in_array(\error_reporting(), [0, E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR | E_PARSE])) {
			throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
		}
		return true;
	}

	public static function shutdown_function(): void
	{
		$message = \ob_get_clean();
		$last_error = \error_get_last();
		if ($last_error && !\headers_sent()) {
			$filename = \pathinfo($last_error['file'], \PATHINFO_FILENAME);
			$line = \str_pad((string)$last_error['line'], 6, '0', \STR_PAD_LEFT);
			$code = "$filename-$line";
			$response_code = 500;
			\http_response_code($response_code);
			if (!\ini_get('display_errors')) {
				\header('Content-Type: text/plain');
				$message = 'An error was encountered.';
				$code = 'error';
			}
			if (Util::is_ajax()) {
				Util::send_json_error([
					'message' => trim(strip_tags((string)$message)),
				], $code, \JSON_PRETTY_PRINT, $response_code);
			}
		}
		echo $message;
	}
}
