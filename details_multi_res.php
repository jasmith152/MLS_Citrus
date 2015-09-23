<?php
// Include config
require 'config.php';

// Get some functions ready to use
require 'functions.php';

// Map some data fields
require 'data-fields.php';
require 'data-fields-residential.php';

// Establish some variables
$mls_id = getVar('mls_id');
$firm_id = getVar('firm_id');
$display_firm = getVar('display_firm');
$office_id = getVar('office_id');
$display_office = getVar('display_office');
$agent_id = getVar('agent_id');
$display_agent = getVar('display_agent');
$require_login = getVar('require_login');
$login_fields = getVar('login_fields');
$user_email = getVar('user_email');
$err_msg = getVar('err_msg');
$output = '';
$exit = 0;

/* for testing only
$mls_id = ""; */

// Preliminary checks
if (empty($mls_id)) {
   $err_msg .= "<span class='err_msg'>No Listing selected.</span>\n";
   $exit++;
}
if (empty($agent_id) && empty($firm_id) && empty($display_agent) && empty($display_firm)) {
   $err_msg .= "<span class='err_msg'>No Firm or Agent selected.</span>\n";
   $exit++;
} else {
   /* Verify agent is allowed to use our system */
   require 'allow_firms.php';
   require 'allow_agents.php';
   if (!stristr($allow_agents,$agent_id) && !stristr($allow_agents,$display_agent) && !stristr($allow_firms,$firm_id) && !stristr($allow_firms,$display_firm)) {
      $err_msg .= "<span class='err_msg'>We are sorry, but this website is not authorized to use this feature.</span>\n";
      $exit++;
   }
}

/* Display any messages needed */
if (!empty($msg)) {
   $output .= "<p class='msg'>$msg</p>\n";
}
if (!empty($err_msg)) {
   $output .= "<p class='error'>$err_msg</p>\n";
}
if ($exit > 0) {
   exit;
}

// Require the customer's information, if requested by referring website.
if ($require_login == 'Y') {
   require 'user_login.php';
}

// Connect to the Db
$dbcnx = dbconn($db_host,$db_username,$db_password,$db_name);

/* Select listing details */
$sql_listing = "SELECT * FROM $db_tbl_multires WHERE $db_mls_id = :mls_id";
$result_listing = $dbcnx->prepare($sql_listing);
$result_listing->bindParam(':mls_id', $mls_id, PDO::PARAM_INT);
$result_listing->execute();
$num_rows1 = count($result_listing);
$data_listing = $result_listing->fetch(PDO::FETCH_ASSOC);

// Check for office info
if ($display_office == '' && $office_id != '') {
   $display_office = $office_id;
}
/* Insist on having a display_office, set a default*/
if ($display_office == '') {
   $display_office = '0';
}
// Determine display firm/agent and select their info
$agent_fields_arr = array($db_agent_id,$db_agent_title,$db_agent_phone,$db_agent_phone2,$db_agent_fax,$db_agent_email,$db_agent_website,$db_agent_fname,$db_agent_lname,$db_agent_credentials);
$agent_field_list = '';
foreach ($agent_fields_arr as $key => $agent_field) {
   if (!empty($agent_field)) {
      if ($key > 0) { $agent_field_list .= ","; }
      $agent_field_list .= "agent.$agent_field as agent_$agent_field";
   }
}
//$agent_field_list = "agent.$db_agent_id as agent_$db_agent_id,agent.$db_agent_title as agent_$db_agent_title,agent.$db_agent_phone as agent_$db_agent_phone,agent.$db_agent_phone2 as agent_$db_agent_phone2,agent.$db_agent_fax as agent_$db_agent_fax,agent.$db_agent_email as agent_$db_agent_email,agent.$db_agent_website as agent_$db_agent_website,agent.$db_agent_fname as agent_$db_agent_fname,agent.$db_agent_lname as agent_$db_agent_lname,agent.$db_agent_credentials as agent_$db_agent_credentials";
$firm_fields_arr = array($db_firm_id,$db_office_id,$db_firm_name,$db_firm_address,$db_firm_address2,$db_firm_city,$db_firm_state,$db_firm_zip,$db_firm_phone,$db_firm_phone2,$db_firm_fax,$db_firm_email,$db_firm_website);
$firm_field_list = '';
foreach ($firm_fields_arr as $key => $firm_field) {
   if (!empty($firm_field)) {
      if ($key > 0) { $firm_field_list .= ","; }
      $firm_field_list .= "firm.$firm_field as firm_$firm_field";
   }
}
//$firm_field_list = "firm.$db_firm_id as firm_$db_firm_id,firm.$db_office_id as firm_$db_office_id,firm.$db_firm_name as firm_$db_firm_name,firm.$db_firm_address as firm_$db_firm_address,firm.$db_firm_address2 as firm_$db_firm_address2,firm.$db_firm_city as firm_$db_firm_city,firm.$db_firm_state as firm_$db_firm_state,firm.$db_firm_zip as firm_$db_firm_zip,firm.$db_firm_phone as firm_$db_firm_phone,firm.$db_firm_phone2 as firm_$db_firm_phone2,firm.$db_firm_fax as firm_$db_firm_fax,firm.$db_firm_email as firm_$db_firm_email,firm.$db_firm_website as firm_$db_firm_website";
if (!empty($display_agent) || !empty($agent_id)) {
   if (empty($display_agent) && !empty($agent_id)) {
      $display_agent = $agent_id;
   }
   $sql_display_entity = "SELECT $agent_field_list,$firm_field_list FROM $db_tbl_agents AS agent, $db_tbl_firms AS firm WHERE agent.$db_agent_id = '$display_agent' AND agent.$db_firm_id = firm.$db_firm_id";
   $sql_display_entity .= " AND firm.$db_office_id = '$display_office'";
} else {
   if (empty($display_firm) && !empty($firm_id)) {
      $display_firm = $firm_id;
   }
   if ($display_office == '' && $office_id != '') {
      $display_office = $office_id;
   }
   if ($display_firm == ltrim($data_listing[$db_listing_firm_id])) {
      $display_office = ltrim($data_listing[$db_listing_office_id]);
   }
   /* Insist on having a display_office, set a default*/
   if ($display_office == '') {
      $display_office = '0';
   }
   if ($display_firm == ltrim($data_listing[$db_listing_firm_id]) && $display_office == ltrim($data_listing[$db_listing_office_id])) {
      $display_agent = ltrim($data_listing[$db_listing_agent_id]);
      $sql_display_entity = "SELECT $agent_field_list,$firm_field_list FROM $db_tbl_agents AS agent,$db_tbl_firms AS firm WHERE agent.$db_agent_id = '$display_agent' AND agent.$db_firm_id = firm.$db_firm_id";
   } else {
      $sql_display_entity = "SELECT $firm_field_list FROM $db_tbl_firms AS firm WHERE firm.$db_firm_id = '$display_firm'";
   }
   $sql_display_entity .= " AND firm.$db_office_id = '$display_office'";
}
try {
   $result_display_entity = $dbcnx->prepare($sql_display_entity);
   $result_display_entity->bindParam(':mls_id', $mls_id, PDO::PARAM_INT);
   $result_display_entity->execute();
   $data_display_entity = $result_display_entity->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
   $err_msg .= "Query error: ".$e->getMessage()."<br />\n";
}
   
