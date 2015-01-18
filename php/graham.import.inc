<?php

#==================================================#
#	DATABASE MIGRATION SCRIPT                        #
#==================================================#
# For migrating data from existing CMS to Drupal.  #
# Outputs progress to screen. Runtime was ~45 min. #
#==================================================#

function graham_import()
{
	set_time_limit(0);
	$start_time = time();
	ob_clean();
	if(ob_get_level() == 0)
		ob_start();
	header("Content-type: text/html; charset=utf-8");
	exit;
	
	//update field hashes
	$rows = db_select('field_data_field_recommendation_requests', 'r')->fields('r')->execute()->fetchAll();
	foreach($rows as $row)
	{
		$entity = graham_load_entity($row->field_recommendation_requests_value);
		if(!empty($entity->field_prop_request_email) && !empty($entity->field_prop_request_email['und'][0]['value']) && empty($entity->field_hash))
		{
			echo_to_browser($entity->field_prop_request_email['und'][0]['value'] . '<br>');
			$entity->field_hash['und'][0]['value'] = drupal_hash_base64($entity->field_prop_request_email['und'][0]['value']);
			echo_to_browser($entity->field_hash['und'][0]['value'] . '<br>');
			entity_save('field_collection_item', $entity);
		}
	}
	
	$rows = db_select('field_data_field_review_app', 'r')->fields('r')->execute()->fetchAll();
	foreach($rows as $row)
	{
		$entity = graham_load_entity($row->field_review_app_value);
		if(!empty($entity->field_reviewers) && empty($entity->field_hash))
		{
			echo_to_browser($entity->field_reviewers);
			/*echo_to_browser($entity->field_prop_request_email['und'][0]['value'] . '<br>');
			$entity->field_hash['und'][0]['value'] = drupal_hash_base64($entity->field_prop_request_email['und'][0]['value']);
			echo_to_browser($entity->field_hash['und'][0]['value'] . '<br>');
			entity_save('field_collection_item', $entity);*/
		}
	}
	
	
	
	exit;
	
	#============#
	#   delete   #
	#============#
	
	echo_to_browser('Deleting content...');
	//delete all nodes except pages and articles. delete in batches of 1000 to avoid memory issues.
	echo_to_browser('nodes...');
	do {
		$nids = db_select('node', 'n')->fields('n', array('nid'))->condition('type', array('rfp', 'proposal', 'course', 'event', 'funding', 'nomination', 'registration', 'review'))->range(0, 1000)->execute()->fetchCol();
		echo_to_browser('.');
		node_delete_multiple($nids);
	} while(count($nids));
	
	//echo_to_browser('field_collection_items...');
	//$entity_ids = db_select('field_collection_item', 'f')->fields('f', array('item_id'))->condition('field_name', 'field_schools_colleges')->execute()->fetchCol();
	//entity_delete_multiple('field_collection_item', $entity_ids);
	
	//delete all users except admins, patumol and the anonymous user. delete in batches of 1000 to avoid memory issues.
	echo_to_browser('users...');
	do {
		$query = db_select('users', 'u');
		$query->leftJoin('users_roles', 'ur', 'u.uid = ur.uid');												//patumol
		$uids = $query->fields('u', array('uid'))->condition('ur.rid', NULL)->condition('u.uid', 0, '<>')->condition('u.uid', 2, '<>')->range(0, 1000)->execute()->fetchCol();
		echo_to_browser('.');
		user_delete_multiple($uids);
	} while(count($uids));
	echo_to_browser('done<br><br>');
	
	#=================#
	#   user import   #
	#=================#
	
	echo_to_browser('Importing users...<br>');
	
	//priority:
		#1 gesi_faculty
		#2 web_contacts
		#3 ia_contacts
		#4 conf_contacts
	
	#1 gesi_faculty
	db_set_active('production');
	$result = db_query("SELECT gesi_faculty.frsID, gesi_faculty.First_Name, gesi_faculty.Last_Name, gesi_faculty.Title, gesi_faculty.Email, gesi_faculty.Phone, gesi_faculty.Website, gesi_faculty.Description, CONVERT(GROUP_CONCAT(distinct CONCAT_WS('|',gesidef_focusareas.Focus_Area,gesidef_subfocusareas.SubFocusArea) separator '||'),CHAR) AS expertise, GROUP_CONCAT(DISTINCT gesidef_departments.departmentid SEPARATOR '|') AS departments, GROUP_CONCAT(gesischools.school SEPARATOR '|') AS schools, GROUP_CONCAT(DISTINCT gesi_coursenum.course_num SEPARATOR '|') AS courses, LastModOn
	   FROM gesi.gesi_faculty 
       LEFT JOIN gesi.gesi_departments ON gesi_faculty.frsID = gesi_departments.frsID 
       LEFT JOIN gesi.gesi_faculty_focusareas ON gesi_faculty.frsID = gesi_faculty_focusareas.frsID 
       LEFT JOIN gesi.gesidef_focusareas ON gesi_faculty_focusareas.FocusAreaID = gesidef_focusareas.FocusAreaID 
       LEFT JOIN gesi.gesidef_subfocusareas ON gesi_faculty_focusareas.SubFocusAreaID = gesidef_subfocusareas.SubFocusAreaID 
       LEFT JOIN gesi.gesidef_departments ON gesi_departments.departmentid = gesidef_departments.departmentid 
       LEFT JOIN gesi.gesischools ON gesidef_departments.schoolid = gesischools.id 
       LEFT JOIN gesi.gesi_courseinstructors ON gesi_faculty.frsID = gesi_courseinstructors.frsid 
       LEFT JOIN gesi.gesi_coursenum ON gesi.gesi_courseinstructors.course_id = gesi_coursenum.id
	   WHERE Email IS NOT NULL
       GROUP BY gesi_faculty.frsID ORDER BY LastModOn DESC");
	db_set_active();
	foreach($result as $row)
	{
		//create a user object
		$u = new stdClass();
		$u->is_new = TRUE;
		$u->status = 1;
		
		if(!empty($row->LastModOn))
			$u->created = strtotime($row->LastModOn);
		
		$u->init = $u->mail = strtolower(trim($row->Email));
		$u->name = preg_replace('/@.*umich\.edu/i', '', $u->mail);
		
		//see if user already exists. if it does be sure to not override any values saved from previous tables.
		if($user = user_load_by_name($u->name))
		{
			$u = $user;
			echo_to_browser('<br>Duplicate user found: ' . $u->name . '<br><br>');
		}
		$u->field_legacy_table['und'][] = array('value' => 'gesi_faculty');
		$u->field_legacy_id['und'][] = array('value' => $row->frsID);
		$u->field_faculty_researcher['und'][0]['value'] = $u->field_um_affiliated['und'][0]['value'] = 1;
		
		if(!empty($row->First_Name) && empty($u->field_first_name))
			$u->field_first_name['und'][0]['value'] = $row->First_Name;
		if(!empty($row->Last_Name) && empty($u->field_last_name))
			$u->field_last_name['und'][0]['value'] = $row->Last_Name;
		if(!empty($row->Title) && empty($u->field_title))
			$u->field_title['und'][0]['value'] = $row->Title;
		if(!empty($row->Phone) && empty($u->field_business_phone))
			$u->field_business_phone['und'][0]['value'] = $row->Phone;
		if(!empty($row->Website) && empty($u->field_urls))
			$u->field_urls['und'][0]['url'] = $row->Website;
		if(!empty($row->Description) && empty($u->field_bio))
			$u->field_bio['und'][0]['value'] = $row->Description;
		
		//focus areas
		$focus_areas = explode('||', $row->expertise);
		if(!empty($focus_areas))
		{
			foreach($focus_areas as $focus_area)
			{
				$sub_focus_areas = explode('|', $focus_area);
				
				if(!empty($sub_focus_areas[0]))
				{
					//get parent tid
					$query = db_select('taxonomy_term_data', 'd');
					$query->join('taxonomy_term_hierarchy', 'h', 'd.tid = h.tid');
					$query2 = clone $query->condition('vid', 2)->fields('d');
					$tid = $query->condition('parent', 0)->condition('name', $sub_focus_areas[0])->execute()->fetchObject()->tid;
					
					//get child tid
					if(!empty($sub_focus_areas[1]))
						$tid = $query2->condition('parent', $tid)->condition('name', $sub_focus_areas[1])->execute()->fetchObject()->tid;
					if(!empty($tid))
						$u->field_area_of_expertise['und'][] = array('tid' => $tid);
					else
						print_r($sub_focus_areas);
				}
			}
		}
		
		//schools & departments
		if(!empty($row->departments))
		{
			//have to do this to handle typos in original db
			$special_mapping = array(
				'Germanic Languages & Literatures' => 'Germanic Languages & Literature',
				'Molecular and Integrative Physiology' => 'Molecular & Integrative Physiology',
			);
			$departments = explode('|', $row->departments);
			foreach($departments as $key => $department)
			{				
				//lookup dept
				db_set_active('production');
				$dept_def = db_query("SELECT * FROM gesischools s LEFT JOIN gesidef_departments d ON d.schoolid = s.id WHERE departmentid = " . $department . " ORDER BY id, department")->fetchObject();
				db_set_active();
				
				//get parent tid
				$query = db_select('taxonomy_term_data', 'd');
				$query->join('taxonomy_term_hierarchy', 'h', 'd.tid = h.tid');
				$query2 = clone $query->condition('vid', 3)->fields('d');
				$tid = $query->condition('parent', 0)->condition('name', $dept_def->school)->execute()->fetchObject()->tid;
				
				if(empty($tid))
					echo_to_browser('Could not find School:' . $dept_def->school . '<br>');
				
				//get child tid
				$tid = $query2->condition('parent', $tid)->condition('name', isset($special_mapping[$dept_def->department]) ? $special_mapping[$dept_def->department] : $dept_def->department)->execute()->fetchObject()->tid;
				if(empty($tid))
					echo_to_browser('Could not find Department:' . $dept_def->department . '<br>');
				else
					$u->field_school_college['und'][] = array('tid' => $tid);
			}
		}
		
		if(filter_var($u->mail, FILTER_VALIDATE_EMAIL))
		{
			user_save($u);			
			echo_to_browser($u->mail . '<br>');
		}
	}
	
	#2 web_contacts
	db_set_active('production');
	$result = db_query("SELECT * FROM web_contacts ORDER BY CreatedOn DESC");	//get newest ones first so older ones don't overwrite new content if there are duplicate records
	db_set_active();
	foreach($result as $row)
	{
		//create a user object
		$u = new stdClass();
		$u->is_new = TRUE;
		$u->status = 1;
		
		$u->created = strtotime($row->CreatedOn);
		$u->init = $u->mail = strtolower(trim($row->Email));
		$u->name = preg_replace('/@.*umich\.edu/i', '', $u->mail);
		
		//see if user already exists. if it does be sure to not override any values saved from previous tables.
		if($user = user_load_by_name($u->name))
		{
			$u = $user;
			echo_to_browser('<br>Duplicate user found: ' . $u->name . '<br><br>');
		}
		$u->field_legacy_table['und'][] = array('value' => 'web_contacts');
		$u->field_legacy_id['und'][] = array('value' => $row->ContactID);
		
		if(empty($u->field_um_affiliated) && $u->name == $u->mail)
			$u->field_um_affiliated['und'][0]['value'] = 0;
		if(empty($u->field_faculty_researcher))
			$u->field_faculty_researcher['und'][0]['value'] = 0;
		
		if(!empty($row->FirstName) && empty($u->field_first_name))
			$u->field_first_name['und'][0]['value'] = $row->FirstName;
		if(!empty($row->LastName) && empty($u->field_last_name))
			$u->field_last_name['und'][0]['value'] = $row->LastName;
		if(!empty($row->StudentID) && empty($u->field_umid))
			$u->field_umid['und'][0]['value'] = $row->StudentID;
		if(!empty($row->Title) && empty($u->field_title))
			$u->field_title['und'][0]['value'] = $row->Title;
		if(!empty($row->Address1) && empty($u->field_business_address_1))
			$u->field_business_address_1['und'][0]['value'] = $row->Address1;
		if(!empty($row->Address2) && empty($u->field_business_address_2))
			$u->field_business_address_2['und'][0]['value'] = $row->Address2;
		if(!empty($row->City) && empty($u->field_business_city))
			$u->field_business_city['und'][0]['value'] = $row->City;
		if(!empty($row->State) && empty($u->field_business_state))
			$u->field_business_state['und'][0]['value'] = $row->State;
		if(!empty($row->Zip) && empty($u->field_business_zip))
			$u->field_business_zip['und'][0]['value'] = $row->Zip;
		if(!empty($row->CampusZip) && empty($u->field_business_campus_zip))
			$u->field_business_campus_zip['und'][0]['value'] = $row->CampusZip;
		if(!empty($row->Country) && empty($u->field_business_country))
			$u->field_business_country['und'][0]['value'] = $row->Country;
		if(!empty($row->Fax) && empty($u->field_fax))
			$u->field_fax['und'][0]['value'] = $row->Fax;
		if(!empty($row->OfficePhone) && empty($u->field_business_phone))
			$u->field_business_phone['und'][0]['value'] = $row->OfficePhone;
		if(!empty($row->CellPhone) && empty($u->field_cell_phone))
			$u->field_cell_phone['und'][0]['value'] = $row->CellPhone;
		if(!empty($row->Organization) && empty($u->field_organization_company))
			$u->field_organization_company['und'][0]['value'] = $row->Organization;
		if(!empty($row->URL))
			$u->field_urls['und'][] = array('url' => $row->URL);
		if(!empty($row->Department))
			$u->field_legacy_department['und'][] = array('value' => $row->Department);

		
		if(filter_var($u->mail, FILTER_VALIDATE_EMAIL))
		{
			//overwrite values with tblcontact values and assign roles
			$roles = array();
			if(!empty($row->lngRoleCode))
				$roles[] = $row->lngRoleCode;
			import_tblcontact($u, $row->ContactIDRdb, $roles);
			
			user_save($u);
			
			if(!empty($row->DegreeProgram) || !empty($row->MinorDegreeProgram))
			{
				//create entity for degree
				$entity = entity_create('field_collection_item', array('field_name' => 'field_degrees'));
				$entity->setHostEntity('user', $u);
				if(!empty($row->DegreeProgram))
					$entity->field_major['und'][0]['value'] = $row->DegreeProgram;
				if(!empty($row->MinorDegreeProgram))
					$entity->field_minor['und'][0]['value'] = $row->MinorDegreeProgram;
				$entity->save();
			}
			echo_to_browser($u->mail . '<br>');
		}
	}
	
	#3 ia_contacts
	db_set_active('production');
	$result = db_query("SELECT * FROM ia_contacts WHERE Email IS NOT NULL ORDER BY COALESCE(LastModOn, CreatedOn) DESC");	//get newest ones first so older ones don't overwrite new content if there are duplicate records
	db_set_active();
	foreach($result as $row)
	{
		//create a user object
		$u = new stdClass();
		$u->is_new = TRUE;
		$u->status = 1;
		
		$u->created = strtotime($row->CreatedOn);
		$u->init = $u->mail = strtolower(trim($row->Email));
		$u->name = preg_replace('/@.*umich\.edu/i', '', $u->mail);
		
		//see if user already exists. if it does be sure to not override any values saved from previous tables.
		if($user = user_load_by_name($u->name))
		{
			$u = $user;
			echo_to_browser('<br>Duplicate user found: ' . $u->name . '<br><br>');
		}
		$u->field_legacy_table['und'][] = array('value' => 'ia_contacts');
		$u->field_legacy_id['und'][] = array('value' => $row->ContactID);
		
		if(empty($u->field_um_affiliated) && $u->name == $u->mail)
			$u->field_um_affiliated['und'][0]['value'] = 0;
		if(empty($u->field_faculty_researcher))
			$u->field_faculty_researcher['und'][0]['value'] = 0;
		
		if(!empty($row->FirstName) && empty($u->field_first_name))
			$u->field_first_name['und'][0]['value'] = $row->FirstName;
		if(!empty($row->LastName) && empty($u->field_last_name))
			$u->field_last_name['und'][0]['value'] = $row->LastName;
		if(!empty($row->StudentID) && empty($u->field_umid))
			$u->field_umid['und'][0]['value'] = $row->StudentID;
		if(!empty($row->Title) && empty($u->field_title))
			$u->field_title['und'][0]['value'] = $row->Title;
		if(!empty($row->Address1) && empty($u->field_business_address_1))
			$u->field_business_address_1['und'][0]['value'] = $row->Address1;
		if(!empty($row->Address2) && empty($u->field_business_address_2))
			$u->field_business_address_2['und'][0]['value'] = $row->Address2;
		if(!empty($row->City) && empty($u->field_business_city))
			$u->field_business_city['und'][0]['value'] = $row->City;
		if(!empty($row->State) && empty($u->field_business_state))
			$u->field_business_state['und'][0]['value'] = $row->State;
		if(!empty($row->Zip) && empty($u->field_business_zip))
			$u->field_business_zip['und'][0]['value'] = $row->Zip;
		if(!empty($row->CampusZip) && empty($u->field_business_campus_zip))
			$u->field_business_campus_zip['und'][0]['value'] = $row->CampusZip;
		if(!empty($row->Country) && empty($u->field_business_country))
			$u->field_business_country['und'][0]['value'] = $row->Country;
		if(!empty($row->Fax) && empty($u->field_fax))
			$u->field_fax['und'][0]['value'] = $row->Fax;
		if(!empty($row->OfficePhone) && empty($u->field_business_phone))
			$u->field_business_phone['und'][0]['value'] = $row->OfficePhone;
		if(!empty($row->CellPhone) && empty($u->field_cell_phone))
			$u->field_cell_phone['und'][0]['value'] = $row->CellPhone;
		if(!empty($row->HomePhone) && empty($u->field_home_phone))
			$u->field_home_phone['und'][0]['value'] = $row->HomePhone;
		if(!empty($row->Organization) && empty($u->field_organization_company))
			$u->field_organization_company['und'][0]['value'] = $row->Organization;
		if(!empty($row->URL))
			$u->field_urls['und'][] = array('url' => $row->URL);
		if(!empty($row->Department))
			$u->field_legacy_department['und'][] = array('value' => $row->Department);
		if(!empty($row->Comments))
			$u->log = $row->Comments;
		
		if(filter_var($u->mail, FILTER_VALIDATE_EMAIL))
		{
			//overwrite values with tblcontact values and assign roles
			$roles = array();
			if(!empty($row->lngRoleCode1))
				$roles[] = $row->lngRoleCode1;
			if(!empty($row->lngRoleCode2))
				$roles[] = $row->lngRoleCode2;
			if(!empty($row->lngRoleCode3))
				$roles[] = $row->lngRoleCode3;
			import_tblcontact($u, $row->ContactIDRdb, $roles);
			
			user_save($u);
			
			if(!empty($row->DegreeProgram))
			{
				//create entity for degree
				$entity = entity_create('field_collection_item', array('field_name' => 'field_degrees'));
				$entity->setHostEntity('user', $u);
				$entity->field_major['und'][0]['value'] = $row->DegreeProgram;
				$entity->save();
			}
			echo_to_browser($u->mail . '<br>');
		}
	}
	
	#4 conf_contacts
	db_set_active('production');
	$result = db_query("SELECT * FROM conf_contacts ORDER BY CreatedOn DESC");	//get newest ones first so older ones don't overwrite new content if there are duplicate records
	db_set_active();
	foreach($result as $row)
	{
		//create a user object
		$u = new stdClass();
		$u->is_new = TRUE;
		$u->status = 1;
		
		$u->created = strtotime($row->CreatedOn);
		$u->init = $u->mail = strtolower(trim($row->Email));
		$u->name = preg_replace('/@.*umich\.edu/i', '', $u->mail);
		
		//see if user already exists. if it does be sure to not override any values saved from previous tables.
		if($user = user_load_by_name($u->name))
		{
			$u = $user;
			echo_to_browser('<br>Duplicate user found: ' . $u->name . '<br><br>');
		}
		$u->field_legacy_table['und'][] = array('value' => 'conf_contacts');
		$u->field_legacy_id['und'][] = array('value' => $row->ContactID);
		
		if(empty($u->field_um_affiliated) && $u->name == $u->mail)
			$u->field_um_affiliated['und'][0]['value'] = 0;
		if(empty($u->field_faculty_researcher))
			$u->field_faculty_researcher['und'][0]['value'] = 0;
		
		if(!empty($row->FirstName) && empty($u->field_first_name))
			$u->field_first_name['und'][0]['value'] = $row->FirstName;
		if(!empty($row->LastName) && empty($u->field_last_name))
			$u->field_last_name['und'][0]['value'] = $row->LastName;
		if(!empty($row->Title) && empty($u->field_title))
			$u->field_title['und'][0]['value'] = $row->Title;
		if(!empty($row->Address1) && empty($u->field_business_address_1))
			$u->field_business_address_1['und'][0]['value'] = $row->Address1;
		if(!empty($row->Address2) && empty($u->field_business_address_2))
			$u->field_business_address_2['und'][0]['value'] = $row->Address2;
		if(!empty($row->City) && empty($u->field_business_city))
			$u->field_business_city['und'][0]['value'] = $row->City;
		if(!empty($row->State) && empty($u->field_business_state))
			$u->field_business_state['und'][0]['value'] = $row->State;
		if(!empty($row->Zip) && empty($u->field_business_zip))
			$u->field_business_zip['und'][0]['value'] = $row->Zip;
		if(!empty($row->CampusZip) && empty($u->field_business_campus_zip))
			$u->field_business_campus_zip['und'][0]['value'] = $row->CampusZip;
		if(!empty($row->Country) && empty($u->field_business_country))
			$u->field_business_country['und'][0]['value'] = $row->Country;
		if(!empty($row->Fax) && empty($u->field_fax))
			$u->field_fax['und'][0]['value'] = $row->Fax;
		if(!empty($row->OfficePhone) && empty($u->field_business_phone))
			$u->field_business_phone['und'][0]['value'] = $row->OfficePhone;
		if(!empty($row->CellPhone) && empty($u->field_cell_phone))
			$u->field_cell_phone['und'][0]['value'] = $row->CellPhone;
		if(!empty($row->Organization) && empty($u->field_organization_company))
			$u->field_organization_company['und'][0]['value'] = $row->Organization;
		if(!empty($row->URL))
			$u->field_urls['und'][] = array('url' => $row->URL);
		if(!empty($row->Department))
			$u->field_legacy_department['und'][] = array('value' => $row->Department);
		if($row->Affiliation == 'External')
			$u->field_um_affiliated['und'][0]['value'] = 0;
		switch($row->UMAffil)
		{
			case 'Student': $u->field_student['und'][0]['value'] = 1; break;
			case 'Faculty':
			case 'Researcher': $u->field_faculty_researcher['und'][0]['value'] = 1; break;
		}
		
		if(filter_var($u->mail, FILTER_VALIDATE_EMAIL))
		{
			//overwrite values with tblcontact values and assign roles
			import_tblcontact($u, $row->ContactIDRdb);
			
			user_save($u);
			echo_to_browser($u->mail . '<br>');
		}
	}
	echo_to_browser('Done<br><br>');
	
	#================#
	#   rfp import   #
	#================#
	
	echo_to_browser('Importing rfps...<br>');
	$nodes = array();
	db_set_active('production');
	$result = db_query("SELECT * FROM proposal_def");
	db_set_active();
	foreach($result as $row)
	{
		//create a node object
		$n = default_node('rfp');
		$n->uid = 3;	//sromansk
		$n->created = strtotime($row->ProposalYear);
		$n->title = strip_tags(replace_br($row->ProposalDesc));
		$n->field_application_type['und'][0]['value'] = 'Proposal';
		if(!empty($row->HeaderText))
		{
			$n->body['und'][0]['value'] = $row->HeaderText;
			$n->body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->EmailFrom))
			$n->field_receipt_email_reply['und'][0]['value'] = $n->field_receipt_email_from['und'][0]['value'] = $row->EmailFrom;
		if(!empty($row->EmailSubject))
			$n->field_receipt_email_subject['und'][0]['value'] = $row->EmailSubject;
		if(!empty($row->EmailText))
		{
			$n->field_receipt_email_body['und'][0]['value'] = nl2br($row->EmailText);
			$n->field_receipt_email_body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->auxEmailSubject))
			$n->field_letter_email_subject['und'][0]['value'] = $row->auxEmailSubject;
		if(!empty($row->auxEmailText))
		{
			$n->field_letter_email_body['und'][0]['value'] = nl2br($row->auxEmailText);
			$n->field_letter_email_body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->ReviewEmailSubject))
			$n->field_reviewers_email_subject['und'][0]['value'] = $row->ReviewEmailSubject;
		if(!empty($row->ReviewEmailText))
		{
			$n->field_reviewers_email_body['und'][0]['value'] = nl2br($row->ReviewEmailText);
			$n->field_reviewers_email_body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->ApplicationDeadline))
			$n->field_prop_deadline['und'][0]['value'] = strtotime($row->ApplicationDeadline);
		if(!empty($row->LIDeadline))
			$n->field_letter_deadline['und'][0]['value'] = strtotime($row->LIDeadline);
		if(!empty($row->ReviewDeadline))
			$n->field_review_deadline['und'][0]['value'] = strtotime($row->ReviewDeadline);
		if(!empty($row->ckNominators))
			$n->field_reco_required['und'][0]['value'] = $n->field_reco_active['und'][0]['value'] = 1;
		$n->field_reco_alternative_label['und'][0]['value'] = !empty($row->RecommenderText) ? $row->RecommenderText : "Please provide the names, title, affiliation, and e-mails of at least two recommenders below";
		if(!empty($row->ckCollaborators))
			$n->field_contact_active['und'][0]['value'] = $n->field_collabs_active['und'][0]['value'] = 1;
		if(!empty($row->ckProposalUpload))	//not sure how to handle this; could be multiple fields; just do "Full Proposal" for now
			$n->field_proj_active['und'][0]['value'] = $n->field_prop_active['und'][0]['value'] = 1;
		if(!empty($row->CollabDesc))
			$n->field_collabs_alternative_label['und'][0]['value'] = $row->CollabDesc . 's';
		if(!empty($row->CollaboratorText))
		{
			$n->field_collabs_instructions['und'][0]['value'] = nl2br($row->CollaboratorText);
			$n->field_collabs_instructions['und'][0]['format'] = 'full_html';
		}		
		if(!empty($row->ReviewHeaderText))
		{
			$n->field_review_header['und'][0]['value'] = nl2br($row->ReviewHeaderText);
			$n->field_review_header['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->UploadText))
			$n->field_prop_alternative_label['und'][0]['value'] = $row->UploadText;
		if(!empty($row->ckOrg))
			$n->field_org_active['und'][0]['value'] = 1;
		if(!empty($row->ckOrgReqd))
			$n->field_org_required['und'][0]['value'] = 1;
		if(!empty($row->OrgAlias))
			$n->field_org_alternative_label['und'][0]['value'] = $row->OrgAlias;
		if(!empty($row->ckDept))
			$n->field_school_active['und'][0]['value'] = 1;
		if(!empty($row->ckDeptReqd))
			$n->field_school_required['und'][0]['value'] = 1;
		if(!empty($row->DeptAlias))
			$n->field_school_alternative_label['und'][0]['value'] = $row->DeptAlias;
		if(!empty($row->ckAddress))
			$n->field_contact_active['und'][0]['value'] = $n->field_address_active['und'][0]['value'] = $n->field_city_active['und'][0]['value'] = $n->field_state_active['und'][0]['value'] = $n->field_zip_active['und'][0]['value'] = 1;
		if(!empty($row->ckAddressRqd))
			$n->field_contact_required['und'][0]['value'] = $n->field_address_required['und'][0]['value'] = $n->field_city_required['und'][0]['value'] = $n->field_state_required['und'][0]['value'] = $n->field_zip_required['und'][0]['value'] = 1;
		if(!empty($row->ckPhone))
			$n->field_phone_active['und'][0]['value'] = 1;
		if(!empty($row->ckPhoneRqd))
			$n->field_phone_required['und'][0]['value'] = 1;
		if(!empty($row->ckProposalTitle))
			$n->field_proj_active['und'][0]['value'] = $n->field_title_active['und'][0]['value'] = 1;
		if(!empty($row->ckProposalTitleRqd))
			$n->field_title_required['und'][0]['value'] = 1;
		if(!empty($row->ProposalTitleAlias))
			$n->field_title_alternative_label['und'][0]['value'] = $row->ProposalTitleAlias;
		if(!empty($row->lngRoleCode) && ($tid = import_translate_role($row->lngRoleCode)))
			$n->field_graham_affiliation['und'][0]['tid'] = $tid;
		
		$n->field_custext1_alternative_label['und'][0]['value'] = 'Proposal Description of Idea (500 words or less )';
		$n->field_custext1_active['und'][0]['value'] = 1;
	
		node_save($n);
		$nodes[$row->ProposalTypeID . $row->ProposalType] = $n->nid;
		
		//review criteria
		db_set_active('production');
		$reviews = db_query("SELECT * FROM proposaldef_reviewcriteria WHERE ProposalTypeID = " . $row->ProposalTypeID);
		db_set_active();
		foreach($reviews as $review)
		{
			if(!empty($review->Criteria))
			{
				$field_collection_item = entity_create('field_collection_item', array('field_name' => 'field_review_criteria'));
				$field_collection_item->setHostEntity('node', $n);
				$field_collection_item->field_criteria['und'][0]['value'] = $review->Criteria;
				$field_collection_item->field_criteria_description['und'][0]['value'] = $review->CriteriaDesc;
				$field_collection_item->save();
			}
		}
		
		echo_to_browser($n->title . '<br>');
	}
	echo_to_browser('Done<br><br>');
	
	#=====================#
	#   proposal import   #
	#=====================#

	echo_to_browser('Importing applications...<br>');
	db_set_active('production');
	$result = db_query("SELECT * FROM proposal_applications");
	db_set_active();
	foreach($result as $row)
	{
		if(isset($nodes[$row->ProposalTypeID . $row->ProposalType]))	//it looks like there are three orphaned applications so don't import those
		{
			//create a node object
			$n = default_node('proposal');
			$n->uid = new_id('web_contacts', $row->ContactID);
			$n->created = strtotime(empty($row->SubmittedOn) ? $row->ProposalYear : $row->SubmittedOn);		//some applications don't have a SubmittedOn value so just use the ProposalYear
			$n->field_rfp['und'][0]['target_id'] = $nodes[$row->ProposalTypeID . $row->ProposalType];
			
			if(!empty($row->ProposalTitle))
				$n->title = $n->field_prop_project_title['und'][0]['value'] = strip_tags(replace_br($row->ProposalTitle));
			else
			{
				$rfp = node_load($n->field_rfp['und'][0]['target_id']);
				$n->title = 'Application for ' . $rfp->title;
			}
			if(!empty($row->Accepted))
				$n->field_application_accepted['und'][0]['value'] = 'Yes';
			if(!empty($row->Declined))
				$n->field_application_accepted['und'][0]['value'] = 'No';
			if(!empty($row->Notes))
				$n->log = $row->Notes;
			if(!empty($row->ProposalDesc))
				$n->field_custext1['und'][0]['value'] = $row->ProposalDesc;
			
			echo_to_browser($n->title . '<br>');
			node_save($n);	//save to get nid for use with files below
			
			//import files from $row and FileIDs
			import_file(NULL, $n->field_prop_full, $n->nid, 'proposals', $row);
			import_file($row->FileID1, $n->field_prop_letter_intent, $n->nid);
			import_file($row->FileID2, $n->field_prop_pilot_study, $n->nid);
			
			node_save($n);	//final save with file fields
			
			//import collaborators
			db_set_active('production');
			$collabs = db_query("SELECT * FROM proposal_coinv WHERE Name <> '' AND ProposalID = " . $row->ProposalID . " ORDER BY SubID");
			db_set_active();
			foreach($collabs as $collab)
			{
				$field_collection_item = entity_create('field_collection_item', array('field_name' => 'field_prop_collabs'));
				$field_collection_item->setHostEntity('node', $n);
				$field_collection_item->field_first_name['und'][0]['value'] = $collab->Name;
				$field_collection_item->field_alternate_email['und'][0]['value'] = $collab->Email;
				$field_collection_item->save();
			}
			
			//import recommendation requests
			db_set_active('production');
			$collabs = db_query("SELECT * FROM proposal_nominator WHERE Name <> '' AND ProposalID = " . $row->ProposalID . " ORDER BY SubID");
			db_set_active();
			foreach($collabs as $collab)
			{
				$field_collection_item = entity_create('field_collection_item', array('field_name' => 'field_recommendation_requests'));
				$field_collection_item->setHostEntity('node', $n);
				$field_collection_item->field_prop_request_name['und'][0]['value'] = $collab->Name;
				$field_collection_item->field_prop_request_title['und'][0]['value'] = $collab->Title;
				$field_collection_item->field_prop_request_affiliation['und'][0]['value'] = $collab->Affiliation;
				$field_collection_item->field_prop_request_email['und'][0]['value'] = $collab->Email;
				$field_collection_item->save();		//first save to get item_id
				import_file(NULL, $field_collection_item->field_prop_request_file, $field_collection_item->item_id, 'recommendations', $collab, 'field_collection_item');
				$field_collection_item->save();		//final save with file field
			}
		}
	}
	echo_to_browser('Done<br><br>');
	
	#================#
	#   job import   #
	#================#
	
	echo_to_browser('Importing jobs...<br>');
	$nodes = array();
	db_set_active('production');
	$result = db_query("SELECT * FROM jobop_def WHERE JobopDesc IS NOT NULL");
	db_set_active();
	foreach($result as $row)
	{
		//create a node object
		$n = default_node('rfp');
		$n->uid = 3;	//sromansk
		$n->created = strtotime($row->JobopYear);
		$n->title = strip_tags(replace_br($row->JobopDesc));
		$n->field_application_type['und'][0]['value'] = 'Job';
		if(!empty($row->HeaderText))
		{
			$n->body['und'][0]['value'] = $row->HeaderText;
			$n->body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->EmailFrom))
			$n->field_receipt_email_reply['und'][0]['value'] = $n->field_receipt_email_from['und'][0]['value'] = $row->EmailFrom;
		if(!empty($row->EmailSubject))
			$n->field_receipt_email_subject['und'][0]['value'] = $row->EmailSubject;
		if(!empty($row->EmailText))
		{
			$n->field_receipt_email_body['und'][0]['value'] = nl2br($row->EmailText);
			$n->field_receipt_email_body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->auxEmailSubject))
			$n->field_letter_email_subject['und'][0]['value'] = $row->auxEmailSubject;
		if(!empty($row->auxEmailText))
		{
			$n->field_letter_email_body['und'][0]['value'] = nl2br($row->auxEmailText);
			$n->field_letter_email_body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->ApplicationDeadline))
			$n->field_application_deadline['und'][0]['value'] = strtotime($row->ApplicationDeadline);
		if(!empty($row->ckOrg))
			$n->field_org_active['und'][0]['value'] = 1;
		if(!empty($row->ckOrgReqd))
			$n->field_org_required['und'][0]['value'] = 1;
		if(!empty($row->OrgAlias))
			$n->field_org_alternative_label['und'][0]['value'] = $row->OrgAlias;
		if(!empty($row->ckDept))
			$n->field_school_active['und'][0]['value'] = 1;
		if(!empty($row->ckDeptReqd))
			$n->field_school_required['und'][0]['value'] = 1;
		if(!empty($row->DeptAlias))
			$n->field_school_alternative_label['und'][0]['value'] = $row->DeptAlias;
		if(!empty($row->ckAddress))
			$n->field_contact_active['und'][0]['value'] = $n->field_address_active['und'][0]['value'] = $n->field_city_active['und'][0]['value'] = $n->field_state_active['und'][0]['value'] = $n->field_zip_active['und'][0]['value'] = 1;
		if(!empty($row->ckAddressRqd))
			$n->field_contact_required['und'][0]['value'] = $n->field_address_required['und'][0]['value'] = $n->field_city_required['und'][0]['value'] = $n->field_state_required['und'][0]['value'] = $n->field_zip_required['und'][0]['value'] = 1;
		if(!empty($row->ckPhone))
			$n->field_phone_active['und'][0]['value'] = 1;
		if(!empty($row->ckPhoneRqd))
			$n->field_phone_required['und'][0]['value'] = 1;
		if(!empty($row->ckStudentStatement))
			$n->field_custext1_active['und'][0]['value'] = $n->field_custext1_required['und'][0]['value'] = 1;
		$n->field_custext1_alternative_label['und'][0]['value'] = !empty($row->StatementAlias) ? $row->StatementAlias : "In 500 words or less, please describe your past and ongoing work/interest areas";
		$n->field_req_docs_active['und'][0]['value'] = $n->field_custext2_active['und'][0]['value'] = 1;
		$n->field_custext2_alternative_label['und'][0]['value'] = "Category";
		if(!empty($row->ckResume))
			$n->field_req_docs_active['und'][0]['value'] = $n->field_cv_active['und'][0]['value'] = $n->field_cv_required['und'][0]['value'] = 1;
		if(!empty($row->ResumeAlias))
			$n->field_cv_alternative_label['und'][0]['value'] = $row->ResumeAlias;
		//we don't have a field for cover letter so put in "Supporting document 1" (field_doc1)
		if(!empty($row->ckCoverLetter))
		{
			$n->field_doc1_active['und'][0]['value'] = $n->field_doc1_required['und'][0]['value'] = 1;
			$n->field_doc1_alternative_label['und'][0]['value'] = 'Cover Letter';
		}
		if(!empty($row->CoverLetterAlias))
			$n->field_doc1_alternative_label['und'][0]['value'] = $row->CoverLetterAlias;
		
		if(!empty($row->ckTranscripts))
			$n->field_trans_active['und'][0]['value'] = $n->field_trans_required['und'][0]['value'] = 1;
		if(!empty($row->TranscriptAlias))
			$n->field_trans_alternative_label['und'][0]['value'] = $row->TranscriptAlias;
		
		//we don't have a field for Application File so put in "Supporting document 2" (field_doc2)
		if(!empty($row->ckApplicationFile))
		{
			$n->field_doc2_active['und'][0]['value'] = $n->field_doc2_required['und'][0]['value'] = 1;
			$n->field_doc2_alternative_label['und'][0]['value'] = 'Application File';
		}
		if(!empty($row->ApplicationFileAlias))
			$n->field_doc2_alternative_label['und'][0]['value'] = $row->ApplicationFileAlias;
		
		if(!empty($row->lngRoleCode) && ($tid = import_translate_role($row->lngRoleCode)))
			$n->field_graham_affiliation['und'][0]['tid'] = $tid;
		
		node_save($n);
		$nodes[$row->JobopID] = $n->nid;
		echo_to_browser($n->title . '<br>');
	}
	echo_to_browser('Done<br><br>');
	
	#=============================#
	#   job applications import   #
	#=============================#

	echo_to_browser('Importing job applications...<br>');
	db_set_active('production');
	$result = db_query("SELECT * FROM jobop_applications ORDER BY SubmittedOn DESC");
	db_set_active();
	foreach($result as $row)
	{
		if(isset($nodes[$row->JobopID]))
		{
			//create a node object
			$n = default_node('proposal');
			$n->uid = new_id('web_contacts', $row->ContactID);
			$n->created = strtotime($row->SubmittedOn);
			$n->field_rfp['und'][0]['target_id'] = $nodes[$row->JobopID];
			$rfp = node_load($nodes[$row->JobopID]);
			$n->title = 'Application for ' . $rfp->title;
			if(!empty($row->Accepted))
				$n->field_application_accepted['und'][0]['value'] = 'Yes';
			if(!empty($row->Declined))
				$n->field_application_accepted['und'][0]['value'] = 'No';
			if(!empty($row->ApplicationText))
				$n->field_custext1['und'][0]['value'] = $row->ApplicationText;
			if(!empty($row->Category))
				$n->field_custext2['und'][0]['value'] = $row->Category;
			
			echo_to_browser($n->title . '<br>');
			node_save($n);	//save to get nid for use with files below
			
			import_file($row->FileID, $n->field_app_cv_resume, $n->nid);
			import_file($row->FileID1, $n->field_app_supporting_document_1, $n->nid);
			import_file($row->FileID2, $n->field_app_transcript, $n->nid);
			import_file($row->FileID3, $n->field_app_supporting_document_2, $n->nid);
			node_save($n);	//final save with file fields
		}
	}
	echo_to_browser('Done<br><br>');
	
	#===================#
	#   course import   #
	#===================#
	
	echo_to_browser('Importing courses...<br>');
	$nodes = array();
	db_set_active('production');
	$result = db_query("SELECT * FROM course_def");
	db_set_active();
	foreach($result as $row)
	{
		//create a node object
		$n = default_node('rfp');
		$n->uid = 3;	//sromansk
		$n->created = strtotime($row->CourseYear);
		$n->title = strip_tags(replace_br($row->CourseDesc));
		$n->field_application_type['und'][0]['value'] = 'Course';
		if(!empty($row->HowToApplyTxt))
		{
			$n->body['und'][0]['value'] = $row->HowToApplyTxt;
			$n->body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->EmailFrom))
			$n->field_receipt_email_reply['und'][0]['value'] = $n->field_receipt_email_from['und'][0]['value'] = $row->EmailFrom;
		if(!empty($row->EmailSubject))
			$n->field_receipt_email_subject['und'][0]['value'] = $row->EmailSubject;
		if(!empty($row->EmailText))
		{
			$n->field_receipt_email_body['und'][0]['value'] = nl2br($row->EmailText);
			$n->field_receipt_email_body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->auxEmailSubject))
			$n->field_letter_email_subject['und'][0]['value'] = $row->auxEmailSubject;
		if(!empty($row->auxEmailText))
		{
			$n->field_letter_email_body['und'][0]['value'] = nl2br($row->auxEmailText);
			$n->field_letter_email_body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->ReviewEmailSubject))
			$n->field_reviewers_email_subject['und'][0]['value'] = $row->ReviewEmailSubject;
		if(!empty($row->ReviewEmailText))
		{
			$n->field_reviewers_email_body['und'][0]['value'] = nl2br($row->ReviewEmailText);
			$n->field_reviewers_email_body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->ApplicationDeadline))
			$n->field_application_deadline['und'][0]['value'] = strtotime($row->ApplicationDeadline);
		
		$n->field_req_docs_active['und'][0]['value'] = $n->field_student1_active['und'][0]['value'] = $n->field_student1_required['und'][0]['value'] = 1;
		$n->field_student1_alternative_label['und'][0]['value'] = !empty($row->StatementTxt) ? $row->StatementTxt : "Please provide a brief written description (no more than 500 words) of why you would like to take this class, particularly how it aids to your studies and planned future activities. (.pdf or .doc format)";
		
		if(!empty($row->ckResume))
			$n->field_cv_active['und'][0]['value'] = $n->field_cv_required['und'][0]['value'] = 1;
		$n->field_cv_alternative_label['und'][0]['value'] = !empty($row->CVTxt) ? $row->CVTxt : "Please upload your current CV (resume) (.pdf or .doc format)";
		
		if(!empty($row->ckTranscript))
			$n->field_trans_active['und'][0]['value'] = $n->field_trans_required['und'][0]['value'] = 1;
		$n->field_trans_alternative_label['und'][0]['value'] = !empty($row->TranscriptTxt) ? $row->TranscriptTxt : "Please provide transcripts of all graduate and undergraduate coursework (in pdf format only). NOTE: Unofficial transcripts are acceptable.";
		
		if(!empty($row->ckNominators))
		{
			$n->field_reco_active['und'][0]['value'] = $n->field_reco_required['und'][0]['value'] = 1;
			$n->field_reco_alternative_label['und'][0]['value'] = !empty($row->NomTxt) ? $row->NomTxt : "Please provide the names of at least 2 nominators.";
		}
		
		if(!empty($row->lngRoleCode) && ($tid = import_translate_role($row->lngRoleCode)))
			$n->field_graham_affiliation['und'][0]['tid'] = $tid;
		
		node_save($n);
		$nodes[$row->CourseID] = $n->nid;
		echo_to_browser($n->title . '<br>');
	}
	echo_to_browser('Done<br><br>');
	
	#================================#
	#   course applications import   #
	#================================#

	echo_to_browser('Importing course applications...<br>');
	db_set_active('production');
	$result = db_query("SELECT * FROM course_applications ORDER BY SubmittedOn DESC");
	db_set_active();
	foreach($result as $row)
	{
		if(isset($nodes[$row->CourseID]))
		{
			//create a node object
			$n = default_node('proposal');
			$n->uid = new_id('web_contacts', $row->ContactID);
			$n->created = strtotime($row->SubmittedOn);
			$n->field_rfp['und'][0]['target_id'] = $nodes[$row->CourseID];
			$rfp = node_load($nodes[$row->CourseID]);
			$n->title = 'Application for ' . $rfp->title;
			if(!empty($row->Accepted))
				$n->field_application_accepted['und'][0]['value'] = 'Yes';
			if(!empty($row->Declined))
				$n->field_application_accepted['und'][0]['value'] = 'No';
			if(!empty($row->ApplicationText))
				$n->field_custext1['und'][0]['value'] = $row->ApplicationText;
			if(!empty($row->AdminComments))
				$n->log = $row->AdminComments;
			
			echo_to_browser($n->title . '<br>');
			node_save($n);	//save to get nid for use with files below
			
			import_file($row->FileID, $n->field_app_transcript, $n->nid);
			import_file($row->FileID1, $n->field_app_student_statement_1, $n->nid);
			import_file($row->FileID2, $n->field_app_cv_resume, $n->nid);
			
			node_save($n);	//final save with file fields
			
			//import recommendation requests
			db_set_active('production');
			$collabs = db_query("SELECT * FROM course_nominator WHERE Name <> '' AND CourseAppID = " . $row->CourseAppID . " ORDER BY SubID");
			db_set_active();
			foreach($collabs as $collab)
			{
				$field_collection_item = entity_create('field_collection_item', array('field_name' => 'field_recommendation_requests'));
				$field_collection_item->setHostEntity('node', $n);
				$field_collection_item->field_prop_request_name['und'][0]['value'] = $collab->Name;
				$field_collection_item->field_prop_request_title['und'][0]['value'] = $collab->Title;
				$field_collection_item->field_prop_request_affiliation['und'][0]['value'] = $collab->Affiliation;
				$field_collection_item->field_prop_request_email['und'][0]['value'] = $collab->Email;
				$field_collection_item->save();		//first save to get item_id
				import_file(NULL, $field_collection_item->field_prop_request_file, $field_collection_item->item_id, 'recommendations', $collab, 'field_collection_item');
				$field_collection_item->save();		//final save with file field
			}
		}
	}
	echo_to_browser('Done<br><br>');
	
	#=======================#
	#   conference import   #
	#=======================#
	
	echo_to_browser('Importing conferences...<br>');
	$nodes = array();
	db_set_active('production');
	$result = db_query("SELECT * FROM conference");
	db_set_active();
	foreach($result as $row)
	{
		//create a node object
		$n = default_node('event');
		$n->uid = 3;	//sromansk
		if(!empty($row->ConfStartDate))
			$n->created = strtotime($row->ConfStartDate) - 60 * 60 * 24 * 30;	//make it a month before the start date
		$n->title = strip_tags(replace_br(empty($row->ConfName) ? $row->ConfDesc : $row->ConfName));
		if(!empty($row->HeaderText))
		{
			$n->body['und'][0]['value'] = $row->HeaderText;
			$n->body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->EmailFrom))
			$n->field_event_reg_email_reply['und'][0]['value'] = $n->field_event_reg_email_from['und'][0]['value'] = $n->field_event_wait_email_reply['und'][0]['value'] = $n->field_event_wait_email_from['und'][0]['value'] = $row->EmailFrom;
		if(!empty($row->EmailSubject))
			$n->field_event_reg_email_subject['und'][0]['value'] = $row->EmailSubject;
		if(!empty($row->EmailText))
		{
			$n->field_event_reg_email_body['und'][0]['value'] = nl2br($row->EmailText);
			$n->field_event_reg_email_body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->ConfStartDate))
			$n->field_date_time['und'][0]['value'] = strtotime($row->ConfStartDate);
		if(!empty($row->ConfEndDate))
			$n->field_date_time['und'][0]['value2'] = strtotime($row->ConfEndDate);
		if(!empty($row->ConfRegDeadline))
			$n->field_registration_deadline['und'][0]['value'] = strtotime($row->ConfRegDeadline);
		if(!empty($row->ConfLocation))
			$n->field_location['und'][0]['value'] = $row->ConfLocation;
		$n->field_sessions_alternative_label['und'][0]['value'] = empty($row->TimeslotTitle) ? "Select Timeslot" : $row->TimeslotTitle;
		if(!empty($row->ckTimeslotReqd))
			$n->field_sessions_active['und'][0]['value'] = $n->field_sessions_required['und'][0]['value'] = 1;
		if(empty($row->ckHideAddress))
			$n->field_contact_active['und'][0]['value'] = $n->field_address_active['und'][0]['value'] = $n->field_city_active['und'][0]['value'] = $n->field_state_active['und'][0]['value'] = $n->field_zip_active['und'][0]['value'] = $n->field_phone_active['und'][0]['value'] = 1;
		if(!empty($row->ckOrg))
			$n->field_org_active['und'][0]['value'] = 1;
		if(!empty($row->ckOrgReqd))
			$n->field_org_required['und'][0]['value'] = 1;
		if(!empty($row->OrgAlias))
			$n->field_org_alternative_label['und'][0]['value'] = $row->OrgAlias;
		if(!empty($row->ckDept))
			$n->field_school_active['und'][0]['value'] = 1;
		if(!empty($row->ckDeptReqd))
			$n->field_school_required['und'][0]['value'] = 1;
		if(!empty($row->DeptAlias))
			$n->field_school_alternative_label['und'][0]['value'] = $row->DeptAlias;
		
		if(!empty($row->lngRoleCode1) && ($tid = import_translate_role($row->lngRoleCode1)))
			$n->field_graham_affiliation['und'][0]['tid'] = $tid;
		
		node_save($n);
		$nodes[$row->ConfID] = $n->nid;
		
		//save sessions
		db_set_active('production');
		$slots = db_query("SELECT * FROM conf_timeslots WHERE ConfID = " . $row->ConfID);
		db_set_active();
		foreach($slots as $slot)
		{
			$field_collection_item = entity_create('field_collection_item', array('field_name' => 'field_sessions'));
			$field_collection_item->setHostEntity('node', $n);
			$field_collection_item->field_session_title['und'][0]['value'] = $slot->TimeSlot;
			$field_collection_item->save();			
		}
		echo_to_browser($n->title . '<br>');
	}
	echo_to_browser('Done<br><br>');
	
	#=====================================#
	#   conference registrations import   #
	#=====================================#

	echo_to_browser('Importing conference registrations...<br>');
	db_set_active('production');
	$result = db_query("SELECT * FROM conf_registration");
	db_set_active();
	foreach($result as $row)
	{
		if(isset($nodes[$row->ConfID]))
		{
			//create a node object
			$n = default_node('registration');
			$n->uid = new_id('conf_contacts', $row->ContactID);
			if(!empty($row->RegisteredOn))
				$n->created = strtotime($row->RegisteredOn);
			$n->field_event['und'][0]['target_id'] = $nodes[$row->ConfID];
			$event = node_load($nodes[$row->ConfID]);
			$n->title = 'Registration for ' . $event->title;
			$n->field_registration_status['und'][0]['value'] = !empty($row->Attended) ? GRAHAM_REG_ATTENDED : GRAHAM_REG_REGISTERED;
				
			//sessions
			db_set_active('production');
			$slots = db_query("SELECT * FROM conf_registration_timeslots WHERE ConfID = " . $row->ConfID . " AND ContactID = " . $row->ContactID);
			db_set_active();
			foreach($slots as $slot)
				$n->field_selected_sessions['und'][] = array('target_id' => $event->field_sessions['und'][$slot->TimeSlotID - 1]['value']);
			
			node_save($n);
			echo_to_browser($n->title . '<br>');
		}
	}
	echo_to_browser('Done<br><br>');
	
	#==========================#
	#   ia conference import   #
	#==========================#
	
	echo_to_browser('Importing ia conferences...<br>');
	$nodes = array();
	db_set_active('production');
	$result = db_query("SELECT * FROM ia_conference WHERE ConfName <> 'Test'");
	db_set_active();
	foreach($result as $row)
	{
		//create a node object
		$n = default_node('event');
		$n->uid = 3;	//sromansk
		if(!empty($row->ConfStartDate))
			$n->created = strtotime($row->ConfStartDate) - 60 * 60 * 24 * 30;	//make it a month before the start date
		$n->title = strip_tags(replace_br(empty($row->ConfName) ? $row->ConfDesc : $row->ConfName));
		if(!empty($row->ConfWebText))
		{
			$n->body['und'][0]['value'] = $row->ConfWebText;
			$n->body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->EmailFrom))
			$n->field_event_reg_email_reply['und'][0]['value'] = $n->field_event_reg_email_from['und'][0]['value'] = $n->field_event_wait_email_reply['und'][0]['value'] = $n->field_event_wait_email_from['und'][0]['value'] = $row->EmailFrom;
		if(!empty($row->EmailSubject))
			$n->field_event_reg_email_subject['und'][0]['value'] = $row->EmailSubject;
		if(!empty($row->EmailText))
		{
			$n->field_event_reg_email_body['und'][0]['value'] = nl2br($row->EmailText);
			$n->field_event_reg_email_body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->ConfStartDate))
			$n->field_date_time['und'][0]['value'] = strtotime($row->ConfStartDate);
		if(!empty($row->ConfEndDate))
			$n->field_date_time['und'][0]['value2'] = strtotime($row->ConfEndDate);
		if(!empty($row->ConfRegDeadline))
			$n->field_registration_deadline['und'][0]['value'] = strtotime($row->ConfRegDeadline);
		if(!empty($row->ConfLocation))
			$n->field_location['und'][0]['value'] = $row->ConfLocation;
		$n->field_sessions_alternative_label['und'][0]['value'] = empty($row->TimeslotTitle) ? "Select Timeslot" : $row->TimeslotTitle;
		if(!empty($row->ckTimeslotReqd))
			$n->field_sessions_active['und'][0]['value'] = $n->field_sessions_required['und'][0]['value'] = 1;
		if(empty($row->ckHideAddress))
			$n->field_contact_active['und'][0]['value'] = $n->field_address_active['und'][0]['value'] = $n->field_city_active['und'][0]['value'] = $n->field_state_active['und'][0]['value'] = $n->field_zip_active['und'][0]['value'] = $n->field_phone_active['und'][0]['value'] = 1;
		if(!empty($row->ckAddressRqd))
			$n->field_address_required['und'][0]['value'] = $n->field_city_required['und'][0]['value'] = $n->field_state_required['und'][0]['value'] = $n->field_zip_required['und'][0]['value'] = 1;
		if(!empty($row->ckPhoneRqd))
			$n->field_phone_required['und'][0]['value'] = 1;
		$n->field_org_active['und'][0]['value'] = 1;
		if(!empty($row->ckOrgRqd))
			$n->field_org_required['und'][0]['value'] = 1;
		$n->field_school_active['und'][0]['value'] = 1;
		
		node_save($n);
		$nodes[$row->ConfID] = $n->nid;
		
		//save sessions
		db_set_active('production');
		$slots = db_query("SELECT * FROM ia_conf_timeslots WHERE ConfID = " . $row->ConfID);
		db_set_active();
		foreach($slots as $slot)
		{
			$field_collection_item = entity_create('field_collection_item', array('field_name' => 'field_sessions'));
			$field_collection_item->setHostEntity('node', $n);
			$field_collection_item->field_session_title['und'][0]['value'] = $slot->TimeSlot;
			$field_collection_item->save();			
		}
		echo_to_browser($n->title . '<br>');
	}
	echo_to_browser('Done<br><br>');
	
	#========================================#
	#   ia conference registrations import   #
	#========================================#

	echo_to_browser('Importing ia conference registrations...<br>');
	db_set_active('production');
	$result = db_query("SELECT * FROM ia_conf_registration");
	db_set_active();
	foreach($result as $row)
	{
		if(isset($nodes[$row->ConfID]))
		{
			//create a node object
			$n = default_node('registration');
			$n->uid = new_id('ia_contacts', $row->ContactID);
			if(!empty($row->RegisteredOn))
				$n->created = strtotime($row->RegisteredOn);
			$n->field_event['und'][0]['target_id'] = $nodes[$row->ConfID];
			$event = node_load($nodes[$row->ConfID]);
			$n->title = 'Registration for ' . $event->title;
			$n->field_registration_status['und'][0]['value'] = GRAHAM_REG_REGISTERED;
			
			//sessions
			db_set_active('production');
			$slots = db_query("SELECT * FROM ia_conf_registration_timeslots WHERE ConfID = " . $row->ConfID . " AND ContactID = " . $row->ContactID);
			db_set_active();
			foreach($slots as $slot)
				$n->field_selected_sessions['und'][] = array('target_id' => $event->field_sessions['und'][$slot->TimeSlotID - 1]['value']);
			
			node_save($n);
			echo_to_browser($n->title . '<br>');
		}
	}
	echo_to_browser('Done<br><br>');
	
	
	#===========================#
	#   faculty course import   #
	#===========================#
	
	echo_to_browser('Importing faculty courses...<br>');
	db_set_active('production');
	$result = db_query("SELECT c.*, hours, course_num, school, department, GROUP_CONCAT(DISTINCT i.frsid) instructors
						FROM gesi_courses c 
						LEFT JOIN gesicredits cr ON cr.id = c.fk_credits 
						LEFT JOIN gesi_coursenum cn ON cn.id = c.id 
						LEFT JOIN gesidef_departments d ON fk_dept = d.departmentid 
						LEFT JOIN gesischools s ON fk_school = s.id
						LEFT JOIN gesi_courseinstructors i ON i.course_id = c.id
						WHERE course_title IS NOT NULL GROUP BY c.id");
	db_set_active();
	foreach($result as $row)
	{
		//create a node object
		$n = default_node('course');
		$n->uid = 3;	//sromansk
		$n->created = strtotime($row->lastmoddate);
		$n->title = strip_tags(replace_br($row->course_title));
		if(!empty($row->description))
		{
			$n->body['und'][0]['value'] = $row->description;
			$n->body['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->UMCourseID))
			$n->field_um_course_id['und'][0]['value'] = sprintf('%06d', $row->UMCourseID);
		if(!empty($row->prerequisites))
		{
			$n->field_prerequisites['und'][0]['value'] = $row->prerequisites;
			$n->field_prerequisites['und'][0]['format'] = 'full_html';
		}
		if(!empty($row->bulletin_url))
			$n->field_bulletin_url['und'][0]['value'] = $row->bulletin_url;
		if(!empty($row->hours))
			$n->field_credits['und'][0]['value'] = $row->hours;
		switch($row->UG_GR)
		{
			case 'ANY': 
			case 'RONR': 
				$n->field_academic_level['und'][] = array('value' => 'Undergraduate');
				$n->field_academic_level['und'][] = array('value' => 'Graduate');
				break;
			case 'UGRD': 
				$n->field_academic_level['und'][] = array('value' => 'Undergraduate'); break;
			case 'GR': 
			case 'RACK': 
				$n->field_academic_level['und'][] = array('value' => 'Graduate'); break;
		}
		
		if(!empty($row->course_num))
			$n->field_course_number['und'][] = array('value' => $row->course_num);
		if(!empty($row->alt_course_num))
		{
			$course_nums = explode('/', $row->alt_course_num);
			foreach($course_nums as $course_num)
				$n->field_course_number['und'][] = array('value' => trim($course_num));
		}
		
		if($row->SustFocusedRelated == 1)
			$n->field_sustainability_category['und'][0]['value'] = 'Related';
		if($row->SustFocusedRelated == 2)
			$n->field_sustainability_category['und'][0]['value'] = 'Focused';
		
		//course instructors
		if(!empty($row->instructors))
		{
			$instructors = explode(',', $row->instructors);
			foreach($instructors as $instructor)
			{
				if($uid = new_id('gesi_faculty', $instructor))
					$n->field_course_instructors['und'][] = array('target_id' => $uid);
				else
					echo_to_browser('Could not find instructor:' . $instructor . '<br>');
			}
		}
		
		//manually map out the semesters
		if(ord($row->Win_09))
			$n->field_semesters['und'][] = array('tid' => 348);
		if(ord($row->Spr_09))
			$n->field_semesters['und'][] = array('tid' => 347);
		if(ord($row->Sum_09))
			$n->field_semesters['und'][] = array('tid' => 346);
		if(ord($row->Fall_09))
			$n->field_semesters['und'][] = array('tid' => 345);
		
		if(ord($row->Win_10))
			$n->field_semesters['und'][] = array('tid' => 344);
		if(ord($row->Spr_10))
			$n->field_semesters['und'][] = array('tid' => 343);
		if(ord($row->Sum_10))
			$n->field_semesters['und'][] = array('tid' => 342);
		if(ord($row->Fall_10))
			$n->field_semesters['und'][] = array('tid' => 195);
		
		if(ord($row->Win_11))
			$n->field_semesters['und'][] = array('tid' => 196);
		if(ord($row->Spr_11))
			$n->field_semesters['und'][] = array('tid' => 197);
		if(ord($row->Sum_11))
			$n->field_semesters['und'][] = array('tid' => 198);
		if(ord($row->Fall_11))
			$n->field_semesters['und'][] = array('tid' => 199);
		
		if(ord($row->Win_12))
			$n->field_semesters['und'][] = array('tid' => 200);
		if(ord($row->Spr_12))
			$n->field_semesters['und'][] = array('tid' => 201);
		if(ord($row->Sum_12))
			$n->field_semesters['und'][] = array('tid' => 202);
		if(ord($row->Fall_12))
			$n->field_semesters['und'][] = array('tid' => 203);
		
		if(ord($row->Win_13))
			$n->field_semesters['und'][] = array('tid' => 204);
		
		//schools & departments
		//have to do this to handle typos in original db
		$special_mapping = array(
			'Germanic Languages & Literatures' => 'Germanic Languages & Literature',
			'Molecular and Integrative Physiology' => 'Molecular & Integrative Physiology',
		);
		if(!empty($row->school))
		{
			//get parent tid
			$query = db_select('taxonomy_term_data', 'd');
			$query->join('taxonomy_term_hierarchy', 'h', 'd.tid = h.tid');
			$query2 = clone $query->condition('vid', 3)->fields('d');
			$tid = $query->condition('parent', 0)->condition('name', $row->school)->execute()->fetchObject()->tid;
			if(empty($tid))
				echo_to_browser('Could not find School:' . $row->school . '<br>');
			else
			{
				//get child tid
				if(!empty($row->department))
					$tid = $query2->condition('parent', $tid)->condition('name', isset($special_mapping[$row->department]) ? $special_mapping[$row->department] : $row->department)->execute()->fetchObject()->tid;
				if(!empty($tid))
					$n->field_school_college['und'][] = array('tid' => $tid);
				else
					echo_to_browser('Could not find Department:' . $row->department . '<br>');
			}
		}
		
		//focus areas
		db_set_active('production');
		$focus_areas = db_query("SELECT * FROM gesicourse_focusareas c 
									LEFT JOIN gesidef_focusareas f ON f.FocusAreaID = c.FocusAreaID
									LEFT JOIN gesidef_subfocusareas s ON s.SubFocusAreaID = c.SubFocusAreaID 
									WHERE CourseID = " . $row->id);
		db_set_active();
		foreach($focus_areas as $focus_area)
		{
			if(!empty($focus_area->Focus_Area))
			{
				//get parent tid
				$query = db_select('taxonomy_term_data', 'd');
				$query->join('taxonomy_term_hierarchy', 'h', 'd.tid = h.tid');
				$query2 = clone $query->condition('vid', 2)->fields('d');
				$tid = $query->condition('parent', 0)->condition('name', $focus_area->Focus_Area)->execute()->fetchObject()->tid;
				
				//get child tid
				if(!empty($focus_area->SubFocusArea))
					$tid = $query2->condition('parent', $tid)->condition('name', $focus_area->SubFocusArea)->execute()->fetchObject()->tid;
				if(!empty($tid))
					$n->field_area_of_expertise['und'][] = array('tid' => $tid);
				else
					print_r($focus_area);
			}
		}
		
		node_save($n);
		echo_to_browser($n->title . '<br>');
	}
	echo_to_browser('Done<br><br>');
	
	echo_to_browser('Took ' . round((time() - $start_time)/60, 1) . ' minutes');
	ob_end_flush();	
	exit;
}

/**
 * Overwrite user data with any data from tblcontact. Unless it's a faculty member.
 * Assign roles whether or not user is found in tblcontact. This lets us assign roles from
 * the original contact record.
 */
function import_tblcontact($u, $lngContactID, $roles = array())
{
	$non_faculty = empty($u->field_faculty_researcher) || empty($u->field_faculty_researcher['und'][0]['value']);
	db_set_active('production');
	if(!empty($lngContactID))
	{
		$row = db_query("SELECT * FROM tblcontact WHERE lngContactID = $lngContactID")->fetchObject();
		if(!empty($row))
		{
			if(!empty($row->strContactFirstName) && ($non_faculty || empty($u->field_first_name)))
				$u->field_first_name['und'][0]['value'] = $row->strContactFirstName;
			if(!empty($row->strContactLastName) && ($non_faculty || empty($u->field_last_name)))
				$u->field_last_name['und'][0]['value'] = $row->strContactLastName;
			if(!empty($row->strPositionTitle) && ($non_faculty || empty($u->field_title)))
				$u->field_title['und'][0]['value'] = $row->strPositionTitle;
			if(!empty($row->GraduationYear))
			{
				$date = $row->GraduationYear;
				if(!empty($row->GradMo))
					$date .= date(' F', mktime(0, 0, 0, $row->GradMo));
				$u->field_anticipated_grad_date['und'][0]['value'] = date('Y-m-d H:i:s', strtotime($date));
			}
			if(!empty($row->DefenseDateYr))
			{
				$date = $row->DefenseDateYr;
				if(!empty($row->DefenseDateMo))
					$date .= date(' F', mktime(0, 0, 0, $row->DefenseDateMo));
				$u->field_anticipated_defense_date['und'][0]['value'] = date('Y-m-d H:i:s', strtotime($date));
			}
			if(!empty($row->PersonalBio) && ($non_faculty || empty($u->field_bio)))
				$u->field_bio['und'][0]['value'] = $row->PersonalBio;
			if(!empty($row->ckStudent) && $non_faculty)
				$u->field_student['und'][0]['value'] = 1;
			
			//get roles from row
			if(!empty($row->lngRoleCode1))
				$roles[] = $row->lngRoleCode1;
			if(!empty($row->lngRoleCode2))
				$roles[] = $row->lngRoleCode2;
			if(!empty($row->lngRoleCode3))
				$roles[] = $row->lngRoleCode3;
			
			//get other roles
			$contact_roles = db_query("SELECT * FROM tblcontact_roles WHERE lngContactID = $lngContactID");
			foreach($contact_roles as $role)
				$roles[] = $role->lngRoleCode;
		}
	}
	if(!empty($roles))
	{
		$roles = array_unique($roles);
		//get role names from tblrolelookup and then matching tids
		$tids = import_translate_roles($roles);
		foreach($tids as $tid)
			graham_add_affiliation($u, array('und' => array(array('tid' => $tid))), FALSE);
	}
	db_set_active();
}

/**
 * Map old roles to new roles
 */
function import_translate_roles($roles)
{
	db_set_active('production');
	$role_names = db_query("SELECT strRole FROM tblrolelookup WHERE lngRoleCode IN (" . implode(',', $roles) . ")")->fetchCol();
	db_set_active();
	return db_query("SELECT tid FROM db_taxonomy_term_data WHERE vid = 5 AND name IN ('" . implode("','", $role_names) . "')")->fetchCol();
}

/**
 * Map old role to new role
 */
function import_translate_role($role)
{
	$tids = import_translate_roles(array($role));
	return count($tids) ? $tids[0] : NULL;
}

/**
 * Move a file from the file_upload table to a given field.
 * If $row is supplied move file from there instead.
 */
function import_file($fid, &$dest, $nid, $folder = 'proposals', $row = NULL, $entity_type = 'node')
{
	if(!empty($fid))
	{
		db_set_active('production');
		$result = db_query("SELECT * FROM file_upload WHERE FileID = :fid LIMIT 1", array(':fid' => $fid));
		db_set_active();
		$row = $result->fetchObject();
	}
	if(!empty($row) && !empty($row->FileContent))
	{
		//save file
		$file = file_save_data($row->FileContent, "private://$folder/" . $row->FileName);
		
		// Record that the file is being used by the node.
		file_usage_add($file, 'file', $entity_type, $nid);
		
		//add missing properties needed to save file field
		$file->description = empty($row->FileDesc) ? '' : $row->FileDesc;
		$file->display = 1;
		
		//attach file to node
		$dest = array('und' => array((array) $file));	//convert file object to array
	}
}

function replace_br($html)
{
	return preg_replace('#<br\s*/?>#i', " ", $html);
}

function new_id($table, $id)
{
	return db_query("SELECT * FROM {field_data_field_legacy_id} i, {field_data_field_legacy_table} t 
							WHERE field_legacy_id_value = $id 
							AND field_legacy_table_value = '$table'
							AND i.entity_id = t.entity_id
							AND i.delta = t.delta")->fetchObject()->entity_id;
}

function echo_to_browser($s)
{
	echo $s;
	echo str_pad('',4096)."\n";  
	ob_flush();
	flush();
}

function default_node($type)
{
	$n = new stdClass();
	$n->is_new = TRUE;
	$n->type = $type;
	$n->status = 1;
	$n->language = 'und';
	return $n;
}
