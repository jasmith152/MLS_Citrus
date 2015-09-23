<?php
 
// For this script to show Awstats graphics correctly, they must be uploaded to your server.
// The graphics from Awstats v6.8 are available from the external link on this wiki page, 
// otherwise you can download a copy of the Awstats package from http://awstats.sourceforge.net/
// Within the package is the directory wwwroot/icon/
// FTP all the folders in this directory to a folder on your server.
 
// To view the stats for an addon or subdomain, just append to the URL:
//  ?config=subdomain.yourdomain.com.au
 
 
// Required settings
$Domain = 'gulfatlanticequipment.com'; // change this to your own domain
$UserId = 'gulfatla';          // change this to your cpanel username
$Secret = 'RockSolid4U';          // change this to your cpanel password
$ImageDir='awstats-images';    // location of the awstats graphics
 
// Optional settings
$DefaultLanguage = 'en';
$HideLanguages   = true;
$HideAwstatsLogo = false;
$CustomLogo      = '';
$CustomAltTitle  = '';
$CustomUrl       = '';
 
// ------------------------------------------------------
 
$SayDomain=(isset($_GET['config'])) ?  $_GET['config'] : $Domain;
if(0==count($_GET))
    { $qs="config=$Domain&lang=$DefaultLanguage&framename=mainright"; }
else
    {
    $qs = ''; 
    foreach ($_GET as $key=>$value) 
        { $value = urlencode(stripslashes($value)); $qs.="$key=$value&"; }
    $qs=substr($qs,0,-1);
    }
 
$Secret=rawurlencode($Secret);
$Stats = file_get_contents("http://$UserId:$Secret@$Domain:2082/awstats.pl?$qs");
$Stats=str_replace('<form name="FormDateFilter"', "<center><h2>Web statistics for $SayDomain</h2></center>\r\n<form name=\"FormDateFilter\"", $Stats);
$Stats=str_replace('awstats.pl', $_SERVER['PHP_SELF'], $Stats);
if(substr($ImageDir,-1)<>'/') $ImageDir.='/';
$Stats=str_replace('/images/awstats/', $ImageDir, $Stats);
$Stats=str_replace('framename=index', 'framename=mainright', $Stats);
$Stats=str_replace('name="framename" value="index"', 'name="framename" value="mainright"', $Stats);
$Stats=str_replace('target="mainright"', '', $Stats);
if($HideLanguages) $Stats=preg_replace('/<td align="right" rowspan="2">(<a .*<\/a>).*<br \/><a .*<\/td>/Us', '<td align="right" rowspan="2">$1</td>', $Stats, 1);
if($HideAwstatsLogo or $CustomLogo<>'')
    {
    $Img="<img src=\"$CustomLogo\" border=\"0\" alt=\"$CustomAltTitle\" title=\"$CustomAltTitle\" />";
    $Logo=(''==$CustomUrl) ? $Img : "<a href=\"$CustomUrl\" target=\"_blank\">$Img</a>";
    if(''==$CustomLogo) $Logo='&nbsp;';
    $Stats=preg_replace('/<td align="right" rowspan="2">(<a .*<\/a>)/Us', '<td align="right" rowspan="2">'.$Logo, $Stats, 1);
    }
echo $Stats;
 
?>