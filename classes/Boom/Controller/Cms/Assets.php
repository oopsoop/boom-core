<?php defined('SYSPATH') OR die('No direct script access.');

/**
  * Asset controller
  * Contains methods for adding / replacing an asset etc.
  *
  * @package	BoomCMS
  * @category	Assets
  * @category	Controllers
  * @author	Rob Taylor
  * @copyright	Hoop Associates
  */
class Boom_Controller_Cms_Assets extends Boom_Controller
{
	/**
	 *
	 * @var	string	Directory where the view files used in this class are stored.
	 */
	protected $_view_directory = 'boom/assets';

	/**
	 *
	 * @var Model_Asset
	 */
	public $asset;

	/**
	 * Check that they can manage assets.
	 */
	public function before()
	{
		parent::before();

		// Permissions check.
		$this->authorization('manage_assets');

		// Instantiate an asset model.
		$this->asset = new Model_Asset($this->request->param('id'));
	}

	/**
	 * Add tags to a single or multiple assets.
	 *
	 * @uses Model_Asset::add_tags()
	 */
	public function action_add_tags()
	{
		// Get the IDs of the assets the tags are being applied to.
		$asset_ids = (array) $this->request->post('assets');

		// Get the IDs of the tags which are being added.
		$tag_ids = (array) $this->request->post('tags');

		// Add the tags to the assets.
		$this->asset->add_tags($tag_ids, $asset_ids);
	}

	/**
	 * Delete multiple assets at a time.
	 *
	 * Takes an array if asset IDs and calls [Model_Asset::delete()] on each one.
	 *
	 * @uses	Model_Asset::delete()
	 * @uses	Boom_Controller::log()
	 */
	public function action_delete()
	{
		// Get the asset IDs from the POST data.
		$asset_ids = (array) $this->request->post('assets');

		// Make sure no assets appear in the array multiple times.
		$asset_ids = array_unique($asset_ids);

		foreach ($asset_ids as $asset_id)
		{
			// Load the asset from the database.
			$this->asset
				->where('id', '=', $asset_id)
				->find();

			if ( ! $this->asset->loaded())
			{
				// Invalid asset ID
				// Move along, nothing to see here, etc.
				continue;
			}

			// Log a different action depending on whether the asset is being completely deleted
			// or just marked as deleted.
			$log_message = ($this->asset->deleted)? "Deleted asset $this->asset->title (ID: $this->asset->id)" : "Moved asset $this->asset->title (ID: $this->asset->id) to rubbish bin.";

			// Call [Model_Asset::delete()]
			// If the asset isn't marked as deleted then it will be marked it as so.
			// If it's already marked as deleted then it will be deleted it for real.
			$this->asset
				->delete()
				->clear();

			// Log the action.
			$this->log($log_message);
		}
	}

	/**
	 * Download controller.
	 * Allows downloading of assets in zip format.
	 *
	 *  This controller performs two roles:
	 *	*	If a single asset ID is sent then the asset is downloaded  'normally'
	 *	*	If multiple asset IDs are sent then a .zip is created which contains the associates assets.
	 *
	 *
	 * **Accepted GET parameters:**
	 * Name		|	Type		|	Description
	 * ---------------|-----------	------|---------------
	 * assets		|  string		|	Comma separated list of asset IDs.
	 *
	 * @uses ZipArchive
	 */
	public function action_download()
	{
		// Get a unique array of asset IDs to download.
		$asset_ids = array_unique(explode(",", $this->request->query('assets')));

		// How many assets are we downloading?
		$asset_count = count($asset_ids);

		if ($asset_count > 1)
		{
			// Multiple asset download - create a zip file of assets.

			// Get the session ID. This is used in a few places within this if block as it's used in the filename for temp zip file.
			$session_id = Session::instance()->id();

			// The name of the temporary file where the zip archive will be created.
			$tmp_filename = APPPATH.'cache/cms_assets_'.$session_id.'file.zip';

			// Create the zip archive.
			$zip = new ZipArchive;
			$zip->open($tmp_filename, ZipArchive::CREATE);

			// Add the assets to the zip archive
			foreach ($asset_ids as $asset_id)
			{
				// Load the asset from the database to check that it exists.
				$this->asset
					->where('id', '=', $asset_id)
					->find();

				if ($this->asset->loaded())
				{
					// Asset exists add it to the archive.
					$zip->addFile(Boom_Asset::$path.$this->asset->id, $this->asset->filename);
				}

				$this->asset->clear();
			}

			// Finished adding files to the archive.
			$zip->close();

			// Send it to the user's browser.
			$this->response
				->headers(array(
					"Content-type" => "application/zip",
					"Content-Disposition" => "attachment; filename=cms_assets.zip",
					"Pragma" => "no-cache",
					"Expires" => "0"
				))
				->body(
					readfile($tmp_filename)
				);

			// Delete the temporary file.
			unlink($tmp_filename);
		}
		elseif ($asset_count == 1)
		{
			// Download a single asset.

			// Load the asset from the database to check that it exists.
			$this->asset
				->where('id', '=', $asset_ids[0])
				->find();

			if ($this->asset->loaded())
			{
				// Asset exists, send the file contents.
				$this->response
					->headers(array(
						"Content-type" => $this->asset->get_mime(),
						"Content-Disposition" => "attachment; filename=" . basename($this->asset->filename),
						"Pragma" => "no-cache",
						"Expires" => "0"
					))
					->body(
						readfile(Boom_Asset::$path . $this->asset->id)
					);
			}
		}
	}

