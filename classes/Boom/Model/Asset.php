<?php defined('SYSPATH') OR die('No direct script access.');

/**
* Asset Model
*
* @package	BoomCMS
* @category	Assets
* @category	Models
* @author	Rob Taylor
* @copyright	Hoop Associates
*
*/
class Boom_Model_Asset extends ORM_Taggable
{
	protected $_belongs_to = array(
		'uploader'	=>	array('model' => 'person', 'foreign_key' => 'uploaded_by'),
	);

	protected $_created_column = array(
		'column'	=>	'uploaded_time',
		'format'	=>	TRUE,
	);

	protected $_table_columns = array(
		'id'				=>	'',
		'title'				=>	'',
		'description'		=>	'',
		'width'			=>	'',
		'height'			=>	'',
		'filename'			=>	'',
		'visible_from'		=>	'',
		'type'			=>	'',
		'filesize'			=>	'',
		'deleted'			=>	FALSE,
		'duration'			=>	'',
		'encoded'			=>	'',
		'views'			=>	'',
		'uploaded_by'		=>	'',
		'uploaded_time'	=>	'',
		'last_modified'		=>	'',
	);

	protected $_table_name = 'assets';

	/**
	 *
	 * @var	array	Cache variable for [Model_Asset::old_files()]
	 */
	protected $_old_files = NULL;

	/**
	 * Delete an asset.
	 *
	 * Assets are deleted in two stages:
	 *
	 * * If the deleted property is false then this is changed to true and the asset is merely marked as deleted.
	 * * If the asset is already marked as deleted then the asset is deleted for real.
	 *
	 * @uses ORM::delete()
	 */
	public function delete()
	{
		if ($this->deleted)
		{
			// Asset is already marked as deleted, so delete it for real.
			return parent::delete();
		}
		else
		{
			// Asset hasn't been marked as deleted yet
			// So only pretend that it's deleted for now.
			return $this
				->set('deleted', TRUE)
				->update();
		}
	}

	public function filters()
	{
		return array(
			'visible_from' => array(
				array('strtotime'),
			),
		);
	}

	/**
	 * Returns an array of old files which have been replaced.
	 * Where an asset has been replaced the array will contain the names of the backup files for the previous versions.
	 *
	 * @return	array
	 */
	public function old_files()
	{
		// If the asset doesn't exist return an empty array.
		if ( ! $this->loaded())
		{
			return array();
		}

		if ($this->_old_files === NULL)
		{
			// Add files for previous versions of the asset.
			// Wrap the glob in array_reverse() so that we end up with an array with the most recent first.
			foreach (array_reverse(glob(Boom_Asset::$path . $this->id . ".*.bak")) as $file)
			{
				// Get the version ID out of the filename.
				preg_match('/' . $this->id . '.(\d+).bak$/', $file, $matches);

				if (isset($matches[1]))
				{
					$this->_old_files[$matches[1]] = $file;
				}
				else
				{
					$this->_old_files[] = $file;
				}
			}
		}

		return $this->_old_files;
	}

	/**
	 * Find the mimetype of the asset file.
	 *
	 * @return string Mimetype string.
	 */
	public function get_mime()
	{
		return File::mime(Boom_Asset::$path . $this->id);
	}

	/**
	 * Returns the asset's type in a human readable format.
	 *
	 * @return 	string
	 */
	public function type()
	{
		return Boom_Asset::type($this->type);
	}

	/**
	 * Returns an array of the type of assets which exist in the database.
	 *
	 * Retrieves the numeric asset types which are stored in the database.
	 * These are then converted to words using [Boom_Asset::type()]
	 *
	 * @uses Boom_Asset::type()
	 * @return array
	 */
	public function types()
	{
		// Get the available asset types in numeric format.
		$types = DB::select('type')
			->distinct(TRUE)
			->from('assets')
			->where('deleted', '=', FALSE)
			->where('type', '!=', 0)
			->execute($this->_db)
			->as_array();

		// Turn the numeric asset types into user friendly strings.
		$types = Arr::pluck($types, 'type');
		$types = array_map(array('Boom_Asset', 'type'), $types);
		$types = array_map('ucfirst', $types);

		// Return the results.
		return $types;
	}

	/**
	 * Gets an array of the ID and name of people who have uploaded assets.
	 *
	 * The returned array will be an associative array of person ID => name.
	 *
	 * People who have uploaded assets, but who's assets are all deleted, will not appear in the returned array.
	 *
	 * @return array
	 */
	public function uploaders()
	{
		return DB::select('id', 'name')
			->from('people')
			->where('id', 'in', DB::select('uploaded_by')
				->from('assets')
				->where('deleted', '=', FALSE)
				->distinct(TRUE)
			)
			->order_by('name', 'asc')
			->execute($this->_db)
			->as_array('id', 'name');
	}
}
