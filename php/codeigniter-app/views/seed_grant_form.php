<?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal')); ?>
	<fieldset>
		<legend>Proposal Info</legend>
		<?php echo $applicant; ?>
		
		
		<?php $db_field = 'ethnicity';
		$field = $db_field . '[]'; ?>
		<div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Ethnicity (optional)</span>
			<div class="controls">
				<?php 
				foreach(array('African American', 'Caucasian', 'Hispanic/Latino', 'Native American', 'Pacific Islander', 'Other') as $value)
					echo '<label class="checkbox">' . form_checkbox($field, $value, set_checkbox($field, $value, in_array($value, (array) $form->{$db_field}))) . ' ' . $value . '</label>';
				?>
			</div>
		</div>
		
		
		<?php $field = 'ethnicity_other'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<div class="controls">
				<p>If other, please specify</p>
				<?php echo form_input(array(
					'name' => $field, 
					'id' => $field,
					'value' => set_value($field, $form->{$field}),
				));	?>
			</div>
		</div>


		<?php $field = 'gender'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Gender (optional)</span>
			<div class="controls">
				<?php foreach(array('No Reply', 'Male', 'Female') as $value)
					echo '<label class="radio">' . form_radio($field, $value, set_radio($field, $value, $value == $form->{$field})) . ' ' . $value . '</label>'; ?>
			</div>
		</div>
		

		<?php $field = 'disabilities'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Disabilities (optional)</span>
			<div class="controls">
				<?php foreach(array('No Reply', 'I do not have a disability', 'I have a disability') as $value)
					echo '<label class="radio">' . form_radio($field, $value, set_radio($field, $value, $value == $form->{$field})) . ' ' . $value . '</label>'; ?>
			</div>
		</div>
		

		<?php $field = 'proposal_title'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Proposal Title', $field, array('class' => 'control-label'));	?>
			<div class="controls">
			<?php echo form_input(array(
				'name' => $field, 
				'id' => $field,
				'value' => set_value($field, $form->{$field}),
				'class' => 'span5',
			));	?>
			</div>
		</div>


		<?php $field = 'current_position'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Current Position', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php echo form_dropdown($field, array(
					'' => '',
					'Research Scientist' => 'Research Scientist',
					'Assistant Professor' => 'Assistant Professor',
					'Associate Professor' => 'Associate Professor',
					'Full Professor' => 'Full Professor',
					'Other - Please Specify' => 'Other - Please Specify',
				), set_value($field, $form->{$field}));
				?>
			</div>
		</div>


		<?php $field = 'current_position_other'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<div class="controls">
				<p>If other, please specify</p>
				<?php echo form_input(array(
					'name' => $field, 
					'id' => $field,
					'value' => set_value($field, $form->{$field}),
				));	?>
			</div>
		</div>


		<?php $field = 'position_duration'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Duration of my present position', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php echo form_dropdown($field, array(
					'' => '',
					'2 years or less' => '2 years or less',
					'2-5 years' => '2-5 years',
					'5-10 years' => '5-10 years',
					'more than 10 years' => 'more than 10 years',
				), set_value($field, $form->{$field}));
				?>
			</div>
		</div>


		<?php $field = 'current_research_position'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Current Research Position', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php echo form_dropdown($field, array(
					'' => '',
					'the PI on one or more research projects' => 'the PI on one or more research projects',
					'a Co-PI or Co-I on one or more research projects' => 'a Co-PI or Co-I on one or more research projects',
					'without research funding' => 'without research funding',
				), set_value($field, $form->{$field}));
				?>
			</div>
		</div>


		<?php $field = 'research_focus'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Research Focus', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<p>Please indicate the relation of proposed research area to current and ongoing research.</p>
				<?php echo form_dropdown($field, array(
					'' => '',
					'a continuation of my ongoing research activities' => 'a continuation of my ongoing research activities',
					'an extension of my ongoing research activities into a closely related area' => 'an extension of my ongoing research activities into a closely related area',
					'an area in which I have not previously been funded and/or done research' => 'an area in which I have not previously been funded and/or done research',
				), set_value($field, $form->{$field}));
				?>
			</div>
		</div>


		<?php $field = 'budget_requested'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Budget Request', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<div class="input-prepend">
					<span class="add-on">$</span>
					<?php echo form_input(array(
						'name' => $field, 
						'id' => $field,
						'value' => set_value($field, $form->{$field}),
						'class' => 'span2',
					));	?>
				</div>
			</div>
		</div>


		<?php $field = 'proposal'; ?><div class="control-group <?php echo isset($proposal_error) && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Proposal', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php if(!empty($form->{$field})) : ?>
					<p class="uploaded-file"><?php echo anchor('program/download/' . $form->app_id . '/' . $field, $form->{$field}); ?>
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


		<?php $field = 'certification'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Certification', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<p>Please type your full name in this box if the affiliate representative on your campus reviewed and approved this proposal for submission:</p>
				<?php echo form_input(array(
					'name' => $field, 
					'id' => $field,
					'value' => set_value($field, $form->{$field}),
				));	?>
				<p class="muted"><small>Please enter your full name.</small></p>
			</div>
		</div>
	</fieldset>
	<div class="form-actions">
		<input type="submit" name="submit" id="submit" value="Submit Application" class="btn btn-primary"/>
		<input type="submit" name="save" id="save" value="Save for Later" class="btn"/>
	</div>
<?php echo form_close();?>
