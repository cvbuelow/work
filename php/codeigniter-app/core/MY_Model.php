<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Model extends CI_Model {
	
	var $table_name;
	var $db_fields = array();
	var $primary_key;
	
    function __construct($id = NULL) 
    {
        parent::__construct();
		$this->_init_fields();
		if($this->primary_key)
			$this->{$this->primary_key} = $id;
		$this->_load();
	}
		
	/**
	 * load a list of the fields for this table
	 */ 
	function _init_fields()
	{
		if($this->table_name)
		{
			$this->db_fields = $this->db->list_fields($this->table_name);
			
			foreach($this->db_fields as $field)
			{
				$this->{$field} = NULL;
			}
		}
	}
	
	/**
	 * load data from row
	 */ 
	function _load()
	{
		if($this->primary_key)
		{
			$row = $this->db->get_where($this->table_name, array(
				$this->primary_key => $this->{$this->primary_key},
			))->result();
			
			if(count($row))
				$this->load_db_values($row[0]);
		}
	}
	
	/**
	 * set model properties from db values
	 */ 
	function load_db_values($row)
	{
		foreach($row as $prop => $val)
			$this->{$prop} = $val;
	}
}