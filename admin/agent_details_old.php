<?php
$cfgProgDir = 'phpSecurePages/';
include($cfgProgDir . "secure.php");

$PHP_SELF = $_SERVER['PHP_SELF'];

/* Set some variables */
//$idx_dir = "/home/idx/";
$idx_dir = "/home/naturmls/public_html/";
$http_home = "http://naturecoastmls.com/";
$mugs = "../agents/";
$http_mugs = $http_home."agents/";
$http_imgs = $http_home."images/";
/* for testing only
$agent_id = ""; */

/* Check for variables */
if (!empty($_POST['agent_id'])) {
   $agent_id = $_POST['agent_id'];
} else {
   $agent_id = $_GET['agent_id'];
}
if (!empty($_POST['dbtosearch'])) {
   $dbtosearch = $_POST['dbtosearch'];
} else {
   $dbtosearch = $_GET['dbtosearch'];
}
if (!empty($_POST['keyword_type'])) {
   $keyword_type = $_POST['keyword_type'];
} else {
   $keyword_type = $_GET['keyword_type'];
}
if (!empty($_POST['keyword'])) {
   $keyword = $_POST['keyword'];
} else {
   $keyword = $_GET['keyword'];
}
if (empty($dbtosearch)) {
   $err_msg = "Missing variable, please go back and select an agent again.";
   $exit++;
}

/* Start HTML */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=windows-1250">
  <meta name="generator" content="PSPad editor, www.pspad.com">
  <title>Agent Details</title>
  </head>
  <body><font face="Arial,Helvetica,sans-serif" size="2">

<?php
echo "  <p><a href='findagent.php?dbtosearch=".$dbtosearch."&keyword=".$keyword."&keyword_type=".$keyword_type."'><< Back to Search</a></p>\n";

/* Display any messages needed */
if (!empty($err_msg)) {
   echo "<div align='center'>$err_msg</div>\n";
}
if ($exit > 0) {
   exit;
}

//Include Db connection script
include '../dbconn.php';

