<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Program_model extends MY_Model {
	
	var $table_name = 'apps_program';
	var $primary_key = 'app_id';
	var $applicant;
	
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
		//save applicant contact info
		$this->contact_id = $this->applicant->save();
		
		$data = array(
			'uacc_id' => $this->uacc_id,
			'contact_id' => $this->contact_id,
			'funding_programs' => serialize($this->input->post('funding_programs')),
			'new_app' => $this->input->post('new_app'),
			'targets' => serialize($this->input->post('targets')),
			'focus' => $this->input->post('focus'),
			'activities_occur' => $this->input->post('activities_occur'),
			'activity_involves' => $this->input->post('activity_involves'),
			'teacher_parents' => $this->input->post('teacher_parents'),
			'followup' => $this->input->post('followup'),
			'augmentation' => $this->input->post('augmentation'),
			'augmentation_targets' => serialize($this->input->post('augmentation_targets')),
			'augmentation_amount' => $this->input->post('augmentation_amount'),
			'pre_college_support' => serialize($this->input->post('pre_college_support')),
			'pre_college_support_other' => $this->input->post('pre_college_support_other'),
			'pre_college_assoc' => $this->input->post('pre_college_assoc'),
			'pre_college_occur' => serialize($this->input->post('pre_college_occur')),
			'public_outreach_support' => serialize($this->input->post('public_outreach_support')),
			'public_outreach_support_other' => $this->input->post('public_outreach_support_other'),
			'teacher_support' => serialize($this->input->post('teacher_support')),
			'teacher_support_other' => $this->input->post('teacher_support_other'),
			'collab_effort' => $this->input->post('collab_effort'),
			'proposal_title' => $this->input->post('proposal_title'),
			'budget_requested' => $this->input->post('budget_requested'),
			'proposal' => $this->proposal,
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
			
			//restore arrays
			foreach(array('funding_programs', 'targets', 'augmentation_targets', 'pre_college_support', 'pre_college_occur', 'public_outreach_support', 'teacher_support') as $field)
				$this->{$field} = unserialize($this->{$field});
		}
		$this->applicant = new Contact_model($this->contact_id, 'applicant');
	}
}