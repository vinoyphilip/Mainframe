<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @description	Allows you to use templates in CodeIgniter
 * 				Written and tested in CI 1.7.0
 * 				Usage (in your controller):
 * 					$this->load->template('template_path/template_filename_without_php');
 * 						(just like you load a view)
 * 					then just do: 
 * 					$this->load->view('view_path/view_filename_without_php');
 * 					for your own conveniece I recommend loading the template in the constructor
 * 					of your controller
 * 
 * 				Limitations:
 * 				i) You can't load multiple views from a method in your controller unless
 * 					your template displays only the top part of a template.
 * 					In plain english, if you load 2 views in a single method, the second view 
 * 					will be loaded after the </body></html> part of your template.
 * 					To overcome this, load any extra views from within the first view so that
 * 					there is only one view loaded from the controller's method
 * 
 * @author		Vangelis Bibakis, bibakisv@gmail.com
 * @license		You are free to use the code in your projects, commercial or not.
 * 				You are free to modify and redistribute the code as long as you 
 * 					don't remove the author information and these lines about the license.
 * 				You are free to charge for installing or supporting this library.
 * 				You are not allowed to redistribute the code under a different license or 
 *	 				sell the code.
 * 				You are not allowed to remove the orginal author info even if you make 
 * 					changes or additions to the code.
 * 
 * @version		0.9
 * @date		23-oct-2011
 * 
 * @original_version	0.1
 * @original_date		26-dec-2008
**/


class MY_Loader extends CI_Loader {

	var $template = '';
	var $data = array();
	var $return = FALSE;
	var $template_loaded;
	var $loading_theme = false;
	var $css_files = array();
	var $js_files = array();
	
	function __construct(){
		parent::__construct();
		// Fix the paths. The next line is the only change here. The rest is the exact CI function.
		$this->_ci_library_paths = array(BASEPATH, APPPATH, COMMONPATH);
	}
	
	/**
	 * Allows the loading of templates. Normaly you want pages with the same layout across your site.
	 * If you decide to load a template, then any of the views you load afterwards will be placed inside
	 * the template's code in the position with the $content variable
	 * 
	 * @param $template		The filename of the template to use, in the style of loading a view
	 * @param $data			Any data you wish to pass to the template, in a data array just like the views
	 * @param $return		If you want to just get the template contents set to true
	 */
	function theme($template = '', $data = array(), $return = FALSE)
	{
		if ($template == '')
		{
			return FALSE;
		}

		$this->template = $template;
		$this->data = $this->_ci_object_to_array($data);
		$this->return = $return;

		if (count($this->css_files) > 0){
			$includes = '';
			foreach ($files as $file_array)
			{
				foreach ($file_array as $type => $file)
				{
					if ( ! ((substr($file,0,7) == 'http://') OR (substr($file,0,7) == 'https:/')))
					{
						$file = base_url().$file;
					}
					
					if ($type == 'css')
					{
						$includes .= '<link rel="stylesheet" type="text/css" href="'.$file.'" media="screen" />';
					}
					elseif ($type == 'js')
					{
						$includes .= '<script type="text/javascript" src="'.$file.'"></script>';
					}
				}
			}
			$this->data['includes'] = $includes;
		}
	}
	

