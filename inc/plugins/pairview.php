<?php

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.");
}

// Hooks
// ADMIN-CP PEEKER
$plugins->add_hook('admin_config_settings_change', 'pairview_settings_change');
$plugins->add_hook('admin_settings_print_peekers', 'pairview_settings_peek');

// misc
$plugins->add_hook('misc_start', 'pairview_misc');

// Alerts
if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
    $plugins->add_hook("global_start", "pairview_alerts");
}


function pairview_info()
{
    return array(
        "name" => "Pärchenübersicht",
        "description" => "Hier können User ihre Pärchen auflisten. Die Beteiligten Personen werden über eine Alert informiert. <b>MyAlert</b> muss Installiert sein! <br /> Die Pärchen werden dann nach dem Eintragen in den verschiedenen Kategorien angezeigt.",
        "website" => "https://github.com/Ales12/pairview-2.0",
        "author" => "Ales",
        "authorsite" => "https://github.com/Ales12",
        "version" => "2.0",
        "guid" => "",
        "codename" => "",
        "compatibility" => "18*"
    );
}

function pairview_install()
{
    global $db, $mybb;
    // Datenbank anlegen

    if ($db->engine == 'mysql' || $db->engine == 'mysqli') {
        $db->query("CREATE TABLE `" . TABLE_PREFIX . "pairview` (
          `pvid` int(10) NOT NULL auto_increment,
          `kind` varchar(255) NOT NULL,
            `lover1` int(10) NOT NULL,  
        `pic1` varchar(500) NOT NULL,
          `lover2` int(10) NOT NULL,
          `pic2` varchar(500) NOT NULL,
          PRIMARY KEY (`pvid`)
        ) ENGINE=MyISAM" . $db->build_create_table_collation());
    }

    // Einstellungen
    $setting_group = array(
        'name' => 'pairview',
        'title' => 'Einstellungen für die Pärchenübersicht',
        'description' => 'Hier kannst du alle Einstellungen für die Pärchenübersicht tätigen.',
        'disporder' => 5, // The order your setting group will display
        'isdefault' => 0
    );


    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(               // A yes/no boolean box
        'pairview_guest' => array(
            'title' => 'Gäste Ansicht erlauben?',
            'description' => 'Dürfen Gäste die Pairview sehen?',
            'optionscode' => 'yesno',
            'value' => 0,
            'disporder' => 1
        ),
        // A text setting
        'pairview_kind' => array(
            'title' => 'Beziehungsarten',
            'description' => 'Gebe hier an, welche Beziehungsarten es gibt:',
            'optionscode' => 'textarea',
            'value' => 'Verheiratet, Verlobt, Beziehung, Situationship, Affäre, Zukünftig, Vergangen', // Default
            'disporder' => 2
        ),
        // A yes/no boolean box
        'pairview_picpf' => array(
            'title' => 'Profilfeld anstatt eigenes Bild? ',
            'description' => 'Soll anstatt einem eigenen Bild ein Profilfeld ausgelesen werden, in welchen schon ein Bild eingefügt wurde?',
            'optionscode' => 'yesno',
            'value' => 0,
            'disporder' => 3
        ),    // A select box
        'pairview_picsize' => array(
            'title' => 'Bildergröße',
            'description' => 'Welche Maße soll das eingefügte  Bild haben?',
            'optionscode' => "text",
            'value' => "100x100",
            'disporder' => 4
        ),
        'pairview_pf' => array(
            'title' => 'Profilfeld',
            'description' => 'Welches Profilfeld soll ausgelesen werden?',
            'optionscode' => "text",
            'value' => "fid2",
            'disporder' => 5
        ),

    );

    foreach ($setting_array as $name => $setting) {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }



    // templaes
    $insert_array = array(
        'title' => 'pairview',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->pairview}</title>
{$headerinclude}
</head>
<body>
{$header}
<table class="tborder" border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}">
	<tr><td class="thead"><strong>{$lang->pairview}</strong></td></tr>
	{$menu}
