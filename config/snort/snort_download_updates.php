<?php
/*
 * snort_download_updates.php
 * part of pfSense
 *
 * Copyright (C) 2004 Scott Ullrich
 * Copyright (C) 2011-2012 Ermal Luci
 * All rights reserved.
 *
 * part of m0n0wall as reboot.php (http://m0n0.ch/wall)
 * Copyright (C) 2003-2004 Manuel Kasper <mk@neon1.net>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

require_once("guiconfig.inc");
require_once("/usr/local/pkg/snort/snort.inc");

global $g, $snort_rules_upd_log, $snort_rules_file, $emergingthreats_filename;

$snortdir = SNORTDIR;

$log = $snort_rules_upd_log;

/* load only javascript that is needed */
$snort_load_jquery = 'yes';
$snort_load_jquery_colorbox = 'yes';
$snortdownload = $config['installedpackages']['snortglobal']['snortdownload'];
$emergingthreats = $config['installedpackages']['snortglobal']['emergingthreats'];
$snortcommunityrules = $config['installedpackages']['snortglobal']['snortcommunityrules'];

/* quick md5s chk */
$snort_org_sig_chk_local = 'N/A';
if (file_exists("{$snortdir}/{$snort_rules_file}.md5"))
	$snort_org_sig_chk_local = file_get_contents("{$snortdir}/{$snort_rules_file}.md5");

$emergingt_net_sig_chk_local = 'N/A';
if (file_exists("{$snortdir}/{$emergingthreats_filename}.md5"))
	$emergingt_net_sig_chk_local = file_get_contents("{$snortdir}/{$emergingthreats_filename}.md5");

$snort_community_sig_chk_local = 'N/A';
if (file_exists("{$snortdir}/{$snort_community_rules_filename}.md5"))
	$snort_community_sig_chk_local = file_get_contents("{$snortdir}/{$snort_community_rules_filename}.md5");

/* Check for postback to see if we should clear the update log file. */
if (isset($_POST['clear'])) {
	if (file_exists("{$snort_rules_upd_log}"))
		mwexec("/bin/rm -f {$snort_rules_upd_log}");
}

if (isset($_POST['update'])) {
	header("Location: /snort/snort_download_rules.php");
	exit;
}

/* check for logfile */
$snort_rules_upd_logfile_chk = 'no';
if (file_exists("{$snort_rules_upd_log}"))
	$snort_rules_upd_logfile_chk = 'yes';

$pgtitle = "Services: Snort: Updates";
include_once("head.inc");
?>

<body link="#000000" vlink="#000000" alink="#000000">

<?php include("fbegin.inc"); ?>
<?if($pfsense_stable == 'yes'){echo '<p class="pgtitle">' . $pgtitle . '</p>';}?>

<script language="javascript" type="text/javascript">
function popup(url) 
{
 params  = 'width='+screen.width;
 params += ', height='+screen.height;
 params += ', top=0, left=0'
 params += ', fullscreen=yes';

 newwin=window.open(url,'LogViewer', params);
 if (window.focus) {newwin.focus()}
 return false;
}

function wopen(url, name, w, h)
{
// Fudge factors for window decoration space.
// In my tests these work well on all platforms & browsers.
w += 32;
h += 96;
 var win = window.open(url,
  name, 
  'width=' + w + ', height=' + h + ', ' +
  'location=no, menubar=no, ' +
  'status=no, toolbar=no, scrollbars=yes, resizable=yes');
 win.resizeTo(w, h);
 win.focus();
}

</script>

<form action="snort_download_updates.php" method="post" name="iform" id="iform">

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr><td>
<?php
        $tab_array = array();
        $tab_array[0] = array(gettext("Snort Interfaces"), false, "/snort/snort_interfaces.php");
        $tab_array[1] = array(gettext("Global Settings"), false, "/snort/snort_interfaces_global.php");
        $tab_array[2] = array(gettext("Updates"), true, "/snort/snort_download_updates.php");
        $tab_array[3] = array(gettext("Alerts"), false, "/snort/snort_alerts.php");
        $tab_array[4] = array(gettext("Blocked"), false, "/snort/snort_blocked.php");
        $tab_array[5] = array(gettext("Whitelists"), false, "/snort/snort_interfaces_whitelist.php");
        $tab_array[6] = array(gettext("Suppress"), false, "/snort/snort_interfaces_suppress.php");
        display_top_tabs($tab_array);
