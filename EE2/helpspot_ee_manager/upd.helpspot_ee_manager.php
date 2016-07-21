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



class Helpspot_ee_manager_upd {

    var $version = '0.2';
    
    /** -------------------------------------------
    /**  Constructor
    /** -------------------------------------------*/
    
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance(); 
    } 
      
    
	

    /** -------------------------------------------
    /**  Module installer
    /** -------------------------------------------*/

    function install()
    {
        $this->EE->load->dbforge(); 
        
        //----------------------------------------
		// EXP_MODULES
		// The settings column, Ellislab should have put this one in long ago.
		// No need for a seperate preferences table for each module.
		//----------------------------------------
		if ($this->EE->db->field_exists('settings', 'modules') == FALSE)
		{
			$this->EE->dbforge->add_column('modules', array('settings' => array('type' => 'TEXT') ) );
		}
        
        $settings = array();
        $settings['url'] = '';
        $settings['login'] = '';
        $settings['password'] = '';
        $settings['use_captcha'] = '';
        $data = array( 'module_name' => 'Helpspot_ee_manager' , 'module_version' => $this->version, 'has_cp_backend' => 'y', 'settings'=> serialize($settings) ); 
        $this->EE->db->insert('modules', $data); 
        
        $data = array( 'class' => 'Helpspot_ee_manager' , 'method' => 'private_request_update' ); 
        $this->EE->db->insert('actions', $data); 
        
        $data = array( 'class' => 'Helpspot_ee_manager' , 'method' => 'public_request_update' ); 
        $this->EE->db->insert('actions', $data); 
        
        $data = array( 'class' => 'Helpspot_ee_manager' , 'method' => 'public_request_create' ); 
        $this->EE->db->insert('actions', $data); 

        return true;
    }
    /* END */
    
    
    /** -------------------------------------------
    /**  Module de-installer
    /** -------------------------------------------*/

    function uninstall()
    {
        $DB = $this->EE->db;
        $PREFS = $this->EE->config;       

        $query = $DB->query("SELECT module_id FROM ".$PREFS->item('db_prefix')."_modules WHERE module_name = 'Helpspot_ee_manager'"); 
                
        $sql[] = "DELETE FROM ".$PREFS->item('db_prefix')."_module_member_groups WHERE module_id = '".$query->row('module_id')."'";        
        $sql[] = "DELETE FROM ".$PREFS->item('db_prefix')."_modules WHERE module_name = 'Helpspot_ee_manager'";
        $sql[] = "DELETE FROM ".$PREFS->item('db_prefix')."_actions WHERE class = 'Helpspot_ee_manager'";
        $sql[] = "DROP TABLE IF EXISTS ".$PREFS->item('db_prefix')."_helpspot_ee_manager";

        foreach ($sql as $query)
        {
            $DB->query($query);
        }

        return true;
    }
    /* END */



}
/* END */
?>