// Determine listing agent and select office name
if ((!empty($display_firm) && ltrim($data_listing[$db_listing_firm_id]) != $display_firm) || (!empty($display_agent) && ltrim($data_listing[$db_listing_agent_id]) != $display_agent)) {
   $sql_lister = "SELECT $db_firm_name FROM $db_tbl_firms WHERE $db_firm_id = :db_listing_firm_id";
   $result_lister = $dbcnx->prepare($sql_lister);
   $result_lister->bindParam(':db_listing_firm_id', $data_listing[$db_listing_firm_id], PDO::PARAM_INT);
   $result_lister->execute();
   $row_lister = $result_lister->fetch(PDO::FETCH_ASSOC);
}

if (!empty($display_agent)) {
   $agent_name = $data_display_entity['agent_'.$db_agent_fname]." ".$data_display_entity['agent_'.$db_agent_lname];
   $agent_phone1 = "(".substr($data_display_entity['agent_'.$db_agent_phone],0,3).")".substr($data_display_entity['agent_'.$db_agent_phone],3,3)."-".substr($data_display_entity['agent_'.$db_agent_phone],6,4);
   if (!empty($data_display_entity['agent_'.$db_agent_phone2])) {
      $agent_phone2 = "(".substr($data_display_entity['agent_'.$db_agent_phone2],0,3).")".substr($data_display_entity['agent_'.$db_agent_phone2],3,3)."-".substr($data_display_entity['agent_'.$db_agent_phone2],6,4);
   }
   $to_email = $data_display_entity['agent_'.$db_agent_email];
   $to_name = $agent_name;
   if (!empty($data_display_entity['agent_'.$db_agent_website])) {
      $website = $data_display_entity['agent_'.$db_agent_website];
   } else {
      $website = $data_display_entity['firm_'.$db_firm_website];
   }
} else {
   $to_email = $data_display_entity['firm_'.$db_firm_email];
   $to_name = $data_display_entity['firm_'.$db_firm_name];
   $website = $data_display_entity['firm_'.$db_firm_website];
}

// Create link variable to this specific listing
$link_back = "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
$arr_index = 0;
foreach ($_GET as $get_key => $get_value) {
   if ($arr_index > 0) { $link_back .= "&"; } else { $link_back .= "?"; }
   $link_back .= $get_key."=".$get_value;
   $arr_index++;
}

/* Debugging info */
if ($debugging) {
   $output .= "<p class='debugging'>\n";
   $output .= "sql1: $sql_listing<br />\n";
   $output .= "num_rows1: $num_rows1<br />\n";
   $output .= "Firm ID on listing: ".ltrim($data_listing[$db_listing_firm_id])."<br />\n";
   $output .= "Display Firm: $display_firm<br />\n";
   $output .= "Office ID on listing: ".ltrim($data_listing[$db_listing_office_id])."<br />\n";
   $output .= "Display Office: $display_office<br />\n";
   $output .= "Agent ID on listing: ".ltrim($data_listing[$db_listing_agent_id])."<br />\n";
   $output .= "Display Agent: $display_agent<br />\n";
   $output .= "sql2: $sql_display_entity<br />\n";
   $output .= "</p>\n";
}

