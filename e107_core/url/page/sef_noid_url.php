<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Custom page routing config
 */
if (!defined('e107_INIT')){ exit; }  
 
class core_page_sef_noid_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'allowMain'		=> true,
				'legacy' 		=> '{e_BASE}page.php', // [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script
				'format'		=> 'path', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'defaultRoute'	=> 'view/index',// [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
				'urlSuffix' 	=> '',		// [optional] default empty; string to append to the URL (e.g. .html)
			
				'mapVars' 		=> array(  
					'page_id' => 'id', 
					'page_sef' => 'name', 
				),
				
				'allowVars' 		=> array(  
					'page',
				),
			),

			'rules' => array(
			
				### using only title for pages is risky enough (empty sef for old DB's)
				'<name:{secure}>' => array('view/index', 'allowVars' => false, 'legacyQuery' => '{name}.{page}', 'parseCallback' => 'itemIdByTitle'),

				### page list
				'/' => array('list/index', 'legacyQuery' => '', ),
			) // rule set array
		);
	}

	/**
	 * Admin callback
	 * Language file not loaded as all language data is inside the lan_eurl.php (loaded by default on administration URL page)
	 */
	public function admin()
	{
		// static may be used for performance
		static $admin = array(
			'labels' => array(
				'name' => LAN_EURL_CORE_PAGE, // Module name
				'label' => LAN_EURL_PAGE_SEFNOID_LABEL, // Current profile name
				'description' => LAN_EURL_PAGE_SEFNOID_DESCR, //
				'examples'  => array("{SITEURL}page/page-title")
			),
			'generate' => array('table'=> 'page', 'primary'=>'page_id', 'input'=>'page_title', 'output'=>'page_sef'),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
	
	### CUSTOM METHODS ###
	
	/**
	 * view/item by name callback
	 * @param eRequest $request
	 */
	public function itemIdByTitle(eRequest $request)
	{
		$name = $request->getRequestParam('name');
		
		e107::getMessage()->addDebug('name = '.$name);
		e107::getMessage()->addDebug(print_r($request,true));
		e107::getAdminLog()->toFile('page_sef_noid_url');
		
		if(($id = $request->getRequestParam('id'))) 
		{
			$request->setRequestParam('name', $id);
			return;
		}
		elseif(!$name || is_numeric($name)) return;
		
		$sql = e107::getDb('url');
		$name = e107::getParser()->toDB($name);
		
		if($sql->select('page', 'page_id', "page_sef='{$name}'")) 
		{
			$name = $sql->fetch();
			$request->setRequestParam('name', $name['page_id']);
		}
		else 
		{
			$request->setRequestParam('name', 0);
		}
	}
}
