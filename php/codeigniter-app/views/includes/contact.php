<?php
$hidden_contact_fields = isset($hidden_contact_fields) ? $hidden_contact_fields : array();

$db_field = 'first_name';
$field = $contact_type . '_' . $db_field;
?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
	<?php echo form_label('First Name', $field, array('class' => 'control-label'));	?>
	<div class="controls">
	<?php echo form_input(array(
		'name' => $field, 
		'id' => $field,
		'value' => set_value($field, $form->{$contact_type}->{$db_field}),
	));	?>
	</div>
</div>


<?php 
$db_field = 'last_name';
$field = $contact_type . '_' . $db_field;
?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
	<?php echo form_label('Last Name', $field, array('class' => 'control-label'));	?>
	<div class="controls">
	<?php echo form_input(array(
		'name' => $field, 
		'id' => $field,
		'value' => set_value($field, $form->{$contact_type}->{$db_field}),
	));	?>
	</div>
</div>


<?php 
$db_field = 'email';
$field = $contact_type . '_' . $db_field;
?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
	<?php echo form_label('E-mail', $field, array('class' => 'control-label'));	?>
	<div class="controls">
	<?php echo form_input(array(
		'name' => $field, 
		'id' => $field,
		'value' => set_value($field, $form->{$contact_type}->{$db_field}),
	));	?>
	</div>
</div>


<?php 
$db_field = 'alt_email';
if(!in_array($db_field, $hidden_contact_fields)) :
	$field = $contact_type . '_' . $db_field;
	?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
		<?php echo form_label('Alternate E-mail', $field, array('class' => 'control-label'));	?>
		<div class="controls">
		<?php echo form_input(array(
			'name' => $field, 
			'id' => $field,
			'value' => set_value($field, $form->{$contact_type}->{$db_field}),
		));	?>
		</div>
	</div>
<?php endif; ?>


<?php 
$db_field = 'phone';
if(!in_array($db_field, $hidden_contact_fields)) :
	$field = $contact_type . '_' . $db_field;
	?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
		<?php echo form_label('Phone', $field, array('class' => 'control-label'));	?>
		<div class="controls">
		<?php echo form_input(array(
			'name' => $field, 
			'id' => $field,
			'value' => set_value($field, $form->{$contact_type}->{$db_field}),
		)); ?>
		</div>
	</div>
<?php endif; ?>


<?php 
$db_field = 'fax';
if(!in_array($db_field, $hidden_contact_fields)) :
	$field = $contact_type . '_' . $db_field;
	?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
		<?php echo form_label('Fax', $field, array('class' => 'control-label'));	?>
		<div class="controls">
		<?php echo form_input(array(
			'name' => $field, 
			'id' => $field,
			'value' => set_value($field, $form->{$contact_type}->{$db_field}),
		));	?>
		</div>
	</div>
<?php endif; ?>


<?php 
$db_field = 'address1';
$field = $contact_type . '_' . $db_field;
?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
	<?php echo form_label('Address', $field, array('class' => 'control-label'));	?>
	<div class="controls">
	<?php
	echo form_input(array(
		'name' => $field, 
		'id' => $field,
		'value' => set_value($field, $form->{$contact_type}->{$db_field}),
	));	?>
	</div>
</div>


<?php 
$db_field = 'address2';
$field = $contact_type . '_' . $db_field;
?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
	<div class="controls">
	<?php echo form_input(array(
		'name' => $field, 
		'id' => $field,
		'value' => set_value($field, $form->{$contact_type}->{$db_field}),
	));	?>
	</div>
</div>


<?php 
$db_field = 'city';
$field = $contact_type . '_' . $db_field;
?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
	<?php echo form_label('City', $field, array('class' => 'control-label'));	?>
	<div class="controls">
	<?php
	echo form_input(array(
		'name' => $field, 
		'id' => $field,
		'value' => set_value($field, $form->{$contact_type}->{$db_field}),
	));	?>
	</div>
</div>


