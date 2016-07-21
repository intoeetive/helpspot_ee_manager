<?php

/*
=====================================================
 HelpSpot EE manager - by Yuriy Salimovskiy
 
 uses PHP API implementation by Joe Landsman
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2010 Yuriy Salimovskiy
=====================================================
 This software is based upon and derived from
 ExpressionEngine software protected under
 copyright dated 2004 - 2010. Please see
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



class Helpspot_ee_manager_CP {

    var $version = '0.1';
    
    /** -------------------------------------------
    /**  Constructor
    /** -------------------------------------------*/

	function Helpspot_ee_manager_CP ($switch = TRUE)
	{
		global $IN, $DB, $PREFS;
		
		/** -------------------------------
		/**  Is the module installed?
		/** -------------------------------*/
        
        $query = $DB->query("SELECT COUNT(*) AS count FROM ".$PREFS->ini('db_prefix')."_modules WHERE module_name = 'Helpspot_ee_manager'");
        
        if ($query->row['count'] == 0)
        {
        	return;
        }
		
		/** -------------------------------
		/**  On with the show!
		/** -------------------------------*/

		if ($switch)
        {
            switch($IN->GBL('P'))
            {
                case 'save'		:  $this->save_configuration();
                    break;
                default			:  $this->homepage();
                    break;
            }
        }
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Control Panel homepage
    /** -------------------------------------------*/

	function homepage($msg = '')
	{
		global $DSP, $LANG, $PREFS, $FNS, $DB;
                        
        $DSP->title = $LANG->line('helpspot_ee_manager_module_name');
        $DSP->crumb = $LANG->line('helpspot_ee_manager_module_name');
       
        $DSP->body .= $DSP->qdiv('tableHeading', $LANG->line('helpspot_ee_manager_configurations')."&nbsp;<a href=\"http://www.intoeetive.com/docs/helpspot_ee_manager.html\">Documentation</a>"); 
        
        if ($msg != '')
        {
			$DSP->body .= $DSP->qdiv('successBox', $DSP->qdiv('success', $msg));
        }
                
        $qs = ($PREFS->ini('force_query_string') == 'y') ? '' : '?';		
        
        $query = $DB->query("SELECT * FROM ".$PREFS->ini('db_prefix')."_helpspot_ee_manager");
        
        $DSP->body	.=	$DSP->toggle();
                
        $DSP->body	.=	$DSP->form_open(
        								array(
        										'action' => 'C=modules'.AMP.'M=helpspot_ee_manager'.AMP.'P=save', 
        										'name'	=> 'target',
        										'id'	=> 'target'
        									)
        								);

        $DSP->body	.=	$DSP->table('tableBorder', '0', '0', '100%').
						$DSP->tr().
						$DSP->table_qcell('tableHeadingAlt', 
											array(
													$LANG->line('helpspot_ee_manager_config_name'),
													$LANG->line('helpspot_ee_manager_config_value')
												 )
											).
						$DSP->tr_c();
		
		$i = 0;

		foreach ($query->result as $row)
		{				
			$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
                      
            $DSP->body .= $DSP->tr();
            
            $DSP->body .= $DSP->table_qcell($style, $LANG->line('helpspot_ee_manager_helpspot_url'), '25%');       													   

            $DSP->body .= $DSP->table_qcell($style,
												$DSP->input_text('helpspot_url', $row['helpspot_url'], '20', '400', 'input', '90%'),
											'75%');
											
			$DSP->body .= $DSP->tr_c();
            
            $DSP->body .= $DSP->tr();
            
            $DSP->body .= $DSP->tr();
            
            $DSP->body .= $DSP->table_qcell($style, $LANG->line('helpspot_ee_manager_helpspot_login'), '25%');       													   

            $DSP->body .= $DSP->table_qcell($style,
												$DSP->input_text('helpspot_login', $row['helpspot_login'], '20', '400', 'input', '90%'),
											'75%');
											
			$DSP->body .= $DSP->tr_c();
            
            $DSP->body .= $DSP->tr();
            
            $DSP->body .= $DSP->table_qcell($style, $LANG->line('helpspot_ee_manager_helpspot_password'), '25%');       													   

            $DSP->body .= $DSP->table_qcell($style,
												$DSP->input_text('helpspot_password', $row['helpspot_password'], '20', '400', 'input', '90%'),
											'75%');
											
			$DSP->body .= $DSP->tr_c();
            
            $DSP->body .= $DSP->tr();
            
            $DSP->body .= $DSP->table_qcell($style, $LANG->line('helpspot_ee_manager_use_captcha'), '25%');       													   

            $DSP->body .= $DSP->table_qcell($style,
												$DSP->input_radio('helpspot_use_captcha', "y", ($row['helpspot_use_captcha']=='y')?1:0).$LANG->line('yes')."&nbsp;".$DSP->input_radio('helpspot_use_captcha', "y", ($row['helpspot_use_captcha']=='y')?0:1).$LANG->line('no'),
											'75%');
											
			$DSP->body .= $DSP->tr_c();
		}
		
        $DSP->body	.=	$DSP->table_c(); 
    	
		$DSP->body	.=	$DSP->qdiv('itemWrapperTop', $DSP->input_submit($LANG->line('save')));             
        
        $DSP->body	.=	$DSP->form_close();     
	}
	/* END */
	

      

	/** -------------------------------------------
    /**  Save Configuration
    /** -------------------------------------------*/

    function save_configuration()
    {
    	global $IN, $DSP, $LANG, $DB, $PREFS;
    	
    	$required	= array('helpspot_url', 'helpspot_login', 'helpspot_password');
    	$data		= array();
    	
    	foreach($required as $var)
    	{
    		if ( ! isset($_POST[$var]) OR $_POST[$var] == '')
    		{
    			return $OUT->show_user_error('submission', $LANG->line('helpspot_ee_manager_mising_fields'));
    		}
    		
    		$data[$var] = $_POST[$var];
    	}
        $data["helpspot_use_captcha"] = (isset($_POST["helpspot_use_captcha"]))?$_POST["helpspot_use_captcha"]:'y';
	
		$DB->query($DB->update_string($PREFS->ini('db_prefix').'_helpspot_ee_manager', $data, "1"));
		$message = $LANG->line('configuration_updated');
    	
    	$this->homepage($message);
    }
    /* END */
      
    
	

    /** -------------------------------------------
    /**  Module installer
    /** -------------------------------------------*/

    function helpspot_ee_manager_module_install()
    {
        global $DB, $PREFS;        
        
        $sql[] = "INSERT INTO ".$PREFS->ini('db_prefix')."_modules 
        		  (module_id, module_name, module_version, has_cp_backend) 
        		  VALUES 
        		  ('', 'Helpspot_ee_manager', '$this->version', 'y')";

    	$sql[] = "CREATE TABLE IF NOT EXISTS `".$PREFS->ini('db_prefix')."_helpspot_ee_manager` (
    			 `helpspot_url` varchar(255) NOT NULL default '',
                 `helpspot_login` varchar(80) NOT NULL default '',
    			 `helpspot_password` varchar(80) NOT NULL default '',
                 `helpspot_use_captcha` char(1) NOT NULL default 'y'
                 );";
        $sql[] = "INSERT INTO `".$PREFS->ini('db_prefix')."_actions` (action_id, class, method) VALUES ('', 'Helpspot_ee_manager', 'private_request_update')";
        $sql[] = "INSERT INTO `".$PREFS->ini('db_prefix')."_actions` (action_id, class, method) VALUES ('', 'Helpspot_ee_manager', 'public_request_update')";
        $sql[] = "INSERT INTO `".$PREFS->ini('db_prefix')."_actions` (action_id, class, method) VALUES ('', 'Helpspot_ee_manager', 'public_request_create')";
 		$sql[] = "INSERT INTO `".$PREFS->ini('db_prefix')."_helpspot_ee_manager` (helpspot_url, helpspot_login, helpspot_password) VALUES ('', '', '')";

        foreach ($sql as $query)
        {
            $DB->query($query);
        }
        
        return true;
    }
    /* END */
    
    
    /** -------------------------------------------
    /**  Module de-installer
    /** -------------------------------------------*/

    function helpspot_ee_manager_module_deinstall()
    {
        global $DB, $PREFS;    

        $query = $DB->query("SELECT module_id FROM ".$PREFS->ini('db_prefix')."_modules WHERE module_name = 'Helpspot_ee_manager'"); 
                
        $sql[] = "DELETE FROM ".$PREFS->ini('db_prefix')."_module_member_groups WHERE module_id = '".$query->row['module_id']."'";        
        $sql[] = "DELETE FROM ".$PREFS->ini('db_prefix')."_modules WHERE module_name = 'Helpspot_ee_manager'";
        $sql[] = "DELETE FROM ".$PREFS->ini('db_prefix')."_actions WHERE class = 'Helpspot_ee_manager'";
        $sql[] = "DROP TABLE IF EXISTS ".$PREFS->ini('db_prefix')."_helpspot_ee_manager";

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