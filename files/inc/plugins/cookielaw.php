<?php
/**
 * Cookie Law 0.1

 * Copyright 2011 Matthew Rogowski

 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at

 ** http://www.apache.org/licenses/LICENSE-2.0

 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
**/

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook('global_start', 'cookielaw_global_start');
$plugins->add_hook('global_end', 'cookielaw_global_end');
$plugins->add_hook('misc_start', 'cookielaw_misc');
$plugins->add_hook('admin_load', 'cookielaw_clear_cookies');

function cookielaw_info()
{
	return array(
		"name" => "Cookie Law",
		"description" => "Give information and gain consent for cookies to be set by the forum.",
		"website" => "http://mattrogowski.co.uk/mybb/",
		"author" => "MattRogowski",
		"authorsite" => "http://mattrogowski.co.uk/mybb/",
		"version" => "0.1",
		"compatibility" => "16*",
		"guid" => "8feb3d39042f9ff5b76561fbb42019b2"
	);
}

function cookielaw_activate()
{
	global $db;
	
	cookielaw_deactivate();
	
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	
	find_replace_templatesets("header", "#".preg_quote('<div id="container">')."#i", '{$cookielaw}<div id="container">');
	find_replace_templatesets("footer", "#".preg_quote('{$lang->bottomlinks_syndication}</a>')."#i", '{$lang->bottomlinks_syndication}</a> | <a href="{$mybb->settings[\'bburl\']}/misc.php?action=cookielaw_info">{$lang->cookielaw_footer}</a>');
	
	$templates = array();
	$templates[] = array(
		"title" => "cookielaw_info",
		"template" => "<html>
<head>
<title>{\$lang->cookielaw_info_title}</title>
{\$headerinclude}
<script type=\"text/javascript\">
document.observe(\"dom:loaded\", function() {
	\$('cookielaw_disallow_bottom').observe('click', function(Event) {
		if(!confirm('{\$lang->cookielaw_disallow_confirm}'))
		{
			Event.stop();
		}
	});
});
</script>
</head>
<body>
{\$header}
<form action=\"{\$mybb->settings['bburl']}/misc.php?action=cookielaw_change\" method=\"post\">
	<table border=\"0\" cellspacing=\"{\$theme['borderwidth']}\" cellpadding=\"{\$theme['tablespace']}\" class=\"tborder\">
		<tr>		
			<td class=\"thead\" colspan=\"4\"><strong>{\$lang->cookielaw_header}</strong></td>
		</tr>
		<tr>		
			<td class=\"trow1\" colspan=\"4\">{\$lang->cookielaw_description}</td>
		</tr>
		<tr>		
			<td class=\"tcat\"><strong>{\$lang->cookielaw_info_cookie_name}</strong></td>
			<td class=\"tcat\"><strong>{\$lang->cookielaw_info_cookie_description}</strong></td>
			<td class=\"tcat\" align=\"center\"><strong>{\$lang->cookielaw_info_cookies_set_logged_in}</strong></td>
			<td class=\"tcat\" align=\"center\"><strong>{\$lang->cookielaw_info_cookies_set_guest}</strong></td>
		</tr>
		{\$cookies_rows}
		<tr>		
			<td class=\"tfoot\" colspan=\"4\"><div style=\"text-align: center;\"><input type=\"submit\" name=\"allow\" value=\"{\$lang->cookielaw_allow}\" /><input type=\"submit\" name=\"disallow\" id=\"cookielaw_disallow_bottom\" value=\"{\$lang->cookielaw_disallow}\" /><input type=\"hidden\" name=\"my_post_key\" value=\"{\$mybb->post_code}\" /></div></td>
		</tr>
	</table>
</form>
{\$footer}
</body>
</html>"
	);
	$templates[] = array(
		"title" => "cookielaw_header",
		"template" => "<script type=\"text/javascript\">
document.observe(\"dom:loaded\", function() {
	\$('cookielaw_disallow_top').observe('click', function(Event) {
		if(!confirm('{\$lang->cookielaw_disallow_confirm}'))
		{
			Event.stop();
		}
	});
});
</script>
<div id=\"cookies\" style=\"width: 100%; text-align: left; margin-bottom: 10px;\">
	<form action=\"{\$mybb->settings['bburl']}/misc.php?action=cookielaw_change\" method=\"post\">
		<table border=\"0\" cellspacing=\"1\" cellpadding=\"4\" class=\"tborder\">
			<tr>		
				<td class=\"thead\"><strong>{\$lang->cookielaw_header}</strong></td>
			</tr>
			<tr>		
				<td class=\"trow1\">{\$lang->cookielaw_description}<br /><br />{\$lang->cookielaw_description_setcookie}</td>
			</tr>
			<tr>		
				<td class=\"tfoot\"><div class=\"float_right\"><input type=\"submit\" name=\"allow\" value=\"{\$lang->cookielaw_allow}\" /><input type=\"submit\" name=\"disallow\" id=\"cookielaw_disallow_top\" value=\"{\$lang->cookielaw_disallow}\" /><input type=\"submit\" name=\"more_info\" value=\"{\$lang->cookielaw_more_info}\" /><input type=\"hidden\" name=\"my_post_key\" value=\"{\$mybb->post_code}\" /></div></td>
			</tr>
		</table>
	</form>
