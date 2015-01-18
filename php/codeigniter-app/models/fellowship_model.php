<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fellowship_model extends MY_Model {
	
	var $table_name = 'apps_fellowship';
	var $primary_key = 'app_id';
	var $applicant;
	var $ref1;
	var $ref2;
	var $education = array();
	var $default_education_rows = 4;
	
	function __construct($app_id = NULL, $uacc_id = NULL)
	{
		parent::__construct($app_id);
		if(!is_null($uacc_id))
			$this->uacc_id = $uacc_id;
	}
	
	/**
	 * save application from form submission
	 */ 
	function save()
	{
		//save applicant contact info and references
		$this->contact_id = $this->applicant->save();
		$this->ref1_contact_id = $this->ref1->save();
		$this->ref2_contact_id = $this->ref2->save();
		
		$data = array(
			'uacc_id' => $this->uacc_id,
			'contact_id' => $this->contact_id,
			'type' => $this->type,
			'is_us_citizen' => $this->input->post('is_us_citizen'),
			'university' => $this->input->post('university'),
			'major' => $this->input->post('major'),
			'minor' => $this->input->post('minor'),
			'school_dept' => $this->input->post('school_dept'),
			'degree_sought' => $this->input->post('degree_sought'),
			'grad_date' => strtotime($this->input->post('grad_date')),
			'current_standing' => $this->input->post('current_standing'),
			'new_app' => $this->input->post('new_app'),
			'progress_report' => $this->progress_report,
			'category' => $this->input->post('category'),
			'focus' => $this->input->post('focus'),
			'focus_other' => $this->input->post('focus_other'),
			'fellowship_type' => $this->input->post('fellowship_type'),
			'augmentation_funding' => $this->input->post('augmentation_funding'),
			'discretionary_funding' => $this->input->post('discretionary_funding'),
			'essay' => $this->essay,
			'ref1_contact_id' => $this->ref1_contact_id,
			'ref2_contact_id' => $this->ref2_contact_id,
			'project_title' => $this->input->post('project_title'),
			'abstract' => $this->input->post('abstract'),
			'advisor_read' => $this->input->post('advisor_read'),
			'gender' => $this->input->post('gender'),
			'ethnicity' => $this->input->post('ethnicity'),
			'race' => $this->input->post('race'),
			'dob' => strtotime($this->input->post('dob')),
			'disability' => $this->input->post('disability'),
			'certification' => $this->input->post('certification'),
			'status' => (int) $this->status,
		);
		
		if($data['status'])
			$this->db->set('submitted', 'NOW()', FALSE);
				
		if(is_null($this->app_id))
		{
			$this->db->set('created', 'NOW()', FALSE);
			$this->db->insert($this->table_name, $data);
			$this->app_id = $this->db->insert_id();
		}
		else
		{
			$this->db->where($this->primary_key, $this->app_id);
			$this->db->update($this->table_name, $data);
		}
		
		//save education history
		foreach($this->education as $edu_row)
		{
			$edu_row->app_id = $this->app_id;
			$edu_row->save();
		}
	}
	
	/**
	 * load draft application for current user
	 */ 
	function load_draft()
	{
		$row = $this->db->get_where($this->table_name, array(
			'uacc_id' => $this->uacc_id,
			'status' => 0,
		))->result();
		
		if(count($row))
		{
			foreach($row[0] as $prop => $val)
				$this->{$prop} = $val;
			
			//format dates
			foreach(array('grad_date', 'dob') as $field)
				$this->{$field} = empty($this->{$field}) ? '' : date('m/d/Y', $this->{$field});
			
			//load education history rows
			$rows = $this->db->get_where('education', array(
				'app_id' => $this->app_id,
			))->result();
			foreach($rows as $row_num => $row)
				$this->education[] = new Education_model($row->education_id, $row_num);
		}
		//fill up empty rows
		for($i = 0; $i < $this->default_education_rows - count($this->education); $i++)
			$this->education[] = new Education_model();
		
		//load contacts
		$this->applicant = new Contact_model($this->contact_id, 'applicant');
		$this->ref1 = new Contact_model($this->ref1_contact_id, 'ref1');
		$this->ref2 = new Contact_model($this->ref2_contact_id, 'ref2');
	}
}