	/**
	 * Checks if a template has already been loaded and then either loads the view directly or puts it inside 
	 * the template and loads it with the view.
	 * 
	 * @see /system/libraries/CI_Loader#view($view, $vars, $return)
	 */
	function view($view, $vars = array(), $return = FALSE){		
		// this part loads a view
		if (($this->template == '') || ($this->template_loaded == TRUE)){
			$this->template_loaded = TRUE;
			return $this->_ci_load(array(
				'_ci_view' => $view, 
				'_ci_vars' => $this->_ci_object_to_array($vars), 
				'_ci_return' => $return)
			);
		}
		// this part loads the template
		else {
			$this->template_loaded = TRUE;
			$data = $this->_ci_object_to_array($vars);
			

			// adds the template $data 
			if (count($this->data) > 0){
				foreach ($this->data as $key => $value){
					$data[$key] = $value;
				}
			}
			
			$data['content'] = $this->_ci_load(array(
				'_ci_view' => $this->template.'/'.$view.'.php', 
				'_ci_vars' => $this->_ci_object_to_array($vars),
				'_ci_return' => TRUE)
			);
			
			
			$data['includes'] = '';
			$this->css_files = array_unique($this->css_files);
			foreach ($this->css_files as $css_file)
			{
				$data['includes'] .= '<link rel="stylesheet" type="text/css" href="'.$css_file.'" media="screen" /> ';
			}
			
			$this->js_files = array_unique($this->js_files);
			foreach ($this->js_files as $js_file)
			{
				$data['includes'] .= '<script type="text/javascript" src="'.$js_file.'"></script>';
			}

			$this->loading_theme = true;
			$themepath = '../../themes/'.$this->template.'/theme.php';
		
			return $this->_ci_load(array(
				'_ci_view' => $themepath, 
				'_ci_vars' => $data, 
				'_ci_return' => $return)
			);
		}
	}

	
	function js($filename)
	{
		if ((substr($filename, 0,7) != 'http://') && (substr($filename, 0,8) != 'https://'))
		{
			//$filename = base_url().$filename.'?v='.$this->config->item('cjsuf');
		}
		
		$this->js_files[] = $filename;
		
		// If the theme is rendered just echo the included CSS file tag
		/*
		if ($this->loading_theme){
			echo '<script type="text/javascript" src="'.$filename.'"></script>';
		}
		else {
			$this->js_files[] = $filename;
		}
		*/
	}
	
	
	function css($filename)
	{		
		if ((substr($filename, 0,7) != 'http://') && (substr($filename, 0,8) != 'https://'))
		{
			//$filename = base_url().$filename.'?v='.$this->config->item('cjsuf');
		}
		
		$this->css_files[] = $filename;
		
		// If the theme is rendered just echo the included CSS file tag
		/*
		if ($this->loading_theme){
			echo '<link rel="stylesheet" type="text/css" href="'.$filename.'" media="screen" /> ';
		}
		else {
			$this->css_files[] = $filename;
		}
		*/
	}
	
	/*
	 * Returns a list of tags which load all CSS & Javascript files declared with $this->load->css() and $this->load->js()
	 * Place $this->load->assets() in your theme where you want the tags to appear (normaly in the <head> section)
	 */
	function assets(){
		if ($this->config->item('assets_pipeline'))
		{
			$ci = &get_instance();
			$ci->load->library('assets_pipeline');
			$output = $ci->assets_pipeline->render($this->css_files, $this->js_files);
		}
		else 
		{
			$output = '';
			foreach ($this->css_files as $file){
				$output .= '<link rel="stylesheet" type="text/css" href="'.$file.'" media="screen" />';
			}
			foreach ($this->js_files as $file){
				$output .= '<script type="text/javascript" src="'.$file.'"></script>';
			}
		}
		return $output;
	}
	
	
	function helper($helpers = array())
	{
		foreach ($this->_ci_prep_filename($helpers, '_helper') as $helper)
		{
			if (isset($this->_ci_helpers[$helper]))
			{
				continue;
			}

			$ext_helper = APPPATH.'helpers/'.config_item('subclass_prefix').$helper.EXT;
			$common_ext_helper = COMMONPATH.'helpers/'.config_item('subclass_prefix').$helper.EXT;
			$common_helper = COMMONPATH.'helpers/'.$helper.EXT;

			// Is this a helper extension request?
			if (file_exists($ext_helper))
			{
				$base_helper = BASEPATH.'helpers/'.$helper.EXT;

				if ( ! file_exists($base_helper))
				{
					show_error('Unable to load the requested file: helpers/'.$helper.EXT);
				}

				include_once($ext_helper);
				include_once($base_helper);

				$this->_ci_helpers[$helper] = TRUE;
				log_message('debug', 'Helper loaded: '.$helper);
				continue;
			}
			// Is this an extension request from the common folder ?
			elseif (file_exists($common_ext_helper))
			{
				$base_helper = BASEPATH.'helpers/'.$helper.EXT;

				if ( ! file_exists($base_helper))
				{
					show_error('Unable to load the requested file: helpers/'.$helper.EXT);
				}

				include_once($common_ext_helper);
				include_once($base_helper);

				$this->_ci_helpers[$helper] = TRUE;
				log_message('debug', 'Helper loaded: '.$helper);
				continue;
			}
			elseif (file_exists($common_helper))
			{
				include_once($common_helper);

				$this->_ci_helpers[$helper] = TRUE;
				log_message('debug', 'Helper loaded: '.$helper);
				continue;
			}

			// Try to load the helper
			foreach ($this->_ci_helper_paths as $path)
			{
				if (file_exists($path.'helpers/'.$helper.EXT))
				{
					include_once($path.'helpers/'.$helper.EXT);

					$this->_ci_helpers[$helper] = TRUE;
					log_message('debug', 'Helper loaded: '.$helper);
					break;
				}
			}

			// unable to load the helper
			if ( ! isset($this->_ci_helpers[$helper]))
			{
				show_error('Unable to load the requested file: helpers/'.$helper.EXT);
			}
		}
	}
	
	
}




/* End of file MY_Loader.php */
/* Location: ./app/core/MY_Loader.php */