$output .= "<table width='780' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;background-color: #CCC;border: 1px solid #999;'>\n";
$output .= " <tr>\n";
$output .= "  <td align='center'>\n";
$output .= "   <table width='100%' border='0' cellpadding='1' cellspacing='0'>\n<tr>\n";
$output .= "    <td width='50%' valign='top'>\n";
/* Find photos and display them */
$output .= " <table cellpadding='0' cellspacing='0' border='0'>\n";
$photo_count = 0;
// Initial photo check
photoCheck($abs_photos,$mls_id);
for ($a = 0; $a <= 15; $a++) {
   if (photo_exists($abs_photos,$mls_id,$a)) {
      $photo_src = photo_exists($abs_photos,$mls_id,$a);
      $photo_count++;
      if ($photo_count == 1) {
         $img_info = getimagesize($abs_photos.$photo_src);
         if ($img_info[1] < 390) {
            $output .= "  <tr><td colspan='5' align='center'><a name='photo'></a><img src='".$http_photos.$photo_src."' alt='Photo' title='Photo' name='Photo' ".$img_info[3]." border='0' /></td></tr>\n";
         } else {
            $output .= "  <tr><td colspan='5' align='center'><a name='photo'></a><img src='".$http_photos.$photo_src."' alt='Photo' title='Photo' name='Photo' width='390' height='293' border='0' /></td></tr>\n";
         }
         $output .= "  <tr>\n";
      }
      $output .= "   <td align='center'><a href='#photo' onclick=\"roll('Photo', '".$http_photos.$photo_src."')\"><img src='".$http_photos.$photo_src."' alt='Photo Thumbnail' title='Photo Thumbnail' width='78' height='59' border='0' /></a></td>\n";
      if ($photo_count == 5 || $photo_count == 10) {
         $output .= "  </tr>\n";
         $output .= "  <tr>\n";
      }
      if ($photo_count == 15) {
         $output .= "  </tr>\n";
      }
   }
}
if ($photo_count == 0) {
   $output .= "  <tr><td align='center'><img src='".$http_imgs."nophoto.jpg' width='120' height='72' border='0' alt='No photo available' title='No photo available' /></td></tr>\n";
} else {
   if ($photo_count < 5) {
      for ($j = $photo_count; $j <= 5; $j++) {
         $output .= "   <td>&nbsp;</td>\n";
      }
      $output .= "  </tr>\n";
   } elseif ($photo_count < 10) {
      for (; $photo_count <= 10; $photo_count++) {
         $output .= "   <td>&nbsp;</td>\n";
      }
      $output .= "  </tr>\n";
   } elseif ($photo_count < 15) {
      for (; $photo_count <= 15; $photo_count++) {
         $output .= "   <td>&nbsp;</td>\n";
      }
      $output .= "  </tr>\n";
   }
}
$output .= " </table>\n";

$output .= "    </td>\n";
$output .= "    <td width='50%' valign='top'>\n";
$output .= "     <table width='75%' border='0' cellpadding='0' cellspacing='2' style='background-color: #E2EEFB; border: solid 1px #A8D2F5; font-size: 13px; font-weight: bold; padding: 5px;'><tr>\n";
$output .= "      <td width='50%' valign='top'><a href=\"javascript:NewWindow('contact_agent.php?mls_id=$mls_id&to_email=$to_email&to_name=$to_name&firm_name=".$data_display_entity['firm_'.$db_firm_name]."&website=$website','contact','550','450','center','front');\"><img src='".$http_imgs."contactus.gif' border='0' alt='Contact Us icon' />Contact Us</a><br /><br /><a href='#' onClick='window.print()'><img src='".$http_imgs."print.gif' border='0' alt='Print icon' />Print</a></td>\n";
$output .= "      <td width='50%' valign='top'><a href=\"javascript:NewWindow('contact_friend.php?mls_id=$mls_id&price=".number_format($data_listing[$db_price])."&link_back=$link_back','contactfriend','550','450','center','front');\"><img src='".$http_imgs."email.gif' border='0' alt='Email icon' />Email a Friend</a>";
$output .= "<br /><br /><a href='http://www.google.com/maps?f=q&hl=en&q=".$data_listing[$db_street_number]."+".$data_listing[$db_street_direction]."+".str_replace(" ","+",$data_listing[$db_street_name])."+".$data_listing[$db_postal_code]."&ie=UTF8&z=12&spn=0.146152,0.322037&om=1' target='_blank'><img src='".$http_imgs."map.gif' border='0' alt='Map icon' />Map Location</a></td>\n";
$output .= "     </tr></table><br />\n";
$output .= "    <p style='font-size: 16px;font-weight: bold; text-align: center'>MLS# ".$data_listing[$db_mls_id]."<br />".$data_listing[$db_property_type]."<br />".$data_listing[$db_city]."</p>\n";
$output .= "    <p style='font-size: 16px;font-weight: bold; text-align: center'><span style='font-size: 14px;'>Offered at</span><br />$".number_format($data_listing[$db_price])."</p>\n";
if (!empty($data_listing[$db_virtual_tour_url])) {
   if (substr($data_listing[$db_virtual_tour_url],0,7) != "http://") {
      $virt_url = "http://".$data_listing[$db_virtual_tour_url];
   } else {
      $virt_url = $data_listing[$db_virtual_tour_url];
   }
   $output .= "    <p style='font-size: 14px;font-weight: bold; text-align: center'><a href='$virt_url' target='_blank'><img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' />View the Virtual Tour</a></p>\n";
}
if ($photo_count > 1) {
   $output .= "    <p style='font-size: 12px; text-align: left;'>Click on a photo thumbnail to the left to view that photo.</p>\n";
   /* Find photos and display them in lightbox */
$output .= " <table cellpadding='0' cellspacing='0' border='0'>\n";
$photo_count = 0;
// Initial photo check for lightbox
photoCheck($abs_photos,$mls_id);
for ($a = 0; $a <= 100; $a++) {
   if (photo_exists($abs_photos,$mls_id,$a)) {
      $photo_src = photo_exists($abs_photos,$mls_id,$a);
      $photo_count++;
      if ($photo_count == 1) {
         $img_info = getimagesize($abs_photos.$photo_src);
         if ($img_info[1] < 390) {
            $output .= "  <tr><td colspan='5' align='center'><p style='font-size: 12px; text-align: center;'>Click <a href='".$http_photos.$photo_src."' rel='lightbox[gallery]'>here</a> view all photos.</p></td></tr>\n";
         }
         $output .= "  <tr>\n";
      }
      $output .= "   <td align='center'><a href='".$http_photos.$photo_src."' rel='lightbox[gallery]'></a></td>\n";
   }
}
$output .= " </table>\n";
}
$output .= "    </td>\n";
$output .= "   </table>\n";
$output .= "  </td>\n";
$output .= " </tr>\n";
$output .= " <tr>\n";
$output .= "  <td align='center' style='background-color: #999;font-size: 16px;font-weight: bold;'>Features</td>\n";
$output .= " </tr>\n";
$output .= " <tr>\n";
$output .= "  <td align='center'>\n";

