<?php

/*
=====================================================
 HelpSpot EE manager - by Yuriy Salimovskiy
 
 uses PHP API implementation by Joe Landsman
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2011 Yuriy Salimovskiy
=====================================================
 This software is based upon and derived from
 ExpressionEngine software protected under
 copyright dated 2004 - 2011. Please see
 http://expressionengine.com/docs/license.html
=====================================================
 File: mcp.helpspot_ee_manager.php
-----------------------------------------------------
 Purpose: HelpSpot API utility for ExpressionEngine
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}



class Helpspot_ee_manager_mcp {

    var $version = '0.2';
    
    var $settings = array();
    
    /** -------------------------------------------
    /**  Constructor
    /** -------------------------------------------*/
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance(); 
        
        $this->EE->lang->loadfile('helpspot_ee_manager');  
        
        $query = $this->EE->db->query("SELECT settings FROM exp_modules WHERE module_name='Helpspot_ee_manager' LIMIT 1");
        $this->settings = unserialize($query->row('settings')); 
    } 
	
	
	/** -------------------------------------------
    /**  Control Panel homepage
    /** -------------------------------------------*/

	function index()
	{
		$this->EE->load->helper('form');
    	$this->EE->load->library('table');
        
        $yesno = array(
                                    'y' => $this->EE->lang->line('yes'),
                                    'n' => $this->EE->lang->line('no')
                                );
 
        $vars['settings'] = array(	
            'helpspot_ee_manager_helpspot_url'	=> form_input('url', $this->settings['url']),
            'helpspot_ee_manager_helpspot_login'	=> form_input('login', $this->settings['login']),
            'helpspot_ee_manager_helpspot_password'	=> form_input('password', $this->settings['password']),
            'helpspot_ee_manager_use_captcha'	=> form_dropdown('use_captcha', $yesno, $this->settings['use_captcha'])
    		);
    	
        $this->EE->cp->set_variable('cp_page_title', lang('helpspot_ee_manager_module_name'));
        $this->EE->cp->set_right_nav(array( 'Documentation' => 'http://www.intoeetive.com/docs/helpspot_ee_manager.html') );
        
    	return $this->EE->load->view('index', $vars, TRUE);
        
	}
	/* END */
	

      

	/** -------------------------------------------
    /**  Save Configuration
    /** -------------------------------------------*/

    function save_settings()
    {

    	$required	= array('url', 'login', 'password', 'use_captcha');
    	$data		= array();
    	
    	foreach($required as $var)
    	{
    		if ( ! isset($_POST[$var]) OR $_POST[$var] == '')
    		{
    			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('helpspot_ee_manager_mising_fields'));
                $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=helpspot_ee_manager'.AMP.'method=index');
                return false;
    		}
    		
    		$data[$var] = $_POST[$var];
    	}

		$this->EE->db->where('module_name', 'Helpspot_ee_manager');
        $this->EE->db->update('modules', array('settings' => serialize($data)));
        
        $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('configuration_updated'));
        
        $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=helpspot_ee_manager'.AMP.'method=index');
        
    }
    /* END */
      
  



}
/* END */
?>