<?php

/**
* CMS controller
*
* @package Sledge
* @author Hoop Associates	www.thisishoop.com	mail@hoopassociates.co.uk
* @copyright 2011, Hoop Associates Ltd
*/
class Controller_Cms extends Sledge_Controller
{	
	public function before()
	{
		parent::before();
		
		$this->template = View::factory( 'cms/standard_template' );
		
		// Require a user to be logged in for anything cmsy.
		if (!Auth::instance()->logged_in())
		{
			Cookie::set( 'redirect_after', Request::current()->uri() );
			
			$this->request->redirect( '/cms/login' );
		}
	}
	
	public function action_index()
	{
		$this->request->redirect( '/' );	
	}

	public function after()
	{
		// Add the header subtemplate.
		$this->template->client = Kohana::$config->load('config')->get( 'client_name' );
		
		parent::after();
	}
}


?>
