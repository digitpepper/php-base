<?php

declare(strict_types = 1);

namespace DP;

use DP\Util;
use DP\Database;
use Exception;

class ErrorAndException
{
	public static function error_handler(int $errno, string $errstr, string $errfile, int $errline): void
	{
		$response_code = 500;
		$message = \sprintf(
			"<br />\r\n<b>%s</b>: %s in <b>%s</b> on line <b>%s</b><br />",
			Util::h(self::get_friendly_error_type($errno)),
			Util::h($errstr),
			Util::h($errfile),
			Util::h((string)$errline)
		);
		$filename = \pathinfo($errfile, \PATHINFO_FILENAME);
		$line = \str_pad((string)$errline, 6, '0', \STR_PAD_LEFT);
		$code = "$filename-$line";
		\error_log(\trim(\strip_tags($message)));
		if (\error_reporting() === 0) {
			$message = 'An error was encountered.';
			$code = 'error';
		}
		if (Util::is_ajax()) {
			Util::send_json_error([
				'message' => \trim(\strip_tags($message)),
			], $code, \JSON_PRETTY_PRINT, $response_code);
		}
		\http_response_code($response_code);
		\header('Content-Type: text/html; charset=UTF-8');
		echo $message;
		exit;
	}

	public static function exception_handler(\Throwable $exception): void
	{
		$message = $exception->getMessage();
		$code = $exception->getCode();
		$filename = \pathinfo($exception->getFile(), \PATHINFO_FILENAME);
		$line = \str_pad((string)$exception->getLine(), 6, '0', \STR_PAD_LEFT);
		\http_response_code(\is_int($code) && 400 <= $code && $code < 600 ? $code : 500);
		if (Util::is_ajax()) {
			$data = [
				'message' => $message,
			];
			if (\error_reporting() !== 0) {
				$data['trace'] = $exception->getTrace();
			}
			Util::send_json_error($data, "$filename-$line", \JSON_PRETTY_PRINT);
		}
		\header('Content-Type: text/plain; charset=UTF-8');
		echo "$message\r\n" . (\error_reporting() !== 0 ? $exception->getTraceAsString() . "\r\n" : '') . "error code: $filename-$line";
		exit;
	}

	public static function shutdown_function(): void
	{
		if (\class_exists('Database', false) && Database::$pdo && Database::$pdo->inTransaction()) {
			try {
				Database::roll_back();
			} catch (\Exception $e) {
			}
		}
	}

	// http://php.net/manual/en/errorfunc.constants.php#109430
	protected static function get_friendly_error_type(int $type): string
	{
		switch ($type) {
			case \E_ERROR:
				return 'E_ERROR';
			case \E_WARNING:
				return 'E_WARNING';
			case \E_PARSE:
				return 'E_PARSE';
			case \E_NOTICE:
				return 'E_NOTICE';
			case \E_CORE_ERROR:
				return 'E_CORE_ERROR';
			case \E_CORE_WARNING:
				return 'E_CORE_WARNING';
			case \E_COMPILE_ERROR:
				return 'E_COMPILE_ERROR';
			case \E_COMPILE_WARNING:
				return 'E_COMPILE_WARNING';
			case \E_USER_ERROR:
				return 'E_USER_ERROR';
			case \E_USER_WARNING:
				return 'E_USER_WARNING';
			case \E_USER_NOTICE:
				return 'E_USER_NOTICE';
			case \E_STRICT:
				return 'E_STRICT';
			case \E_RECOVERABLE_ERROR:
				return 'E_RECOVERABLE_ERROR';
			case \E_DEPRECATED:
				return 'E_DEPRECATED';
			case \E_USER_DEPRECATED:
				return 'E_USER_DEPRECATED';
		}
		return 'UNKNOWN';
	}
}