</div>"
	);
	$templates[] = array(
		"title" => "cookielaw_header_no_cookies",
		"template" => "<div id=\"cookies\" style=\"display: inline-block; text-align: left; margin-bottom: 10px; padding: 4px; font-size: 10px; border: 1px solid #000000;\">
	{\$lang->cookielaw_description_no_cookies}
</div>"
	);
	
	foreach($templates as $template)
	{
		$insert = array(
			"title" => $db->escape_string($template['title']),
			"template" => $db->escape_string($template['template']),
			"sid" => "-1",
			"version" => "1600",
			"status" => "",
			"dateline" => TIME_NOW
		);
		
		$db->insert_query("templates", $insert);
	}
}

function cookielaw_deactivate()
{
	global $db;
	
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	
	find_replace_templatesets("header", "#".preg_quote('{$cookielaw}')."#i", '', 0);
	find_replace_templatesets("footer", "#".preg_quote(' | <a href="{$mybb->settings[\'bburl\']}/misc.php?action=cookielaw_info">{$lang->cookielaw_footer}</a>')."#i", '', 0);
	
	$db->delete_query("templates", "title IN ('cookielaw_info','cookielaw_header','cookielaw_header_no_cookies')");
}

function cookielaw_global_start()
{
	global $mybb, $lang, $templates, $cookielaw;
	
	$lang->load('cookielaw');
	
	if(!isset($mybb->cookies['mybb']['allow_cookies']))
	{
		eval("\$cookielaw = \"".$templates->get("cookielaw_header")."\";");
	}
	elseif(isset($mybb->cookies['mybb']['allow_cookies']) && $mybb->cookies['mybb']['allow_cookies'] == '0')
	{
		$lang->cookielaw_description_no_cookies = $lang->sprintf($lang->cookielaw_description_no_cookies, $mybb->settings['bburl']);
		eval("\$cookielaw = \"".$templates->get("cookielaw_header_no_cookies")."\";");
	}
	
	cookielaw_clear_cookies();
}

function cookielaw_global_end()
{
	 cookielaw_clear_cookies();
}

