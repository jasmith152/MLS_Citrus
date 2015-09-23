<?php
// Establish GET & POST variables
import_request_variables("gp");
$PHP_SELF = $_SERVER['PHP_SELF'];

//Include Db connection script
include 'dbconn.php';
$debugging = false;

/* Set some variables */
$idx_dir = "/home/naturmls/public_html/";
$http_home = "http://naturecoastmls.com/";

/* Clear certain variables */
unset($str_url_vars);
$str_url_var_num = 0;
unset($where_clause);
$where_clause_num = 0;
unset($order_clause);
$order_clause_num = 0;
unset($order_clause_res);
$order_clause_res_num = 0;

/* Check for variables */
if (empty($agent_id) && empty($firm_id) && empty($display_agent) && empty($display_firm)) {
   $err_msg .= "<span class='err_msg'>No Firm or Agent selected.</span>\n";
   $exit = 1;
} else {
   /* Verify agent is allowed to use our system */
   include 'allow_firms.php';
   include 'allow_agents.php';
   if (!stristr($allow_agents,$agent_id) && !stristr($allow_agents,$display_agent) && !stristr($allow_firms,$firm_id) && !stristr($allow_firms,$display_firm)) {
      $err_msg .= "<span class='err_msg'>We are sorry, but this website is not authorized to use this feature.</span>\n";
      $exit = 1;
   }
}
if (empty($show) || $show == 'all') {
   $show = "residential,vacant land,multires,commercial,business op";
}

if (isset($agent_id)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "agent_id=$agent_id";
   $str_url_var_num++;
}
if (isset($display_agent)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "display_agent=$display_agent";
   $str_url_var_num++;
}
if (isset($firm_id)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "firm_id=$firm_id";
   $str_url_var_num++;
}
if (isset($display_firm)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "display_firm=$display_firm";
   $str_url_var_num++;
}
if (isset($office_id)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "office_id=$office_id";
   $str_url_var_num++;
}
if (isset($display_office)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "display_office=$display_office";
   $str_url_var_num++;
}
if (isset($require_login)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "require_login=$require_login";
   $str_url_var_num++;
}
if (isset($login_fields)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "login_fields=$login_fields";
   $str_url_var_num++;
}

if (empty($bgcolor1)) {
   $bgcolor1 = '#ccc';
}
if (empty($bgcolor2)) {
   $bgcolor2 = '#999';
}

/* Construct Where clause */
if (!empty($agent_id)) {
   if ($where_clause_num > 0) { $where_clause .= " AND"; }
   $where_clause .= " TRIM(ListingAgentID) = '".$agent_id."'";
   $where_clause_num++;
}
if (!empty($firm_id)) {
   if ($where_clause_num > 0) { $where_clause .= " AND"; }
   $where_clause .= " TRIM(ListingFirmID) = '".$firm_id."'";
   $where_clause_num++;
   if (isset($office_id)) {
      $where_clause .= " AND TRIM(ListingOfficeID) = '".$office_id."'";
      $where_clause_num++;
   }
}
if (!empty($listingsin_city)) {
   if (stripos($listingsin_city,"|")) {
   $cities_array = explode("|", $listingsin_city);
   foreach ($cities_array as $list_key => $city_value) {
           if ($where_clause_num > 0) {
              if ($list_key < 1) {
                 $where_clause .= " AND (";
              } else {
              	 $where_clause .= " OR";
              }
           } else {
              $where_clause .= " (";
           }
           $where_clause .= " $db_city = '".$city_value."'";
           $where_clause_num++;
   }
   $where_clause .= ")";
   } else {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " $db_city = '".$listingsin_city."'";
      $where_clause_num++;
   }
}
if (!empty($listingsin_subdivision)) {
   if (stripos($listingsin_subdivision,"|")) {
   $subdivisions_array = explode("|", $listingsin_subdivision);
   foreach ($subdivisions_array as $list_key => $subdivision_value) {
           if ($where_clause_num > 0) {
              if ($list_key < 1) {
                 $where_clause .= " AND (";
              } else {
              	 $where_clause .= " OR";
              }
           } else {
              $where_clause .= " (";
           }
           $where_clause .= " $db_subdivision = '".$subdivision_value."'";
           $where_clause_num++;
   }
   $where_clause .= ")";
   } else {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " $db_subdivision = '".$listingsin_subdivision."'";
      $where_clause_num++;
   }
}
if (!empty($waterfront_yn)) {
   if ($where_clause_num > 0) { $where_clause .= " AND"; }
   $where_clause .= " $db_waterfront_yn = '".$waterfront_yn."'";
   $where_clause_num++;
}
if (!empty($specific_listing)) {
   $mls_ids_array = explode("|", $specific_listing);
   foreach ($mls_ids_array as $list_key => $mls_id) {
           if ($where_clause_num > 0) {
              if ($list_key < 1) {
                 $where_clause .= " AND (";
              } else {
              	 $where_clause .= " OR ";
              }
           }
           $where_clause .= "ListingID = '".$mls_id."'";
           $where_clause_num++;
   }
   $where_clause .= ")";
}
if (!empty($where_custom)) {
   if ($where_clause_num > 0) { $where_clause .= " AND"; }
   $where_clause .= $where_custom;
   $where_clause_num++;
}
if (!empty($where_clause)) {
   $where_clause = " WHERE".$where_clause;
} else {
   // If no search criteria and no limit is specified, then all listings would be displayed (and probably crash the server)
   if (empty($limit) && empty($limit_residential) && empty($limit_vacantland) && empty($limit_multires) && empty($limit_commercial) && empty($limit_businessop)) {
      $err_msg .= "<span class='err_msg'>No search criteria specified.</span>\n";
      $exit = 1;
   }
}