$output .= "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
$output .= "     <tr><td width='15%'><b>Cross Street:</b> </td><td width='40%'>".$data_listing['LM_MST_CRSTREET']."</td>\n";
$output .= "     <td width='20%'><b># Units:</b> </td><td width='45%'>".$data_listing['LM_MST_UNITS']."</td></tr>\n";
$output .= "     <tr><td><b>County:</b> </td><td>".$data_listing['LM_MST_COUNTY']."</td>\n";
$output .= "     <td><b>Area/Zone:</b> </td><td>".$data_listing['LM_MST_ZONE']."</td></tr>\n";
$output .= "     <tr><td><b>Subdivision:</b> </td><td>".$data_listing['LM_MST_SUBDIV']."</td>\n";
$output .= "     <td><b>Year Built:</b> </td><td>".$data_listing['LM_MST_YR_BLT']."</td></tr>\n";
$output .= "     <tr><td><b>Waterfront:</b> </td><td>".$data_listing['LM_MST_WFRT_YN']."</td>\n";
$output .= "     <td><b>Apx. Acres:</b> </td><td>".$data_listing['LM_MST_ACRES']."</td></tr>\n";
$output .= "     <tr><td><b>Waterfront Ft:</b> </td><td>".$data_listing['LM_MST_WTRFRT_N']."</td>\n";
$output .= "     <td><b>Sec/Twp/Rng:</b> </td><td>".$data_listing['LM_MST_SEC']."/".$data_listing['LM_MST_TWP']."/".$data_listing['LM_MST_RANGE']."</td></tr>\n";
$output .= "     <tr><td><b>Private Pool:</b> </td><td>".$data_listing['LM_MST_POOL_YN']."</td>\n";
echo "     <td><b>Alt Key:</b> </td><td>".$data_listing['LM_MST_PARC_NO']."</td></tr>\n";
$output .= "     <tr><td><b>Total Bldg SqFt:</b> </td><td>".$data_listing['LM_MST_SQFT_N']."</td>\n";
$output .= "     <td><b>Flood Plain:</b> </td><td>".$data_listing['LM_MST_FLD_YN']."</td></tr>\n";
$output .= "     <tr><td><b>Apx. Lot Size:</b> </td><td>".$data_listing['LM_MST_DLOT']."</td>\n";
$output .= "     <td colspan='2'>&nbsp;</td></tr>\n";
$output .= "     <tr><td valign='top'><b>Public Rems:</b> </td><td colspan='3' valign='top'>".$data_listing['LM_MLT_REMARK']."</td></tr>\n";
$output .= "     <tr><td valign='top'><b>Directions:</b> </td><td colspan='3' valign='top'>".$data_listing['LM_MST_DIR']."</td></tr>\n";
$output .= "   </table>\n";
$output .= "  </td>\n";
$output .= " </tr>\n";
$output .= " <tr>\n";
$output .= "  <td align='center'>\n";
$output .= "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
$output .= "     <tr style='background-color: #999;'><td>&nbsp;</td><td><b>Unit #</b></td><td><b>Month Rent</b></td><td><b>Beds</b></td><td><b>Baths</b></td><td><b>Apx Liv SqFt</b></td><td><b>Lease Exp</b></td><td><b>Covered Parking/Garage</b></td></tr>\n";
if (!empty($data_listing['LM_MLT_U1_RENT'])) {
   $output .= "     <tr><td><b>U1</b></td><td>".$data_listing['LM_MLT_U1_ID']."</td><td>$".number_format($data_listing['LM_MLT_U1_RENT'])."</td><td>".$data_listing['LM_MLT_N_BED1']."</td><td>".$data_listing['LM_MLT_N_BTH1']."</td><td>".$data_listing['LM_MLT_SQFT1']."</td><td>".$data_listing['LM_MLT_TRMUN1']."</td><td>".$data_listing['LM_MLT_GARYN_U1']."</td></tr>\n";
}
if (!empty($data_listing['LM_MLT_U2_RENT'])) {
   $output .= "     <tr><td><b>U2</b></td><td>".$data_listing['LM_MLT_U2_ID']."</td><td>$".number_format($data_listing['LM_MLT_U2_RENT'])."</td><td>".$data_listing['LM_MLT_N_BED2']."</td><td>".$data_listing['LM_MLT_N_BTH2']."</td><td>".$data_listing['LM_MLT_SQFT2']."</td><td>".$data_listing['LM_MLT_TRMUN2']."</td><td>".$data_listing['LM_MLT_GARYN_U2']."</td></tr>\n";
}
if (!empty($data_listing['LM_MLT_U3_RENT'])) {
   $output .= "     <tr><td><b>U3</b></td><td>".$data_listing['LM_MLT_U3_ID']."</td><td>$".number_format($data_listing['LM_MLT_U3_RENT'])."</td><td>".$data_listing['LM_MLT_N_BED3']."</td><td>".$data_listing['LM_MLT_N_BTH3']."</td><td>".$data_listing['LM_MLT_SQFT3']."</td><td>".$data_listing['LM_MLT_TRMUN3']."</td><td>".$data_listing['LM_MLT_GARYN_U3']."</td></tr>\n";
}
if (!empty($data_listing['LM_MLT_U4_RENT'])) {
   $output .= "     <tr><td><b>U4</b></td><td>".$data_listing['LM_MLT_U4_ID']."</td><td>$".number_format($data_listing['LM_MLT_U4_RENT'])."</td><td>".$data_listing['LM_MLT_N_BED4']."</td><td>".$data_listing['LM_MLT_N_BTH4']."</td><td>".$data_listing['LM_MLT_SQFT4']."</td><td>".$data_listing['LM_MLT_TRMUN4']."</td><td>".$data_listing['LM_MLT_GARYN_U4']."</td></tr>\n";
}
if (!empty($data_listing['LM_MLT_U5_RENT'])) {
   $output .= "     <tr><td><b>U5</b></td><td>".$data_listing['LM_MLT_U5_ID']."</td><td>$".number_format($data_listing['LM_MLT_U5_RENT'])."</td><td>".$data_listing['LM_MLT_N_BED5']."</td><td>".$data_listing['LM_MLT_N_BTH5']."</td><td>".$data_listing['LM_MLT_SQFT5']."</td><td>".$data_listing['LM_MLT_TRMUN5']."</td><td>".$data_listing['LM_MLT_GARYN5']."</td></tr>\n";
}
if (!empty($data_listing['LM_MLT_U6_RENT'])) {
   $output .= "     <tr><td><b>U6</b></td><td>".$data_listing['LM_MLT_U6_ID']."</td><td>$".number_format($data_listing['LM_MLT_U6_RENT'])."</td><td>".$data_listing['LM_MLT_N_BED6']."</td><td>".$data_listing['LM_MLT_N_BTH6']."</td><td>".$data_listing['LM_MLT_SQFT6']."</td><td>".$data_listing['LM_MLT_TRMUN6']."</td><td>".$data_listing['LM_MLT_GARYN6']."</td></tr>\n";
}
if (!empty($data_listing['LM_MLT_U7_RENT'])) {
   $output .= "     <tr><td><b>U7</b></td><td>".$data_listing['LM_MLT_U7_ID']."</td><td>$".number_format($data_listing['LM_MLT_U7_RENT'])."</td><td>".$data_listing['LM_MLT_N_BED7']."</td><td>".$data_listing['LM_MLT_N_BTH7']."</td><td>".$data_listing['LM_MLT_SQFT7']."</td><td>".$data_listing['LM_MLT_TRMUN7']."</td><td>".$data_listing['LM_MLT_GARYN7']."</td></tr>\n";
}
if (!empty($data_listing['LM_MLT_U8_RENT'])) {
   $output .= "     <tr><td><b>U8</b></td><td>".$data_listing['LM_MLT_U8_ID']."</td><td>$".number_format($data_listing['LM_MLT_U8_RENT'])."</td><td>".$data_listing['LM_MLT_N_BED8']."</td><td>".$data_listing['LM_MLT_N_BTH8']."</td><td>".$data_listing['LM_MLT_SQFT8']."</td><td>".$data_listing['LM_MLT_TRMUN8']."</td><td>".$data_listing['LM_MLT_GARYN8']."</td></tr>\n";
}
if (!empty($data_listing['LM_MLT_U9_RENT'])) {
   $output .= "     <tr><td><b>U9</b></td><td>".$data_listing['LM_MLT_U9_ID']."</td><td>$".number_format($data_listing['LM_MLT_U9_RENT'])."</td><td>".$data_listing['LM_MLT_N_BED9']."</td><td>".$data_listing['LM_MLT_N_BTH9']."</td><td>".$data_listing['LM_MLT_SQFT9']."</td><td>".$data_listing['LM_MLT_TRMUN9']."</td><td>".$data_listing['LM_MLT_GARYN9']."</td></tr>\n";
}
if (!empty($data_listing['LM_MLT_U10_RENT'])) {
   $output .= "     <tr><td><b>U10</b></td><td>".$data_listing['LM_MLT_U10_ID']."</td><td>$".number_format($data_listing['LM_MLT_U10_RENT'])."</td><td>".$data_listing['LM_MLT_N_BEDX']."</td><td>".$data_listing['LM_MLT_N_BTHX']."</td><td>".$data_listing['LM_MLT_SQFTX']."</td><td>".$data_listing['LM_MLT_TRMUNX']."</td><td>".$data_listing['LM_MLT_GARYNX']."</td></tr>\n";
}
$output .= "   </table>\n";
$output .= "  </td>\n";
$output .= " </tr>\n";
$output .= " <tr>\n";
$output .= "  <td align='center' style='background-color: #999;font-size: 14px;font-weight: bold;'>Additional Features</td>\n";
$output .= " </tr>\n";
$output .= " <tr>\n";
$output .= "  <td align='center'>\n";
$output .= "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
$output .= "     <tr><td width='15%'><b>Waterfront:</b> </td><td width='85%'>".$data_listing['LM_MST_CFF_WATERFRONT']."</td></tr>\n";
$output .= "     <tr><td><b>Int. Features:</b> </td><td>".$data_listing['LM_MST_CFF_INT_FEATURES']."</td></tr>\n";
$output .= "     <tr><td><b>Ext. Features:</b> </td><td>".$data_listing['LM_MST_CFF_EXT_FEATURES']."</td></tr>\n";
$output .= "     <tr><td><b>Appliances:</b> </td><td>".$data_listing['LM_MST_CFF_EQUIP']."</td></tr>\n";
$output .= "     <tr><td><b>Special Info:</b> </td><td>".$data_listing['LM_MST_CFF_SPECIAL_INFO']."</td></tr>\n";
$output .= "     <tr><td><b>Garage:</b> </td><td>".$data_listing['LM_MST_CFF_GARAGE']."</td></tr>\n";
$output .= "   </table><br />\n";
$output .= "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
$output .= "     <tr><td><b>Style:</b> </td><td>".$data_listing['LM_MST_CFF_STYLE']."</td>\n";
$output .= "     <td><b>Roof:</b> </td><td>".$data_listing['LM_MST_CFF_ROOF']."</td></tr>\n";
$output .= "     <tr><td><b>Heat/Cool:</b> </td><td>".$data_listing['LM_MST_CFF_HEAT_COOL']."</td>\n";
$output .= "     <td><b>Floor:</b> </td><td>".$data_listing['LM_MST_CFF_FLOOR']."</td></tr>\n";
$output .= "     <tr><td><b>Road Type:</b> </td><td>".$data_listing['LM_MST_CFF_ROAD_TYPE']."</td>\n";
$output .= "     <td><b>Sewer/Water:</b> </td><td>".$data_listing['LM_MST_CFF_SEWER_WATER']."</td></tr>\n";
$output .= "     <tr><td><b>Pool Type:</b> </td><td>".$data_listing['LM_MST_CFF_POOL_TYPE']."</td>\n";
$output .= "     <td><b>Energy Feat:</b> </td><td>".$data_listing['LM_MST_CFF_ENERGY_FEATURES']."</td></tr>\n";
$output .= "     <tr><td><b>Const/Found:</b> </td><td>".$data_listing['LM_MST_CFF_FOUNDATION']."</td>\n";
$output .= "     <td><b>Lot Desc:</b> </td><td>".$data_listing['LM_MST_CFF_LOT_DESCRIPTION']."</td></tr>\n";
$output .= "     <tr><td><b>Restrictions:</b> </td><td>".$data_listing['LM_MST_CFF_RESTRICTIONS']."</td>\n";
$output .= "     <td><b>Terms Avail:</b> </td><td>".$data_listing['LM_MST_CFF_TERMS_AVAILABLE']."</td></tr>\n";
$output .= "     <tr><td><b>Comm Amen:</b> </td><td>".$data_listing['LM_MST_CFF_COMMUNITY_AMENITIES']."</td>\n";
$output .= "     <td><b>Possession:</b> </td><td>".$data_listing['LM_MST_CFF_POSSESSION']."</td></tr>\n";
$output .= "     <tr><td><b>Short Sale:</b> </td><td>".$data_listing['LM_MST_SHTSL_YN']."</td>\n";
$output .= "     <td><b>Foreclosure:</b> </td><td>".$data_listing['LM_MST_FORCL_YN']."</td></tr>\n";
$output .= "   </table><br />\n";
$output .= "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
$output .= "     <tr><td><b>Taxes:</b> </td><td>$".number_format($data_listing['LM_MST_TAX_AMT'])."</td>\n";
$output .= "     <td><b>Tax Year:</b> </td><td>".$data_listing['LM_MST_TAX_YR']."</td>\n";
$output .= "     <td><b>Homestead:</b> </td><td>".$data_listing['LM_MST_HMSTD_YN']."</td>\n";
$output .= "     <td><b>Rear Exposure:</b> </td><td>".$data_listing['LM_MST_DRFCES']."</td></tr>\n";
$output .= "     <tr><td><b>HOA/COA:</b> </td><td>".$data_listing['LM_MST_HOA']."</td>\n";
$output .= "     <td><b>HOA Fee:</b> </td><td>$".number_format($data_listing['LM_MST_HOAFEE'])."</td>\n";
$output .= "     <td><b>Agent Owned:</b> </td><td>".$data_listing['LM_MST_AGTOWN']."</td>\n";
$output .= "     <td><b>Land Use:</b> </td><td>".$data_listing['LM_MST_ZONING']."</td></tr>\n";
$output .= "     <tr><td><b>Elementary:</b> </td><td>".$data_listing['LM_MST_SCH_ELEM']."</td>\n";
$output .= "     <td><b>Middle:</b> </td><td>".$data_listing['LM_MST_SCH_MID']."</td>\n";
$output .= "     <td><b>High:</b> </td><td>".$data_listing['LM_MST_SCH_HIGH']."</td>\n";
$output .= "     <td colspan='2'>&nbsp;</td></tr>\n";
$output .= "   </table><br />\n";
$output .= "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
$output .= "     <tr><td><b>Legal:</b> </td><td>".$data_listing[$db_legal_descr]."</td></tr>\n";
$output .= "   </table><br />\n";