	/**
	 * Generates the HTML for the filters section of the asset manager.
	 *
	 * @todo The HTML generated by this could be cached. A long cache time could be used as long as the data is deleted when an asset is uploaded or deleted.
	 *
	 * @uses Model_Asset::uploaders()
	 * @uses Model_Asset::types()
	 */
	public function action_filters()
	{
		$this->template = View::factory("$this->_view_directory/filters", array(
			'uploaders'	=>	$this->asset->uploaders(),
			'types'		=>	$this->asset->types(),
		));
	}

	/**
	 * Display the asset manager.
	 *
	 */
	public function action_index()
	{
		$this->template = View::factory("$this->_view_directory/index", array(
			'content'	=>	Request::factory('cms/assets/manager')
				->execute()
				->body(),
			'person'	=>	$this->person,
		));
	}

	/**
	 * Display a list of assets matching certain filters.
	 * This is used for the main content of the asset manager.
	 *
	 * **Accepted GET parameters:**
	 * Name		|	Type		|	Description
	 * ---------------|-----------------|---------------
	 * page		|	 int	 	|	The current page to display. Optional, default is 1.
	 * perpage	|	 int		|	Number of assets to display on each page. Optional, default is 30.
	 * tag		|	 string	|	A tag to filter assets by. Through the magic of hackery also used to filter assets by filters.
	 * sortby		|	 string	|	The column to sort results by and sort order. Optional, default is last_modified-desc.
	 *
	 */
	public function action_list()
	{
		// Get the query data.
		$query_data = $this->request->query();

		// Load the query data into variables.
		$page		=	Arr::get($query_data, 'page', 1);
		$perpage		=	Arr::get($query_data, 'perpage', 30);
		$tags		=	explode("-", Arr::get($query_data, 'tag'));
		$uploaded_by	=	Arr::get($query_data, 'uploaded_by');
		$type		=	Arr::get($query_data, 'type');
		$sortby		=	Arr::get($query_data, 'sortby');
		$title			=	Arr::get($query_data, 'title');

		// Prepare the database query.
		$query = DB::select()
			->from('assets');

		// If a valid tag was given then filter the results by tag..
		if ( ! empty($tags))
		{
			$query
				->join(array('assets_tags', 't1'), 'inner')
				->on('assets.id', '=', 't1.asset_id')
				->distinct(TRUE);

			if (($tag_count = count($tags)) > 1)
			{
				// Get assets which are assigned to all of the given tags.
				$query
					->join(array('assets_tags', 't2'), 'inner')
					->on("t1.asset_id", '=', "t2.asset_id")
					->where('t2.tag_id', 'IN', $tags)
					->group_by("t1.asset_id")
					->having(DB::expr('count(distinct t2.tag_id)'), '>=', $tag_count);
			}
			else
			{
				// Filter by a single tag.
				$query->where('t1.tag_id', '=', $tags[0]);
			}
		}

		// Filtering by title?
		if ($title)
		{
			$query->where('title', 'like', "%$title%");
		}

		$column = 'last_modified';
		$order = 'desc';

		if ( strpos( $sortby, '-' ) > 1 ){
			$sort_params = explode( '-', $sortby );
			$column = $sort_params[0];
			$order = $sort_params[1];
		}

		if (($column == 'last_modified' OR $column == 'title' OR $column == 'filesize') AND ($order == 'desc' OR $order == 'asc'))
		{
			// A valid sort column and direction was given so use them.
			$sortby = $column . '-' . $order;
			$query->order_by($column, $order);
		}
		else
		{
			// No sort column or direction was given, or one of them was invalid, sort by title ascending by default.
			$column = 'title';
			$order = 'asc';
			$query->order_by('title', 'asc');
		}

		// Apply an uploaded by filter.
		if ($uploaded_by)
		{
			// Filtering by uploaded by.
			$query->where('uploaded_by', '=', $uploaded_by);
		}

		// Apply an asset type filter.
		if ($type)
		{
			// Filtering by asset type.
			$query->where('assets.type', '=', constant('Boom_Asset::' . strtoupper($type)));
		}

		// Filtering by deleted assets?
		$query->where('deleted', '=', ($this->request->query('rubbish') == 'rubbish'));

		// Clone the query to count the number of matching assets and their total size.
		$query2 = clone $query;
		$result = $query2
			->select(array(DB::expr('sum(filesize)'), 'filesize'))
			->select(array(DB::expr('count(*)'), 'total'))
			->execute();

		// Get the asset count and total size from the result
		$size = $result->get('filesize');
		$total = $result->get('total');

		// Were any assets found?
		if ($total === 0)
		{
			// Nope, show a message explaining that we couldn't find anything.
			$this->template = View::factory("$this->_view_directory/none_found");
		}
		else
		{
			// Retrieve the results and load Model_Asset classes
			$assets = $query
				->select('assets.*')
				->limit($perpage)
				->offset(($page - 1) * $perpage)
				->as_object('Model_Asset')
				->execute();

			// Put everthing in the views.
			$this->template = View::factory("$this->_view_directory/list", array(
				'assets'		=>	$assets,
				'total_size'	=>	$size,
				'total'		=>	$total,
				'sortby'		=>	$sortby,
				'order'		=>	$order,
			));

			// How many pages are there?
			$pages = ceil($total / $perpage);

			if ($pages > 1)
			{
				// More than one page - generate pagination links.
				$url = '#tag/' . $this->request->query('tag');
				$pagination = View::factory('pagination/query', array(
					'current_page'		=>	$page,
					'total_pages'		=>	$pages,
					'base_url'			=>	$url,
					'previous_page'		=>	$page - 1,
					'next_page'		=>	($page == $pages) ? 0 : ($page + 1),
				));

				// Add the pagination view to the main view.
				$this->template->set('pagination', $pagination);
			}
		}
	}

