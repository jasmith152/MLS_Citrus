<?php
// Establish GET & POST variables
import_request_variables("gp");
$PHP_SELF = $_SERVER['PHP_SELF'];

//Include Db connection script
include 'dbconn.php';
$debugging = true;

/* Set some variables */
$idx_dir = "/home/naturmls/public_html/";
$http_home = "http://naturecoastmls.com/";
$http_photos = $http_home."photos/";
$http_imgs = $http_home."images/";
/* for testing only
$agent_id = ""; //for displaying only listings for this agent
$firm_id = ""; //for displaying only listings for this firm
$office_id = ""; //for displaying only listings for a specific office of this firm
$display_agent = ""; //for displaying contact info on details page
$display_firm = ""; //for displaying contact info on details page
$specific_listing = ""; //for displaying only a specific listing (for lists seperate with |)
$limit = ""; //for limiting number of results (also can use $limit_[property_type])
$show = ""; //for displaying only specific property types
$hide = ""; //for hiding specific property types
$sort_order = ""; //for specifying a sort order (duh)
$random = ""; //use 1 to select a listing randomly
 */
$str_url_vars = "";
$str_url_var_num = 0;
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
$result1 = $dbcnx->prepare($sql1);
try {
   $result1->execute($where_clause_arr);
} catch(PDOException $e) {
   $err_msg .= "Db Error: ".$e->getMessage()."<br />\n";
   /* Debugging info */
	$err_msg .=  "<p class='debugging'>\n";
	$err_msg .=  "num_rows1: $num_rows1<br />\n";
	$err_msg .=  "str_url_vars: $str_url_vars<br />\n";
	$err_msg .=  "</p>\n";
}

$prev_prop_type = 'Residential';
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
while ($row1 = $result1->fetchAll(PDO::FETCH_ASSOC)) {
      echo "<tr>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_residential.php?mls_id=".$row1['ListingID']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row1['ListingID']."1.jpg")) {
         echo "<img src='".$http_photos.$row1['ListingID']."1.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row1['ListingID']."2.jpg")) {
            echo "<img src='".$http_photos.$row1['ListingID']."2.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row1['ListingID']."3.jpg")) {
               echo "<img src='".$http_photos.$row1['ListingID']."3.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row1['ListingID']."4.jpg")) {
                  echo "<img src='".$http_photos.$row1['ListingID']."4.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row1['ListingID']."5.jpg")) {
                     echo "<img src='".$http_photos.$row1['ListingID']."5.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>".$row1['City']."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>$".number_format($row1['ListPrice'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td>".substr($row1['PublicRemarks'],0,97)."...<br /><a href='".$http_home."details_residential.php?mls_id=".$row1['ListingID']."&$str_url_vars' target='_blank'>More Details</a></td>\n";
      echo "</tr>\n";

}
echo "</table>\n";
}

/* End of Residential Listings */


if (stristr($show,"vacant land") && !stristr($hide,"vacant land")) {
/* Vacant Land Listings */
$sql2 = "SELECT * FROM tbl_idx_vacant_land".$where_clause.$order_clause.$limit_vacantland;
$result2 = $dbcnx->prepare($sql2);
try {
   $result2->execute($where_clause_arr);
} catch(PDOException $e) {
   $err_msg .= "Db Error: ".$e->getMessage()."<br />\n";
   /* Debugging info */
	$err_msg .=  "<p class='debugging'>\n";
	$err_msg .=  "num_rows1: $num_rows1<br />\n";
	$err_msg .=  "str_url_vars: $str_url_vars<br />\n";
	$err_msg .=  "</p>\n";
}

$prev_prop_type = 'vacant land';
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;background-color: #CCC;'>\n";
while ($row2 = $result2->fetchAll(PDO::FETCH_ASSOC)) {
      echo "<tr>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_vacantland.php?mls_id=".$row2['ListingID']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row2['ListingID']."a.jpg")) {
         echo "<img src='".$http_photos.$row2['ListingID']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row2['ListingID']."b.jpg")) {
            echo "<img src='".$http_photos.$row2['ListingID']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row2['ListingID']."c.jpg")) {
               echo "<img src='".$http_photos.$row2['ListingID']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row2['ListingID']."d.jpg")) {
                  echo "<img src='".$http_photos.$row2['ListingID']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row2['ListingID']."e.jpg")) {
                     echo "<img src='".$http_photos.$row2['ListingID']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>".$row2['City']."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>$".number_format($row2['ListPrice'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td>".substr($row2['PublicRemarks'],0,97)."...<br /><a href='".$http_home."details_vacantland.php?mls_id=".$row2['ListingID']."&$str_url_vars' target='_blank'>More Details</a></td>\n";
      echo "</tr>\n";
}
echo "</table>\n";
/* End of Vacant Land Listings */
}