$output .= "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
if (!empty($row_lister[$db_firm_name])) {
   $output .= "    <tr><td colspan='3'><b>Courtesy of:</b> ".$row_lister[$db_firm_name]."</td></tr>\n";
}
$output .= "    <tr><td colspan='3'><b>For more information or to visit this property, Contact:</b></td></tr>\n";
$output .= "    <tr><td>\n";
if (!empty($agent_name)) {
   if (agent_photo_exists($display_agent)) {
      $photo_src = agent_photo_exists($display_agent);
      $img_info = getimagesize($abs_agents.$photo_src);
      $output .= "<img src='".$http_mugs.$photo_src."' ".$img_info[3]." alt='$agent_name' border='0' />\n";
   } else {
      if (firm_logo_exists($display_firm)) {
         $photo_src = firm_logo_exists($display_firm);
         $img_info = getimagesize($abs_agents.$photo_src);
         $output .= "<img src='".$http_mugs.$photo_src."' ".$img_info[3]." alt='".$data_display_entity['firm_'.$db_firm_name]."' border='0' />\n";
      }
   }
} else {
   if (firm_logo_exists($display_firm)) {
      $photo_src = firm_logo_exists($display_firm);
      $img_info = getimagesize($abs_agents.$photo_src);
      $output .= "<img src='".$http_mugs.$photo_src."' ".$img_info[3]." alt='".$data_display_entity['firm_'.$db_firm_name]."' border='0' />\n";
   }
}
$output .= "     </td>\n";
$output .= "     <td valign='top'><b>";
if (!empty($agent_name)) { $output .= $agent_name."<br />\n"; }
$output .= $data_display_entity['firm_'.$db_firm_name]."</b><br />\n".$data_display_entity['firm_'.$db_firm_address]."<br />\n";
//if (!empty($data_display_entity_assoc['firm.address2'])) { $output .= $data_display_entity_assoc['firm.address2']."<br />\n"; }
$output .= $data_display_entity['firm_'.$db_firm_city]." ".$data_display_entity['firm_'.$db_firm_state].", ".$data_display_entity['firm_'.$db_firm_zip]."</td>\n";

