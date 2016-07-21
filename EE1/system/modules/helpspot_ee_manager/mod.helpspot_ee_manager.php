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
 File: mod.helpspot_ee_manager.php
-----------------------------------------------------
 Purpose: HelpSpot API utility for ExpressionEngine
=====================================================
*/


if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}


class Helpspot_ee_manager {

    var $return_data	= ''; 						// Bah!
    var $secret         = 'sfh6p-3Rs3r';            // Used to generate secure email address hashes

    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function Helpspot_ee_manager()
    {        
    	global $LANG, $DB, $PREFS;
    	
    	$LANG->fetch_language_file('helpspot_ee_manager');
    	
    	/** ----------------------------------------
    	/**  Configuration Options
    	/** ----------------------------------------*/
    	
    	$query = $DB->query("SELECT * FROM ".$PREFS->ini('db_prefix')."_helpspot_ee_manager WHERE 1 LIMIT 1");
    		
    	if ($query->num_rows > 0)
    	{
    		foreach($query->row as $name => $pref)
    		{
    			$name = str_replace('helpspot_', '', $name);
    			
   				$this->{$name} = $pref;
    		}
    	}
    }
    /* END */
    

	/** -----------------------------------------
    /**  USAGE: Get requests by passing data
    /** -----------------------------------------*/
    
    function private_request_search()
    {
        global $TMPL, $SESS, $FNS, $DB;
        if ( ! class_exists('HelpSpotAPI'))
		{
			require PATH_MOD.'helpspot_ee_manager/HelpSpotAPI'.EXT;
		}
   	    $api = new HelpSpotAPI(array(
                                    "helpSpotApiURL" => trim($this->url, "/")."/api/index.php",
                                    'username' => $this->login,
								    'password' => $this->password,
                                    'cacheDir' => PATH_MOD.'helpspot_ee_manager/cache'
                                )); 
        if ($TMPL->fetch_param('email')!='')
        {
            $params['sEmail'] = ($TMPL->fetch_param('email')!='{logged_in_email}')?$TMPL->fetch_param('email'):$SESS->userdata['email'];
        }

        if ($TMPL->fetch_param('member_id')!='')
        {
            $q = $DB->query("SELECT email FROM exp_members WHERE member_id=".intval($TMPL->fetch_param('member_id')));
            $params['sEmail'] = $q->row['email'];
        }

        if (empty($params))
        {
            return $TMPL->no_results();
        }
        $params['cacheRequest'] = ($TMPL->fetch_param('cache')=='on')?true:false;	

        $result = $api->privateRequestSearch($params);	
        $responces = $result["requests"]["request"];
        $out = '';
        $count = 0;
        $open = 0;
        $total = count($responces);
        if ($total==0 || $responces=='')
        {
            $cond['no_results'] = TRUE;
            $out = $FNS->prep_conditionals($TMPL->tagdata, $cond);
            return $TMPL->no_results();
        }
        //var_dump($result);
        $raw_tagdata = $TMPL->swap_var_single('request_total', $total, $TMPL->tagdata);
        //$raw_tagdata = $TMPL->swap_var_single('request_email', $responces["sEmail"], $raw_tagdata);
        //$raw_tagdata = $TMPL->swap_var_single('request_email_hash', md5($responces["sEmail"].$this->secret), $raw_tagdata);
        if ($TMPL->fetch_param('getpassword')=='true')
        {
            $raw_tagdata = $TMPL->swap_var_single('customer_password', $this->private_customer_password_get($params['sEmail']), $raw_tagdata);
        }
        foreach ($responces as $responce)
        {
            $tagdata = $raw_tagdata;
            $count++;
            $tagdata = $TMPL->swap_var_single('request_count', $count, $tagdata);
            $tagdata = $TMPL->swap_var_single('request_id', $responce["xRequest"], $tagdata);
            $tagdata = $TMPL->swap_var_single('request_password', $responce["sRequestPassword"], $tagdata);            
            $tagdata = $TMPL->swap_var_single('request_status', $responce["xStatus"], $tagdata);
            $tagdata = $TMPL->swap_var_single('request_category', $responce["xCategory"], $tagdata);
            $tagdata = $TMPL->swap_var_single('request_dateopen', $responce["dtGMTOpened"], $tagdata);
            $tagdata = $TMPL->swap_var_single('request_dateclose', $responce["dtGMTClosed"], $tagdata);
            $tagdata = $TMPL->swap_var_single('request_lastreplyby', ($responce["iLastReplyBy"]!='0')?$responce["iLastReplyBy"]:'', $tagdata);
            $tagdata = $TMPL->swap_var_single('request_text', $responce["tNote"], $tagdata);
            
            if ($responce["fOpen"]==1)
            {
                $cond['request_open'] = TRUE;
                $open++;
            }
            else
            {
                $cond['request_open'] = FALSE;
            }
            $cond['request_urgent'] = ($responce["fUrgent"]==1)?TRUE:FALSE;
            $tagdata = $FNS->prep_conditionals($tagdata, $cond);
            
            if ($count==$total)
            {
                
                $tagdata = $TMPL->swap_var_single('request_numberopen', $open, $tagdata);
            }
            
            $out .= $tagdata;
        }
        $cond = array();
        $cond['no_results'] = FALSE;
        $out = $FNS->prep_conditionals($out, $cond);
        

        return $out;
    }
    /* END */
    
    
	/** -----------------------------------------
    /**  USAGE: Get requests by passing data
    /** -----------------------------------------*/
    