<tr><td class="trow1">
{$pairview_cat}
	</td></tr>	</table>
{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'pairview_add',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->pairview_add}</title>
{$headerinclude}
</head>
<body>
{$header}
<table class="tborder" border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}">
	<tr><td class="thead"><strong>{$lang->pairview_add}</strong></td></tr>
	{$menu}
<tr><td class="trow1">
<form action="misc.php?action=pairview_add" method="post" id="add_pair">
	<table style="margin: auto; width: 80%">
		<tr>
			<td class="tcat" colspan="2"><strong>{$lang->pairview_add_cat}</strong></td>
		</tr>
		<tr>
			<td class="trow1" colspan="2" align="center">
				<select name="kind">
					{$option_cat}
				</select>
			</td>
		</tr>
		<tr>
			<td class="tcat"><strong>{$lang->pairview_add_partner1}</strong></td>
			<td class="tcat"><strong>{$lang->pairview_add_partner2}</strong></td>
		</tr>
		<tr>
			<td class="trow1" align="center">
			<input type="text" name="lover1" id="lover1" value="{$lover1}" class="textbox" size="40" maxlength="1155"  style="min-width: 150px; max-width: 100%;"   />
			</td>
			<td class="trow2" align="center">
		<input type="text" name="lover2" id="lover2" value="{$lover2}" class="textbox" size="40" maxlength="1155" v style="min-width: 150px; max-width: 100%;"   />
			</td>
		</tr>
		{$pic_input}
		<tr>
			<td class="trow1" colspan="2"  align="center">
				<input type="submit" name="add_pair" value="{$lang->pairview_add_submit}" id="submit" class="button">
			</td>
		</tr>
	</table>
	</form></td></tr>	</table>
{$footer}
</body>
</html>

