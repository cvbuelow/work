<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
 
    function __construct() 
    {
        parent::__construct();
		
  		// IMPORTANT! This global must be defined BEFORE the flexi auth library is loaded! 
 		// It is used as a global that is accessible via both models and both libraries, without it, flexi auth will not work.
		$this->auth = new stdClass;
		
		// Load 'standard' flexi auth library by default.
		$this->load->library('flexi_auth');	
		
		//globally set error delimiters
		$this->form_validation->set_error_delimiters($this->auth->message_settings['delimiters']['error_prefix'], $this->auth->message_settings['delimiters']['error_suffix']);
		
		//make sure user is logged in
		if(!$this->flexi_auth->is_logged_in() && !preg_match('/^auth[\/]*/', uri_string())) 
		{
			//save the url user was trying to access so we can redirect there once they login
			$this->session->set_userdata('redirect', uri_string());
			redirect('auth');
		}
		
		// Define a global variable to store data that is then used by the end view page.
		$this->data = array('content' => '', 'message' => '');
	}
	
	/**
	 * render a content template and add to the main content area
	 */ 
	function _content($path, $return = FALSE)
	{
		$html = $this->load->view($path, $this->data, TRUE);
		if($return)
			return $html;
		$this->data['content'] .= $html;
	}
	
	/**
	 * render the main temlate
	 */ 
	function _render()
	{
		// Get any status message that may have been set.
		$this->data['message'] = empty($this->data['message']) ? $this->session->flashdata('message') : $this->data['message'];		
		$this->load->view('index', $this->data);
	}
	
	/**
	 * set an error message
	 */ 
	function _error_message($text)
	{
		$this->data['message'] .= $this->auth->message_settings['delimiters']['error_prefix'] . $text . $this->auth->message_settings['delimiters']['error_suffix'];
	}

	/**
	 * file serve
	 */ 
	public function download($app_id = NULL, $field = NULL)
    {		
		$app = new $this->model($app_id);
		if(!empty($app->{$field}) && ($this->flexi_auth->get_user_id() == $app->uacc_id || $this->flexi_auth->is_admin()))
		{
			$data = read_file($this->upload_path . $app->{$field});
			force_download($app->{$field}, $data);
		}
		else
		{
			echo 'Access Denied';
		}
	}
	
	/**
	 * handle file upload and deletion
	 */ 
	function _upload($app, $field)
    {
		//delete file
		if($this->input->post($field . '_remove') == 1 && !empty($app->{$field}))
		{
			unlink($this->upload_path . $app->{$field});
			$app->{$field} = '';
		}
		
		//handle file upload
		if($this->upload->do_upload($field))
		{
			$f = $this->upload->data();
			$app->{$field} = $f['file_name'];
		}
		else
		{
			$upload_error = $this->upload->display_errors('', '');
			
			if($this->input->post('save') && $_FILES[$field]['error'] !== 4)
			{
				$this->_error_message($upload_error);
			}
		}
		
		if($this->input->post('submit') && empty($app->{$field}) && $this->{$field . '_required'})
		{
			$this->_error_message($upload_error);
			$this->data[$field . '_error'] = TRUE;
			$this->valid = FALSE;
		}
	}
}