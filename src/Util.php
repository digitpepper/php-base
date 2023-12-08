<?php

declare(strict_types = 1);

namespace DP;

class Util
{
	/**
	 * @var bool
	 */
	public static $is_constructed = false;

	/**
	 * @var array<string>
	 */
	public static $allowed_language_codes = [];

	/**
	 * @var string
	 */
	public static $language_code = '';

	public static function construct(): void
	{
		if (self::$is_constructed) {
			return;
		}
		if (\defined('\ALLOWED_LANGUAGE_CODES')) {
			self::$allowed_language_codes = \ALLOWED_LANGUAGE_CODES;
		}
		self::init_language_code();
		self::$is_constructed = true;
	}

	public static function is_ajax(): bool
	{
		return (isset($_SERVER['HTTP_ACCEPT']) && \strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === 0) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
	}

	/**
	 * @param mixed $data
	 * @param int $flags
	 */
	public static function send_json($data, int $flags = 0): void
	{
		\header('Content-Type: application/json; charset=UTF-8');
		echo \json_encode($data, $flags);
		exit;
	}

	/**
	 * @param array <mixed> $data
	 * @param string|null $code
	 * @param int $flags
	 * @param int $response_code
	 */
	public static function send_json_error(array $data, string $code = null, int $flags = 0, int $response_code = 400): void
	{
		if ($code === null) {
			$values = \array_values(\debug_backtrace()[0]);
			$file = $values[0];
			$line = $values[1] ?? '';
			$filename = \pathinfo($file, PATHINFO_FILENAME);
			$line = \str_pad((string)$line, 6, '0', STR_PAD_LEFT);
			$code = "$filename-$line";
		}
		\http_response_code($response_code);
		self::send_json([
			'error' => \array_merge($data, [
				'code' => $code,
			]),
		], $flags);
	}

	public static function h(string $string): string
	{
		return \htmlspecialchars($string, ENT_QUOTES);
	}

	/**
	 * @param array <mixed> ...$string
	 * @return mixed
	 */
	public static function t(...$string)
	{
		self::construct();
		$allowed_language_codes = self::$allowed_language_codes;
		$count = \count($allowed_language_codes);
		$string = \array_merge($string, \array_fill(0, $count, ''));
		$map = \array_combine($allowed_language_codes, \array_slice($string, 0, $count));
		return $map[self::$language_code] ?? $string[0] ?? '';
	}

	protected static function init_language_code(): string
	{
		$allowed_language_codes = self::$allowed_language_codes;
		$language_code = $_REQUEST['language'] ?? $allowed_language_codes[0] ?? '';
		if (!\in_array($language_code, $allowed_language_codes)) {
			$language_code = $allowed_language_codes[0] ?? '';
		}
		self::$language_code = $language_code;
		return $language_code;
	}
}
