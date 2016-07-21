<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=helpspot_ee_manager'.AMP.'method=save_settings');?>

 

<?php 
$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
    array('data' => lang('helpspot_ee_manager_config_name'), 'style' => 'width:50%;'),
    lang('helpspot_ee_manager_config_value')
);


foreach ($settings as $key => $val)
{
	$this->table->add_row(lang($key, $key), $val);
}

echo $this->table->generate();

?>
<?php $this->table->clear()?>

<p><?=form_submit('submit', lang('save'), 'class="submit"')?></p>

<?php
form_close();