$output .= "     <td valign='top'><b>Off. Ph#:</b> (".substr($data_display_entity['firm_'.$db_firm_phone],0,3).")".substr($data_display_entity['firm_'.$db_firm_phone],3,3)."-".substr($data_display_entity['firm_'.$db_firm_phone],6,4)."<br />\n";
//if (!empty($data_display_entity['phone2'])) { $output .= "<b>Off. Ph#:</b> (".substr($data_display_entity['phone2'],0,3).")".substr($data_display_entity['phone2'],3,3)."-".substr($data_display_entity['phone2'],6,4)."<br />\n"; }
if (!empty($agent_phone1)) { $output .= "<b>Agt. Ph#:</b> $agent_phone1<br />\n"; }
if (!empty($agent_phone2)) { $output .= "<b>Agt. Ph#:</b> $agent_phone2<br />\n"; }
$output .= "<b><a href=\"javascript:NewWindow('contact_agent.php?mls_id=$mls_id&to_email=$to_email&to_name=$to_name&firm_name=".$data_display_entity['firm_'.$db_firm_name]."&website=$website','contact','550','450','center','front');\">Contact Now</a></b>\n";
$output .= "     </td></tr>\n";
$output .= "   </table>\n";
$output .= "  </td>\n";
$output .= " </tr>\n";
$output .= "</table><br />\n";