if (stristr($show,"multires") && !stristr($hide,"multires")) {
/* Multi Residential Listings */
$sql3 = "SELECT * FROM tbl_idx_multi_res".$where_clause.$order_clause.$limit_multires;
$result3 = $dbcnx->prepare($sql3);
try {
   $result3->execute($where_clause_arr);
} catch(PDOException $e) {
   $err_msg .= "Db Error: ".$e->getMessage()."<br />\n";
   /* Debugging info */
	$err_msg .=  "<p class='debugging'>\n";
	$err_msg .=  "num_rows1: $num_rows1<br />\n";
	$err_msg .=  "str_url_vars: $str_url_vars<br />\n";
	$err_msg .=  "</p>\n";
}

$prev_prop_type = 'multires';
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;background-color: #CCC;'>\n";
while ($row3 = $result3->fetchAll(PDO::FETCH_ASSOC)) {
      echo "<tr>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_multi_res.php?mls_id=".$row3['ListingID']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row3['ListingID']."a.jpg")) {
         echo "<img src='".$http_photos.$row3['ListingID']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row3['ListingID']."b.jpg")) {
            echo "<img src='".$http_photos.$row3['ListingID']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row3['ListingID']."c.jpg")) {
               echo "<img src='".$http_photos.$row3['ListingID']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row3['ListingID']."d.jpg")) {
                  echo "<img src='".$http_photos.$row3['ListingID']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row3['ListingID']."e.jpg")) {
                     echo "<img src='".$http_photos.$row3['ListingID']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>".$row3['City']."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>$".number_format($row3['ListPrice'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td>".substr($row3['PublicRemarks'],0,97)."...<br /><a href='".$http_home."details_multi_res.php?mls_id=".$row3['ListingID']."&$str_url_vars' target='_blank'>More Details</a></td>\n";
      echo "</tr>\n";
}
echo "</table>\n";
/* End of Multi Residential Listings */
}


if (stristr($show,"commercial") && !stristr($hide,"commercial")) {
/* Commercial Listings */
$sql4 = "SELECT * FROM tbl_idx_commercial".$where_clause.$order_clause.$limit_commercial;
$result4 = $dbcnx->prepare($sql4);
try {
   $result4->execute($where_clause_arr);
} catch(PDOException $e) {
   $err_msg .= "Db Error: ".$e->getMessage()."<br />\n";
   /* Debugging info */
	$err_msg .=  "<p class='debugging'>\n";
	$err_msg .=  "num_rows1: $num_rows1<br />\n";
	$err_msg .=  "str_url_vars: $str_url_vars<br />\n";
	$err_msg .=  "</p>\n";
}

$prev_prop_type = 'commercial';
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;background-color: #CCC;'>\n";
while ($row4 = $result4->fetchAll(PDO::FETCH_ASSOC)) {
      echo "<tr>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_commercial.php?mls_id=".$row4['ListingID']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row4['ListingID']."a.jpg")) {
         echo "<img src='".$http_photos.$row4['ListingID']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row4['ListingID']."b.jpg")) {
            echo "<img src='".$http_photos.$row4['ListingID']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row4['ListingID']."c.jpg")) {
               echo "<img src='".$http_photos.$row4['ListingID']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row4['ListingID']."d.jpg")) {
                  echo "<img src='".$http_photos.$row4['ListingID']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row4['ListingID']."e.jpg")) {
                     echo "<img src='".$http_photos.$row4['ListingID']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>".$row4['City']."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>$".number_format($row4['ListPrice'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td>".substr($row4['PublicRemarks'],0,97)."...<br /><a href='".$http_home."details_commercial.php?mls_id=".$row4['ListingID']."&$str_url_vars' target='_blank'>More Details</a></td>\n";
      echo "</tr>\n";
}
echo "</table>\n";
/* End of Commercial Listings */
}

if (stristr($show,"business op") && !stristr($hide,"business op")) {
/* Business Op Listings */
$sql5 = "SELECT * FROM tbl_idx_business_op".$where_clause.$order_clause.$limit_businessop;
$result5 = $dbcnx->prepare($sql5);
try {
   $result5->execute($where_clause_arr);
} catch(PDOException $e) {
   $err_msg .= "Db Error: ".$e->getMessage()."<br />\n";
   /* Debugging info */
	$err_msg .=  "<p class='debugging'>\n";
	$err_msg .=  "num_rows1: $num_rows1<br />\n";
	$err_msg .=  "str_url_vars: $str_url_vars<br />\n";
	$err_msg .=  "</p>\n";
}

$prev_prop_type = 'business op';
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;background-color: #CCC;'>\n";
while ($row5 = $result5->fetchAll(PDO::FETCH_ASSOC)) {
      echo "<tr>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_business_op.php?mls_id=".$row5['ListingID']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row5['ListingID']."a.jpg")) {
         echo "<img src='".$http_photos.$row5['ListingID']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row5['ListingID']."b.jpg")) {
            echo "<img src='".$http_photos.$row5['ListingID']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row5['ListingID']."c.jpg")) {
               echo "<img src='".$http_photos.$row5['ListingID']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row5['ListingID']."d.jpg")) {
                  echo "<img src='".$http_photos.$row5['ListingID']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row5['ListingID']."e.jpg")) {
                     echo "<img src='".$http_photos.$row5['ListingID']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>".$row5['City']."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>$".number_format($row5['ListPrice'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td>".substr($row5['PublicRemarks'],0,97)."...<br /><a href='".$http_home."details_business_op.php?mls_id=".$row5['ListingID']."&$str_url_vars' target='_blank'>More Details</a></td>\n";
      echo "</tr>\n";
}
echo "</table>\n";

/* End of Business Op Listings */
}

/* Close out the result set */
if (!empty($result1)) { $result1 = 'null'; }
if (!empty($result2)) { $result2 = 'null'; }
if (!empty($result3)) { $result3 = 'null'; }
if (!empty($result4)) { $result4 = 'null'; }
if (!empty($result5)) { $result5 = 'null'; }
/* Closing connection */
$dbcnx = 'null';
?>
