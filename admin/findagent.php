<?php
$PHP_SELF = $_SERVER['PHP_SELF'];

/* Set some variables */
//$idx_dir = "/home/idx/";
$idx_dir = "/home/mychurchserver/domains/citrusmls.mychurchserver.com/public_html/";
$http_home = "http://citrusmls.mychurchserver.com/";
$http_mugs = $http_home."agents/";
$http_imgs = $http_home."images/";
/* for testing only
$agent_id = ""; */

/* Check for variables */
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
if (empty($keyword)) {
   $err_msg = 'No search term provided, please try your search again with a search term.';
   $exit++;
}
if (empty($keyword_type)) {
   $keyword_type = 'lname';
}
if (empty($dbtosearch)) {
   $dbtosearch = 'ncwd';
}

/* Start HTML */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=windows-1250">
  <meta name="generator" content="PSPad editor, www.pspad.com">
  <title>Find an Agent</title>
  </head>
  <body><font face="Arial,Helvetica,sans-serif" size="2">
<?php
/* Display search box */
echo "<form action='$PHP_SELF' method='post' name='search'>\n";
echo "Search <input type='radio' name='dbtosearch' value='citrus'";
if (empty($dbtosearch) || $dbtosearch == 'citrus') { echo " checked"; }
echo " />Citrus MLS <input type='radio' name='dbtosearch' value='marion'";
if ($dbtosearch == 'marion') { echo " checked"; }
echo " />Marion MLS <input type='radio' name='dbtosearch' value='ncwd'";
if ($dbtosearch == 'ncwd') { echo " checked"; }
echo " />NCWD Db<br />\n";
echo "By <input type='radio' name='keyword_type' value='agent_id'";
if ($keyword_type == 'agent_id') { echo " checked"; }
echo " />Agent ID <input type='radio' name='keyword_type' value='firm_id'";
if (empty($keyword_type) || $keyword_type == 'firm_id') { echo " checked"; }
echo " />Firm ID <input type='radio' name='keyword_type' value='lname'";
if ($keyword_type == 'lname') { echo " checked"; }
echo " />Last Name<br />\n";
echo "<input type='text' size='15' name='keyword' value='$keyword' /> <input type='submit' name='submit' value='Search' />\n";
echo "</form>\n";
echo "<hr size='1' noshade />\n";

/* Display any messages needed */
if (!empty($err_msg)) {
   echo "<div align='center'>$err_msg</div>\n";
}
if ($exit > 0) {
   exit;
}

//Include Db connection script
include '../dbconn.php';