/* Disclaimer */
$output .= $disclaimer;

/* Count the hit */
if (stripos($link_back,'agent') !== false) {
   $hit = countHit($dbcnx,$mls_id,'detail','agent',$display_agent);
}
if (stripos($link_back,'firm') !== false) {
   $hit = countHit($dbcnx,$mls_id,'detail','firm',$display_firm);
}
/*$sql_hit = "INSERT INTO tbl_hits (mls_no,agent_id,firm_id,hit_date,type) VALUES (:mls_no,:agent_id,:firm_id,:hit_date,:type)";
$today = date("Y-m-d H:i:s");
$hit_type = 'detail';
try {
   $result_hit = $dbcnx->prepare($sql_hit);
   $result_hit->bindParam(':mls_no', $data_listing[$db_mls_id], PDO::PARAM_INT);
   $result_hit->bindParam(':agent_id', $data_listing[$db_agent_id], PDO::PARAM_INT);
   $result_hit->bindParam(':firm_id', $data_listing[$db_firm_id], PDO::PARAM_INT);
   $result_hit->bindParam(':hit_date', $today, PDO::PARAM_STR);
   $result_hit->bindParam(':type', $hit_type, PDO::PARAM_STR);
   $result_hit->execute();
} catch (PDOException $e) {
   $err_msg .= "Query error: ".$e->getMessage()."<br />\n";
}*/

