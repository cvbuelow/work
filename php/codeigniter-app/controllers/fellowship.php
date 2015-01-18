<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fellowship extends MY_Controller {
	
	var $upload_path;
	var $model = 'Fellowship_model';
	
    function __construct() 
    {
        parent::__construct();
		$this->load->model('contact_model');
		$this->load->model('education_model');
		$this->load->model('fellowship_model');
		$this->upload_path = APPPATH . '../files/fellowship/';
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
	public function apply($type = 'grad')
    {		
		if(!in_array($type, array('undergrad', 'grad')))
			exit;
		
		$app = new $this->model(NULL, $this->flexi_auth->get_user_id());
		$app->load_draft();
		$user = $this->flexi_auth->get_user_by_id()->result();
		$app->applicant->email = $user[0]->uacc_email;
		$app->type = $type;
		
		if($this->input->post('submit') || $this->input->post('save'))
		{
			//validation
			$this->form_validation->set_rules($this->_validation_rules());
			$this->valid = $this->form_validation->run();
			$this->data['show_errors'] = FALSE;
			
			$this->_upload($app, 'progress_report');
			$this->_upload($app, 'essay');
						
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
				redirect('fellowship/success');
		}
		
		$this->data['page_title'] = ($type == 'grad' ? 'Graduate' : 'Undergraduate') . ' Fellowship Program';
		$this->data['form'] = $app;
		$this->data['type'] = $type;
		
		//build applicant view
		$this->data['contact_type'] = 'applicant';
		$this->data['hidden_contact_fields'] = array('phone', 'fax', 'org', 'org_other', 'school_dept', 'title', 'institution');
		$this->data['applicant'] = $this->_content('includes/contact', TRUE);
		
		//build ref1 view
		$this->data['contact_type'] = 'ref1';
		$this->data['hidden_contact_fields'] = array('alt_email', 'fax', 'org', 'org_other', 'send_txts', 'cell_phone', 'cell_provider', 'alt_phone');
		$this->data['ref1'] = $this->_content('includes/contact', TRUE);
		
		//build ref2 view
		$this->data['contact_type'] = 'ref2';
		$this->data['ref2'] = $this->_content('includes/contact', TRUE);
		
		//build education history rows
		$this->data['education'] = '';
		foreach($app->education as $row_num => $edu_row)
		{
			$this->data['row_num'] = $row_num;
			$this->data['education'] .= $this->_content('includes/education', TRUE);
		}
		
		$this->_content('fellowship_form');
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
		$this->progress_report_required = FALSE;
		$this->essay_required = TRUE;
		
		return array(
			array('field' => 'applicant_first_name', 'rules' => 'required'),
			array('field' => 'applicant_last_name', 'rules' => 'required'),
			array('field' => 'applicant_email', 'rules' => 'required'),
			array('field' => 'applicant_alt_email', 'rules' => ''),
			array('field' => 'applicant_address1', 'rules' => 'required'),
			array('field' => 'applicant_address2', 'rules' => ''),
			array('field' => 'applicant_city', 'rules' => 'required'),
			array('field' => 'applicant_state', 'rules' => 'required'),
			array('field' => 'applicant_zip', 'rules' => 'required'),
			array('field' => 'applicant_send_txts', 'rules' => ''),
			array('field' => 'applicant_cell_phone', 'rules' => ''),
			array('field' => 'applicant_cell_provider', 'rules' => ''),
			array('field' => 'applicant_alt_phone', 'rules' => ''),

			array('field' => 'ref1_first_name', 'rules' => ''),
			array('field' => 'ref1_last_name', 'rules' => ''),
			array('field' => 'ref1_email', 'rules' => ''),
			array('field' => 'ref1_phone', 'rules' => ''),
			array('field' => 'ref1_address1', 'rules' => ''),
			array('field' => 'ref1_address2', 'rules' => ''),
			array('field' => 'ref1_city', 'rules' => ''),
			array('field' => 'ref1_state', 'rules' => ''),
			array('field' => 'ref1_zip', 'rules' => ''),
			array('field' => 'ref1_title', 'rules' => ''),
			array('field' => 'ref1_institution', 'rules' => ''),
			array('field' => 'ref1_school_dept', 'rules' => ''),

			array('field' => 'ref2_first_name', 'rules' => ''),
			array('field' => 'ref2_last_name', 'rules' => ''),
			array('field' => 'ref2_email', 'rules' => ''),
			array('field' => 'ref2_phone', 'rules' => ''),
			array('field' => 'ref2_address1', 'rules' => ''),
			array('field' => 'ref2_address2', 'rules' => ''),
			array('field' => 'ref2_city', 'rules' => ''),
			array('field' => 'ref2_state', 'rules' => ''),
			array('field' => 'ref2_zip', 'rules' => ''),
			array('field' => 'ref2_title', 'rules' => ''),
			array('field' => 'ref2_institution', 'rules' => ''),
			array('field' => 'ref2_school_dept', 'rules' => ''),

			array('field' => 'is_us_citizen', 'rules' => 'required'),
			array('field' => 'university', 'rules' => 'required'),
			array('field' => 'major', 'rules' => 'required'),
			array('field' => 'minor', 'rules' => 'required'),
			array('field' => 'school_dept', 'rules' => 'required'),
			array('field' => 'degree_sought', 'rules' => 'required'),
			array('field' => 'grad_date', 'rules' => 'required'),
			array('field' => 'current_standing', 'rules' => 'required'),
			array('field' => 'new_app', 'rules' => 'required'),
			array('field' => 'category', 'rules' => 'required'),
			array('field' => 'focus', 'rules' => 'required'),
			array('field' => 'focus_other', 'rules' => ''),
			array('field' => 'fellowship_type', 'rules' => ''),
			array('field' => 'augmentation_funding', 'rules' => ''),
			array('field' => 'discretionary_funding', 'rules' => ''),
			array('field' => 'project_title', 'rules' => 'required'),
			array('field' => 'abstract', 'rules' => 'required'),
			array('field' => 'advisor_read', 'rules' => 'required'),
			array('field' => 'gender', 'rules' => ''),
			array('field' => 'ethnicity', 'rules' => ''),
			array('field' => 'race', 'rules' => ''),
			array('field' => 'dob', 'rules' => ''),
			array('field' => 'disability', 'rules' => ''),
			array('field' => 'certification', 'rules' => 'required'),
		);
	}
}

/* End of file program.php */
/* Location: ./application/controllers/program.php */