	/**
	 * Display the asset manager.
	 *
	 * Used by the CMS assets page (/cms/assets) and for editing asset chunks, slideshows, feature images etc.
	 *
	 */
	public function action_manager()
	{
		$this->template = View::factory("$this->_view_directory/manager", array(
			'filters'	=>	Request::factory('cms/assets/filters')->execute(),
		));
	}

	/**
	 * Remove tags from an asset.
	 *
	 * **Accepted POST parameters:**
	 * Name		|	Type		|	Description
	 * ---------------|-----------------|---------------
	 * tags		|	array 	|	Array of tag IDs to be removed from the asset.
	 *
	 */
	public function action_remove_tags()
	{
		// Get an array of tag IDs which are being removed.
		$tag_ids = (array) $this->request->post('tags');

		// Remove the tags from the asset.
		$this->asset->remove('tags', $tag_ids);
	}

	public function action_restore()
	{
		$timestamp = $this->request->query('timestamp');

		if (file_exists(Boom_Asset::$path.$this->asset->id.".".$timestamp.".bak"))
		{
			// Backup the current active file.
			@rename(Boom_Asset::$path.$this->asset->id, Boom_Asset::$path.$this->asset->id.".".$_SERVER['REQUEST_TIME'].".bak");

			// Restore the old file.
			@copy(Boom_Asset::$path.$this->asset->id.".".$timestamp.".bak", Boom_Asset::$path.$this->asset->id);
		}

		// Delete the cache files.
		foreach (glob(Boom_Asset::$path.$this->asset->id."_*.cache") as $cached)
		{
			unlink($cached);
		}

		$this->asset
			->set('last_modified', $_SERVER['REQUEST_TIME'])
			->update();

		// Go back to viewing the asset.
		$this->redirect('/cms/assets/#asset/'.$this->asset->id);
	}

	/**
	 * Save changes to a single asset.
	 *
	 */
	public function action_save()
	{
		// Does the asset exist?
		if ( ! $this->asset->loaded())
		{
			throw new HTTP_Exception_404;
		}

		// Update the asset's details.
		$this->asset
			->values($this->request->post(), array('title','description','visible_from'))
			->update();
	}

	/**
	 * View details about a single asset.
	 *
	 */
	public function action_view()
	{
		// Check that the asset exists
		if ( ! $this->asset->loaded())
		{
			throw new HTTP_Exception_404;
		}

		// If the asset is a BOTR video which isn't marked as encoded then attempt to update the information.
		if ($this->asset->type == Boom_Asset::BOTR AND ! $this->asset->encoded)
		{
			Request::factory('cms/video/sync/' . $asset->id)->execute();
			$this->asset->reload();
		}

		$this->template = View::factory("$this->_view_directory/view", array(
			'asset'	=>	$this->asset,
			'tags'	=>	$this->asset
				->tags
				->order_by('name', 'asc')
				->find_all()
		));
	}
}
