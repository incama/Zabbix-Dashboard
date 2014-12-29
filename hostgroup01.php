<?php
// load the ZabbixApi
require 'lib/php/ZabbixApiAbstract.class.php';
require 'lib/php/ZabbixApi.class.php';
// connect to Zabbix API
$api = new ZabbixApi('http://url-to-zabbix-api/zabbix/api_jsonrpc.php', 'user', 'pass');
// Set Defaults
$api->setDefaultParams(array(
	'output' => 'extend',
));
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Zabbix Dashboard</title>
	<link rel="stylesheet" type="text/css" href="style/reset.css" />
	<link rel="stylesheet" type="text/css" href="style/theme-alt.css" />
	<script src="lib/js/jquery-2.1.1.min.js"></script>
	<!-- added the masonry js so all blocks are better alligned -->
	<script src="lib/js/masonry.pkgd.min.js"></script>
	<!-- Removed this temporary because I disliked the look -->
	<!--<body class="js-masonry"  data-masonry-options='{ "columnWidth": 250, "itemSelector": ".groupbox" }'>-->
<body id="bg-two">
<!-- START GET RENDER DATE -->

<div id="timestamp">
    <div id="date"><?php echo date("d F Y", time()); ?></div>
    <div id="time"><?php echo date("H:i", time()); ?></div>
</div>
<!-- END GET RENDER DATE -->

<!-- We could use the HostGroup name here --> 
<div id="sheetname">Windows 2012</div>

<?php
// get hostgroupid with hosts
    $groupids = $api->hostgroupGet(array(
	'output' => 'extend',
	'selectHosts' => 'extend',
	'select_acknowledges' => 'extend',
	'only_true' => '1'
    ));


// get all hosts from each groupid
    foreach($groupids as $groupid) {
	$groupname = $groupid->name;
	$hosts = $groupid->hosts;

	if ($hosts) {
    	$count = "0";
//	echo "<div class=\"groupbox\">"; // again, we dont want to use the groupfunction yet
//      echo "<div class=\"title\">" . $groupname . "</div>";

    // print all host IDs
    		foreach($hosts as $host) {
			// Check if host is not disabled
			$flaghost = $host->flags;

			if ($flaghost == "0" && $count == "0") {
				echo "<div class=\"groupbox js-masonry\" data-masonry-options='{ \"itemSelector\": \".hostbox\" }'\">";
        			// echo "<div class=\"title\">" . $groupname . "</div>";
				$count++;
			}

			if ($flaghost == "0" && $count != "0") {

	        		$hostid = $host->hostid;
				$hostname = $host->name;
				$maintenance = $host->maintenance_status;
		
				$trigger = $api->triggerGet(array(
					'output' => 'extend',
					'hostids' => $hostid,
					'sortfield' => 'priority',
					'sortorder' => 'DESC',
					'only_true' => '1',
					'active' => '1', // include trigger state active not active
					'withUnacknowledgedEvents' => '1' // show only unacknowledgeevents
				));
	
				if ($trigger) {

					//Highest Priority error
					$hostboxprio = $trigger[0]->priority; 
					if ($maintenance != "0") {
						echo "<div class=\"hostbox maintenance\">";
					} else {
						echo "<div class=\"hostbox nok" . $hostboxprio . "\">";
					}
					echo "<div class=\"title\">" . $hostname . "</div><div class=\"hostid\">" . $hostid . "</div>";
					$count = "0";
					foreach ($trigger as $event) {
						if ($count++ <= 2 ) { 
       	        					$priority = $event->priority;
       							$description = $event->description;
				
							// Remove hostname or host.name in description
							$search = array('{HOSTNAME}', '{HOST.NAME}');
							$description = str_replace($search, "", $description);
				
							// View
       							echo "<div class=\"description nok" . $priority ."\">" . $description . "</div>";
						} else {
							break;
						}		
					}
				} else {
					echo "<div class=\"hostbox ok\">";
	       	 		        echo "<div class=\"title\">" . $hostname . "</div><div class=\"hostid\">" . $hostid . "</div>";
				}
				echo "</div>";
			}
    		}
        if ($count != "0") {echo "</div>";}
	}
    }
?> 
<!-- Second piece of js to gracefully reload the page -->
<script>
	function ReloadPage() {
	   location.reload();
	};
	$(document).ready(function() {
	  setTimeout("ReloadPage()", 60000);
	});
</script> 
</body>
</html>
