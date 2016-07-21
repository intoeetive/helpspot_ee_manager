<h1>HelpSpot EE manager</h1>

<p>HelpSpot EE manager enables integration of HelpSpot helpdesk software by UserScape into ExpressionEngine. It uses PHP API implementation by Joe Landsman, copyrighted by UserScape Inc (used by permission).</p>
<p>The module is available for EE 1.x only. The version for EE 2.x will be developed when EE 1.x modules goes out of beta.</p>

<h2>Requirements</h2>

<p>You will need both Private and Public API enabled in your HelpSpot installation. <br />Do not forget to set your HelpSport URL, username and password in module settings.</p>

<h2>Support issues</h2>

<p>Support is provided through <a href="http://devot-ee.com/add-ons/helpspot-ee-manager/">devot:ee</a>. Please include screenshots and other important data into your requests.</p>

<h2>Settings</h2>

<p>You will have to provide the module with link to your HelpSpot installation, login and password. You can optionally select to use CAPTCHA for creating requests.</p>
<p><img src="http://www.intoeetive.com/docs/helpspot-settings.png" /></p>
    
<h2>Usage</h2>

<p>Use as you would use any module :)<br />The module does not require HelpSpot passwords from customers, as EE authorization should be enough. You can even show requests to non-authorized users, if you like. The only thing you should ask from people is their email or request accesskey.</p>

<h2>Tags</h2>

<ul>
	
    
    <li><a href="#public_request_create_form">public_request_create_form</a> - prepare form to create new request</li>
    <li><a href="#custom_fields_form">custom_fields_form</a> - add custom fields to request creation form</li>
    <li><a href="#public_request_update_form">public_request_update_form</a> - prepare form to update request (for customer)</li>
    <li><a href="#public_request_get_by_id">public_request_get_by_id</a> - get request data by passing request ID</li>
	<li><a href="#public_request_get_by_key">public_request_get_by_key</a> - get request data by passing access key</li>
    <li><a href="#public_request_search">public_request_search</a> - search requests by email or member_id</li>
    <li><a href="#private_request_search">private_request_search</a> - search requests by email or member_id</li>
	
	<li><a href="#private_customer_password_get">private_customer_password_get</a> - get HelpSpot password by email</li>
	<li><a href="#private_request_get">private_request_get</a> - get request data</li>
	
    <li><a href="#public_customer_requests_get">public_customer_requests_get</a> - get list of user's request</li>
    <li><a href="#private_request_update_form">private_request_update_form</a> - prepare form to update request data (for support staff)</li>
</ul>

<p><strong>NOTE:</strong> The main difference between private_ and public_ tags is the API that it uses - private or public respectively. Private is intended for staff use and public is for customers. However you might find useful using combination of both. Please also note that some public_ tags still utilize Private API functions, so you'll need to have both APIs enabled even if you intend using only public_ tags.</p>

<h2><a name="public_request_create_form"></a>public_request_create_form</h2>

<p>The tag outputs the form for creating new request</p>


<code>{exp:helpspot_ee_manager:public_request_create_form return="support/viewrequest/%%accesskey%%"}<br />
<br />
&lt;p&gt;&lt;label&gt;Name (required)&lt;/label&gt;<br />
&lt;input type="text" name="screen_name" value="{screen_name}" /&gt;&lt;/p&gt;<br />
&lt;p&gt;&lt;label&gt;Email (required)&lt;/label&gt;<br />
&lt;input type="text" name="email" value="{email}" /&gt;&lt;/p&gt;<br />
&lt;p&gt;&lt;label&gt;Phone&lt;/label&gt;<br />
&lt;input type="text" name="phone" value="" /&gt;&lt;/p&gt;<br />
&lt;p&gt;&lt;label&gt;Category&lt;/label&gt;<br />
&lt;select name="category" id="support-category"&gt;<br />
{categories}<br />
&lt;option value="{category_id}"&gt;{category_name}&lt;/option&gt;<br />
{/categories}<br />
&lt;/select&gt;&lt;/p&gt;<br />
&lt;p&gt;&lt;input type="checkbox" name="urgent" value="1" /&gt; Urgent?&lt;/p&gt;<br />
<br />
&lt;div id="support-form-custom-fields"&gt;&lt;/div&gt;<br />
&lt;script type="text/javascript"&gt;<br />
$(document).ready(function() {<br />
&nbsp;&nbsp;$('#support-form-custom-fields').load("/_ajax/support-form-custom-fields/"+$("#support-category").val());<br />
&nbsp;&nbsp;$("#support-category").change(function() {<br />
&nbsp;&nbsp;&nbsp;&nbsp;$('#support-form-custom-fields').load("/_ajax/support-form-custom-fields/"+$("#support-category").val());<br />
&nbsp;&nbsp;});<br />
});<br />
&lt;/script&gt;<br />
<br />
&lt;p&gt;&lt;label&gt;Message to support&lt;/label&gt;<br />
&lt;textarea name="note" cols="60" rows="6"&gt;&lt;/textarea&gt;&lt;/p&gt;<br />
&lt;p&gt;&lt;input type="submit" /&gt;&lt;/p&gt;<br />
<br />
{/exp:helpspot_ee_manager:public_request_create_form}</code>

