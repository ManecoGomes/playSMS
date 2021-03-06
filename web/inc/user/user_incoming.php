<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

switch ($op)
{
	case "user_incoming":
		$db_query = "SELECT count(*) as count FROM "._DB_PREF_."_tblSMSIncoming WHERE in_uid='$uid' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$nav = themes_nav($db_row['count'], "index.php?app=menu&inc=user_incoming&op=user_incoming");

		$content = "
	    <h2>"._('Incoming SMS')."</h2>
	    <p>".$nav['form']."</p>
	    <form name=\"fm_incoming\" action=\"index.php?app=menu&inc=user_incoming&op=act_del\" method=post onSubmit=\"return SureConfirm()\">
	    <table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
        <thead>
	    <tr>
	      <th align=center width=4>*</th>
	      <th align=center width=20%>"._('Time')."</th>
	      <th align=center width=10%>"._('From')."</th>
	      <th align=center width=10%>"._('Keyword')."</th>
	      <th align=center width=40%>"._('Content')."</th>
	      <th align=center width=10%>"._('Feature')."</th>
	      <th align=center width=10%>"._('Status')."</th>
	      <th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_incoming)></td>
	    </tr>
        </thead>
        <tbody>
	";

		$db_query = "SELECT * FROM "._DB_PREF_."_tblSMSIncoming WHERE in_uid='$uid' AND flag_deleted='0' ORDER BY in_id DESC LIMIT ".$nav['limit']." OFFSET ".$nav['offset'];
		$db_result = dba_query($db_query);
		$i = $nav['top'];
		$j = 0;
		while ($db_row = dba_fetch_array($db_result))
		{
			$in_message = core_display_text($db_row['in_message'], 25);
			$db_row = core_display_data($db_row);
			$j++;
			$in_id = $db_row['in_id'];
			$in_sender = $db_row['in_sender'];
			$p_desc = phonebook_number2name($in_sender);
			$current_sender = $in_sender;
			if ($p_desc)
			{
				$current_sender = "$in_sender<br>($p_desc)";
			}
			$in_keyword = $db_row['in_keyword'];
			$in_datetime = core_display_datetime($db_row['in_datetime']);
			$in_feature = $db_row['in_feature'];
			$in_status = ( $db_row['in_status'] == 1 ? '<p><font color=green>'._('handled').'</font></p>' : '<p><font color=red>'._('unhandled').'</font></p>' );
			$i--;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
		<tr>
	          <td valign=top class=$td_class align=left>$i.</td>
	          <td valign=top class=$td_class align=center>$in_datetime</td>
	          <td valign=top class=$td_class align=center>$current_sender</td>
	          <td valign=top class=$td_class align=center>$in_keyword</td>
	          <td valign=top class=$td_class align=left>$in_message</td>
	          <td valign=top class=$td_class align=center>$in_feature</td>
	          <td valign=top class=$td_class align=center>$in_status</td>
		<td class=$td_class width=4>
		    <input type=hidden name=inid".$j." value=\"$in_id\">
		    <input type=checkbox name=chkid".$j.">
		</td>		  
		</tr>
	    ";
		}
		$item_count = $j;
		$content .= "
    </tbody>
    </table>
	<table width=100% cellpadding=0 cellspacing=0 border=0>
	<tr>
	    <td width=100% colspan=2 align=right>
		<input type=hidden name=item_count value=\"$item_count\">
		<input type=submit value=\""._('Delete selection')."\" class=button />
	    </td>
	</tr>
	</table>	    
    </form>
    <p>".$nav['form']."</p>
    ";
		if ($err = $_SESSION['error_string'])
		{
			echo "<div class=error_string>$err</div><br><br>";
		}
		echo $content;
		break;
	case "user_incoming_del":
		$_SESSION['error_string'] = _('Fail to delete incoming SMS');
		if ($in_id = $_REQUEST['inid'])
		{
			$db_query = "UPDATE "._DB_PREF_."_tblSMSIncoming SET c_timestamp='".mktime()."',flag_deleted='1' WHERE in_uid='$uid' AND in_id='$in_id'";
			$db_result = dba_affected_rows($db_query);
			if ($db_result > 0)
			{
				$_SESSION['error_string'] = _('Selected incoming SMS has been deleted');
			}
		}
		header("Location: index.php?app=menu&inc=user_incoming&op=user_incoming");
		exit();
		break;
	case "act_del":
		$item_count = $_POST['item_count'];

		for ($i=1;$i<=$item_count;$i++)
		{
			$chkid = $_POST['chkid'.$i];
			$inid = $_POST['inid'.$i];

			if(($chkid=="on") && $inid)
			{
				$db_query = "UPDATE "._DB_PREF_."_tblSMSIncoming SET c_timestamp='".mktime()."',flag_deleted='1' WHERE in_uid='$uid' AND in_id='$inid'";
				$db_result = dba_affected_rows($db_query);
			}
		}
		$_SESSION['error_string'] = _('Selected incoming SMS has been deleted');
		header("Location: index.php?app=menu&inc=user_incoming&op=user_incoming");
		exit();
		break;
}

?>