function cookielaw_misc()
{
	global $mybb, $lang, $templates, $theme, $cookielaw_info, $header, $headerinclude, $footer;
	
	$lang->load('cookielaw');
	
	if($mybb->input['action'] == 'cookielaw_change')
	{
		if(isset($mybb->input['more_info']))
		{
			// hack to show no redirect
			$mybb->settings['redirects'] = 0;
			redirect('misc.php?action=cookielaw_info');
		}
		else
		{
			if(isset($mybb->input['disallow']))
			{
				my_setcookie('mybb[allow_cookies]', '0');
				cookielaw_clear_cookies();
			}
			else
			{
				my_setcookie('mybb[allow_cookies]', '1');
			}
			redirect('index.php', $lang->cookielaw_redirect);
		}
	}
	elseif($mybb->input['action'] == 'cookielaw_info')
	{
		$cookies_rows = '';
		$cookies = cookielaw_get_cookies();
		foreach($cookies as $cookie_name => $info)
		{
			if(isset($info['mod']) || isset($info['admin']))
			{
				$cookie_user_type = '';
				if($info['mod'])
				{
					$cookie_user_type = $lang->cookielaw_info_cookies_set_mod;
				}
				elseif($info['admin'])
				{
					$cookie_user_type = $lang->cookielaw_info_cookies_set_admin;
				}
				
				$trow = alt_trow();
				$cookie_description = 'cookielaw_cookie_'.$cookie_name.'_desc';
				$cookies_rows .= '<tr>
					<td class="'.$trow.'">'.$cookie_name.'</td>
					<td class="'.$trow.'">'.$lang->$cookie_description.'</td>
					<td class="'.$trow.'" align="center">'.$cookie_user_type.'</td>
					<td class="'.$trow.'" align="center">-</td>
				</tr>';
			}
			else
			{
				$cookie_member = $cookie_guest = '';
				if($info['member'])
				{
					$cookie_member = '<img src="'.$mybb->settings['bburl'].'/images/valid.gif" alt="" title="" />';
				}
				else
				{
					$cookie_member = '<img src="'.$mybb->settings['bburl'].'/images/invalid.gif" alt="" title="" />';
				}
				if($info['guest'])
				{
					$cookie_guest = '<img src="'.$mybb->settings['bburl'].'/images/valid.gif" alt="" title="" />';
				}
				else
				{
					$cookie_guest = '<img src="'.$mybb->settings['bburl'].'/images/invalid.gif" alt="" title="" />';
				}
				$trow = alt_trow();
				$cookie_description = 'cookielaw_cookie_'.$cookie_name.'_desc';
				$cookies_rows .= '<tr>
					<td class="'.$trow.'">'.$cookie_name.'</td>
					<td class="'.$trow.'">'.$lang->$cookie_description.'</td>
					<td class="'.$trow.'" align="center">'.$cookie_member.'</td>
					<td class="'.$trow.'" align="center">'.$cookie_guest.'</td>
				</tr>';
			}
		}
		
		eval("\$cookielaw_info = \"".$templates->get("cookielaw_info")."\";");
		output_page($cookielaw_info);
	}
}

function cookielaw_clear_cookies()
{
	global $mybb;
	
	if(isset($mybb->cookies['mybb']['allow_cookies']) && $mybb->cookies['mybb']['allow_cookies'] == '0')
	{
		$cookies = cookielaw_get_cookies(true);
		foreach($cookies as $cookie_name => $info)
		{
			if($cookie_name == 'mybb[allow_cookies]')
			{
				continue;
			}
			my_unsetcookie($cookie_name);
		}
		foreach($mybb->cookies as $key => $val)
		{
			if(strpos($key, 'inlinemod_') !== false)
			{
				my_unsetcookie($key);
			}
		}
		unset($mybb->user);
		unset($mybb->session);
	}
}

function cookielaw_get_cookies($all = false)
{
	global $mybb;
	
	$cookies = array(
		'sid' => array(
			'member' => true,
			'guest' => true
		),
		'mybbuser' => array(
			'member' => true,
			'guest' => false
		),
		'mybb[lastvisit]' => array(
			'member' => false,
			'guest' => true
		),
		'mybb[lastactive]' => array(
			'member' => false,
			'guest' => true
		),
		'mybb[threadread]' => array(
			'member' => false,
			'guest' => true
		),
		'mybb[forumread]' => array(
			'member' => false,
			'guest' => true
		),
		'mybb[readallforums]' => array(
			'member' => false,
			'guest' => true
		),
		'mybb[announcements]' => array(
			'member' => true,
			'guest' => true
		),
		'mybb[referrer]' => array(
			'member' => false,
			'guest' => true
		),
		'forumpass' => array(
			'member' => true,
			'guest' => true
		),
		'mybblang' => array(
			'member' => false,
			'guest' => true
		),
		'collapsed' => array(
			'member' => true,
			'guest' => true
		),
		'coppauser' => array(
			'member' => false,
			'guest' => true
		),
		'coppadob' => array(
			'member' => false,
			'guest' => true
		),
		'loginattempts' => array(
			'member' => false,
			'guest' => true
		),
		'fcollapse' => array(
			'member' => false,
			'guest' => true
		),
		'multiquote' => array(
			'member' => true,
			'guest' => true
		),
		'pollvotes' => array(
			'member' => true,
			'guest' => true
		),
		'mybbratethread' => array(
			'member' => false,
			'guest' => true
		),
		'mybb[allow_cookies]' => array(
			'member' => true,
			'guest' => true
		)
	);
	
	if($all || is_moderator())
	{
		$cookies['inlinemod_*'] = array(
			'mod' => true
		);
	}
	
	if($all || $mybb->usergroup['cancp'] == 1)
	{
		$cookies['adminsid'] = array(
			'admin' => true
		);
		$cookies['acploginattempts'] = array(
			'admin' => true
		);
	}
	
	return $cookies;
}
?>