/* Display Agent Details */
if (!empty($agent_id)) {
   /* Insert/Update record as requested */
   if (!empty($_POST['submit_update'])) {
      $sql_update = "UPDATE tbl_agents_info SET 
  		              agent_id='".addslashes($_POST['agent_id'])."',
  		              firm_id='".addslashes($_POST['firm_id'])."',
  		              off_id='".addslashes($_POST['off_id'])."',
  		              title='".addslashes($_POST['title'])."',
  		              phone1='".addslashes($_POST['phone1'])."',
  		              phone2='".addslashes($_POST['phone2'])."',
  		              fax='".addslashes($_POST['fax'])."',
  		              email_leads='".addslashes($_POST['email_leads'])."',
  		              descr='".addslashes($_POST['descr'])."',
  		              website1='".addslashes($_POST['website1'])."',
  		              website2='".addslashes($_POST['website2'])."',
  		              service_level='".addslashes($_POST['service_level'])."',
  		              sort_num='".addslashes($_POST['sort_num'])."',
  		              fname='".addslashes($_POST['fname'])."',
  		              lname='".addslashes($_POST['lname'])."',
  		              credentials='".addslashes($_POST['credentials'])."',
		                email_reports='".addslashes($_POST['email_reports'])."'";
			$sql_update .= " WHERE agent_id='".$_POST['current_agent_id']."'";
			//echo("sql_update: ".$sql_update."<br>");
			if (mysql_query($sql_update)) {
			   echo "<p style='color: #00f;'>Agent updated successfully.</p>\n";
      } else {
				 echo "<p class='error'>Error updating item: " . mysql_error() . "</p>";
			}
   }
   if (!empty($_POST['submit_insert'])) {
      $sql_insert = "INSERT INTO tbl_agents_info SET 
  		              agent_id='".addslashes($_POST['agent_id'])."',
  		              firm_id='".addslashes($_POST['firm_id'])."',
  		              off_id='".addslashes($_POST['off_id'])."',
  		              title='".addslashes($_POST['title'])."',
  		              phone1='".addslashes($_POST['phone1'])."',
  		              phone2='".addslashes($_POST['phone2'])."',
  		              fax='".addslashes($_POST['fax'])."',
  		              email_leads='".addslashes($_POST['email_leads'])."',
  		              descr='".addslashes($_POST['descr'])."',
  		              website1='".addslashes($_POST['website1'])."',
  		              website2='".addslashes($_POST['website2'])."',
  		              service_level='".addslashes($_POST['service_level'])."',
  		              sort_num='".addslashes($_POST['sort_num'])."',
  		              fname='".addslashes($_POST['fname'])."',
  		              lname='".addslashes($_POST['lname'])."',
  		              credentials='".addslashes($_POST['credentials'])."',
		                email_reports='".addslashes($_POST['email_reports'])."'";
			//echo("sql_insert: ".$sql_insert."<br>");
			if (mysql_query($sql_insert)) {
			   echo "<p style='color: #00f;'>Agent inserted successfully.</p>\n";
      } else {
				 echo "<p class='error'>Error inserting item: " . mysql_error() . "</p>";
			}
   }
   
   /* Display requested records */
   switch ($dbtosearch) {
   case 'citrus':
        $db_name = "Citrus MLS";
        $sql1 = "SELECT * FROM tbl_idx_agents WHERE TRIM(MM_AGT_AGT_ID) = '$agent_id'";
   break;
   case 'marion':
        $db_name = "Marion MLS";
        $sql1 = "SELECT * FROM tbl_far_agents WHERE TRIM(Agt_Id) = '$agent_id'";
   break;
   case 'ncwd':
        $db_name = "NCWD";
        $sql1 = "SELECT * FROM tbl_agents_info WHERE agent_id = '$agent_id'";
   break;
   }
   $result1 = mysql_query($sql1);
   if (!$result1) {
      echo "<p>Error performing query: ".mysql_error()."</p>\n";
      echo "sql1: $sql1<br />\n";
   }
   $num_fields1 = mysql_num_fields($result1);
   $sql2 = "SELECT * FROM tbl_agents_info WHERE agent_id = '$agent_id'";
   $result2 = mysql_query($sql2);
   if (!$result2) {
      echo "<p>Error performing query: ".mysql_error()."</p>\n";
      echo "sql2: $sql2<br />\n";
   }
   $num_rows2 = mysql_num_rows($result2);
   echo "<table width='780' border='0' cellpadding='2' cellspacing='2'>\n";
   echo " <tr>\n";
   echo "  <td valign='top'>\n";
   echo "  <p style='font-size: 15px; font-weight: bold;'>Agent Details from the $db_name Db</p>\n";
   while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
         $row1_keys = array_keys($row1);
         echo "   <table border='0' cellpadding='2' cellspacing='2' style='border: 1px solid #000;'>\n";
         /* Debugging Info
         print_r($row1);
         echo "<br /><br />\n";
         print_r($row1_keys);
         echo "<br /><br />\n"; */
         for ($i = 0; $i <= $num_fields1; $i++) {
             echo "   <tr>\n";
             echo "    <td style='font-size: 14px; font-weight: bold;'>".$row1_keys[$i].": </td>\n";
             echo "    <td style='font-size: 14px;'>".$row1[$row1_keys[$i]]."</td>\n";
             echo "   </tr>\n";
             //echo $row1_keys[$i].": ".$row1[$row1_keys[$i]]."<br />\n";
         }
         echo "   </table>\n";
   }
   echo "  </td>\n";
   echo "  <td valign='top'>\n";
   $row2 = mysql_fetch_array($result2);
         echo "   <p style='font-size: 15px; font-weight: bold;'>Agent Record in NCWD Db</p>\n";
         echo "   <form action='$PHP_SELF' method='post' name='ncwd_update'>\n";
         echo "   <input type='hidden' name='current_agent_id' value='".$row2['agent_id']."' />\n";
         echo "   <input type='hidden' name='dbtosearch' value='$dbtosearch' />\n";
         echo "   <input type='hidden' name='keyword' value='".$keyword."' />\n";
         echo "   <input type='hidden' name='keyword_type' value='".$keyword_type."' />\n";
         echo "   <table border='0' cellpadding='2' cellspacing='2' style='border: 1px solid #000;'>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>agent_id: </td>\n";
         echo "    <td><input type='text' name='agent_id' size='20' value='".$row2['agent_id']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>firm_id: </td>\n";
         echo "    <td><input type='text' name='firm_id' size='20' value='".$row2['firm_id']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>off_id: </td>\n";
         echo "    <td><input type='text' name='off_id' size='20' value='".$row2['off_id']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>title: </td>\n";
         echo "    <td><input type='text' name='title' size='20' value='".$row2['title']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>fname: </td>\n";
         echo "    <td><input type='text' name='fname' size='20' value='".$row2['fname']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>lname: </td>\n";
         echo "    <td><input type='text' name='lname' size='20' value='".$row2['lname']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>credentials: </td>\n";
         echo "    <td><input type='text' name='credentials' size='20' value='".$row2['credentials']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>phone1: </td>\n";
         echo "    <td><input type='text' name='phone1' size='20' value='".$row2['phone1']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>phone2: </td>\n";
         echo "    <td><input type='text' name='phone2' size='20' value='".$row2['phone2']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>fax: </td>\n";
         echo "    <td><input type='text' name='fax' size='20' value='".$row2['fax']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>email_leads: </td>\n";
         echo "    <td><input type='text' name='email_leads' size='20' value='".$row2['email_leads']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>email_reports: </td>\n";
         echo "    <td><input type='text' name='email_reports' size='20' value='".$row2['email_reports']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td colspan='2' style='font-size: 14px; font-weight: bold;'>descr: <br />\n";
         echo "     <textarea name='descr' rows='4' cols='25'>".$row2['descr']."</textarea></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>website1: </td>\n";
         echo "    <td><input type='text' name='website1' size='20' value='".$row2['website1']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>website2: </td>\n";
         echo "    <td><input type='text' name='website2' size='20' value='".$row2['website2']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>service_level: </td>\n";
         echo "    <td><input type='text' name='service_level' size='20' value='".$row2['service_level']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td style='font-size: 14px; font-weight: bold;'>sort_num: </td>\n";
         echo "    <td><input type='text' name='sort_num' size='4' value='".$row2['sort_num']."' /></td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td colspan='2' style='font-size: 14px; font-weight: bold;'>";
         if (file_exists($mugs.'agent'.$row2['agent_id'].'.jpg')) {
            echo "<img src='".$mugs."agent".$row2['agent_id'].".jpg' alt='agent photo on file' border='0' />";
         }
         echo "</td>\n";
         echo "   </tr>\n";
         echo "   <tr>\n";
         echo "    <td align='right'>";
         if ($num_rows2 > 0) {
            echo "<input type='submit' name='submit_update' value='Update' />";
         } else {
            echo "<input type='submit' name='submit_insert' value='Insert' />";
         }
         echo "    </td>\n";
         echo "    <td align='left'><input type='reset' name='reset' value='Reset' /></td>\n";
         echo "   </tr>\n";
         echo "   </table>\n";
         echo "   </form>\n";
   
   echo "  </td>\n";
   echo " </tr>\n";
   echo " <tr>\n<td colspan='2'><hr size='1' noshade /></td>\n</tr>\n";
   echo "</table>\n";
   
   /* Debugging info
   echo "sql1: $sql1<br />\n";
   echo "sql2: $sql2<br />\n"; */
}

/* End HTML */
?>
  </font>
  </body>
</html>
<?php

/* Close out the result sets */
if (!empty($result1)) {
   mysql_free_result($result1);
}
if (!empty($result1)) {
   mysql_free_result($result2);
}
/* Closing connection */
mysql_close($dbcnx);
?>
