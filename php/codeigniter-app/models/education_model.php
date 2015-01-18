<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Education_model extends MY_Model {
	
	var $table_name = 'education';
	var $primary_key = 'education_id';
	var $row_num;

	function __construct($id = NULL, $row_num = 0)
	{
		parent::__construct($id);
		$this->row_num = $row_num;
	}
	
	function save()
	{
		foreach($this->db_fields as $field)
		{
			if($field == 'transcript')
				$data[$field] = $this->{$field};
			elseif($field !== $this->primary_key && isset($_POST[$field][$this->row_num]))
			{
				$post_array = $this->input->post($field);
				$data[$field] = $post_array[$this->row_num];
			}
		}
		
		if(is_null($this->education_id))
		{
			$this->db->insert($this->table_name, $data);
			$this->education_id = $this->db->insert_id();
		}
		else
		{
			$this->db->where($this->primary_key, $this->education_id);
			$this->db->update($this->table_name, $data);
		}
		return $this->education_id;
	}
}