<p>
The mandatory fields for the form are:
<ul>
<li><strong>email</strong> - email address</li>
<li><strong>screen_name</strong> - screen name or real name (will be parsed into first and last names OR into 'Member xxx' if screen_name is one word)</li>
<li><strong>note</strong> - the actual message to support</li>
</ul>
</p>
<p>
The other possible fields are:
<ul>
<li><strong>phone</strong> - phone number</li>
<li><strong>urgent</strong> - set to 1 if the request as urgent </li>
<li><strong>category</strong> - ID of category to assign support</li>
<li><strong>Custom#</strong> (ex. Custom3) - custom request field. The example above is using jQuery to load the fields based on category selected, see <a href="#custom_fields_form">custom_fields_form</a> for more details.</li>
<li><strong>return</strong> - URL to redirect after successful request creation. If not specified, system message will be shown.</li>
</ul>
</p>

<p><samp>return</samp> parameter can contain special parameters as URL segments:
<ul>
<li><strong>%%accesskey%%</strong> will be replaced with access key for created request</li>
<li><strong>%%request_id%%</strong> will be replaced with ID of created request</li>
</ul>
</p>


<p><samp>{categories}</samp> is a tag pair that outputs the list of request categories available. It outputs 2 variables:  
<ul>
<li><strong>category_id</strong> - category ID</li>
<li><strong>category_name</strong> - category name</li>
</ul>
</p>


<h2><a name="custom_fields_form"></a>custom_fields_form</h2>

<p>The purpose of tag is to output the list of custom fields. It's common use would be together with public_request_create_form.</p>


<code>{exp:helpspot_ee_manager:custom_fields_form category_id="{segment_3}"}<br />
{fields}<br />
&lt;p&gt;&lt;label&gt;{field_label}&lt;/label&gt;<br />
{field_input}&lt;/p&gt;<br />
{/fields}<br />
{/exp:helpspot_ee_manager:custom_fields_form}</code>

<p>The fields loop must be surrounded with <samp>{fields}</samp> tag pair.</p>

<p>The <samp>category_id</samp> parameter is optional. Use it if you need to limit the list of fields to specific category - otherwise full custom fields list will be returned</p>