/* Display Search Results */
if (!empty($keyword)) {
   switch ($dbtosearch) {
   case 'citrus':
        $db_name = "Citrus MLS";
        $sql = "SELECT MM_AGT_AGT_ID,CONCAT(MM_AGT_LNAME,', ',MM_AGT_FNAME),MM_AGT_FRM_ID,MM_OFF_NAME,MM_AGT_OFF_ID FROM tbl_idx_agents";
        switch ($keyword_type) {
        case 'agent_id':
             $sql .= " WHERE TRIM(MM_AGT_AGT_ID) = '$keyword' ORDER BY MM_AGT_OFF_ID,MM_AGT_LNAME";
        break;
        case 'firm_id':
             $sql .= " WHERE TRIM(MM_AGT_FRM_ID) = '$keyword' ORDER BY MM_AGT_OFF_ID,MM_AGT_LNAME";
        break;
        case 'lname':
             $sql .= " WHERE MM_AGT_LNAME Like '$keyword' ORDER BY MM_AGT_OFF_ID,MM_AGT_LNAME";
        break;
        }
        $agent_fname_field = 'MM_AGT_FNAME';
        $agent_lname_field = 'MM_AGT_LNAME';
        $agent_id_field = 'MM_AGT_AGT_ID';
        $firm_id_field = 'MM_AGT_FRM_ID';
        $firm_name_field = 'MM_OFF_NAME';
        $office_id_field = 'MM_AGT_OFF_ID';
   break;
   case 'marion':
        $db_name = "Marion MLS";
        $sql = "SELECT Agt_Id,Agt_Name,Agt_Firm,Office_Name,Off_Id FROM tbl_far_agents";
        switch ($keyword_type) {
        case 'agent_id':
             $sql .= " WHERE TRIM(Agt_Id) = '$keyword' ORDER BY Off_Id,Agt_Name";
        break;
        case 'firm_id':
             $sql .= " WHERE TRIM(Agt_Firm) = '$keyword' ORDER BY Off_Id,Agt_Name";
        break;
        case 'lname':
             $sql .= " WHERE Agt_Name Like '%$keyword%' ORDER BY Off_Id,Agt_Name";
        break;
        }
        $agent_name_field = 'Agt_Name';
        $agent_id_field = 'Agt_Id';
        $firm_id_field = 'Agt_Firm';
        $firm_name_field = 'Office_Name';
        $office_id_field = 'Off_Id';
   break;
   case 'ncwd':
        $db_name = "NCWD";
        $sql = "SELECT agent.agent_id,CONCAT(agent.lname,', ',agent.fname),agent.firm_id,firm.name,agent.off_id,firm.firm_id FROM tbl_agents_info AS agent, tbl_firms_info AS firm";
        switch ($keyword_type) {
        case 'agent_id':
             $sql .= " WHERE agent.firm_id = firm.firm_id AND agent.agent_id = '$keyword' ORDER BY agent.off_id,agent.lname";
        break;
        case 'firm_id':
             $sql .= " WHERE agent.firm_id = firm.firm_id AND agent.firm_id = '$keyword' ORDER BY agent.off_id,agent.lname";
        break;
        case 'lname':
             $sql .= " WHERE agent.firm_id = firm.firm_id AND agent.lname Like '$keyword' ORDER BY agent.off_id,agent.lname";
        break;
        }
        $agent_fname_field = 'fname';
        $agent_lname_field = 'lname';
        $agent_id_field = 'agent_id';
        $firm_id_field = 'firm_id';
        $firm_name_field = 'name';
        $office_id_field = 'off_id';
   break;
   }
   $result = mysql_query($sql);
   if (!$result) {
      echo "<p>Error performing query: ".mysql_error()."</p>\n";
      echo "sql: $sql<br />\n";
   }
   echo "<p style='font-size: 16px;'><b>Search Results from the $db_name Db</b></p>\n";
   echo "<table width='780' border='0' cellpadding='2' cellspacing='2'>\n";
   echo " <tr>\n";
   echo "  <td><b>Agent Name</b></td>\n";
   echo "  <td><b>Agent ID</b></td>\n";
   echo "  <td><b>Firm Name</b></td>\n";
   echo "  <td><b>Firm ID</b></td>\n";
   echo "  <td><b>Office ID</b></td>\n";
   echo " </tr>\n";
   echo " <tr>\n<td colspan='5'><hr size='1' noshade /></td>\n</tr>\n";
   while ($row = mysql_fetch_array($result)) {
         echo " <tr>\n";
         echo "  <td><a href='agent_details.php?agent_id=".trim($row[0])."&dbtosearch=".$dbtosearch."&keyword=".$keyword."&keyword_type=".$keyword_type."'>".$row[1]."</a></td>\n";
         echo "  <td>".$row[0]."</td>\n";
         echo "  <td>".$row[3]."</td>\n";
         echo "  <td>".$row[2]."</td>\n";
         echo "  <td>".$row[4]."</td>\n";
         echo " </tr>\n";
   }
   echo " <tr>\n<td colspan='5'><hr size='1' noshade /></td>\n</tr>\n";
   echo "</table>\n";
   
   /* Debugging info
   echo "sql: $sql<br />\n"; */
}

/* End HTML */
?>
  </font>
  </body>
</html>
<?php

/* Close out the result sets */
mysql_free_result($result);
/* Closing connection */
mysql_close($dbcnx);
?>
