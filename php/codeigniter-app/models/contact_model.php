<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Contact_model extends MY_Model {
	
	var $table_name = 'contacts';
	var $primary_key = 'contact_id';
	var $contact_type;

	function __construct($id = NULL, $type = '')
	{
		parent::__construct($id);
		$this->contact_type = $type;
	}
	
	function save()
	{
		$prefix = $this->contact_type . '_';
		
		foreach($this->db_fields as $field)
		{
			if($field !== $this->primary_key && isset($_POST[$prefix . $field]))
				$data[$field] = $this->input->post($prefix . $field);
		}
		
		//handle checkbox
		if(empty($data['send_txts']))
			$data['send_txts'] = 0;
		
		if(is_null($this->contact_id))
		{
			$this->db->insert($this->table_name, $data);
			$this->contact_id = $this->db->insert_id();
		}
		else
		{
			$this->db->where($this->primary_key, $this->contact_id);
			$this->db->update($this->table_name, $data);
		}
		return $this->contact_id;
	}
}