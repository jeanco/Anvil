<?php namespace Anvil\View;

use Anvil\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\FileViewFinder as IlluminateViewFinder;

class FileViewFinder extends IlluminateViewFinder {

	/**
	 * The filesystem instance.
	 *
	 * @var Illuminate\Filesystem
	 */
	protected $files;

	/**
	 * The path to the current themes' directory.
	 *
	 * @var string
	 */
	protected $themePath;

	/**
	 * The path to the modules' directory.
	 *
	 * @var string
	 */
	protected $modulePath;

	/**
	 * Additional paths registered to the view finder. These paths
	 * take precedence over the theme path and the module path.
	 *
	 * @var array
	 */
	protected $paths = array();

	/**
	 * The extensions recognized by the View Finder.
	 *
	 * @var array
	 */
	protected $extensions = array('php', 'blade.php');

	/**
	 * Create a new instance.
	 *
	 * @param  Anvil\Application                 $anvil
	 * @param  Illuminate\Filesystem\Filesystem  $files
	 * @return void
	 */
	public function __construct(Application $anvil, Filesystem $files)
	{
		$this->anvil = $anvil;
		$this->files = $files;
	}

	/**
	 * Set the path to the themes.
	 *
	 * @param  string  $path
	 * @return void
	 */
	public function setThemePath($path)
	{
		$this->themePath = rtrim($path, '/').'/';
	}

	/**
	 * Set the module path.
	 *
	 * @param  string  $path
	 * @return void
	 */
	public function setModulePath($path)
	{
		$this->modulePath = rtrim($path, '/').'/';
	}

	/**
	 * Get the current theme path.
	 *
	 * @return string
	 */
	public function currentThemePath()
	{
		$theme = $this->anvil->getTheme();

		return $this->themePath.$theme.'/';
	}

	/**
	 * Find a view.
	 *
	 * @param  string  $name
	 * @return string
	 */
	public function find($name)
	{
		list($module, $name) = $this->prepare($name);

		return $this->findInPaths($name, $this->generatePaths($module));
	}

	/**
	 * Separate the module's and view's name.
	 *
	 * @param  string  $name
	 * @return array
	 */
	protected function prepare($name)
	{
		$module = null;

		if(strpos($name, '::') !== false)
		{
			list($module, $name) = explode('::', $name);
		}

		return array($module, $name);
	}

	/**
	 * Generate all of the possible view paths for a module.
	 *
	 * @param  string  $module
	 * @return array
	 */
	protected function generatePaths($module)
	{
		$paths = $this->paths;

		if( ! is_null($module))
		{
			// If we're rendering a module's view, let's add the theme's
			// path to view partials.
			$paths[] = $this->currentThemePath().'views/partials/'.$module;

			// Next, register the corresponding module's views.
			$paths[] = $this->modulePath.$module.'/views';
		}

		else
		{
			// If the view doesn't belong to any modules, let's add
			// the template's path as a fallback.
			$paths[] = $this->currentThemePath().'views/';
		}

		return $paths;
	}

	/**
	 * Add a path to the view finder.
	 *
	 * @param  string  $location
	 * @return void
	 */
	public function addLocation($location)
	{
		$this->paths[] = $location;
	}

	public function addNamespace($namespace, $hint) {}
}