?>
</td></tr>
<tr>
		<td>
		<div id="mainarea3">
		<table id="maintable4" class="tabcont" width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr align="center">
				<td>
				<br/>
				<table id="download_rules" height="32px" width="725px" border="0"
					cellpadding="5px" cellspacing="0">
					<tr>
						<td id="download_rules_td" style="background-color: #eeeeee">
						<div height="32" width="725px" style="background-color: #eeeeee">

						<font color="#777777" size="2.5px">
						<p style="text-align: left; margin-left: 225px;">
							<b><?php echo gettext("INSTALLED RULESET SIGNATURES"); ?></b></font><br/>
							<font color="#FF850A" size="1px"><b>SNORT.ORG&nbsp;&nbsp;--></b></font>
							<font size="1px" color="#000000">&nbsp;&nbsp;<? echo $snort_org_sig_chk_local; ?></font><br>
							<font color="#FF850A" size="1px"><b>EMERGINGTHREATS.NET&nbsp;&nbsp;--></b></font>
							<font size="1px" color="#000000">&nbsp;&nbsp;<? echo $emergingt_net_sig_chk_local; ?></font><br>
							<font color="#FF850A" size="1px"><b>SNORT GPLv2 COMMUNITY RULES&nbsp;&nbsp;--></b></font>
							<font size="1px" color="#000000">&nbsp;&nbsp;<? echo $snort_community_sig_chk_local; ?></font><br>
						</p>
						</div>
						</td>
					</tr>
				</table>
				<br/>
				<table id="download_rules" height="32px" width="725px" border="0"
					cellpadding="5px" cellspacing="0">
					<tr>
						<td id="download_rules_td" style='background-color: #eeeeee'>
						<div height="32" width="725px" style='background-color: #eeeeee'>

						<p style="text-align: left; margin-left: 225px;">
						<font color='#777777' size='2.5px'><b><?php echo gettext("UPDATE YOUR RULESET"); ?></b></font><br>
						<br/>

			<?php

						if ($snortdownload != 'on' && $emergingthreats != 'on') {
							echo '
			<button disabled="disabled"><span class="download">' . gettext("Update Rules") . '</span></button><br/>
			<p style="text-align:left; margin-left:150px;">
			<font color="#fc3608" size="2px"><b>' . gettext("WARNING:") . '</b></font><font size="1px" color="#000000">&nbsp;&nbsp;' . gettext('No rule types have been selected for download. ') .
			gettext('Visit the ') . '<a href="snort_interfaces_global.php">Global Settings Tab</a>' . gettext(' to select rule types.') . '</font><br>';

							echo '</p>' . "\n";
						} else {

							echo '
			<input type="submit" value="' . gettext("Update Rules") . '" name="update" id="Submit" class="formbtn" /><br/>' . "\n";

						}

			?> <br/>
						</p>
						</div>
						</td>
					</tr>
				</table>
				<br/>
				<table id="download_rules" height="32px" width="725px" border="0"
					cellpadding="5px" cellspacing="0">
					<tr>
						<td id="download_rules_td" style='background-color: #eeeeee'>
						<div height="32" width="725px" style='background-color: #eeeeee'>

						<p style="text-align: left; margin-left: 225px;">
						<font color='#777777' size='2.5px'><b><?php echo gettext("VIEW UPDATE LOG"); ?></b></font><br>
						<br>
				<?php

						if ($snort_rules_upd_logfile_chk == 'yes') {
							echo "
				<button class=\"formbtn\" onclick=\"wopen('snort_log_view.php?logfile={$log}', 'LogViewer', 800, 600)\"><span class='pwhitetxt'>" . gettext("View Log") . "</span></button>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Clear Log\" name=\"clear\" id=\"Submit\" class=\"formbtn\" />\n";
						}else{
							echo "
				<button disabled='disabled'><span class='pwhitetxt'>" . gettext("View Log") . "</span></button>&nbsp;&nbsp;&nbsp;" . gettext("Log is empty.") . "\n";
						}
						echo '<br><br>' . gettext("The log file is limited to 1024K in size and automatically clears when the limit is exceeded.");
				?>
						<br/>
						</p>
						</div>
						</td>
					</tr>
				</table>

				<br/>

				<table id="download_rules" height="32px" width="725px" border="0"
					cellpadding="5px" cellspacing="0">
					<tr>
						<td id="download_rules_td" style='background-color: #eeeeee'>
						<div height="32" width="725px" style='background-color: #eeeeee'>
							<font size='1px'><span class="red"><b><?php echo gettext("NOTE:"); ?></b></span></font><font size='1px'
								color='#000000'>&nbsp;&nbsp;<?php echo gettext("Snort.org and EmergingThreats.net " .
								"will go down from time to time. Please be patient."); ?>
							</font>
						</div>
						</td>
					</tr>
				</table>

				</td>
			</tr>
		</table>
		</div>
		<br>
		</td>
	</tr>
</table>
<!-- end of final table --></div>
		</form>
<?php include("fend.inc"); ?>
</body>
</html>
