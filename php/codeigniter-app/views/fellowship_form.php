<?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal')); ?>
	<fieldset>
		<legend>Personal Information</legend>
		<?php echo $applicant; ?>
		<?php $field = 'is_us_citizen'; $value = 1; ?>
		<div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Verification of US Citizenship</span>
			<div class="controls">
				<label class="checkbox"><?php echo form_checkbox($field, $value, set_checkbox($field, $value, $form->{$field} == $value));	?> I am a citizen of the United States of America</label>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Educational Information</legend>
		<?php $field = 'university'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('University', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<p>What Michigan Space Grant University will you be attending in the Fall?</p>
				<?php echo form_dropdown($field, array(
					'' => '',
					'CMICH' => 'Central Michigan University',
					'EMICH' => 'Eastern Michigan University',
					'GVSU' => 'Grand Valley State University',
					'MSU' => 'Michigan State University',
					'MTU' => 'Michigan Technological University',
					'Oakland' => 'Oakland University',
					'SVSU' => 'Saginaw Valley State University',
					'UMICH' => 'University of Michigan',
					'Wayne' => 'Wayne State University',
					'WMICH' => 'Western Michigan University',
				), set_value($field, $form->{$field}));
				?>
			</div>
		</div>


		<?php $field = 'major'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Major', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php echo form_input(array(
					'name' => $field, 
					'id' => $field,
					'value' => set_value($field, $form->{$field}),
				));	?>
			</div>
		</div>


		<?php $field = 'minor'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Minor', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php echo form_input(array(
					'name' => $field, 
					'id' => $field,
					'value' => set_value($field, $form->{$field}),
				));	?>
			</div>
		</div>


		<?php $field = 'school_dept'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('School and/or Department', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php echo form_input(array(
					'name' => $field, 
					'id' => $field,
					'value' => set_value($field, $form->{$field}),
				));	?>
			</div>
		</div>


		<?php $field = 'degree_sought'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Degree Sought', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php if($type == 'undergrad')
				{
					$values = array(
						'' => '',
						'BA' => 'BA',
						'BBA' => 'BBA',
						'BS' => 'BS',
						'AB' => 'AB',
						'Bachelors' => 'Bachelors',
					);
				}
				else
				{
					$values = array(
						'' => '',
						'MA' => 'MA',
						'MBA' => 'MBA',
						'MS' => 'MS',
						'MES' => 'MES',
						'Masters' => 'Masters',
						'JD' => 'JD',
						'MD' => 'MD',
						'PhD' => 'PhD',
						'Doctorate' => 'Doctorate',
					);
				}
				echo form_dropdown($field, $values, set_value($field, $form->{$field})); ?>
			</div>
		</div>
		
		
		<?php $field = 'grad_date'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Expected Date of Graduation', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<div class="input-append">
				<?php echo form_input(array(
					'name' => $field, 
					'id' => $field,
					'value' => set_value($field, $form->{$field}),
					'class' => 'datepicker span2',
				));	?><span class="add-on"><i class="icon-calendar"></i></span>
				</div>
			</div>
		</div>
		

		<?php $field = 'current_standing'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Current Standing', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php if($type == 'undergrad')
				{
					$values = array(
						'' => '',
						'Freshman' => 'Freshman',
						'Sophomore' => 'Sophomore',
						'Junior' => 'Junior',
						'Senior' => 'Senior',
					);
				}
				else
				{
					$values = array(
						'' => '',
						'Masters' => 'Masters',
						'PhD' => 'PhD',
					);
				}
				echo form_dropdown($field, $values, set_value($field, $form->{$field})); ?>
			</div>
		</div>
		
		
		<div class="control-group">
			<span class="control-label">Educational History</span>
			<div class="controls">
				<p>List the universities that you have attended beginning with the institution you are currently attending.  Other than your current institution, only list universities where you have received a degree or earned more than 10 credit hours.  Submitted transcripts may be unofficial. If you do not have a PDF conversion tool you can use one of these free on-line PDF converters <a href="http://www.pdfonline.com/convert_pdf.asp" target="blank">PDF Online</a> - <a href="http://convert.neevia.com/" target="blank">Neevia Technology</a></p>
				<p>To ensure that your transcripts may be properly included in your application file, please ensure that they are not protected via encryption, a password, a signed certificate or in any other manner.</p>
				<table class="table">
					<tr>
						<th>College/University</th>
						<th>Major</th>
						<th>Degree</th>
						<th>Grad Date/<br>Grad Expected Date</th>
						<th>GPA<br>(4&nbsp;pt&nbsp;scale)</th>
						<th>Transcript</th>
					</tr>
					<?php echo $education; ?>
				</table>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Fellowship Information</legend>
		<?php $field = 'new_app'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">New or Continuation</span>
			<div class="controls">
				<?php foreach(array(
					'New Proposal' => 'This is a new proposal',
					'Continuation Proposal' => 'This proposal is for continuation of an effort funded last year and a progress report is attached',
				) as $key => $value)
					echo '<label class="radio">' . form_radio($field, $key, set_radio($field, $key, $key == $form->{$field})) . ' ' . $value . '</label>';
				?>
			</div>
		</div>


		<?php $field = 'progress_report'; ?><div class="control-group <?php echo isset($progress_report_error) && $show_errors ? 'error' : '' ?>">
			<div class="controls">
				<?php if(!empty($form->{$field})) : ?>
					<p class="uploaded-file"><?php echo anchor('fellowship/download/' . $form->app_id . '/' . $field, $form->{$field}); ?>
					<label class="checkbox inline"><?php echo form_checkbox($field . '_remove', 1); ?> <a class="btn btn-mini remove-file">Remove</a></label></p>
				<?php endif; ?>
				<div>
					<?php echo form_upload(array(
						'name' => $field, 
						'id' => $field,
					));	?>
					<p class="muted"><small>PDF format only</small></p>
				</div>
			</div>
		</div>


		<?php $field = 'category'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Fellowship Category', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php echo form_dropdown($field, array(
					'' => '',
					'Scientific Research' => 'Scientific Research',
					'Educational Research' => 'Educational Research',
					'Public Service' => 'Public Service',
				), set_value($field, $form->{$field}));
				?>
			</div>
		</div>
		
		
		<?php $field = 'focus'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Fellowship Focus', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php echo form_dropdown($field, array(
					'' => '',
					'Aerospace' => 'Aerospace',
					'Space Science' => 'Space Science',
					'Earth System Science' => 'Earth System Science',
					'Other' => 'Other',
				), set_value($field, $form->{$field}));
				?>&nbsp;&nbsp;&nbsp;If other, please specify
				<?php $field = 'focus_other';
				echo form_input(array(
					'name' => $field, 
					'id' => $field,
					'value' => set_value($field, $form->{$field}),
				));	?>
			</div>
		</div>
		
		
		<?php $field = 'fellowship_type'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Fellowship Type', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php if($type == 'undergrad') {
					echo form_dropdown($field, array(
						'' => '',
						'Regular Fellowship Grant' => 'Regular Fellowship Grant',
						'Undergraduate Underrepresented Minority Program' => 'Undergraduate Underrepresented Minority Program',
					), set_value($field, $form->{$field}));
				} else {
					echo '<p>Regular Fellowship Grant</p>';
				}?>
			</div>
		</div>
		
		
		<?php $field = 'augmentation_funding'; $value = 1; ?>
		<div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<div class="controls">
				<?php if($type == 'grad') : ?>
					<p>If you are an underrepresented minority, please answer the following question.</p>
				<?php else: ?>
					<p>If you are an underrepresented minority and you are applying to the Regular Fellowship Grant, please answer the following question.</p>
				<?php endif; ?>
				<label class="checkbox"><?php echo form_checkbox($field, $value, set_checkbox($field, $value, $form->{$field} == $value));	?> My mentor is requesting $500 in funding augmentation.</label>
			</div>
		</div>

		
		<?php if($type == 'undergrad') : ?>
			<?php $field = 'discretionary_funding'; $value = 1; ?>
			<div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
				<div class="controls">
					<p>If you are applying for an Undergraduate Underrepresented Minority Program please answer the following question.</p>
					<label class="checkbox"><?php echo form_checkbox($field, $value, set_checkbox($field, $value, $form->{$field} == $value));	?> My mentor is requesting discretionary funding as part of this application.</label>
				</div>
			</div>
		<?php endif; ?>
   
	</fieldset>
	<fieldset>
		<legend>Essay</legend>
		<?php $field = 'essay'; ?><div class="control-group <?php echo isset($essay_error) && $show_errors ? 'error' : '' ?>">
			<div class="controls">
				<p>In three pages or less:</p>
				<ul>
					<li>describe the research or public service activity you intend to pursue</li>
					<li>explain how the proposed activity is related to NASA strategic interests</li>
					<li>describe your specific responsibilities in the project and specific outcomes expected for the research</li>
					<li>describe your future career/educational interests</li>
				</ul>
				<?php if(!empty($form->{$field})) : ?>
					<p class="uploaded-file"><?php echo anchor('fellowship/download/' . $form->app_id . '/' . $field, $form->{$field}); ?>
					<label class="checkbox inline"><?php echo form_checkbox($field . '_remove', 1); ?> <a class="btn btn-mini remove-file">Remove</a></label></p>
				<?php endif; ?>
				<div>
					<?php echo form_upload(array(
						'name' => $field, 
						'id' => $field,
					));	?>
					<p class="muted"><small>PDF format only</small></p>
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>References</legend>
		<div class="row-fluid">
			<div class="span6">
				<h4>Mentor</h4>
				<?php echo $ref1; ?>
			</div>
			<div class="span6">
				<h4>Reference #2</h4>
				<?php echo $ref2; ?>
			</div>
		</div>
		<p>Once you have submitted your application both of these individuals will received an email requesting their supporting material. Your application will not be complete until this information has been received. Please inform both of these individuals that they will receive an email request from <b>notices@spacegrant.net</b> for this information on your behalf. The deadline for submission of reference letters is Friday, November 23, 2012.</p>
	</fieldset>
	<fieldset>
		<legend>Fellowship Project</legend>
		<?php $field = 'project_title'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Project Title', $field, array('class' => 'control-label'));	?>
			<div class="controls">
			<?php echo form_input(array(
				'name' => $field, 
				'id' => $field,
				'value' => set_value($field, $form->{$field}),
				'class' => 'span5',
			));	?>
			</div>
		</div>


		<?php $field = 'abstract'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Abstract', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php echo form_textarea(array(
					'name' => $field, 
					'id' => $field,
					'value' => set_value($field, $form->{$field}),
					'class' => 'span5',
				));	?>
				<p class="muted"><small>150 words or less</small></p>
			</div>
		</div>


		<?php $field = 'advisor_read'; $value = 1; ?>
		<div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<div class="controls">
				<label class="checkbox"><?php echo form_checkbox($field, $value, set_checkbox($field, $value, $form->{$field} == $value));	?> My faculty advisor has read and approved this research statement and has agreed to supervise me while I conduct this research.</label>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Other Information</legend>
		<p>Students are strongly encouraged to provide the information requested below which is for reporting purposes only.</p>

		<?php $field = 'gender'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Gender</span>
			<div class="controls">
				<?php foreach(array('Male', 'Female') as $value)
					echo '<label class="radio">' . form_radio($field, $value, set_radio($field, $value, $value == $form->{$field})) . ' ' . $value . '</label>';
				?>
			</div>
		</div>


		<?php $field = 'ethnicity'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Ethnicity', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php echo form_dropdown($field, array(
					'' => '',
					'Hispanic or Latino' => 'Hispanic or Latino',
					'Not Hispanic or Latino' => 'Not Hispanic or Latino',
				), set_value($field, $form->{$field}));
				?>
			</div>
		</div>


		<?php $field = 'race'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Race', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php echo form_dropdown($field, array(
					'' => '',
					'American Indian or Alaska Native' => 'American Indian or Alaska Native',
					'Asian' => 'Asian',
					'Black or African American' => 'Black or African American',
					'Native Hawaiian or Other Pacific Islander' => 'Native Hawaiian or Other Pacific Islander',
					'White' => 'White',
				), set_value($field, $form->{$field}));
				?>
			</div>
		</div>
		
		
		<?php $field = 'dob'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Birthdate', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<div class="input-append">
				<?php echo form_input(array(
					'name' => $field, 
					'id' => $field,
					'value' => set_value($field, $form->{$field}),
					'class' => 'datepicker span2',
				));	?><span class="add-on"><i class="icon-calendar"></i></span>
				</div>
			</div>
		</div>
		

		<?php $field = 'disability'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Disability</span>
			<div class="controls">
				<?php foreach(array('Yes', 'No') as $value)
					echo '<label class="radio">' . form_radio($field, $value, set_radio($field, $value, $value == $form->{$field})) . ' ' . $value . '</label>';
				?>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Verification</legend>
		<?php $field = 'certification'; $value = 1; ?>
		<div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<div class="controls">
				<label class="checkbox"><?php echo form_checkbox($field, $value, set_checkbox($field, $value, $form->{$field} == $value));	?> I certify that all of the information contained in this application is complete and correct and that I meet all of the eligibility requirements stated in this application.</label>
			</div>
		</div>
	</fieldset>
	<div class="form-actions">
		<input type="submit" name="submit" id="submit" value="Submit Application" class="btn btn-primary"/>
		<input type="submit" name="save" id="save" value="Save for Later" class="btn"/>
	</div>
<?php echo form_close();?>