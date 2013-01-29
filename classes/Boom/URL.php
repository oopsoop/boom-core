<?php defined('SYSPATH') OR die('No direct script access.');
/**
* @package Boom
* @category Helpers
*/
class Boom_URL extends Kohana_URL
{
	/**
	 * Default gravatar options for [URL::gravatar()]
	 *
	 * @var array
	 */
	public static $gravatar_options = array(
		's'	=>	80,
		'd'	=>	'wavatar',
	);

	/**
	 * Generate a unique URI
	 *
	 * @param	string	$base 	URL base, e.g. /path/to/page
	 * @param 	string	$title 	Title of the page.
	 */
	public static function generate($base, $title)
	{
		// Make sure there's no &amps; etc. in the title otherwise these won't be stripped out properly by URL::title()
		$title = html_entity_decode($title);

		// Remove any non-urlable characters.
		$title = URL::title($title);

		// If the base URL isn't empty and there's no trailing / then add one.
		if ($base AND substr($base, -1) != "/")
		{
			$base = $base."/";
		}

		// Only append the base if it's more than just '/'.
		$start_uri = ($base == '/')? $title : $base.$title;
		$append = 0;

		// Get a page URL model which we'll use to call Model_Page_URL::location_available()
		$page_url = new Model_Page_URL;

		// Get a unique URI.
		do
		{
			$uri = ($append > 0)? ($start_uri.$append) : $start_uri;
			$append++;
		}
		while ( ! $page_url->location_available($uri));
		
		return $uri;
	}

	/**
	 * Generate a gravatar URL.
	 *
	 * @param	string	$email	Emailaddress of the gravater.
	 * @param	array	$options	Options to include in the request
	 * @param	boolean	$secure	Whether the gravatar request should use HTTPS
	 * @return	string
	 */
	public static function gravatar($email, array $options = NULL, $secure = FALSE)
	{
		$url = ($secure)? "https://secure.gravatar.com/avatar/" : "http://www.gravatar.com/avatar/";

		// Add the MD5 email to the URL.
		$url .= md5($email);

		// Merge the given options with the default options.
		$options = array_merge(URL::$gravatar_options, (array) $options);

		// Are there any options?
		if ($options !== NULL AND ! empty($options))
		{
			// Turn the options array into a http query string.
			$query = http_build_query($options);

			// Add the query string to the URL.
			$url .= "?" . $query;
		}

		return  $url;
	}
}