/* Construct Order clause */
switch ($sort_order) {
	 case 'none':
	    $order_clause_res = "";
      $order_clause = "";
   break;
   case 'price_low':
      // property type, then price (low to high)
      $order_clause_res = " ORDER BY ListPrice DESC";
      $order_clause = " ORDER BY ListPrice";
   break;
   default:
      // property type, then price (high to low)
      $order_clause_res = " ORDER BY ListPrice DESC";
      $order_clause = " ORDER BY ListPrice DESC";
   break;
}

/* Construct Limit clause */
if (!empty($limit_residential)) {
   $limit_residential = " LIMIT $limit_residential";
}
if (!empty($limit_vacantland)) {
   $limit_vacantland = " LIMIT $limit_vacantland";
}
if (!empty($limit_multires)) {
   $limit_multires = " LIMIT $limit_multires";
}
if (!empty($limit_commercial)) {
   $limit_commercial = " LIMIT $limit_commercial";
}
if (!empty($limit_businessop)) {
   $limit_businessop = " LIMIT $limit_businessop";
}
if (!empty($limit_rental)) {
   $limit_rental = " LIMIT $limit_rental";
}
if (!empty($limit)) {
   $limit_residential = " LIMIT $limit";
   $limit_vacantland = " LIMIT $limit";
   $limit_multires = " LIMIT $limit";
   $limit_commercial = " LIMIT $limit";
   $limit_businessop = " LIMIT $limit";
   $limit_rental = " LIMIT $limit";
}

/* Display any messages needed */
if (!empty($err_msg)) {
   echo "<div align='center'>$err_msg</div>\n";
}
if ($exit > 0) {
   exit;
}

if (stristr($show,"residential") && !stristr($hide,"residential")) {
/* Residential Listings */
$sql1 = "SELECT * FROM tbl_idx_residential".$where_clause.$order_clause_res.$limit_residential;
if ($debugging) {
   echo "<p class='debugging'>\n";
   echo "where_clause_num: $where_clause_num<br />\n";
   echo "sql1: $sql1<br />\n";
   echo "</p>\n";
}
$result1 = $dbcnx->prepare($sql1);
//$result->bindParam(':mls_id', $mls_id, PDO::PARAM_INT);
$result1->execute($where_clause_arr);
$num_rows1 = count($data_results1);
/* Debugging info */
if ($debugging) {
   echo "<p class='debugging'>\n";
   echo "num_rows1: $num_rows1<br />\n";
   echo "str_url_vars: $str_url_vars<br />\n";
   echo "</p>\n";
}

/* Add mls numbers to the variable */
while ($row1 = $result1->fetchAll(PDO::FETCH_ASSOC)) {
   if (strlen($specific_listing > 1)) { $specific_listing .= "|"; }
   $specific_listing .= $row1['ListingID'];
}

} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql1: $sql1<br />\n";
}
/* End of Residential Listings */

if (stristr($show,"vacant land") && !stristr($hide,"vacant land")) {
/* Vacant Land Listings */
$sql2 = "SELECT * FROM tbl_idx_vacant_land".$where_clause.$order_clause.$limit_vacantland;
if ($debugging !== false) {
   echo "<p class='debugging'>\n";
   echo "where_clause_num: $where_clause_num<br />\n";
   echo "sql1: $sql1<br />\n";
   echo "</p>\n";
}
$result1 = $dbcnx->prepare($sql1);
//$result->bindParam(':mls_id', $mls_id, PDO::PARAM_INT);
$result1->execute($where_clause_arr);
$num_rows1 = count($data_results1);
/* Debugging info */
if ($debugging !== false) {
   echo "<p class='debugging'>\n";
   echo "num_rows1: $num_rows1<br />\n";
   echo "str_url_vars: $str_url_vars<br />\n";
   echo "</p>\n";
}

/* Add mls numbers to the variable */
while ($row2 = $result1->fetchAll(PDO::FETCH_ASSOC)) {
   if (strlen($specific_listing > 1)) { $specific_listing .= "|"; }
   $specific_listing .= $row2['ListingID'];
}

} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql2: $sql2<br />\n";
}
/* End of Vacant Land Listings */