    function public_request_search()
    {
        global $TMPL, $SESS, $FNS, $DB, $LOC;
        if ( ! class_exists('HelpSpotAPI'))
		{
			require PATH_MOD.'helpspot_ee_manager/HelpSpotAPI'.EXT;
		}
   	    $api = new HelpSpotAPI(array(
                                    "helpSpotApiURL" => trim($this->url, "/")."/api/index.php",
                                    'username' => $this->login,
								    'password' => $this->password,
                                    'cacheDir' => PATH_MOD.'helpspot_ee_manager/cache'
                                )); 
        if ($TMPL->fetch_param('email')!='')
        {
            $params['sEmail'] = ($TMPL->fetch_param('email')!='{logged_in_email}')?$TMPL->fetch_param('email'):$SESS->userdata['email'];
        }

        if ($TMPL->fetch_param('member_id')!='')
        {
            $q = $DB->query("SELECT email FROM exp_members WHERE member_id=".intval($TMPL->fetch_param('member_id')));
            $params['sEmail'] = $q->row['email'];
        }

        if (empty($params))
        {
            return $TMPL->no_results();
        }
        $params['cacheRequest'] = ($TMPL->fetch_param('cache')=='on')?true:false;
        
        //get password for email provided
        $password = $api->privateCustomerGetPasswordByEmail(array('sEmail'=>$params['sEmail']));

        //get requests list using customer email and password
        $result = $api->customerGetRequests(array('sEmail'=>$params['sEmail'], 'sPassword'=>$password["results"]["sPassword"]));
        if (empty($result["requests"]))
        {
            $cond['no_results'] = TRUE;
            $out = $FNS->prep_conditionals($TMPL->tagdata, $cond);
            return $TMPL->no_results();
        }
        $responces = $result["requests"]["request"];
        $out = '';
        $count = 0;
        $open = 0;
        $total = count($responces);
        
        $raw_tagdata = $TMPL->swap_var_single('request_total', $total, $TMPL->tagdata);
        $raw_tagdata = $TMPL->swap_var_single('request_email', $params["sEmail"], $raw_tagdata);
        
        /*$categories_req = $api->requestGetCategories();
        $categories = array();
        foreach ($categories_req["categories"]["category"] as $cat)
        {
            $categories[$cat["xCategory"]] = $cat["sCategory"];
        }*/

        foreach ($responces as $responce)
        {
            $tagdata = $raw_tagdata;
            $count++;
            $tagdata = $TMPL->swap_var_single('request_count', $count, $tagdata);
            $tagdata = $TMPL->swap_var_single('request_id', $responce["xRequest"], $tagdata);
            $tagdata = $TMPL->swap_var_single('request_password', $responce["sRequestPassword"], $tagdata);   
            $tagdata = $TMPL->swap_var_single('request_status', $responce["sStatus"], $tagdata);
            
            $tagdata = $TMPL->swap_var_single('request_category', $responce["sCategory"], $tagdata);      
            $tagdata = $TMPL->swap_var_single('request_category_id', $responce["xCategory"], $tagdata);         
            /*$tagdata = $TMPL->swap_var_single('request_status', ($responce["xStatus"]==1)?'Open':'Closed', $tagdata);
            
            $tagdata = $TMPL->swap_var_single('request_category', $categories[$responce["xCategory"]], $tagdata);*/

    		if (preg_match_all("/".LD."request_dateopen\s+format=[\"'](.*?)[\"']".RD."/is", $tagdata, $matches))
    		{
    			for ($j = 0; $j < count($matches['0']); $j++)
    			{
                    $tagdata = str_replace($matches['0'][$j], $LOC->decode_date($matches['1'][$j], $responce["dtGMTOpened"]), $tagdata);
    			}
    		}

            $tagdata = $TMPL->swap_var_single('request_text', stripslashes($responce["tNote"]), $tagdata);
            $tagdata = $TMPL->swap_var_single('request_author', $responce["fullname"], $tagdata);
            $tagdata = $TMPL->swap_var_single('accesskey', $responce["accesskey"], $tagdata);
            
            $cond['request_open'] = ($responce["fOpen"]==1)?TRUE:FALSE;
            $cond['request_urgent'] = ($responce["fUrgent"]==1)?TRUE:FALSE;
            $tagdata = $FNS->prep_conditionals($tagdata, $cond);
            
            $out .= $tagdata;
        }
        $cond = array();
        $cond['no_results'] = FALSE;
        $out = $FNS->prep_conditionals($out, $cond);
        

        return $out;
    }
    /* END */    
    
    
    /** -----------------------------------------
    /**  USAGE: Get password by email
    /** -----------------------------------------*/
    
    function private_customer_password_get($email='')
    {
        global $TMPL, $SESS, $FNS, $DB;
        if ( ! class_exists('HelpSpotAPI'))
		{
			require PATH_MOD.'helpspot_ee_manager/HelpSpotAPI'.EXT;
		}
   	    $api = new HelpSpotAPI(array(
                                    "helpSpotApiURL" => trim($this->url, "/")."/api/index.php",
                                    'username' => $this->login,
								    'password' => $this->password,
                                    'cacheDir' => PATH_MOD.'helpspot_ee_manager/cache'
                                )); 

        $params['sEmail'] = ($email!='')?$email:$TMPL->fetch_param('email');

        if (empty($params))
        {
            return false;
        }
        
        $password = $api->privateCustomerGetPasswordByEmail($params);	
                
        return $password;
    }
    /* END */

	/** -----------------------------------------
    /**  USAGE: Get request data
    /** -----------------------------------------*/
    