/*  Send lead info to firm/agent if requested */
if (($require_login == 'Y') && (!empty($user_email))) {
   /* Get the user's info again */
   $sql_user = "SELECT * FROM tbl_leads WHERE email = :email";
   $result_user = $dbcnx->prepare($sql_user);
   $result_user->bindParam(':email', $user_email, PDO::PARAM_STR);
   $result_user->execute();
   $data_user = $result_user->fetch(PDO::FETCH_ASSOC);

   /* Set some variables */
   $eol = "\n";
   $to = "$to_name <$to_email>";
   //$to = "Chris <chris@naturecoastdesign.net>,Pete <pete.reiman@realogy.com>"; for testing only
   $subject = "A prospective lead just viewed MLS# $mls_id online.";
   //$from = $data_user['fname']." ".$data_user['lname']." <$user_email>";
   $headers = "From: $user_email".$eol;
   $headers .= "Reply-To: $from".$eol;
   $headers .= "Return-Path: $from".$eol;
   $headers .= "Message-ID: <".time()." Postmaster@".$_SERVER['SERVER_NAME'].">".$eol;
   $headers .= "X-Mailer: PHP v".phpversion()."".$eol;
   $headers .= "MIME-Version: 1.0".$eol;
   $headers .= "Content-type: text/html; charset=\"us-ascii\"";

   $text = "Date: ".date('m-d-Y')." MLS# $mls_id<br />";
   $text .= "Here is the information that was provided by the user:<br />";
   $text .= "Email: ".$data_user['email']." <br />";
   if (!empty($data_user['fname']) || !empty($data_user['lname'])) {
      $text .= "Name: ".$data_user['fname']." ".$data_user['lname']." <br />";
   }
   if (!empty($data_user['address1'])) {
      $text .= "Street Address: ".$data_user['address1']." <br />";
   }
   if (!empty($data_user['address2'])) {
      $text .= "Street Address (con't): ".$data_user['address2']." <br />";
   }
   if (!empty($data_user['city'])) {
      $text .= "City: ".$data_user['city']." <br />";
   }
   if (!empty($data_user['state'])) {
      $text .= "State: ".$data_user['state']." <br />";
   }
   if (!empty($data_user['zip'])) {
      $text .= "Zip: ".$data_user['zip']." <br />";
   }
   if (!empty($data_user['day_phone'])) {
      $text .= "Daytime Phone: ".$data_user['day_phone']." <br />";
   }
   if (!empty($data_user['evening_phone'])) {
      $text .= "Evening Phone: ".$data_user['evening_phone']." <br />";
   }
   if (!empty($data_user['buyer_timeframe'])) {
      $text .= "Buyer's Time frame: ".$data_user['buyer_timeframe']." <br />";
   }
   if (!empty($data_user['buyer_pricerange'])) {
      $text .= "Buyer's Price Range: ".$data_user['buyer_pricerange']." <br />";
   }
   $text .= "------------------------------------------------<br />";

   if (mail("$to","$subject","$text","$headers")) {
      $output .= "Thank you for your inquiry. An agent should be contacting you very soon.<br />\n";
   } else {
      $output .= "There has been an error and we are unable to send your message at this time. Please try again later.<br />\n";
   }
}

/* Debugging info */
if ($debugging) {
   $output .= "<p class='debugging'>\n";
   $output .= "err_msg: $err_msg<br />\n";
   $output .= "hit: $hit<br />\n";
   $output .= "sql_hit: $sql_hit<br />\n";
   $output .= "row2: <pre>".print_r($data_display_entity,true)."</pre><br />\n";
   $output .= "agent_name: $agent_name<br />\n";
   $output .= "agent_phone1: $agent_phone1<br />\n";
   $output .= "agent_phone2: $agent_phone2<br />\n";
   $output .= "to_email: $to_email<br />\n";
   $output .= "to_name: $to_name<br />\n";
   $output .= "website: $website<br />\n";
   $output .= "link_back: $link_back<br />\n";
   $output .= "</p>\n";
}

/* Display the html */
require 'template.php';

/* Closing connection */
$dbcnx = null;
?>