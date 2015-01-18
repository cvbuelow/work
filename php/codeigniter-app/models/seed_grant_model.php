<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Seed_grant_model extends MY_Model {
	
	var $table_name = 'apps_seed_grant';
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
			'ethnicity' => serialize($this->input->post('ethnicity')),
			'ethnicity_other' => $this->input->post('ethnicity_other'),
			'gender' => $this->input->post('gender'),
			'disabilities' => $this->input->post('disabilities'),
			'proposal_title' => $this->input->post('proposal_title'),
			'current_position' => $this->input->post('current_position'),
			'current_position_other' => $this->input->post('current_position_other'),
			'position_duration' => $this->input->post('position_duration'),
			'current_research_position' => $this->input->post('current_research_position'),
			'research_focus' => $this->input->post('research_focus'),
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
			foreach(array('ethnicity') as $field)
				$this->{$field} = unserialize($this->{$field});
		}
		$this->applicant = new Contact_model($this->contact_id, 'applicant');
	}
}