if (stristr($show,"multires") && !stristr($hide,"multires")) {
/* Multi Residential Listings */
$sql3 = "SELECT * FROM tbl_idx_multi_res".$where_clause.$order_clause.$limit_multires;
if ($debugging !== false) {
   echo "<p class='debugging'>\n";
   echo "where_clause_num: $where_clause_num<br />\n";
   echo "sql1: $sql1<br />\n";
   echo "</p>\n";
}
$result1 = $dbcnx->prepare($sql1);
//$result->bindParam(':mls_id', $mls_id, PDO::PARAM_INT);
$result1->execute($where_clause_arr);
$num_rows1 = count($data_results1);
/* Debugging info */
if ($debugging !== false) {
   echo "<p class='debugging'>\n";
   echo "num_rows1: $num_rows1<br />\n";
   echo "str_url_vars: $str_url_vars<br />\n";
   echo "</p>\n";
}

/* Add mls numbers to the variable */
while ($row3 = $result1->fetchAll(PDO::FETCH_ASSOC)) {
   if (strlen($specific_listing > 1)) { $specific_listing .= "|"; }
   $specific_listing .= $row3['ListingID'];
}

} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql3: $sql3<br />\n";
}
/* End of Multi Residential Listings */

if (stristr($show,"commercial") && !stristr($hide,"commercial")) {
/* Commercial Listings */
$sql4 = "SELECT * FROM tbl_idx_commercial".$where_clause.$order_clause.$limit_commercial;
if ($debugging !== false) {
   echo "<p class='debugging'>\n";
   echo "where_clause_num: $where_clause_num<br />\n";
   echo "sql1: $sql1<br />\n";
   echo "</p>\n";
}
$result1 = $dbcnx->prepare($sql1);
//$result->bindParam(':mls_id', $mls_id, PDO::PARAM_INT);
$result1->execute($where_clause_arr);
$num_rows1 = count($data_results1);
/* Debugging info */
if ($debugging !== false) {
   echo "<p class='debugging'>\n";
   echo "num_rows1: $num_rows1<br />\n";
   echo "str_url_vars: $str_url_vars<br />\n";
   echo "</p>\n";
}

/* Add mls numbers to the variable */
while ($row4 = $result1->fetchAll(PDO::FETCH_ASSOC)) {
   if (strlen($specific_listing > 1)) { $specific_listing .= "|"; }
   $specific_listing .= $row4['ListingID'];
}

} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql4: $sql4<br />\n";
}
/* End of Commercial Listings */

if (stristr($show,"business op") && !stristr($hide,"business op")) {
/* Business Op Listings */
$sql5 = "SELECT * FROM tbl_idx_business_op".$where_clause.$order_clause.$limit_businessop;
if ($debugging !== false) {
   echo "<p class='debugging'>\n";
   echo "where_clause_num: $where_clause_num<br />\n";
   echo "sql1: $sql1<br />\n";
   echo "</p>\n";
}
$result1 = $dbcnx->prepare($sql1);
//$result->bindParam(':mls_id', $mls_id, PDO::PARAM_INT);
$result1->execute($where_clause_arr);
$num_rows1 = count($data_results1);
/* Debugging info */
if ($debugging !== false) {
   echo "<p class='debugging'>\n";
   echo "num_rows1: $num_rows1<br />\n";
   echo "str_url_vars: $str_url_vars<br />\n";
   echo "</p>\n";
}

/* Add mls numbers to the variable */
while ($row5 = $result1->fetchAll(PDO::FETCH_ASSOC)) {
   if (strlen($specific_listing > 1)) { $specific_listing .= "|"; }
   $specific_listing .= $row5['ListingID'];
}

} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql5: $sql5<br />\n";
}
/* End of Business Op Listings */

/* Clear certain variables */
unset($str_url_vars);
$str_url_var_num = 0;
unset($where_clause);
$where_clause_num = 0;
unset($order_clause);
$order_clause_num = 0;
unset($order_clause_res);
$order_clause_res_num = 0;
unset($limit);
unset($limit_residential);
unset($limit_vacantland);
unset($limit_multires);
unset($limit_commercial);
unset($limit_businessop);
unset($show);
unset($hide);
unset($sql1);
unset($row1);
unset($sql2);
unset($row2);
unset($sql3);
unset($row3);
unset($sql4);
unset($row4);
unset($sql5);
unset($row5);

/* Close out the result set */
if (!empty($result1)) { $result1 = 'null'; }
if (!empty($result2)) { $result2 = 'null'; }
if (!empty($result3)) { $result3 = 'null'; }
if (!empty($result4)) { $result4 = 'null'; }
if (!empty($result5)) { $result5 = 'null'; }
/* Closing connection */
$dbcnx = 'null';
?>