    function private_request_get()
    {
        global $TMPL, $SESS, $FNS, $DB;
        if ( ! class_exists('HelpSpotAPI'))
		{
			require PATH_MOD.'helpspot_ee_manager/HelpSpotAPI'.EXT;
		}
   	    $api = new HelpSpotAPI(array(
                                    "helpSpotApiURL" => trim($this->url, "/")."/api/index.php",
                                    'username' => $this->login,
								    'password' => $this->password,
                                    'cacheDir' => PATH_MOD.'helpspot_ee_manager/cache'
                                )); 
        if ($TMPL->fetch_param('id')!='')
        {
            $params['xRequest'] = $TMPL->fetch_param('id');
        }

        if (empty($params))
        {
            return $TMPL->no_results();
        }
        $params['cacheRequest'] = ($TMPL->fetch_param('cache')=='on')?true:false;	

        $result = $api->privateRequestGet($params);	

        $responce = $result["request"];
        $out = '';
        $count = 0;

        $total = count($responce["request_history"]["item"]);

        if ($total==0)
        {
            $cond['no_results'] = TRUE;
            $out = $FNS->prep_conditionals($TMPL->tagdata, $cond);
            return $out;
        }
        
        $tagdata = $TMPL->tagdata;
        $items = $responce["request_history"]["item"];
        if ($TMPL->fetch_param('sort')=='asc')
        {
            krsort($items);
        }

        preg_match_all("/".LD."items".RD."(.*?)".LD.SLASH."items".RD."/s", $tagdata, $rows);
        
        foreach($rows[0] as $row_key => $row_tag)
        {
        	$row_chunk = $rows[0][$row_key];
        
        	$row_chunk_content = $rows[1][$row_key];
        
        	$row_inner = '';
        
        	// loop over the row_data
        	foreach ($items as $item)
        	{
        		if ($item["fPublic"]==1) 
                {
                    $row_template = $row_chunk_content;
    
            		$row_template = $TMPL->swap_var_single('request_item_text', stripslashes($item["tNote"]), $row_template);
                    $row_template = $TMPL->swap_var_single('request_item_date', $item["dtGMTChange"], $row_template);
                    $row_template = $TMPL->swap_var_single('request_item_author', $item["xPerson"], $row_template);
                    $row_template = $TMPL->swap_var_single('request_item_files', $item["files"], $row_template);
                    
            		
            		$row_inner .= $row_template;
                }
        	}

        	$tagdata = str_replace($row_chunk, $row_inner, $tagdata);
        }
        $out = $tagdata;        
        
        $out = $TMPL->swap_var_single('request_id', $responce["xRequest"], $out);
        $out = $TMPL->swap_var_single('request_status', $responce["xStatus"], $out);
        $out = $TMPL->swap_var_single('request_category', $responce["xCategory"], $out);
        $out = $TMPL->swap_var_single('request_dateopen', $responce["dtGMTOpened"], $out);
        $out = $TMPL->swap_var_single('request_dateclose', $responce["dtGMTClosed"], $out);
        $out = $TMPL->swap_var_single('request_lastreplyby', ($responce["iLastReplyBy"]!='0')?$out["iLastReplyBy"]:'', $out);
        $out = $TMPL->swap_var_single('request_items_total', $total, $out);
    
        $cond = array();
        $cond['no_results'] = FALSE;
        $cond['request_open'] = ($responce["fOpen"]==1)?TRUE:FALSE;
        $cond['request_urgent'] = ($responce["fUrgent"]==1)?TRUE:FALSE;
        $out = $FNS->prep_conditionals($out, $cond);
        

        return $out;
    }
    /* END */


	/** -----------------------------------------
    /**  USAGE: Get request data
    /** -----------------------------------------*/
    
    function public_request_get_by_id()
    {
        global $TMPL, $SESS, $FNS, $DB;
        if ( ! class_exists('HelpSpotAPI'))
		{
			require PATH_MOD.'helpspot_ee_manager/HelpSpotAPI'.EXT;
		}
   	    $api = new HelpSpotAPI(array(
                                    "helpSpotApiURL" => trim($this->url, "/")."/api/index.php",
                                    'username' => $this->login,
								    'password' => $this->password,
                                    'cacheDir' => PATH_MOD.'helpspot_ee_manager/cache'
                                )); 
        if ($TMPL->fetch_param('id')!='')
        {
            $params['xRequest'] = $TMPL->fetch_param('id');
        }

        if (empty($params))
        {
            return $TMPL->no_results();
        }
        $params['cacheRequest'] = ($TMPL->fetch_param('cache')=='on')?true:false;	
        
        //get the request
        $result = $api->privateRequestGet($params);	
        
        $responce = $result["request"];
        
        //if email hash is set, compare it
        if ($TMPL->fetch_param('check_email_hash')!='')
        {
            if ($TMPL->fetch_param('email_hash')!=md5($responce["sEmail"].$this->secret)) 
            {
                return $LANG->line('helpspot_ee_manager_noaccess');
            }
        }
        
        //get password for email provided
        $password = $api->privateCustomerGetPasswordByEmail(array('sEmail'=>$responce["sEmail"]));

        //get requests list using customer email and password
        $requests = $api->customerGetRequests(array('sEmail'=>$responce["sEmail"], 'sPassword'=>$password["results"]["sPassword"]));
        
        $all_requests = $requests["requests"]["request"];
        
        $this_request = false;
        foreach ($all_requests as $request)
        {
            if ($request['xRequest']==$params['xRequest'])
            {
                $this_request = $request;
                break;
            }
        }

        if ($this_request==false)
        {
            $cond['no_results'] = TRUE;
            $out = $FNS->prep_conditionals($TMPL->tagdata, $cond);
            return $out;
        }
        
        //finally! got accesskey
        $accesskey = $this_request['accesskey'];
        
        $out = $this->public_request_get_by_key($accesskey);
        

        return $out;
    }
    /* END */


	/** -----------------------------------------
    /**  USAGE: Get request data
    /** -----------------------------------------*/
    
    function public_request_get_by_key($accesskey='')
    {
        global $TMPL, $SESS, $FNS, $DB, $LANG, $LOC, $PREFS, $OUT;
        if ( ! class_exists('HelpSpotAPI'))
		{
			require PATH_MOD.'helpspot_ee_manager/HelpSpotAPI'.EXT;
		}
   	    $api = new HelpSpotAPI(array(
                                    "helpSpotApiURL" => trim($this->url, "/")."/api/index.php",
                                    'username' => $this->login,
								    'password' => $this->password,
                                    'cacheDir' => PATH_MOD.'helpspot_ee_manager/cache'
                                )); 
        if ($accesskey=='')
        {
            $params['accesskey'] = $TMPL->fetch_param('accesskey');
        }

        if ($params['accesskey']=='')
        {
            $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_error'),
							'heading'	=> $LANG->line('helpspot_ee_manager_error'),
							'content'	=> $LANG->line('helpspot_ee_manager_norequests'),							
							'link'		=> array($FNS->fetch_site_index(), $PREFS->ini('site_name'))
						 );
					
			$OUT->show_message($data);
            return;
        }
        
        $responce_data = $api->requestGet(array('accesskey'=>$params['accesskey']));	

        if ($responce_data==false)
        {
            $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_error'),
							'heading'	=> $LANG->line('helpspot_ee_manager_error'),
							'content'	=> $LANG->line('helpspot_ee_manager_norequests'),							
							'link'		=> array($FNS->fetch_site_index(), $PREFS->ini('site_name'))
						 );
					
