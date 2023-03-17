<?php

declare(strict_types = 1);

namespace DP;

use DP\ErrorAndException;

class Base
{
	public static function init(): void
	{
		self::register_autoload();
		self::set_error_handler();
		self::set_exception_handler();
		self::register_shutdown_function();
	}

	public static function register_autoload(): void
	{
		\spl_autoload_register(function ($class) {
			$names = \explode('_', $class);
			$dir = \count($names) > 1 ? '/' . \strtolower(\array_pop($names)) . 's' : '/classes';
			$dirname = \dirname($_SERVER['SCRIPT_FILENAME']);
			if (\is_file("$dirname/protected$dir/$class.php")) {
				require "$dirname/protected$dir/$class.php";
			}

			// https://stackoverflow.com/a/20767037/1916294
			if (\method_exists($class, 'construct')) {
				$class::construct();
			}
		});
	}

	public static function set_error_handler(): void
	{
		\set_error_handler([ErrorAndException::class, 'error_handler']);
	}

	public static function set_exception_handler(): void
	{
		\set_exception_handler([ErrorAndException::class, 'exception_handler']);
	}

	public static function register_shutdown_function(): void
	{
		\register_shutdown_function([ErrorAndException::class, 'shutdown_function']);
	}
}
