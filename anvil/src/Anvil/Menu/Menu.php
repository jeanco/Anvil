<?php namespace Anvil\Menu;

use Cache;
use Menu\Factory as MenuFactory;
use Anvil\Menu\Models\Menu as MenuModel;
use Anvil\Menu\Models\Link as LinkModel;

class Menu {

	/**
	 * The menu factory used to generate the menu's HTML.
	 *
	 * @var Menu\Factory
	 */
	protected $factory;

	/**
	 * Register the menu factory.
	 *
	 * @param  Menu\Factory
	 * @return void
	 */
	public function __construct(MenuFactory $factory)
	{
		$this->factory = $factory;
	}

	/**
	 * Add a callback to filter links on menus.
	 *
	 * @param  Closure  $filter
	 * @return void
	 */
	public function filter($filter)
	{
		$this->factory->addFilter($filter);
	}

	/**
	 * Retrieve a menu.
	 *
	 * @param  string  $name
	 * @return Menu\Items\Collection
	 */
	public function get($name, $power = null)
	{
		$menu = $this->factory->get($name);

		// We first need to get all of the links that the current user
		// has access to on the menu.
		$links = $this->fetchLinks($name, $power);

		// Now, add the links to the menu.
		return $this->populateMenu($menu, $links);
	}

	/**
	 * Fetch a menu's links.
	 *
	 * @param  string  $name
	 * @param  int     $power
	 * @return array
	 */
	public function fetchLinks($name, $power = null)
	{
		// Filter the links that do not fit the power's requirements.
		// We will just fetch all of the menu's links if no power was given.
		$links = 'links';

		if( ! is_null($power))
		{
			$links = array('links' => function($query) use($power)
			{
				$query->where(function($query) use ($power)
				{
					$query->whereNull('required_power');
					$query->orWhere('required_power', '<=', $power);
				});

				$query->where(function($query) use($power)
				{
					$query->whereNull('max_power');
					$query->orWhere('max_power', '>=', $power);
				});
			});
		}

		$menu = MenuModel::with($links)
					->where('slug', '=', $name)
					->first();

		if( ! is_null($menu))
		{
			return $menu->links;
		}

		else
		{
			// Todo: throw an exception if the menu doesn't exist?
			return array();
		}
	}

	/**
	 * Add the menu's links to the menu.
	 *
	 * @param  Menu\Menu  $menu
	 * @param  array      $links
	 * @return Menu\Menu
	 */
	public function populateMenu($menu, $links)
	{
		foreach($links as $link)
		{
			$parent = $this->getParent($menu, $link);

			$parent->add($link->title, function($item) use ($link)
			{
				$item->attribute('li.id', $link->id);
				$item->url = $link->url;
			});

			// Let's also add a dropdown.
			$parent->attribute('li.class', 'dropdown');
			$parent->attribute('a.role', 'button');
			$parent->attribute('a.class', 'dropdown-toggle');
			$parent->attribute('a.data-toggle', 'dropdown');
			$parent->attribute('ul.class', 'dropdown-menu');
			$parent->attribute('ul.role', 'menu');
		}

		return $menu;
	}

	/**
	 * Get a link's parent menu.
	 *
	 * @param  Menu\Items\Collection
	 * @param  Menu\Items\Item
	 * @return Menu\Items\Item
	 */
	protected function getParent($menu, $link)
	{
		$parent = $menu;

		if( ! is_null($link->parent_id))
		{
			// Fetch the item whose id matches the current item's parent id.
			$parent = $menu->get(function($item) use ($link)
			{
				return ($item['id'] == $link->parent_id);
			});

			// If there are no items that match the current item's parent id,
			// simply add it to the menu.
			if(is_null($parent))
			{
				// To do: throw an exception?
				$parent = $menu;
			}
		}

		return $parent;
	}	
}