<p>The variables the tag outputs are:
<ul>
<li><strong>field_id</strong> - the ID of custom field</li>
<li><strong>field_label</strong> - the label, or name, of the field</li>
<li><strong>field_type</strong> - the type of custom field</li>
<li><strong>field_input</strong> - the pre-parsed html input element according to the type of the field. (NOTE: currently supports only 'date', 'select', 'checkbox, 'lrgtext' and 'text' field types - for others simple text input will be used. We suggest using jQuery datepicker for best experience with date fieldtype)</li>
</ul>
</p>



<h2><a name="public_request_update_form"></a>public_request_update_form</h2>

<p>The purpose of tag is to create a form for user to update, or reply to the request.</p>


<code>{exp:helpspot_ee_manager:public_request_update_form accesskey="{segment_3}"}<br />
&lt;p&gt;&lt;label&gt;Email&lt;/label&gt;<br />
&lt;input type="text" name="email" value="{email}" /&gt;&lt;/p&gt;<br />
&lt;p&gt;&lt;label&gt;Message to support&lt;/label&gt;<br />
&lt;textarea name="note" cols="60" rows="6"&gt;&lt;/textarea&gt;&lt;/p&gt;<br />
&lt;p&gt;&lt;input type="submit" /&gt;&lt;/p&gt;<br />
{/exp:helpspot_ee_manager:public_request_update_form}</code>

<p>The only parameter is <strong>accesskey</strong> - the access key for request to be updated. It is required.</p>

<p>The form fields are (all required):
<ul>
<li><strong>email</strong> - email address</li>
<li><strong>note</strong> - the message/reply to be added to request</li>
</ul>
</p>


<h2><a name="public_request_get_by_key"></a>public_request_get_by_key</h2>

<p>The tag lets you display request data having access key specified.</p>


<code>{exp:helpspot_ee_manager:public_request_get_by_key accesskey="{segment_3}" sort="desc"}<br />
&lt;h1&gt;Support request #{request_id}&lt;/h1&gt;<br />
&lt;div class="request"&gt;<br />
{items}<br />
&lt;div class="request-row"&gt;<br />
&lt;span&gt;{request_item_author}&lt;/span&gt; on {request_item_date format="%Y-%m-%d %H:%i"}&lt;br /&gt;<br />			
{request_item_text}<br />
<br />
{files}<br />
&lt;p&gt;Attachment: &lt;a href="{file_url}"&gt;{file_name}&lt;/a&gt;&lt;/p&gt;
{/files}<br />

&lt;/div&gt;<br />
{/items}<br />
{/exp:helpspot_ee_manager:public_request_get_by_key}</code>

<p>The accepted parameters are:
<ul>
<li><strong>accesskey</strong> (required) - the access key for request</li>
<li><strong>sort</strong> - set to <span class="highlight">desc</span> to display request replies in descending order (the oldest at the top)</li>
</ul>
</p>

<p>The <samp>{custom_fields}</samp> tag pair lets you output all custom fields provided with initial request</p>
<p>The variables available <u>inside</u> the {custom_fields} loop:
<ul>
<li><strong>field_label</strong> - custom field name/label</li>
<li><strong>field_value</strong> - the field value set when creating request</li>
<li><strong>field_type</strong> - the type of field (as set in HelpSpot)</li>
</ul>
</p>

<p>The <samp>{items}</samp> tag pair loops though all posts in request. Note that only public posts are displayed.</p>

<p>The variables available <u>inside</u> the {items} loop:
<ul>
<li><strong>request_item_author</strong> - shows who posted the item. Displays 'You' or 'Support' by default, the text can be changed in lang.helpspot_ee_manager.php</li>
<li><strong>request_item_text</strong> - the text of post/reply</li>
<li><strong>request_item_date format="%Y-%m-%d"</strong> - the date of post. EE formatting rules apply</li>
</ul>
</p>

<p>The <samp>{files}</samp> tag pair can be placed inside {items} tag pair to display files attached to the post. It returns 2 variables:
<ul>
<li><strong>file_name</strong> - filename of uploaded file</li>
<li><strong>file_url</strong> - link to donwload the attachment</li>
</ul>
</p>

<p>The variables available <u>inside</u> the {items} loop:
<ul>
<li><strong>request_item_author</strong> - shows who posted the item. Displays 'You' or 'Support' by default, the text can be changed in lang.helpspot_ee_manager.php</li>
<li><strong>request_item_text</strong> - the text of post/reply</li>
<li><strong>request_item_date format="%Y-%m-%d"</strong> - the date of post. EE formatting rules apply</li>
</ul>
</p>

<p>
There conditionals available <u>inside</u> the {items} loop inclue:
<ul>
<li><strong>if initial</strong> - determines whether this is initial request or reply</li>

</ul>
</p>

<p>The variables available <u>outside</u> the loops:
<ul>
<li><strong>request_id</strong> - ID of request</li>
<li><strong>accesskey</strong> - unique access key for request</li>
<li><strong>request_status</strong> - the status of request</li>
<li><strong>request_category</strong> - the name category that request is assigned to</li>
<li><strong>request_items_total</strong> - total number of posts/replies</li>
</ul>
</p>

<p>
There are also conditionals available <u>outside</u> the loops/tag pairs:
<ul>
<li><strong>if no_replies</strong> - the request did not get any replies from support yet</li>
<li><strong>if no_results</strong></li>
<li><strong>if request_open</strong></li>
<li><strong>if request_urgent</strong></li>

</ul>
</p>


<h2><a name="public_request_get_by_id"></a>public_request_get_by_id</h2>

<p>The tag lets you display request data having request ID specified.</p>

<p>The syntax and available data are the same that for <a name="public_request_get_by_key">public_request_get_by_key</a>. Please note that using this tag in your public templates might give anyone possibility to view any request by specifing only its ID, so use with caution.</p>

<h2><a name="public_request_search"></a>public_request_search</h2>

<p>The tag lets you display the list of requests opened by a user.</p>


<code>{exp:helpspot_ee_manager:public_request_search email="{email}"}<br />
{if no_results}<br />
&lt;p&gt;No support requests for you&lt;/p&gt;<br />
{/if}<br />
{if '{request_count}'=='1'}<br />
&lt;table class="pm-list"&gt;<br />
&lt;tr class="header"&gt;<br />
&lt;th width="5%"&gt;Request ID&lt;/th&gt;<br />
&lt;th width="10%" align="left" nowrap="nowrap"&gt;Status&lt;/th&gt;<br />
&lt;th width="10%" align="left" nowrap="nowrap"&gt;Category&lt;/th&gt;<br />
&lt;th width="10%" align="left" nowrap="nowrap"&gt;Date open&lt;/th&gt;<br />
&lt;th width="65%" align="left"&gt;Summary&lt;/th&gt;<br />
&lt;/tr&gt;<br />
{/if}<br />
<br />
&lt;tr&gt;<br />
&lt;td&gt;&lt;a href="{path=support/viewrequest/{accesskey}}"&gt;{request_id}&lt;/a&gt;&lt;/td&gt;<br />
&lt;td nowrap="nowrap"&gt;{request_status}&lt;/td&gt;<br />
&lt;td nowrap="nowrap"&gt;{request_category}&lt;/td&gt;<br />
&lt;td nowrap="nowrap"&gt;{request_dateopen format="%Y-%m-%d %H:%i"}&lt;/td&gt;<br />
&lt;td&gt;{request_text}&lt;/td&gt;<br />
&lt;/tr&gt;<br />
<br />
{if '{request_count}'=='{request_total}'}&lt;/table&gt;{/if}<br />
<br />
{/exp:helpspot_ee_manager:public_request_search}</code>

<p>The accepted parameters are:
<ul>
<li><strong>email</strong> - email address</li>
<li><strong>member_id</strong> - ExpressionEngine member ID</li>
</ul>
</p>
<p>You need to specify either one of these parameters.</p>

<p>Variables available:
<ul>
<li><strong>request_total</strong> - total number of requests returned</li>
<li><strong>request_email</strong> - email address used to look up requests</li>
<li><strong>request_count</strong> - the index number for each request</li>
<li><strong>request_id</strong> - request ID</li>
<li><strong>request_password</strong> - request password</li>
<li><strong>request_status</strong> - request status (text)</li>
<li><strong>request_category</strong> - request category (text)</li>
<li><strong>request_category_id</strong> - request category ID</li>
<li><strong>request_dateopen format="%Y-%m-%d"</strong> - the date when request has been opened. EE formatting rules apply</li>
<li><strong>request_text</strong> - the actual text of request</li>
<li><strong>request_author</strong> - full name of author</li>
<li><strong>accesskey</strong> - unique access key</li>
</ul>
</p>

<p>
There are also conditionals available:
<ul>
<li><strong>if no_results</strong></li>
<li><strong>if request_open</strong></li>
<li><strong>if request_urgent</strong></li>

</ul>
</p>


<h2><a name="private_request_search"></a>private_request_search</h2>

<p>The tag lets you display the list of requests opened by a user.</p>


<code>{exp:helpspot_ee_manager:private_request_search email="{email}"}<br />
{if no_results}<br />
&lt;p&gt;No support requests for you&lt;/p&gt;<br />
{/if}<br />
{if '{request_count}'=='1'}<br />
&lt;table class="pm-list"&gt;<br />
&lt;tr class="header"&gt;<br />
&lt;th width="5%"&gt;Request ID&lt;/th&gt;<br />
&lt;th width="10%" align="left" nowrap="nowrap"&gt;Status&lt;/th&gt;<br />
&lt;th width="10%" align="left" nowrap="nowrap"&gt;Category&lt;/th&gt;<br />
&lt;th width="65%" align="left"&gt;Summary&lt;/th&gt;<br />
&lt;/tr&gt;<br />
{/if}<br />
<br />
&lt;tr&gt;<br />
&lt;td&gt;{request_id}&lt;/td&gt;<br />
&lt;td nowrap="nowrap"&gt;{request_status}&lt;/td&gt;<br />
&lt;td nowrap="nowrap"&gt;{request_category}&lt;/td&gt;<br />
&lt;td&gt;{request_text}&lt;/td&gt;<br />
&lt;/tr&gt;<br />
<br />
{if '{request_count}'=='{request_total}'}&lt;/table&gt;{/if}<br />
<br />
{/exp:helpspot_ee_manager:private_request_search}</code>

<p>The accepted parameters are:
<ul>
<li><strong>email</strong> - email address</li>
<li><strong>member_id</strong> - ExpressionEngine member ID</li>
<li><strong>getpassword</strong> - set to 'true' if you want to get customer's HelpSpot password</li>
</ul>
</p>
<p>You need to specify either one of these parameters.</p>

<p>Variables available:
<ul>

<li><strong>customer_password</strong> -  customer's HelpSpot password. Available only if getpassword='true'.</li>

<li><strong>request_total</strong> - total number of requests returned</li>

<li><strong>request_count</strong> - the index number for each request</li>
<li><strong>request_id</strong> - request ID</li>
<li><strong>request_password</strong> - request password</li>
<li><strong>request_status</strong> - request status (text)</li>
<li><strong>request_category</strong> - request category (text)</li>

<li><strong>request_text</strong> - the actual text of request</li>

</ul>
</p>

<p>
There are also conditionals available:
<ul>
<li><strong>if no_results</strong></li>
<li><strong>if request_open</strong></li>
<li><strong>if request_urgent</strong></li>

</ul>
</p>




<h2><a name="private_customer_password_get"></a>private_customer_password_get</h2>

<p>Get HelpSpot portal password by providing email address.</p>


<code>{exp:helpspot_ee_manager:private_customer_password_get email="mail@address.com"}</code>

<p>No variables returned, no closing tag needed.</p>













<h2><a name="public_customer_requests_get"></a>public_customer_requests_get</h2>

<p>Get list of user's request by providing email and password. Currently is capable to return only raw data.</p>


<code>{exp:helpspot_ee_manager:public_customer_requests_get email="email@address.com" password="paSSw0rd"}</code>

<p>The accepted parameters are:
<ul>
<li><strong>email</strong> - email address</li>
<li><strong>password</strong> - HelpSpot password</li>
</ul>
</p>
<p>Does not return any variables, only raw data.</p>










<h2><a name="private_request_update_form"></a>private_request_update_form</h2>

<p>The purpose of tag is to create a form <em>for support staff</em> to update, or reply to the request.</p>


<code>{exp:helpspot_ee_manager:private_request_update_form request_id="{segment_3}"}<br />
&lt;p&gt;&lt;label&gt;Email&lt;/label&gt;<br />
&lt;input type="text" name="email" value="{email}" /&gt;&lt;/p&gt;<br />
&lt;p&gt;&lt;label&gt;Message to user&lt;/label&gt;<br />
&lt;textarea name="note" cols="60" rows="6"&gt;&lt;/textarea&gt;&lt;/p&gt;<br />
&lt;p&gt;&lt;input type="submit" /&gt;&lt;/p&gt;<br />
{/exp:helpspot_ee_manager:private_request_update_form}</code>

<p>The only parameter is <strong>request_id</strong> - the ID of request to be updated. It is required.</p>

<p>The form fields are (all required):
<ul>
<li><strong>email</strong> - email address</li>
<li><strong>note</strong> - the message/reply to be added to request</li>
</ul>
</p>
<p>Be careful using this tag, it is indended for staff only, not for your site visitors.</p>