<?php 
$db_field = 'state';
$field = $contact_type . '_' . $db_field;
?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
	<?php echo form_label('State', $field, array('class' => 'control-label'));	?>
	<div class="controls">
	<?php echo form_dropdown($field, array(
		'' => '',
		'AK' => 'AK',
		'AL' => 'AL',
		'AS' => 'AS',
		'AZ' => 'AZ',
		'AR' => 'AR',
		'CA' => 'CA',
		'CO' => 'CO',
		'CT' => 'CT',
		'DE' => 'DE',
		'DC' => 'DC',
		'FM' => 'FM',
		'FL' => 'FL',
		'GA' => 'GA',
		'GU' => 'GU',
		'HI' => 'HI',
		'ID' => 'ID',
		'IL' => 'IL',
		'IN' => 'IN',
		'IA' => 'IA',
		'KS' => 'KS',
		'KY' => 'KY',
		'LA' => 'LA',
		'ME' => 'ME',
		'MH' => 'MH',
		'MD' => 'MD',
		'MA' => 'MA',
		'MI' => 'MI',
		'MN' => 'MN',
		'MS' => 'MS',
		'MO' => 'MO',
		'MT' => 'MT',
		'NE' => 'NE',
		'NV' => 'NV',
		'NH' => 'NH',
		'NJ' => 'NJ',
		'NM' => 'NM',
		'NY' => 'NY',
		'NC' => 'NC',
		'ND' => 'ND',
		'MP' => 'MP',
		'OH' => 'OH',
		'OK' => 'OK',
		'OR' => 'OR',
		'PW' => 'PW',
		'PA' => 'PA',
		'PR' => 'PR',
		'RI' => 'RI',
		'SC' => 'SC',
		'SD' => 'SD',
		'TN' => 'TN',
		'TX' => 'TX',
		'UT' => 'UT',
		'VT' => 'VT',
		'VI' => 'VI',
		'VA' => 'VA',
		'WA' => 'WA',
		'WV' => 'WV',
		'WI' => 'WI',
		'WY' => 'WY', 
	), set_value($field, $form->{$contact_type}->{$db_field}));
	?>
	</div>
</div>


<?php 
$db_field = 'zip';
$field = $contact_type . '_' . $db_field;
?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
	<?php echo form_label('Zip', $field, array('class' => 'control-label'));	?>
	<div class="controls">
	<?php echo form_input(array(
		'name' => $field, 
		'id' => $field,
		'value' => set_value($field, $form->{$contact_type}->{$db_field}),
	));	?>
	</div>
</div>


<?php 
$db_field = 'org';
if(!in_array($db_field, $hidden_contact_fields)) :
	$field = $contact_type . '_' . $db_field;
	?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
		<?php echo form_label('Organization', $field, array('class' => 'control-label'));	?>
		<div class="controls">
		<?php echo form_dropdown($field, array(
			'' => '',
			'Calvin College' => 'Calvin College',
			'Central Michigan University' => 'Central Michigan University',
			'Eastern Michigan University' => 'Eastern Michigan University',
			'Grand Valley State University' => 'Grand Valley State University',
			'Hope College' => 'Hope College',
			'Michigan State University' => 'Michigan State University',
			'Michigan Technological University' => 'Michigan Technological University',
			'Oakland University' => 'Oakland University',
			'Other' => 'Other Organization - Please specify below',
			'Saginaw Valley State University' => 'Saginaw Valley State University',
			'University of Michigan' => 'University of Michigan',
			'Wayne State University' => 'Wayne State University',
			'Western Michigan University' => 'Western Michigan University',
		), set_value($field, $form->{$contact_type}->{$db_field}));
		?>
		</div>
	</div>
<?php endif; ?>


<?php 
$db_field = 'org_other';
if(!in_array($db_field, $hidden_contact_fields)) :
	$field = $contact_type . '_' . $db_field;
	?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
		<div class="controls">
		<p>If Other, please specify</p>
		<?php echo form_input(array(
			'name' => $field, 
			'id' => $field,
			'value' => set_value($field, $form->{$contact_type}->{$db_field}),
		));	?>
		</div>
	</div>
