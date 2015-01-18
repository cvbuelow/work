<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Seed_grant extends MY_Controller {
	
	var $upload_path;
	var $model = 'Seed_grant_model';
	
    function __construct() 
    {
        parent::__construct();
		$this->load->model('contact_model');
		$this->load->model('seed_grant_model');
		$this->upload_path = APPPATH . '../files/seed_grant/';
	}

	/**
	 * index
	 */ 
	public function index()
    {
		$this->apply();
	}

	/**
	 * application form page
	 */ 
	public function apply()
    {		
		$app = new $this->model(NULL, $this->flexi_auth->get_user_id());
		$app->load_draft();
		$user = $this->flexi_auth->get_user_by_id()->result();
		$app->applicant->email = $user[0]->uacc_email;
		
		if($this->input->post('submit') || $this->input->post('save'))
		{
			//validation
			$this->form_validation->set_rules($this->_validation_rules());
			$this->valid = $this->form_validation->run();
			$this->data['show_errors'] = FALSE;
			
			$this->_upload($app, 'proposal');
						
			if($this->input->post('submit'))
			{
				if($this->valid)
				{
					//change application status to submitted
					$app->status = 1;
				}
				else
				{
					$this->data['show_errors'] = TRUE;
					if(validation_errors() !== '')
						$this->_error_message('Please fill out all the required fields, which are marked in red.');
				}
			}
			
			//save application
			$app->save();
			
			if($app->status)
				redirect('seed_grant/success');
		}
		
		$this->data['page_title'] = 'Research Seed Grant Proposal Submission';
		$this->data['form'] = $app;
		$this->data['contact_type'] = 'applicant';
		$this->data['hidden_contact_fields'] = array('alt_email', 'org_other', 'title', 'institution', 'send_txts', 'cell_phone', 'cell_provider', 'alt_phone');
		$this->data['applicant'] = $this->_content('includes/contact', TRUE);
		$this->_content('seed_grant_form');
		$this->_render();
	}
	
	/**
	 * confirmation page
	 */ 
	public function success()
    {		
		$this->data['page_title'] = 'Proposal Submission Successful';
		$this->_content('confirmation');
		$this->_render();		
	}
	
	/**
	 * form validation rules
	 */ 
	function _validation_rules()
	{
		$config['upload_path'] = $this->upload_path;
		$config['allowed_types'] = 'pdf';
		$this->upload->initialize($config);
		$this->proposal_required = TRUE;
		
		return array(
			array('field' => 'applicant_first_name', 'rules' => 'required'),
			array('field' => 'applicant_last_name', 'rules' => 'required'),
			array('field' => 'applicant_email', 'rules' => 'required'),
			array('field' => 'applicant_phone', 'rules' => 'required'),
			array('field' => 'applicant_fax', 'rules' => 'required'),
			array('field' => 'applicant_address1', 'rules' => 'required'),
			array('field' => 'applicant_address2', 'rules' => ''),
			array('field' => 'applicant_city', 'rules' => 'required'),
			array('field' => 'applicant_state', 'rules' => 'required'),
			array('field' => 'applicant_zip', 'rules' => 'required'),
			array('field' => 'applicant_org', 'rules' => 'required'),
			array('field' => 'applicant_school_dept', 'rules' => 'required'),
			array('field' => 'ethnicity[]', 'rules' => ''),
			array('field' => 'ethnicity_other', 'rules' => ''),
			array('field' => 'gender', 'rules' => ''),
			array('field' => 'disabilities', 'rules' => ''),
			array('field' => 'proposal_title', 'rules' => 'required'),
			array('field' => 'current_position', 'rules' => 'required'),
			array('field' => 'current_position_other', 'rules' => ''),
			array('field' => 'position_duration', 'rules' => 'required'),
			array('field' => 'current_research_position', 'rules' => 'required'),
			array('field' => 'research_focus', 'rules' => 'required'),
			array('field' => 'budget_requested', 'rules' => 'required'),
			array('field' => 'certification', 'rules' => 'required'),
		);
	}
}

/* End of file program.php */
/* Location: ./application/controllers/program.php */