<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/select2/select2.css?ver=1807">
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/select2/select2.min.js?ver=1806"></script>
<script type="text/javascript">
<!--
if(use_xmlhttprequest == "1")
{
    MyBB.select2();
    $("#lover1").select2({
        placeholder: "{$lang->search_user}",
        minimumInputLength: 2,
        maximumSelectionSize: \'\',
        multiple: true,
        ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
            url: "xmlhttp.php?action=get_users",
            dataType: \'json\',
            data: function (term, page) {
                return {
                    query: term, // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to alter remote JSON data
                return {results: data};
            }
        },
        initSelection: function(element, callback) {
            var query = $(element).val();
            if (query !== "") {
                var newqueries = [];
                exp_queries = query.split(",");
                $.each(exp_queries, function(index, value ){
                    if(value.replace(/\s/g, \'\') != "")
                    {
                        var newquery = {
                            id: value.replace(/,\s?/g, ","),
                            text: value.replace(/,\s?/g, ",")
                        };
                        newqueries.push(newquery);
                    }
                });
                callback(newqueries);
            }
        }
    })
}
// -->
</script>

<script type="text/javascript">
<!--
if(use_xmlhttprequest == "1")
{
    MyBB.select2();
    $("#lover2").select2({
        placeholder: "{$lang->search_user}",
        minimumInputLength: 2,
        maximumSelectionSize: \'\',
        multiple: true,
        ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
            url: "xmlhttp.php?action=get_users",
            dataType: \'json\',
            data: function (term, page) {
                return {
                    query: term, // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to alter remote JSON data
                return {results: data};
            }
        },
        initSelection: function(element, callback) {
            var query = $(element).val();
            if (query !== "") {
                var newqueries = [];
                exp_queries = query.split(",");
                $.each(exp_queries, function(index, value ){
                    if(value.replace(/\s/g, \'\') != "")
                    {
                        var newquery = {
                            id: value.replace(/,\s?/g, ","),
                            text: value.replace(/,\s?/g, ",")
                        };
                        newqueries.push(newquery);
                    }
                });
                callback(newqueries);
            }
        }
    })
}
// -->
</script>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'pairview_cat',
        'template' => $db->escape_string('<div class="pairview_cat"><strong>{$cat}</strong></div>
<div class="pairview_pairs">
	{$pairview_pairs}
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

$insert_array = array(
    'title' => 'pairview_pairs',
    'template' => $db->escape_string('<div class="pairview_pair">
    <div class="pairview_lovers_pic">
        <img src="{$pic1}">
    </div>
    <div class="pairview_lovers">
        <div class="lovername">{$lover1_username}</div>
                <div class="lovername">{$lover2_username}</div>
        <div class="pairview_and">&amp;</div>
    </div>
        <div class="pairview_lovers_pic">
        <img src="{$pic2}">
    </div>
{$options}
</div>'),
    'sid' => '-1',
    'version' => '',
    'dateline' => TIME_NOW
);
$db->insert_query("templates", $insert_array);
	
    $insert_array = array(
        'title' => 'pairview_menu',
        'template' => $db->escape_string('<tr>
	<td class="trow2">
		<div class="pairview_flex">
			<div>
				<a href="misc.php?action=pairview">{$lang->pairview_menu_main}</a>
			</div>
			<div>
					<a href="misc.php?action=pairview_add">{$lang->pairview_menu_add}</a>
			</div>
		</div>
	</td>
</tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'pairview_options',
        'template' => $db->escape_string('<div class="pairview_options">
	<a href="misc.php?action=pairview&delete_pair={$pvid}">{$lang->pairview_delete}</a>
<a onclick="$(\'#pv_{$pvid}\').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== \'undefined\' ? modal_zindex : 9999) }); return false;" style="cursor: pointer;" class="postbit_quote postbit_mirage">	
{$lang->pairview_edit}</a>	
</div>

<div class="modal" id="pv_{$pvid}" style="display: none;">
	<form action="misc.php?action=pairview" method="post" id="edit_pair">
		<input value="{$pvid}" type="hidden" name="pvid">
	<table style="margin: auto; width: 80%">
		<tr>
			<td class="tcat" colspan="2"><strong>{$lang->pairview_add_cat}</strong></td>
		</tr>
		<tr>
			<td class="trow1" colspan="2" align="center">
				<select name="kind">
					<option value="{$lovers[\'kind\']}">{$lovers[\'kind\']}</option>
					{$option_cat}
				</select>
			</td>
		</tr>
		<tr>
			<td class="tcat"><strong>{$lang->pairview_add_partner1}</strong></td>
			<td class="tcat"><strong>{$lang->pairview_add_partner2}</strong></td>
		</tr>
		<tr>
			<td class="trow1" align="center">
			<input type="text" name="lover1" id="lover1" value="{$lover1_username}" class="textbox" size="40" maxlength="1155"  style="min-width: 150px; max-width: 100%;"   />
			</td>
			<td class="trow2" align="center">
		<input type="text" name="lover2" id="lover2" value="{$lover2_username}" class="textbox" size="40" maxlength="1155" v style="min-width: 150px; max-width: 100%;"   />
			</td>
		</tr>
		{$pic_input}
		<tr>
			<td class="trow1" colspan="2"  align="center">
				<input type="submit" name="edit_pair" value="{$lang->pairview_edit_submit}" id="submit" class="button">
			</td>
		</tr>
	</table>
	</form>
</div>


<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/select2/select2.css?ver=1807">
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/select2/select2.min.js?ver=1806"></script>
<script type="text/javascript">
<!--
if(use_xmlhttprequest == "1")
{
    MyBB.select2();
    $("#lover1").select2({
        placeholder: "{$lang->search_user}",
        minimumInputLength: 2,
        maximumSelectionSize: \'\',
        multiple: true,
        ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
            url: "xmlhttp.php?action=get_users",
            dataType: \'json\',
            data: function (term, page) {
                return {
                    query: term, // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to alter remote JSON data
                return {results: data};
            }
        },
        initSelection: function(element, callback) {
            var query = $(element).val();
            if (query !== "") {
                var newqueries = [];
                exp_queries = query.split(",");
                $.each(exp_queries, function(index, value ){
                    if(value.replace(/\s/g, \'\') != "")
                    {
                        var newquery = {
                            id: value.replace(/,\s?/g, ","),
                            text: value.replace(/,\s?/g, ",")
                        };
                        newqueries.push(newquery);
                    }
                });
                callback(newqueries);
            }
        }
    })
}
// -->
</script>

<script type="text/javascript">
<!--
if(use_xmlhttprequest == "1")
{
    MyBB.select2();
    $("#lover2").select2({
        placeholder: "{$lang->search_user}",
        minimumInputLength: 2,
        maximumSelectionSize: \'\',
        multiple: true,
        ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
            url: "xmlhttp.php?action=get_users",
            dataType: \'json\',
            data: function (term, page) {
                return {
                    query: term, // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to alter remote JSON data
                return {results: data};
            }
        },
        initSelection: function(element, callback) {
            var query = $(element).val();
            if (query !== "") {
                var newqueries = [];
                exp_queries = query.split(",");
                $.each(exp_queries, function(index, value ){
                    if(value.replace(/\s/g, \'\') != "")
                    {
                        var newquery = {
                            id: value.replace(/,\s?/g, ","),
                            text: value.replace(/,\s?/g, ",")
                        };
                        newqueries.push(newquery);
                    }
                });
                callback(newqueries);
            }
        }
    })
}
// -->
</script>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'pairview_pic_input',
        'template' => $db->escape_string('<tr>
	<td class="trow1" align="center">
		<input type="text" name="pic1" id="pic1" placeholder="https://" class="textbox" value="{$pic1}"/>
		<div class="smalltext">{$pic_desc}</div>
	</td>
	<td class="trow2" align="center">
				<input type="text" name="pic2" id="pic2" placeholder="https://" class="textbox" value="{$pic2}" />
			<div class="smalltext">{$pic_desc}</div>
	</td>
</tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    //CSS einfügen
    $css = array(
        'name' => 'pairview.css',
        'tid' => 1,
        'attachedto' => '',
        "stylesheet" => '.pairview_flex {
	display: flex;
	justify-content: center;
}

.pairview_flex > div{
	padding: 5px 10px;
	margin: 2px 10px;
}


.pairview_cat{
	background: #0f0f0f url(../../../images/tcat.png) repeat-x;
  color: #fff;
  border-top: 1px solid #444;
  border-bottom: 1px solid #000;
  padding: 7px;
}
.pairview_pairs{
	display: flex;
	align-items: center;
}


.pairview_pair{
	width: 45%;
	display: flex;
	justify-content: center;
	align-items: center;
	margin: 10px 20px;
	flex-wrap: wrap;
}

.pairview_lovers_pic{
	height: 100px;
	width: 100px;
	border-radius: 100%;
	padding: 5px;
	text-align: center;	
	border: 1px solid #0066a2;
}

.pairview_lovers_pic img{
		height: 100px;
	width: 100px;
	border-radius: 100%;

}

.pairview_lovers{
	padding: 5px 10px;
	text-align: center;
	position: relative;
	z-index: 2;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
}

.lovername{
	width: 100%;	
}

.pairview_and{
  text-align: center;
  color:#0066a2;
  z-index: 1 !important;
  opacity: .2;
  font-size: 100px;
  position: absolute;
  text-indent: 0px;
  text-transform: uppercase;
}

.pairview_options{
	width: 100%;	
	text-align: center;
}
',
        'cachefile' => $db->escape_string(str_replace('/', '', 'pairview.css')),
        'lastmodified' => time()
    );

    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

    $sid = $db->insert_query("themestylesheets", $css);
    $db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=" . $sid), "sid = '" . $sid . "'", 1);

    $tids = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($tids)) {
        update_theme_stylesheet_list($theme['tid']);
    }



    // Don't forget this!
    rebuild_settings();
}


function pairview_is_installed()
{
    global $db;
    if ($db->table_exists("pairview")) {
        return true;
    }
    return false;
}

function pairview_uninstall()
{
    global $db, $mybb;
    if ($db->table_exists("pairview")) {
        $db->drop_table("pairview");
    }

    $db->delete_query('settings', "name IN ('pairview_kind','pairview_picpf','pairview_picsize', 'pairview_guest')");
    $db->delete_query('settinggroups', "name = 'pairview'");


    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";
    $db->delete_query("themestylesheets", "name = 'pairview.css'");
    $query = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($query)) {
        update_theme_stylesheet_list($theme['tid']);
    }

    $db->delete_query("templates", "title LIKE '%pairview%'");
    // Don't forget this
    rebuild_settings();
}

function pairview_activate()
{
    global $db, $cache;
    //Alertseinstellungen
    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('pairview_alert_add'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('pairview_alert_edit'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);
    }

    require MYBB_ROOT . "/inc/adminfunctions_templates.php";
}

function pairview_deactivate()
{
    global $db, $cache;

    //Alertseinstellungen
    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertTypeManager->deleteByCode('pairview_alert_add');
        $alertTypeManager->deleteByCode('pairview_alert_edit');

    }

    require MYBB_ROOT . "/inc/adminfunctions_templates.php";
}


function pairview_settings_change()
{
    global $db, $mybb, $pairview_settings_peeker;

    $result = $db->simple_select('settinggroups', 'gid', "name='pairview'", array("limit" => 1));
    $group = $db->fetch_array($result);
    if (isset($mybb->input['gid'])) {
        $pairview_settings_peeker = ($mybb->input['gid'] == $group['gid']) && ($mybb->request_method != 'post');
    }
}
function pairview_settings_peek(&$peekers)
{
    global $mybb, $pairview_settings_peeker;

    if ($pairview_settings_peeker) {
        $peekers[] = 'new Peeker($(".setting_pairview_picpf"), $("#row_setting_pairview_picsize"),/0/,true)';
        $peekers[] = 'new Peeker($(".setting_pairview_picpf"), $("#row_setting_pairview_pf"),/1/,true)';

    }
}


// In the body of your plugin
function pairview_misc()
{
    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $lang, $menu, $pairview_cat, $option_cat, $db, $pairview_pairs, $options, $pic_input, $theme;
    $lang->load('pairview');

    // Einstellungen
    $allow_guest = $mybb->settings['pairview_guest'];
    $kind = $mybb->settings['pairview_kind'];
    $picorpf = $mybb->settings['pairview_picpf'];
    $picsize = $mybb->settings['pairview_picsize'];
    $pf = $mybb->settings['pairview_pf'];

    if ($mybb->get_input('action') == 'pairview') {
        add_breadcrumb($lang->pairview, "misc.php?action=pairview");

        if ($allow_guest == 0 && $mybb->user['uid'] == 0) {
            error_no_permission();
        }

        $kind = str_replace(", ", ",", $kind);
        $get_kind = explode(",", $kind);

        foreach ($get_kind as $cat) {
            $pairview_pairs = "";
            $all_lovers = $db->query("SELECT *
            FROM " . TABLE_PREFIX . "pairview
            WHERE kind = '" . $cat . "'
            ");

            while ($lovers = $db->fetch_array($all_lovers)) {
                $lover1 = "";
                $pic1 = "";
                $lover2 = "";
                $pic2 = "";
                $options = "";
                $pvid = 0;
                $option_cat = "";
                $pvid = $lovers['pvid'];

                $lover1_uid = $lovers['lover1'];
                $get_lover1 = get_user($lover1_uid);
                $lover2_uid = $lovers['lover2'];
                $get_lover2 = get_user($lover2_uid);

                $lover1_username = $get_lover1['username'];
                $lover2_username = $get_lover2['username'];
                if ($picorpf == 1) {
                    $pic1 = $db->fetch_field($db->simple_select("userfields", "{$pf}", "ufid = {$lover1_uid}"), $pf);
                    $pic2 = $db->fetch_field($db->simple_select("userfields", "{$pf}", "ufid = {$lover2_uid}"), $pf);
                } else {
                    $pic1 = $lovers['pic1'];
                    $pic2 = $lovers['pic2'];
                }
                if ($mybb->user['uid'] == $lover1_uid or $mybb->user['uid'] == $lover2_uid or $mybb->usergroup['canmodcp'] == 1) {
                    if ($picorpf == 0) {
                        $pic_desc = $lang->sprintf($lang->pairview_add_pic, $picsize);
                        eval ("\$pic_input = \"" . $templates->get("pairview_pic_input") . "\";");
                    }

                    foreach ($get_kind as $kind) {

                        $selected = "";
                        if ($kind == $lovers['kind']) {
                            $selected = "selected";
                        }
                        $option_cat .= "<option value='{$kind}' {$selected}>{$kind}</option>";
                    }

                    eval ("\$options .= \"" . $templates->get("pairview_options") . "\";");
                }



                $username = format_name($get_lover1['username'], $get_lover1['usergroup'], $get_lover1['displaygroup']);
                $lover1 = build_profile_link($username, $get_lover1['uid']);
                $username = format_name($get_lover2['username'], $get_lover2['usergroup'], $get_lover2['displaygroup']);
                $lover2 = build_profile_link($username, $get_lover2['uid']);

                eval ("\$pairview_pairs .= \"" . $templates->get("pairview_pairs") . "\";");
            }

            eval ("\$pairview_cat .= \"" . $templates->get("pairview_cat") . "\";");
        }

        // Pärchen bearbeiten
        if (isset($mybb->input['edit_pair'])) {
            $pvid = $mybb->input['pvid'];
            $kind = $db->escape_string($mybb->input['kind']);
            $lover1 = $mybb->input['lover1'];
            $pic1 = $db->escape_string($mybb->input['pic1']);
            $lover2 = $mybb->input['lover2'];
            $pic2 = $db->escape_string($mybb->input['pic2']);

            $lover_user = get_user_by_username($lover1, array('fields' => '*'));
            $lover1_uid = $lover_user['uid'];

            $lover_user2 = get_user_by_username($lover2, array('fields' => '*'));
            $lover2_uid = $lover_user2['uid'];

            $edit_pair = array(
                "kind" => $kind,
                "lover1" => $lover1_uid,
                "pic1" => $pic1,
                "lover2" => $lover2_uid,
                "pic2" => $pic2
            );
            $creator = $mybb->user['uid'];
            if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('pairview_alert_edit');

                if ($creator == $lover1_uid) {
                    if ($alertType != NULL && $alertType->getEnabled()) {
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int) $lover2_uid, $alertType);
                        $alert->setExtraDetails([
                            'lover1' => $lover1,
                            'lover2' => $lover2
                        ]);
                        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                } elseif ($creator == $lover2_uid) {
                    if ($alertType != NULL && $alertType->getEnabled()) {
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int) $lover1_uid, $alertType);
                        $alert->setExtraDetails([
                            'lover1' => $lover1,
                            'lover2' => $lover2
                        ]);
                        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                } else {
                    $lovers = array(
                        "lover1" => $lover1_uid,
                        "lover2" => $lover2_uid,
                    );

                    foreach ($lovers as $lover) {
                        if ($alertType != NULL && $alertType->getEnabled()) {
                            $alert = new MybbStuff_MyAlerts_Entity_Alert((int) $lover, $alertType);
                            $alert->setExtraDetails([
                                'lover1' => $lover1,
                                'lover2' => $lover2
                            ]);
                            MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                        }

                    }

                }
            }
            $db->update_query("pairview", $edit_pair, "pvid ={$pvid} ");
            redirect("misc.php?action=pairview");
        }

        // Pärchen Löschen
        $delete_pair = $mybb->input['delete_pair'];
        if ($delete_pair) {
            $db->delete_query("pairview", "pvid ={$delete_pair} ");
            redirect("misc.php?action=pairview");
        }

        eval ("\$menu = \"" . $templates->get("pairview_menu") . "\";");
        eval ("\$page = \"" . $templates->get("pairview") . "\";");
        output_page($page);
    }

    if ($mybb->get_input('action') == 'pairview_add') {
        add_breadcrumb($lang->pairview_add, "misc.php?action=pairview_add");

        if ($mybb->user['uid'] == 0) {
            error_no_permission();
        }

        $kind = str_replace(", ", ",", $kind);
        $get_kind = explode(",", $kind);

        foreach ($get_kind as $cat) {
            $option_cat .= "<option value='{$cat}'>{$cat}</option>";
        }
        if ($picorpf == 0) {
            $pic1 = "";
            $pic2 = "";
            $pic_desc = $lang->sprintf($lang->pairview_add_pic, $picsize);
            eval ("\$pic_input = \"" . $templates->get("pairview_pic_input") . "\";");
        }

        $lover1 = "";
        $lover2 = "";
        if (isset($_POST['add_pair'])) {
            $kind = $db->escape_string($_POST['kind']);
            $lover1 = $_POST['lover1'];
            $pic1 = $db->escape_string($_POST['pic1']);
            $lover2 = $_POST['lover2'];
            $pic2 = $db->escape_string($_POST['pic2']);

            $lover_user = get_user_by_username($lover1, array('fields' => '*'));
            $lover1_uid = $lover_user['uid'];

            $lover_user2 = get_user_by_username($lover2, array('fields' => '*'));
            $lover2_uid = $lover_user2['uid'];

            $new_pair = array(
                "kind" => $kind,
                "lover1" => $lover1_uid,
                "pic1" => $pic1,
                "lover2" => $lover2_uid,
                "pic2" => $pic2
            );
            $creator = $mybb->user['uid'];
            if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('pairview_alert_add');

                if ($creator == $lover1_uid) {
                    if ($alertType != NULL && $alertType->getEnabled()) {
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int) $lover2_uid, $alertType);
                        $alert->setExtraDetails([
                            'kind' => $kind,
                            'lover1' => $lover1,
                            'lover2' => $lover2
                        ]);
                        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                } elseif ($creator == $lover2_uid) {
                    if ($alertType != NULL && $alertType->getEnabled()) {
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int) $lover1_uid, $alertType);
                        $alert->setExtraDetails([
                            'kind' => $kind,
                            'lover1' => $lover1,
                            'lover2' => $lover2
                        ]);
                        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                } else {
                    $lovers = array(
                        "lover1" => $lover1_uid,
                        "lover2" => $lover2_uid,
                    );

                    foreach ($lovers as $lover) {
                        if ($alertType != NULL && $alertType->getEnabled()) {
                            $alert = new MybbStuff_MyAlerts_Entity_Alert((int) $lover, $alertType);
                            $alert->setExtraDetails([
                                'kind' => $kind,
                                'lover1' => $lover1,
                                'lover2' => $lover2
                            ]);
                            MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                        }

                    }

                }
            }


            $db->insert_query("pairview", $new_pair);
            redirect("misc.php?action=pairview");

        }

        eval ("\$menu = \"" . $templates->get("pairview_menu") . "\";");
        eval ("\$page = \"" . $templates->get("pairview_add") . "\";");
        output_page($page);
    }
}


// Benachrichtungen generieren
function pairview_alerts()
{
    global $mybb, $lang;
    $lang->load('pairview');


    /**
     * Alert, wenn die pairview angenommen wurde
     */
    class MybbStuff_MyAlerts_Formatter_AddPairFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         *
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->pairview_alert_add,
                $outputAlert['from_user'],
                $alertContent['lover1'],
                $alertContent['lover2'],
                $alertContent['kind'],
                $outputAlert['dateline']
            );
        }


        /**
         * Init function called before running formatAlert(). Used to load language files and initialize other required
         * resources.
         *
         * @return void
         */
        public function init()
        {
        }

        /**
         * Build a link to an alert's content so that the system can redirect to it.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
         *
         * @return string The built alert, preferably an absolute link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/misc.php?action=pairview';
        }
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_AddPairFormatter($mybb, $lang, 'pairview_alert_add')
        );
    }

    /**
     * Alert, wenn die pairview editiert wurde
     */
    class MybbStuff_MyAlerts_Formatter_EditPairFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         *
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->pairview_alert_edit,
                $outputAlert['from_user'],
                $alertContent['lover1'],
                $alertContent['lover2'],
                $outputAlert['dateline']
            );
        }


        /**
         * Init function called before running formatAlert(). Used to load language files and initialize other required
         * resources.
         *
         * @return void
         */
        public function init()
        {
        }

        /**
         * Build a link to an alert's content so that the system can redirect to it.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
         *
         * @return string The built alert, preferably an absolute link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/misc.php?action=pairview';
        }
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_EditPairFormatter($mybb, $lang, 'pairview_alert_edit')
        );
    }

}
