<?php

namespace BoomCMS\Core\Menu;

use \BoomCMS\Core\Config;
use \BoomCMS\Core\Auth\Auth as Auth;

use Illuminate\Support\Facades\View;

class Menu
{
    /**
	 *
	 * @var	array	Array of menu items
	 */
    protected $menuItems = [];

    /**
	 *
	 * @var	array	Array of variables to be set in the view.
	 */
    protected $viewData = [];

    /**
	 *
	 * @var	string	Filename of the view to display the menu.
	 */
    protected $viewFilename;

    /**
	 * Convert the menu object to a string by calling [Menu::render()]
	 */
    public function __toString()
    {
        return (string) $this->render();
    }

    /**
	 * Generate a menu object
	 *
	 * @param	string	$group	The menu group to be generated. The default can be set via [Menu::$default]
	 * @param	array	$data	Array of variables to be set in the menu's view.
	 * @uses		Menu::$default
	 */
    public function __construct(Auth $auth, array $data = [])
    {
        $config = Config::get("menu");
        $this->viewFilename = array_get($config, 'view_filename');
        $this->menuItems = (array) array_get($config, 'items');
        $this->viewData = $data;
        $this->auth = $auth;
    }

    /**
	 * Filter the menu items so that any items which have a role set are only displayed if the current user is logged in to the specified role.
	 */
    protected function _filter_items()
    {
        // Array of items we're going to include for this menu.
        $itemsToInclude = [];

        foreach ($this->menuItems as $item) {
            if ( ! isset($item['role']) or $this->auth->loggedIn($item['role'])) {
                $itemsToInclude[] = $item;
            }
        }

        $this->menuItems = $itemsToInclude;
    }

    /**
	 * Display the menu.
	 *
	 * @param	string	$viewFilename		Set the name of the view to use to display the menu.
	 * @return	string
	 */
    public function render($viewFilename = null)
    {
        // If a view filename has been given then set it.
        if ($viewFilename !== NULL) {
            $this->viewFilename = $viewFilename;
        }

        $this->_filter_items();
        $this->sort('priority');

        // Check that we've got some items to add to the menu.
        if ( ! empty($this->menuItems)) {
            // If there's a template for this section then use that, otherwise use a generic template.
            $view = View::make($this->viewFilename, $this->viewData);
            $view->menu_items = $this->menuItems;

            return $view->render();
        }
    }

    /**
	 * Set paramaters for the view.
	 *
	 * @param	mixed	$key	A variable name or array of name => values.
	 * @param	mixed	$value	Value when setting a single variable.
	 * @return	Menu	Returns the current menu object.
	 */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $name => $value) {
                $this->viewData[$name] = $value;
            }
        } else {
            $this->viewData[$key] = $value;
        }

        return $this;
    }

    /**
	 * Sort the items in the menu by the specified key.
	 * Can also be a passed a callback function for custom sorting.
	 *
	 * @param	mixed	$key
	 * @return	Menu	Current menu object
	 */
    public function sort($key)
    {
        if (is_string($key)) {
            $key = usort($this->menuItems, function ($a, $b) use ($key) {
                return $a[$key] - $b[$key];
            });
        }

        if (is_callable($key)) {
            call_user_func($key, $this->menuItems);
        }

        return $this;
    }

    /**
	 * Get / set the filename of the view which will be used to display the menu items.
	 *
	 * @param string $view_filename
	 * @return mixed Returns a string when used as a getter or an instance of Menu when used as a setter.
	 * @uses [Menu::$_view_filename]
	 */
    public function view($view_filename = null)
    {
        if ($view_filename === NULL) {
            // Act as a getter.
            return $this->viewFilename;
        } else {
            // Act as a setter.
            $this->viewFilename = $view_filename;

            return $this;
        }
    }
}