			$OUT->show_message($data);
            return;
        }
        
        $responce = $responce_data["request"];

        $out = '';
        $count = 0;

        $total = count($responce["request_history"]["item"]);
                        
        $tagdata = $TMPL->tagdata;
        $cond = array();
        if (@is_array($responce["request_history"]["item"][0]))
        {
            $items = $responce["request_history"]["item"];
            $cond['no_replies'] = FALSE;
        }
        else
        {
            $items = $responce["request_history"];
            $cond['no_replies'] = TRUE;
        }
        //var_dump($items);
        if ($TMPL->fetch_param('sort')=='desc')
        {
            krsort($items);
        }

        preg_match_all("/".LD."items".RD."(.*?)".LD.SLASH."items".RD."/s", $tagdata, $rows);
        
        foreach($rows[0] as $row_key => $row_tag)
        {
        	$row_chunk = $rows[0][$row_key];
        
        	$row_chunk_content = $rows[1][$row_key];
        
        	$row_inner = '';
        
        	// loop over the row_data
        	foreach ($items as $item)
        	{

                //var_dump($item);
                $row_template = $row_chunk_content;

        		$row_template = $TMPL->swap_var_single('request_item_text', stripslashes($item["tNote"]), $row_template);
                if (preg_match_all("/".LD."request_item_date\s+format=[\"'](.*?)[\"']".RD."/is", $row_template, $matches))
        		{
        			for ($j = 0; $j < count($matches['0']); $j++)
        			{
                        $row_template = str_replace($matches['0'][$j], $LOC->decode_date($matches['1'][$j], $item["dtGMTChange"]), $row_template);
        			}
        		}

                $row_template = $TMPL->swap_var_single('request_item_author', ($item["xPerson"]==0)?$LANG->line('you'):$LANG->line('support'), $row_template);
                
                preg_match_all("/".LD."files".RD."(.*?)".LD.SLASH."files".RD."/s", $row_template, $file_rows);

                foreach($file_rows[0] as $file_row_key => $file_row_tag)
                {
                	$file_row_chunk = $file_rows[0][$file_row_key];
                
                	$file_row_chunk_content = $file_rows[1][$file_row_key];
                
                	$file_row_inner = '';
                
                	// loop over the row_data
                    if (@is_array($item["files"]["file"]))
                    {
                        $files = $item["files"]["file"];
                	//foreach ($files as $file)
                	//{
        
                        //var_dump($item);
                        $file_row_template = $file_row_chunk_content;
                        
                        $file_row_template = $TMPL->swap_var_single('file_name', $files["sFilename"], $file_row_template);
                        $file_row_template = $TMPL->swap_var_single('file_url', $files["url"], $file_row_template);
                        $file_row_inner .= $file_row_template;
                    //}
                    }
                    
                }
                    
                $row_template = str_replace($file_row_chunk, $file_row_inner, $row_template);

                
                $row_cond = array();
                $row_cond['initial'] = ($item["fInitial"]==1)?TRUE:FALSE;
                $row_template = $FNS->prep_conditionals($row_template, $row_cond);
        		
        		$row_inner .= $row_template;
        	}

        	$tagdata = str_replace($row_chunk, $row_inner, $tagdata);
        }
        
        $custom_fields_request = $api->privateRequestGetCustomFields(array('xCategory'=>$responce["xCategory"]));	

        $custom_fields_responce = $custom_fields_request["customfields"]["field"];
        
        function _sort_fields($a, $b)
        {
            
            if ($a['iOrder']==$b['iOrder']) return 0;
            if ($a['iOrder']>$b['iOrder']) return 1;
            if ($a['iOrder']<$b['iOrder']) return -1;
        }
        if (!empty($custom_fields_responce))
        {
            usort($custom_fields_responce, "_sort_fields");
        }
        else
        {
            $custom_fields_responce = array();
        }
        
        preg_match_all("/".LD."custom_fields".RD."(.*?)".LD.SLASH."custom_fields".RD."/s", $tagdata, $rows);
        
        foreach($rows[0] as $row_key => $row_tag)
        {
        	$row_chunk = $rows[0][$row_key];
        
        	$row_chunk_content = $rows[1][$row_key];
        
        	$row_inner = '';
        
        	// loop over the row_data
        	foreach ($custom_fields_responce as $item)
        	{

                if ($item["isPublic"]==1)
                {
                    $row_template = $row_chunk_content;
     
            		$row_template = $TMPL->swap_var_single('field_label', stripslashes($item["fieldName"]), $row_template);
                    $fieldid = "Custom".$item["xCustomField"];
                                		
                    if (preg_match_all("/".LD."field_value\s+format=[\"'](.*?)[\"']".RD."/is", $row_template, $rmatches))
            		{
            			for ($j = 0; $j < count($rmatches['0']); $j++)
            			{
                            $row_template = str_replace($rmatches['0'][$j], $LOC->decode_date($rmatches['1'][$j], $responce[$fieldid]), $row_template);
            			}
            		}
                    
                    $row_template = $TMPL->swap_var_single('field_value', $responce[$fieldid], $row_template);
                    $row_template = $TMPL->swap_var_single('field_type', $item["fieldType"], $row_template);
                    
            		$row_inner .= $row_template;
                }
        	}

        	$tagdata = str_replace($row_chunk, $row_inner, $tagdata);
        }
        
        $out = $tagdata;        
        //var_dump($responce);
        $out = $TMPL->swap_var_single('request_id', $responce["xRequest"], $out);
        $out = $TMPL->swap_var_single('accesskey', $accesskey, $out);
        $out = $TMPL->swap_var_single('request_status', $responce["sStatus"], $out);
        $out = $TMPL->swap_var_single('request_category', $responce["sCategory"], $out);
        $out = $TMPL->swap_var_single('request_dateopen', $responce["dtGMTOpened"], $out);
        $out = $TMPL->swap_var_single('request_dateclose', $responce["dtGMTClosed"], $out);
        $out = $TMPL->swap_var_single('request_items_total', $total, $out);
    
        
        $cond['no_results'] = FALSE;
        $cond['request_open'] = ($responce["fOpen"]==1)?TRUE:FALSE;
        $cond['request_urgent'] = ($responce["fUrgent"]==1)?TRUE:FALSE;
        $out = $FNS->prep_conditionals($out, $cond);
        

        return $out;
    }
    /* END */


	/** -----------------------------------------
    /**  USAGE: Get custom fields
    /** -----------------------------------------*/
    
    function custom_fields_form()
    {
        global $TMPL, $SESS, $FNS, $DB, $LANG, $LOC;
        if ( ! class_exists('HelpSpotAPI'))
		{
			require PATH_MOD.'helpspot_ee_manager/HelpSpotAPI'.EXT;
		}
   	    $api = new HelpSpotAPI(array(
                                    "helpSpotApiURL" => trim($this->url, "/")."/api/index.php",
                                    'username' => $this->login,
								    'password' => $this->password,
                                    'cacheDir' => PATH_MOD.'helpspot_ee_manager/cache'
                                )); 
        if ($TMPL->fetch_param('category_id')!='')
        {
            $params['xCategory'] = $TMPL->fetch_param('category_id');
        }
        
        $responce_data = $api->privateRequestGetCustomFields($params);	

        $responce = $responce_data["customfields"]["field"];
        if ($responce==FALSE)
        {
            return $TMPL->no_results;
        }
        //var_dump($responce);                
        $tagdata = $TMPL->tagdata;
        
        function _sort_fields($a, $b)
        {
            
            if ($a['iOrder']==$b['iOrder']) return 0;
            if ($a['iOrder']>$b['iOrder']) return 1;
            if ($a['iOrder']<$b['iOrder']) return -1;
        }
        
        usort($responce, "_sort_fields");
        
        if ($TMPL->fetch_param('sort')=='asc')
        {
            krsort($responce);
        }

        preg_match_all("/".LD."fields".RD."(.*?)".LD.SLASH."fields".RD."/s", $tagdata, $rows);
        
        foreach($rows[0] as $row_key => $row_tag)
        {
        	$row_chunk = $rows[0][$row_key];
        
        	$row_chunk_content = $rows[1][$row_key];
        
        	$row_inner = '';
        
        	// loop over the row_data
        	foreach ($responce as $item)
        	{

                if ($item["isPublic"]==1)
                {
                    $row_template = $row_chunk_content;
    
            		$row_template = $TMPL->swap_var_single('field_type', $item["fieldType"], $row_template);
                    $row_template = $TMPL->swap_var_single('field_label', $item["fieldName"], $row_template);
                    $row_template = $TMPL->swap_var_single('field_id', $item["xCustomField"], $row_template);

          // <iOrder>0</iOrder>
                    switch ($item["fieldType"])
                    {
                        
                        case 'select':
                            $field_input = "<select name=\"Custom".$item["xCustomField"]."\">";
                            foreach ($item["listItems"]["item"] as $option)
                            {
                                $field_input .= "<option value=\"$option\">$option</option>";
                            }
                            $field_input .= "</select>";
                            break;
                        case 'checkbox':
                            $field_input = "<input type=\"checkbox\" name=\"Custom".$item["xCustomField"]."\" value=\"1\" />";
                            break;
                        case 'lrgtext':
                            $field_input = ($item["lrgTextRows"]!='')?"<textarea name=\"Custom".$item["xCustomField"]."\" rows=\"".$item["lrgTextRows"]."\"></textarea>":"<textarea name=\"Custom".$item["xCustomField"]."\"></textarea>";
                            break;
                        case 'date':
                            $field_input = "<input type=\"text\" class=\"datepicker\" name=\"Custom".$item["xCustomField"]."\" value=\"\" />";
                            break;
                        case 'text':
                        default:
                            $field_input = ($item["sTxtSize"]!='')?"<input type=\"text\" name=\"Custom".$item["xCustomField"]."\" value=\"\" maxlength=\"".$item["sTxtSize"]."\" />":"<input type=\"text\" name=\"Custom".$item["xCustomField"]."\" value=\"\" />";
                            break;
                    }
                    $row_template = $TMPL->swap_var_single('field_input', $field_input, $row_template);
                    
                    $cond = array();
                    $cond['field_required'] = ($item["isRequired"]==1)?TRUE:FALSE;
                    $row_template = $FNS->prep_conditionals($row_template, $cond);
            		
            		$row_inner .= $row_template;
                }
        	}

        	$tagdata = str_replace($row_chunk, $row_inner, $tagdata);
        }
        $out = $tagdata;                

        return $out;
    }
    /* END */


   /** -----------------------------------------
    /**  USAGE: Get list of customer requests
    /** -----------------------------------------*/
    
    function public_customer_requests_get($email='', $password='', $raw = true)
    {
        global $TMPL, $SESS, $FNS, $DB;

        if ( ! class_exists('HelpSpotAPI'))
		{
			require PATH_MOD.'helpspot_ee_manager/HelpSpotAPI'.EXT;
		}
   	    $api = new HelpSpotAPI(array(
                                    "helpSpotApiURL" => trim($this->url, "/")."/api/index.php",
                                    'cacheDir' => PATH_MOD.'helpspot_ee_manager/cache'
                                )); 

        $params['sEmail'] = ($email!='')?$email:$TMPL->fetch_param('email');
        $params['sPassword'] = ($password!='')?$password:$TMPL->fetch_param('password');

        if (empty($params['sEmail'])||empty($params['sPassword']))
        {
            return false;
        }
        
        $result = $api->customerGetRequests($params);	
        
        if ($raw == true)     
        {
            return $result;
        }   
        return $result;
    }
    /* END */

	/** -----------------------------------------
    /**  USAGE: Update request data
    /** -----------------------------------------*/
    
    function private_request_update()
    {
        global $TMPL, $SESS, $FNS, $OUT, $IN;
        
        if ($IN->GBL('note', 'POST')=='')
        {
            $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_error'),
							'heading'	=> $LANG->line('helpspot_ee_manager_error'),
							'content'	=> $LANG->line('helpspot_ee_manager_missing_note'),
							'redirect'	=> $_POST['RET'],							
							'link'		=> array($_POST['RET'], $LANG->line('helpspot_ee_manager_return'))
						 );
					
			$OUT->show_message($data);
            return;
        }
        
        if ($IN->GBL('email', 'POST')=='' || $this->_check_email_address($IN->GBL('email', 'POST'))===false)
        {
            $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_error'),
							'heading'	=> $LANG->line('helpspot_ee_manager_error'),
							'content'	=> $LANG->line('helpspot_ee_manager_missing_email'),
							'redirect'	=> $_POST['RET'],							
							'link'		=> array($_POST['RET'], $LANG->line('helpspot_ee_manager_return'))
						 );
					
			$OUT->show_message($data);
            return;
        }
        
        if ($IN->GBL('request_id', 'POST')=='')
        {
            $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_error'),
							'heading'	=> $LANG->line('helpspot_ee_manager_error'),
							'content'	=> $LANG->line('helpspot_ee_manager_missing_request_id'),
							'redirect'	=> $_POST['RET'],							
							'link'		=> array($_POST['RET'], $LANG->line('helpspot_ee_manager_return'))
						 );
					
			$OUT->show_message($data);
            return;
        }
        
        if ( ! class_exists('HelpSpotAPI'))
		{
			require PATH_MOD.'helpspot_ee_manager/HelpSpotAPI'.EXT;
		}
   	    $api = new HelpSpotAPI(array(
                                    "helpSpotApiURL" => trim($this->url, "/")."/api/index.php",
                                    'username' => $this->login,
								    'password' => $this->password,
                                    'cacheDir' => PATH_MOD.'helpspot_ee_manager/cache'
                                )); 
        
        $params['xRequest'] = $IN->GBL('request_id', 'POST');	
        $params['tNote'] = $IN->GBL('note', 'POST');	
        $params['sEmail'] = $IN->GBL('email', 'POST');	
        $params['fNoteType'] = 1;	
        $params['fOpen'] = 1;
        $firstspace = (strpos($IN->GBL('screen_name', 'POST'), " ")!==false)?strpos($IN->GBL('screen_name', 'POST'), " "):strlen($IN->GBL('screen_name', 'POST'));
        $params['sFirstName'] = trim(substr($IN->GBL('screen_name', 'POST'), 0, $firstspace));
        $params['sLastName'] = trim(substr($IN->GBL('screen_name', 'POST'), $firstspace));
        if ($params['sLastName']=='') 
        {
            $params['sLastName']=$params['sFirstName'];
            $params['sFirstName']='Support Person';
        }

        $result = $api->privateRequestUpdate($params);	

        $FNS->redirect($IN->GBL('RET', 'POST'));
    }
    /* END */

	/** -----------------------------------------
    /**  USAGE: Update Request form 
    /** -----------------------------------------*/
    
    function private_request_update_form()
    {
        global $TMPL, $SESS, $FNS;
        
        $hidden_fields = array(
                                'ACT'      	=> $FNS->fetch_action_id('Helpspot_ee_manager', 'private_request_update'),
                                'RET'      	=> $FNS->fetch_current_uri(),
                                'request_id'=> $TMPL->fetch_param('request_id')
                              );
        
        $data = array(
						'hidden_fields'	=> $hidden_fields,
						'action'		=> '/?ACT='.$FNS->fetch_action_id('Helpspot_ee_manager', 'private_request_update'),
                        'method'		=> 'POST',
						'id'			=> 'helpspot_request_update_form'
					);
        $tagdata = $TMPL->tagdata;
        foreach ($TMPL->var_single as $key => $val)
        {     
            if (isset($SESS->userdata["$key"]))
            {
                $tagdata = $TMPL->swap_var_single("$key", $SESS->userdata["$key"], $tagdata);
            }
        }

        $out  = $FNS->form_declaration($data);  
        
        $out .= stripslashes($tagdata);
        $out .= "</form>";
        return $out;
    }
    /* END */


	/** -----------------------------------------
    /**  USAGE: Update request data
    /** -----------------------------------------*/
    
    function public_request_update()
    {
        global $TMPL, $SESS, $FNS, $OUT, $IN;
        
        if ($IN->GBL('note', 'POST')=='')
        {
            $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_error'),
							'heading'	=> $LANG->line('helpspot_ee_manager_error'),
							'content'	=> $LANG->line('helpspot_ee_manager_missing_note'),
							'redirect'	=> $_POST['RET'],							
							'link'		=> array($_POST['RET'], $LANG->line('helpspot_ee_manager_return'))
						 );
					
			$OUT->show_message($data);
            return;
        }
        
        if ($IN->GBL('email', 'POST')=='' || $this->_check_email_address($IN->GBL('email', 'POST'))===false)
        {
            $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_error'),
							'heading'	=> $LANG->line('helpspot_ee_manager_error'),
							'content'	=> $LANG->line('helpspot_ee_manager_missing_email'),
							'redirect'	=> $_POST['RET'],							
							'link'		=> array($_POST['RET'], $LANG->line('helpspot_ee_manager_return'))
						 );
					
			$OUT->show_message($data);
            return;
        }
        
        if ($IN->GBL('accesskey', 'POST')=='')
        {
            $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_error'),
							'heading'	=> $LANG->line('helpspot_ee_manager_error'),
							'content'	=> $LANG->line('helpspot_ee_manager_missing_accesskey'),
							'redirect'	=> $_POST['RET'],							
							'link'		=> array($_POST['RET'], $LANG->line('helpspot_ee_manager_return'))
						 );
					
			$OUT->show_message($data);
            return;
        }
        
        if ( ! class_exists('HelpSpotAPI'))
		{
			require PATH_MOD.'helpspot_ee_manager/HelpSpotAPI'.EXT;
		}
   	    $api = new HelpSpotAPI(array(
                                    "helpSpotApiURL" => trim($this->url, "/")."/api/index.php",
                                    'cacheDir' => PATH_MOD.'helpspot_ee_manager/cache'
                                )); 
        
        $params['accesskey'] = $IN->GBL('accesskey', 'POST');	
        $params['tNote'] = $IN->GBL('note', 'POST');	

        $result = $api->requestUpdate($params);	

        $FNS->redirect($IN->GBL('RET', 'POST'));
    }
    /* END */

	/** -----------------------------------------
    /**  USAGE: Update Request form 
    /** -----------------------------------------*/
    
    function public_request_update_form()
    {
        global $TMPL, $SESS, $FNS;
        
        if ($TMPL->fetch_param('accesskey')=='')
        {
            return $TMPL->no_results;
        }
        
        $hidden_fields = array(
                                'ACT'      	=> $FNS->fetch_action_id('Helpspot_ee_manager', 'public_request_update'),
                                'RET'      	=> $FNS->fetch_current_uri(),
                                'accesskey' => $TMPL->fetch_param('accesskey')
                              );
        
        $data = array(
						'hidden_fields'	=> $hidden_fields,
						'action'		=> '/?ACT='.$FNS->fetch_action_id('Helpspot_ee_manager', 'public_request_update'),
                        'method'		=> 'POST',
						'id'			=> 'helpspot_request_update_form'
					);
        $tagdata = $TMPL->tagdata;
        
        foreach ($TMPL->var_single as $key => $val)
        {     
            if (isset($SESS->userdata["$key"]))
            {
                $tagdata = $TMPL->swap_var_single("$key", $SESS->userdata["$key"], $tagdata);
            }
        }

        $out  = $FNS->form_declaration($data);  
        
        $out .= stripslashes($tagdata);
        $out .= "</form>";
        return $out;
    }
    /* END */


	/** -----------------------------------------
    /**  USAGE: Create new request
    /** -----------------------------------------*/
    
    function public_request_create()
    {
        global $TMPL, $SESS, $FNS, $OUT, $IN, $PREFS, $LANG, $DB, $EXT;
        
        /* -------------------------------------------
		/* 'helpspot_public_request_create_start' hook.
		/*  - Do something when the new request data are received
		*/
			$edata = $EXT->call_extension('helpspot_public_request_create_start');
			if ($EXT->end_script === TRUE) return;
		/*
		/* -------------------------------------------*/
        
        if ($this->use_captcha=='y' && $SESS->userdata['member_id']==0)
        {
            $captcha_error = false;
            
            if ( ! isset($_POST['captcha']) || $_POST['captcha'] == '')
            {
                $captcha_error = true;
            }
            
            $query = $DB->query("SELECT COUNT(*) AS count FROM exp_captcha WHERE word='".$DB->escape_str($_POST['captcha'])."' AND ip_address = '".$IN->IP."' AND date > UNIX_TIMESTAMP()-7200");
		
            if ($query->row['count'] == 0)
            {
				$captcha_error = true;
			}
            
            if ($captcha_error == true)
            {
                $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_error'),
    							'heading'	=> $LANG->line('helpspot_ee_manager_error'),
    							'content'	=> $LANG->line('helpspot_ee_manager_wrong_captcha'),
    							'redirect'	=> $_POST['RET'],							
    							'link'		=> array($_POST['RET'], $LANG->line('helpspot_ee_manager_return'))
    						 );
    					
    			$OUT->show_message($data);
                return;
            }
            
            $DB->query("DELETE FROM exp_captcha WHERE (word='".$DB->escape_str($_POST['captcha'])."' AND ip_address = '".$IN->IP."') OR date < UNIX_TIMESTAMP()-7200");
            
        }
        
        if ($IN->GBL('note', 'POST')=='')
        {
            $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_error'),
							'heading'	=> $LANG->line('helpspot_ee_manager_error'),
							'content'	=> $LANG->line('helpspot_ee_manager_missing_note'),
							'redirect'	=> $_POST['RET'],							
							'link'		=> array($_POST['RET'], $LANG->line('helpspot_ee_manager_return'))
						 );
					
			$OUT->show_message($data);
            return;
        }
        
        if ($IN->GBL('email', 'POST')=='' || $this->_check_email_address($IN->GBL('email', 'POST'))===false)
        {
            $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_error'),
							'heading'	=> $LANG->line('helpspot_ee_manager_error'),
							'content'	=> $LANG->line('helpspot_ee_manager_missing_email'),
							'redirect'	=> $_POST['RET'],							
							'link'		=> array($_POST['RET'], $LANG->line('helpspot_ee_manager_return'))
						 );
					
			$OUT->show_message($data);
            return;
        }
        
        if ($IN->GBL('screen_name', 'POST')=='')
        {
            $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_error'),
							'heading'	=> $LANG->line('helpspot_ee_manager_error'),
							'content'	=> $LANG->line('helpspot_ee_manager_missing_name'),
							'redirect'	=> $_POST['RET'],							
							'link'		=> array($_POST['RET'], $LANG->line('helpspot_ee_manager_return'))
						 );
					
			$OUT->show_message($data);
            return;
        }
        
        if ( ! class_exists('HelpSpotAPI'))
		{
			require PATH_MOD.'helpspot_ee_manager/HelpSpotAPI'.EXT;
		}
   	    $api = new HelpSpotAPI(array(
                                    "helpSpotApiURL" => trim($this->url, "/")."/api/index.php",
                                    'cacheDir' => PATH_MOD.'helpspot_ee_manager/cache'
                                )); 
        
        $params['tNote'] = $IN->GBL('note', 'POST');	
        $params['sEmail'] = $IN->GBL('email', 'POST');	
        if ($IN->GBL('phone', 'POST')!='') $params['sPhone'] = $IN->GBL('phone', 'POST');	
        $firstspace = (strpos($IN->GBL('screen_name', 'POST'), " ")!==false)?strpos($IN->GBL('screen_name', 'POST'), " "):strlen($IN->GBL('screen_name', 'POST'));
        $params['sFirstName'] = trim(substr($IN->GBL('screen_name', 'POST'), 0, $firstspace));
        $params['sLastName'] = trim(substr($IN->GBL('screen_name', 'POST'), $firstspace));
        if ($params['sLastName']=='') 
        {
            $params['sLastName']=$params['sFirstName'];
            $params['sFirstName']='Member';
        }
        if ($IN->GBL('urgent', 'POST')==1) 
        {
            $params['fUrgent'] = 1;
        }
        if ($IN->GBL('category', 'POST')!='') 
        {
            $params['xCategory'] = $IN->GBL('category', 'POST');
        }
        foreach ($_POST as $name=>$value)
        {
            if (strpos($name, 'Custom')!==FALSE && $value!='')
            {
                $params["$name"] = $IN->GBL("$name", 'POST');	
            }
        }
        
        $result = $api->requestCreate($params);	
        var_dump($api->errors);
        if ($result===false)
        {
            $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_error'),
							'heading'	=> $LANG->line('helpspot_ee_manager_error'),
							'content'	=> $LANG->line('helpspot_ee_manager_request_create_error'),						
							'redirect'	=> $_POST['RET'],							
							'link'		=> array($_POST['RET'], $LANG->line('helpspot_ee_manager_return'))
						 );
			unset($_POST);		
			$OUT->show_message($data);
            return;
        }

        if ($IN->GBL('return', 'POST')!='')
        {
            $link_tagdata = $IN->GBL('return', 'POST');
            $link_tagdata = str_replace('%%accesskey%%', $result["request"]["accesskey"], $link_tagdata);
            $link_tagdata = str_replace('%%request_id%%', $result["request"]["xRequest"], $link_tagdata);
            unset($_POST);		
            $FNS->redirect($FNS->create_url($link_tagdata));
        }
        else
        {
            $data = array(	'title' 	=> $LANG->line('helpspot_ee_manager_success'),
							'heading'	=> $LANG->line('helpspot_ee_manager_success'),
							'content'	=> $LANG->line('helpspot_ee_manager_request_created'),						
							'redirect'	=> $_POST['RET'],							
							'link'		=> array($_POST['RET'], $LANG->line('helpspot_ee_manager_return'))
						 );
			unset($_POST);				
			$OUT->show_message($data);
        }
        return;
    }
    /* END */


	/** -----------------------------------------
    /**  USAGE: Create Request form 
    /** -----------------------------------------*/
    
    function public_request_create_form()
    {
        global $TMPL, $SESS, $FNS, $PREFS;
        
        $hidden_fields = array(
                                'ACT'      	=> $FNS->fetch_action_id('Helpspot_ee_manager', 'public_request_create'),
                                'RET'      	=> $FNS->fetch_current_uri(),
                                'return'	=> $TMPL->fetch_param('return'),
                                'BKY'		=> $PREFS->ini('recaptcha_publickey')//remove this from release
                              );
        
        $data = array(
						'hidden_fields'	=> $hidden_fields,
						'action'		=> '/?ACT='.$FNS->fetch_action_id('Helpspot_ee_manager', 'public_request_create'), 
                        'method'		=> 'POST',
						'id'			=> 'helpspot_request_create_form'
					);
        $tagdata = $TMPL->tagdata;
        
        if ( ! class_exists('HelpSpotAPI'))
		{
			require PATH_MOD.'helpspot_ee_manager/HelpSpotAPI'.EXT;
		}
   	    $api = new HelpSpotAPI(array(
                                    "helpSpotApiURL" => trim($this->url, "/")."/api/index.php",
                                    'cacheDir' => PATH_MOD.'helpspot_ee_manager/cache'
                                )); 
        $categories_req = $api->requestGetCategories();
        $categories = $categories_req["categories"]["category"];
        
        preg_match_all("/".LD."categories".RD."(.*?)".LD.SLASH."categories".RD."/s", $tagdata, $rows);
        
        foreach($rows[0] as $row_key => $row_tag)
        {
        	$row_chunk = $rows[0][$row_key];
        
        	$row_chunk_content = $rows[1][$row_key];
        
        	$row_inner = '';
        
        	// loop over the row_data
        	foreach ($categories as $category)
        	{

                $row_template = $row_chunk_content;

        		$row_template = $TMPL->swap_var_single('category_id', $category["xCategory"], $row_template);
                $row_template = $TMPL->swap_var_single('category_name', $category["sCategory"], $row_template);

        		$row_inner .= $row_template;
        	}

        	$tagdata = str_replace($row_chunk, $row_inner, $tagdata);
        }
        
        if ($this->use_captcha=='y' && $SESS->userdata['member_id']==0)
		{ 
			$tagdata = preg_replace("/{captcha}/", $FNS->create_captcha(), $tagdata);
		}
                        
        foreach ($TMPL->var_single as $key => $val)
        {     
            if (isset($SESS->userdata["$key"]))
            {
                $tagdata = $TMPL->swap_var_single("$key", $SESS->userdata["$key"], $tagdata);
            }
        }
        
        

        $out  = $FNS->form_declaration($data);  
        
        $out .= stripslashes($tagdata);
        $out .= "</form>";
        return $out;
    }
    /* END */



    
    /** -----------------------------------------
    /**  USAGE: make request
    /** -----------------------------------------*/
    
    function _request($params, $type='public')
    {
        
        $url = "http://example.com/api/index.php?method=private.request.search&sEmail=someone@somewhere.tld";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERPWD, ':');
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($curl);
        return $result;
    }
    /* END */
    
    
    /** -----------------------------------------
    /**  USAGE: validate email. From http://www.linuxjournal.com/article/9585 
    /** -----------------------------------------*/
    function _check_email_address($email) {
                $isValid = true;
       $atIndex = strrpos($email, "@");
       if (is_bool($atIndex) && !$atIndex)
       {
          $isValid = false;
       }
       else
       {
          $domain = substr($email, $atIndex+1);
          $local = substr($email, 0, $atIndex);
          $localLen = strlen($local);
          $domainLen = strlen($domain);
          if ($localLen < 1 || $localLen > 64)
          {
             // local part length exceeded
             $isValid = false;
          }
          else if ($domainLen < 1 || $domainLen > 255)
          {
             // domain part length exceeded
             $isValid = false;
          }
          else if ($local[0] == '.' || $local[$localLen-1] == '.')
          {
             // local part starts or ends with '.'
             $isValid = false;
          }
          else if (preg_match('/\\.\\./', $local))
          {
             // local part has two consecutive dots
             $isValid = false;
          }
          else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
          {
             // character not valid in domain part
             $isValid = false;
          }
          else if (preg_match('/\\.\\./', $domain))
          {
             // domain part has two consecutive dots
             $isValid = false;
          }
          else if
    (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                     str_replace("\\\\","",$local)))
          {
             // character not valid in local part unless 
             // local part is quoted
             if (!preg_match('/^"(\\\\"|[^"])+"$/',
                 str_replace("\\\\","",$local)))
             {
                $isValid = false;
             }
          }
          /*if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
          {
             // domain not found in DNS
             $isValid = false;
          }*/
       }
       return $isValid;
    }


    

}
/* END */
?>