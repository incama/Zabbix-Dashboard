Zabbix-Dashboard
================

Trying to build a better dashboard function for Zabbix via the php api.

<img src="http://all-about-incama.org/wp-content/uploads/2014/11/Zabbix-Alternative-Dashboard1-1024x351.png" />

<strong>Features/requirements:</strong>
<ul>
<li>Gets triggers from hosts which are nested in hostgroups</li>
<li>Currently we defined differrent users (within Zabbix) per hostgroup with read only rights </li>
<li>Screens are optimized for 1920px capable monitors</li>
<li>Masonry js library is used to align host blocks tightly</li>
<li>Requires <a href="http://zabbixapi.confirm.ch">the Zabbix php api</a> which is included in this build</li>
</ul>
  
<strong>Host block features:</strong>
<ul>
    <li>Each host block displays a maximum of 3 triggers</li>
    <li>In case of multipe triggers fired on a host, the highest priority trigger will adjust the color and or size of the hostblock</li>
    <li>There are 5 stages defined in which a block is displayed based upon trigger severity</li>
    <li>Triggered host blocks will get the state normal when the trigger state is "OK" (via acknowledgment of trigger or threshold level is normal)</li>
</ul>

I have tested the dashboard on Zabbix 2.2.2 but I think it will work fine in 2.4, although you might need a newer php api version.
As we make use of the Zabbix php api, we have included it's GPLv3 license
