<?php namespace Core;

class Loader {
	/**
	 * The loaded files
	 */
	protected static $files = array();

	/**
	 * The Directories added to the loading
	 */
	protected static $directories = array();

	/**
	 * The namespace with their path
	 */
	protected static $namespaces = array();

	/**
	 * Was the class initialised?
	 */
	protected static $initialised = false;

	// ------------------------------------------------------------

	/**
	 * Prevent Instanciation
	 */
	protected function __construct () {}

	/**
	 * Initialise the loader
	 */
	public static function initialise ($autoload_location) {
		$autoload = file_get_contents($autoload_location);
		$autoload = json_decode($autoload, true);

		$directories = &$autoload['directories'];
		$namespaces = &$autoload['PSR-4'];

		// add the directory from the autoload.json
		foreach ($directories as $key => $directory) {
			$directory = '/' . trim($directory, '/');
			$directory = \get_template_directory().$directory;
			static::addDirectory($directory);
		}

		// add the namespace from the autoload.json
		foreach ($namespaces as $namespace => $directory) {
			$directory = '/' . trim($directory, '/');
			$directory = \get_template_directory().$directory;
			static::addNamespace($namespace, $directory);
		}

		// If we call a non existing class we can try to include the files
		spl_autoload_register('static::autoload');

		// We just initialised the class
		static::$initialised = true;
	}

	/**
	 * Add a namespace to the namespace queue
	 */
	public static function addNamespace ($namespace, $path, $prepend = false) {
		// normalise the namespace
		$namespace = rtrim($namespace, '\\') . '\\';

		// normalise the path
		$path = rtrim($path, '/');

		// init the array if it's not set
		if (!isset(static::$namespaces[$namespace])) {
			static::$namespaces[$namespace] = array();
		}

		// Insert
		if ($prepend) {
			array_unshift(static::$namespaces[$namespace], $path);
		} else {
			array_push(static::$namespaces[$namespace], $path);
		}
	}

	/**
	 * Add a directory to the autoload queue
	 */
	public static function addDirectory ($directory, $prepend = false) {
		// normalise the path
		$directory = '/' . trim($directory, '/');

		// Insert
		if ($prepend) {
			array_unshift(static::$directories, $directory);
		} else {
			array_push(static::$directories, $directory);
		}
	}

	/**
	 * Decide if it needs to load the class from the namespace or
	 * from the directories.
	 */
	public static function autoload ($class) {
		// is it using namespace?
		if ($pos = strrpos($class, '\\')) {
			// Grab the namespace and class from the string
			$namespace = substr($class, 0, $pos+1);
			$class = substr($class, $pos+1);

			// Load the class from the namespace
			static::loadFromNamespaces($namespace, $class);
		} else {
			static::loadFromDirectories($class);
		}
	}

	/**
	 * Look for path in the namespace array
	 */
	protected static function loadFromNamespaces ($namespace, $class) {
		// Get the sub and main namespace
		$pos = strpos($namespace, '\\');
		$mainNS = substr($namespace, 0, $pos+1);
		$subNS = substr($namespace, $pos+1);
		$subNS = ($subNS === false) ? '' : $subNS ;

		// Define the namespace path
		$NSPath = str_replace('\\', '/', $namespace);
		$NSPath = rtrim($NSPath, '/');

		// Is there a registed namespace?
		if (isset(static::$namespaces[$mainNS])) {
			foreach (static::$namespaces[$mainNS] as $key => $directory) {
				// build the path to the file.
				$path = "{$directory}/{$NSPath}/{$class}.php";

				// Try to load the file.
				if (static::loadFile($path)) { return; }
			}
		}
	}

	/**
	 * Look for path in the directory array
	 */
	protected static function loadFromDirectories ($class) {
		// Loop through the directories to find the class
		foreach (static::$directories as $key => $directory) {
			// build the path to the file.
			$path = "{$directory}/{$class}.php";

			// Try to load the file.
			if (static::loadFile($path)) { return; }
		}
	}

	/**
	 * Load a file
	 */
	public static function loadFile ($path) {
		if (is_readable($path)) {
			array_push(static::$files, $path);
			require_once($path);
			return true;
		}
		// no files were included
		return false;
	}

	/**
	 * Return the included files
	 */
	public static function getIncludedFiles () {
		return static::$files;
	}

	/**
	 * Return the registered directories for autoload
	 */
	public static function getRegisteredDirectories () {
		return static::$directories;
	}

	/**
	 * Return the registered namespaces for autoload
	 */
	public static function getRegisteredNamespaces () {
		return static::$namespaces;
	}
}