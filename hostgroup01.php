<?php
// load the Zabbix Php API which is included in this build (tested on Zabbix v2.2.2)
require 'lib/php/ZabbixApiAbstract.class.php';
require 'lib/php/ZabbixApi.class.php';
// connect to Zabbix Json API
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
	<!-- Let's reset the default style properties -->
	<link rel="stylesheet" type="text/css" href="style/reset.css" />
	<link rel="stylesheet" type="text/css" href="style/theme-alt.css" />
	<!-- added the jQuery library for reloading the page and future features -->
	<script src="lib/js/jquery-2.1.1.min.js"></script>
	<!-- added the masonry js so all blocks are better alligned -->
	<script src="lib/js/masonry.pkgd.min.js"></script>
	<!-- Removed this temporary because I disliked the look -->
	<!--<body class="js-masonry"  data-masonry-options='{ "columnWidth": 250, "itemSelector": ".groupbox" }'>-->
<body id="bg-two">
	
<!-- START GET RENDER DATE - Which will show date and time of generating this file -->
<div id="timestamp">
    <div id="date"><?php echo date("d F Y", time()); ?></div>
    <div id="time"><?php echo date("H:i", time()); ?></div>
</div>
<!-- END GET RENDER DATE -->

<!-- We could use the Zabbix HostGroup name here, but would not work in a nice way when using a dozen of hostgroups, yet! So we hardcoded it here. --> 
<div id="sheetname">Your Group</div>

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
//	echo "<div class=\"groupbox\">"; // Again, we dont want to use the groupfunction yet
//      echo "<div class=\"title\">" . $groupname . "</div>";

    // print all host IDs
    		foreach($hosts as $host) {
			// Check if host is not disabled, we don't want them!
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

					// Highest Priority error
					$hostboxprio = $trigger[0]->priority;
					//First filter the hosts that are in maintenance and assign the maintenance class if is true
					if ($maintenance != "0") {
						echo "<div class=\"hostbox maintenance\">";
					} 
					// If hosts are not in maintenance, check for trigger(s) and assign the appropriate class to the box 
					else {
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
					} 
					// If there are no trigger(s) for the host found, assign the "ok" class to the box
					else {
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
<!-- Second piece of js to gracefully reload the page (value in ms) -->
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