<?php endif; ?>


<?php 
$db_field = 'title';
if(!in_array($db_field, $hidden_contact_fields)) :
	$field = $contact_type . '_' . $db_field;
	?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
		<?php echo form_label('Title', $field, array('class' => 'control-label'));	?>
		<div class="controls">
		<?php echo form_input(array(
			'name' => $field, 
			'id' => $field,
			'value' => set_value($field, $form->{$contact_type}->{$db_field}),
		));	?>
		</div>
	</div>
<?php endif; ?>


<?php 
$db_field = 'institution';
if(!in_array($db_field, $hidden_contact_fields)) :
	$field = $contact_type . '_' . $db_field;
	?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
		<?php echo form_label('Institution', $field, array('class' => 'control-label'));	?>
		<div class="controls">
		<?php echo form_input(array(
			'name' => $field, 
			'id' => $field,
			'value' => set_value($field, $form->{$contact_type}->{$db_field}),
		));	?>
		</div>
	</div>
<?php endif; ?>


<?php 
$db_field = 'school_dept';
if(!in_array($db_field, $hidden_contact_fields)) :
	$field = $contact_type . '_' . $db_field;
	?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
		<?php echo form_label('School and/or Department', $field, array('class' => 'control-label'));	?>
		<div class="controls">
		<?php echo form_input(array(
			'name' => $field, 
			'id' => $field,
			'value' => set_value($field, $form->{$contact_type}->{$db_field}),
		));	?>
		</div>
	</div>
<?php endif; ?>


<?php 
$db_field = 'send_txts';
if(!in_array($db_field, $hidden_contact_fields)) :
	$field = $contact_type . '_' . $db_field;
	$value = 1;
	?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
		<span class="control-label">Text messaging</span>
		<div class="controls">
			<label class="checkbox"><?php echo form_checkbox($field, $value, set_checkbox($field, $value, $form->{$contact_type}->{$db_field} == $value));	?> Send applications updates via text message</label>
			<p class="muted"><small>Both cell phone and provider are required for this option. If your cell phone provider is not listed then this system does not support the texting option for your cell phone provider.</small></p>
		</div>
	</div>
<?php endif; ?>


<?php 
$db_field = 'cell_phone';
if(!in_array($db_field, $hidden_contact_fields)) :
	$field = $contact_type . '_' . $db_field;
	?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
		<?php echo form_label('Cell Phone', $field, array('class' => 'control-label'));	?>
		<div class="controls">
		<?php echo form_input(array(
			'name' => $field, 
			'id' => $field,
			'value' => set_value($field, $form->{$contact_type}->{$db_field}),
		));	?>
		</div>
	</div>
<?php endif; ?>


<?php 
$db_field = 'cell_provider';
if(!in_array($db_field, $hidden_contact_fields)) :
	$field = $contact_type . '_' . $db_field;
	?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
		<?php echo form_label('Cell Phone Provider', $field, array('class' => 'control-label'));	?>
		<div class="controls">
			<?php echo form_dropdown($field, array(
				'' => '',
				'Alltel' => 'Alltel',
				'AT&amp;T' => 'AT&amp;T',
				'Nexttel' => 'Nexttel',
				'Sprint' => 'Sprint',
				'T-mobile' => 'T-mobile',
				'Verizon' => 'Verizon',
			), set_value($field, $form->{$contact_type}->{$db_field}));
			?>
		</div>
	</div>
<?php endif; ?>


<?php 
$db_field = 'alt_phone';
if(!in_array($db_field, $hidden_contact_fields)) :
	$field = $contact_type . '_' . $db_field;
	?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
		<?php echo form_label('Alternate Phone', $field, array('class' => 'control-label'));	?>
		<div class="controls">
		<?php echo form_input(array(
			'name' => $field, 
			'id' => $field,
			'value' => set_value($field, $form->{$contact_type}->{$db_field}),
		));	?>
		</div>
	</div>
<?php endif; ?>
