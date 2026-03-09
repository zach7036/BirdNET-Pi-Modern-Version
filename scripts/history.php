<?php

/* Prevent XSS input */
$_GET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

error_reporting(E_ALL);
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
require_once 'scripts/common.php';
$config = get_config();

if(isset($_GET['date'])){
$theDate = $_GET['date'];
} else {
$theDate = date('Y-m-d');
}
$chart = "Combo-$theDate.png";
$chart2 = "Combo2-$theDate.png";

$db = new SQLite3('./scripts/birds.db', SQLITE3_OPEN_READONLY);
$db->busyTimeout(1000);

$statement1 = $db->prepare("SELECT COUNT(*) FROM detections WHERE Date == \"$theDate\"");
ensure_db_ok($statement1);
$result1 = $statement1->execute();
$totalcount = $result1->fetchArray(SQLITE3_ASSOC);

if(isset($_GET['blocation']) ) {

	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=result_file.csv");
	header("Pragma: no-cache");
	header("Expires: 0");

	$list = array ();

	//$hrsinday = intval(($sunset-$sunrise)/60/60);
	$hrsinday = 24;
	for($i=0;$i<$hrsinday;$i++) {
		$starttime = strtotime("12 AM") + (3600*$i);

		$statement1 = $db->prepare("SELECT DISTINCT(Com_Name), COUNT(*) FROM detections WHERE Date == \"$theDate\" AND Time > '".date("H:i", $starttime)."' AND Time < '".date("H:i",$starttime + 3600)."' AND Confidence > 0.75 GROUP By Com_Name ORDER BY COUNT(*) DESC");
		ensure_db_ok($statement1);
		$result1 = $statement1->execute();

		$detections = [];
		while($detection=$result1->fetchArray(SQLITE3_ASSOC))
		{
			$detections[$detection["Com_Name"]] = $detection["COUNT(*)"];
		}
		foreach($detections as $com_name=>$scount)
		{
			array_push($list, array($com_name,'','','1','',$_GET['blocation'],$config["LATITUDE"],$config["LONGITUDE"],date("m/d/Y", strtotime($theDate)), date("H:i", $starttime), $_GET['state'], $_GET['country'], $_GET['protocol'], $_GET['num_observers'], '60', 'Y', $_GET['dist_traveled'],'',$_GET['notes'] ) );
		}
	}

	$output = fopen("php://output", "w");
    foreach ($list as $row) {
        fputcsv($output, $row);
    }
    fclose($output);

	die();
}
if (get_included_files()[0] === __FILE__) {
	echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>';
	}
?>
<style>
#attribution-dialog {
  border: none;
  border-radius: 16px;
  padding: 0;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
  max-width: 480px;
  width: 90%;
  overflow: visible;
  background: var(--bg-card, #fff);
}
#attribution-dialog::backdrop {
  background: rgba(0,0,0,0.4);
  backdrop-filter: blur(4px);
}
.ebird-dialog-header {
  background: linear-gradient(135deg, var(--accent, #6366f1) 0%, #818cf8 100%);
  color: white;
  padding: 20px 24px;
  font-size: 1.2em;
  font-weight: 700;
  margin: 0;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
}
.ebird-dialog-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  overflow: visible; /* Prevent tooltips from being clipped */
}
.ebird-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.ebird-field label {
  font-size: 0.85em;
  font-weight: 600;
  color: var(--text-secondary, #64748b);
  text-transform: uppercase;
  letter-spacing: 0.3px;
}
.ebird-field input,
.ebird-field select {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid var(--border, #e2e8f0);
  background: var(--bg-input, #fff);
  color: var(--text-primary, #1e293b);
  font-size: 0.95em;
  font-family: inherit;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.ebird-field input:focus,
.ebird-field select:focus {
  border-color: var(--accent, #6366f1);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
}
.ebird-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.ebird-actions {
  display: flex;
  gap: 10px;
  margin-top: 8px;
}
.ebird-btn-submit {
  flex: 1;
  padding: 12px;
  border: none !important;
  border-radius: 10px;
  background: #6366f1 !important;
  color: white !important;
  font-size: 1em;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s ease;
}
.ebird-btn-submit:hover { background: #4f46e5 !important; color: white !important; transform: translateY(-1px); }
.ebird-btn-cancel {
  padding: 12px 20px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 10px;
  background: transparent;
  color: var(--text-secondary, #64748b);
  font-size: 1em;
  font-weight: 500;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s ease;
}
.ebird-btn-cancel:hover { background: var(--bg-hover, #f8fafc); }
.ebird-success {
  padding: 24px;
  text-align: center;
}
.ebird-success h3 {
  color: #166534;
  font-size: 1.4em;
  margin: 0 0 12px 0;
}
.ebird-success p {
  color: var(--text-secondary, #64748b);
  line-height: 1.6;
  margin: 0 0 16px 0;
}
.ebird-success .note {
  font-size: 0.85em;
  padding: 12px;
  background: #fef9c3;
  border-radius: 10px;
  color: #854d0e;
  margin-bottom: 16px;
}
.ebird-export-trigger {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  border: none !important;
  border-radius: 12px;
  background: #6366f1 !important;
  color: white !important;
  font-size: 1em;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s ease;
  box-shadow: 0 4px 6px -1px rgba(99,102,241,0.3);
}
.ebird-export-trigger:hover { background: #4f46e5 !important; color: white !important; transform: translateY(-2px); box-shadow: 0 8px 15px -3px rgba(99,102,241,0.4); }

/* Tooltip Styles */
.ebird-tooltip {
  position: relative;
  display: inline-flex;
  margin-left: 4px;
  color: var(--text-muted, #94a3b8);
  cursor: help;
  vertical-align: middle;
  transition: color 0.2s ease;
}
.ebird-tooltip:hover {
  color: var(--accent, #6366f1);
}
.ebird-tooltip svg {
  width: 14px;
  height: 14px;
}
.ebird-tooltip .ebird-tooltip-text {
  visibility: hidden;
  width: 220px;
  background-color: #1e293b;
  color: #f8fafc;
  text-align: left;
  border-radius: 8px;
  padding: 12px 14px;
  position: absolute;
  z-index: 9999;
  bottom: calc(100% + 10px);
  left: 50%;
  transform: translateX(-50%) translateY(5px);
  opacity: 0;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 0.85rem;
  font-weight: 500;
  line-height: 1.5;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3);
  pointer-events: none;
  white-space: normal;
  text-transform: none;
  letter-spacing: normal;
}
.ebird-tooltip .ebird-tooltip-text::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  margin-left: -6px;
  border-width: 6px;
  border-style: solid;
  border-color: #1e293b transparent transparent transparent;
}
.ebird-tooltip:hover .ebird-tooltip-text {
  visibility: visible;
  opacity: 1;
  transform: translateX(-50%) translateY(0);
}
</style>

<script src="static/dialog-polyfill.js"></script>
<div class="history centered">

<dialog id="attribution-dialog">
  <p style="display:none" id="filename"></p>
  <div class="ebird-dialog-header">📋 eBird Checklist Export</div>
  <div class="ebird-dialog-body">
    <div class="ebird-row">
      <div class="ebird-field">
        <label>Export Date <span style="color:red">*</span> <span class="ebird-tooltip"><svg xmlns="http://www.apache.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg><span class="ebird-tooltip-text">The calendar date to export detections from. eBird checklists must correspond to a single calendar date.</span></span></label>
        <input type="date" id="export_date" value="<?php echo $theDate; ?>" required>
      </div>
      <div class="ebird-field">
        <label>Location Name <span style="color:red">*</span> <span class="ebird-tooltip"><svg xmlns="http://www.apache.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg><span class="ebird-tooltip-text">A descriptive name for where your BirdNET-Pi is located (e.g., "My Backyard" or "McFadden Marsh").</span></span></label>
        <input placeholder="e.g. My backyard" id="blocation" required>
      </div>
    </div>
    <div class="ebird-row">
      <div class="ebird-field">
        <label>State Code <span style="color:red">*</span> <span style="font-weight: normal; font-size: 0.85em; color: var(--text-muted);">(1-3 letters, e.g., OH)</span> <span class="ebird-tooltip"><svg xmlns="http://www.apache.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg><span class="ebird-tooltip-text">The state or province where the station is located. Must be 1-3 letters.</span></span></label>
        <input type="text" maxlength="3" pattern="[A-Za-z]{1,3}" style="text-transform: uppercase;" placeholder="e.g. OH" id="state" oninput="this.value = this.value.toUpperCase()" required>
      </div>
      <div class="ebird-field">
        <label>Country Code <span style="color:red">*</span> <span style="font-weight: normal; font-size: 0.85em; color: var(--text-muted);">(Exactly 2 letters, e.g., US)</span> <span class="ebird-tooltip"><svg xmlns="http://www.apache.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg><span class="ebird-tooltip-text">The country where the station is located. Must be exactly 2 letters.</span></span></label>
        <input type="text" maxlength="2" minlength="2" pattern="[A-Za-z]{2}" style="text-transform: uppercase;" placeholder="e.g. US" id="country" oninput="this.value = this.value.toUpperCase()" required>
      </div>
    </div>
    <div class="ebird-row">
      <div class="ebird-field">
        <label>Protocol <span style="color:red">*</span> <span class="ebird-tooltip"><svg xmlns="http://www.apache.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg><span class="ebird-tooltip-text">How you went birding. For fixed automated recorders, "Stationary" is almost always the correct protocol to use.</span></span></label>
        <select id="protocol" required>
          <option value="casual">Casual</option>
          <option selected value="stationary">Stationary</option>
          <option value="traveling">Traveling</option>
          <option value="area">Area</option>
        </select>
      </div>
      <div class="ebird-field">
        <label>Observers <span style="color:red">*</span> <span class="ebird-tooltip"><svg xmlns="http://www.apache.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg><span class="ebird-tooltip-text">The number of people birding. For an automated recorder, this is typically 1.</span></span></label>
        <input type="number" placeholder="1" id="num_observers" value="1" required>
      </div>
    </div>
    <div class="ebird-field">
      <label>Distance Traveled (miles) <span class="ebird-tooltip"><svg xmlns="http://www.apache.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg><span class="ebird-tooltip-text">Only required if you select the "Traveling" protocol. Leave blank for Stationary recorders.</span></span></label>
      <input type="number" placeholder="0" id="dist_traveled">
    </div>
    <div class="ebird-field">
      <label>Notes <span class="ebird-tooltip"><svg xmlns="http://www.apache.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg><span class="ebird-tooltip-text">Optional checklist comments (e.g., "Automated recording station run by BirdNET-Pi").</span></span></label>
      <input placeholder="Optional notes..." id="notes">
    </div>
    <div class="ebird-actions">
      <button class="ebird-btn-cancel" onclick="closeDialog()">Cancel</button>
      <button class="ebird-btn-submit" onclick="submitID()">Export Checklist</button>
    </div>
  </div>
</dialog>
<script>
var dialog = document.querySelector('dialog');
dialogPolyfill.registerDialog(dialog);

function showDialog() {
  document.getElementById('attribution-dialog').showModal();
}

function closeDialog() {
  document.getElementById('attribution-dialog').close();
}

function submitID() {
  var blocation = document.getElementById("blocation").value.trim();
  var state = document.getElementById("state").value.trim();
  var country = document.getElementById("country").value.trim();
  var protocol = document.getElementById("protocol").value;
  var num_observers = document.getElementById("num_observers").value;
  var dist_traveled = document.getElementById("dist_traveled").value;
  var notes = document.getElementById("notes").value;
  var export_date = document.getElementById("export_date").value;

  if (!blocation || !state || !country || !num_observers || !export_date) {
      alert("Please fill out all required fields (marked with an asterisk).");
      return;
  }
  
  if (country.length !== 2) {
      alert("Country Code must be exactly 2 letters.");
      return;
  }

  if (protocol === "traveling" && !dist_traveled) {
      alert("Distance Traveled is required for the Traveling protocol.");
      return;
  }

  window.open("history.php?blocation="+blocation+"&state="+state+"&country="+country+"&protocol="+protocol+"&num_observers="+num_observers+"&dist_traveled="+dist_traveled+"&notes="+notes+"&date="+export_date);

  document.getElementById('attribution-dialog').innerHTML = "<div class='ebird-dialog-header'>✅ Export Complete</div><div class='ebird-success'><h3>Success!</h3><p>Your checklist will start downloading momentarily.</p><p>Refer to <a target='_blank' href='https://ebird.org/content/eBirdCommon/docs/ebird_import_data_process.pdf'>this guide</a> for information on how to import it in eBird.<br>The checklist file format is: 'eBird Record Format (Extended)'.</p><div class='note'>Only detections with confidence &gt; 75% were included. Entries are limited to 1 per hour per species to comply with eBird guidelines. Always verify your checklist before submitting.</div><button class='ebird-btn-submit' onclick='closeDialog()'>Close</button></div>";

}

</script>  

<div style="text-align: center; margin: 20px auto; max-width: 650px;">
    <div class="ebird-info-box" style="background: var(--bg-secondary, #f1f5f9); padding: 20px; border-radius: 12px; font-size: 0.9em; color: var(--text-secondary); margin-bottom: 24px; line-height: 1.5; text-align: left; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
      <strong style="color: var(--text-heading); font-size: 1.15em; border-bottom: 2px solid var(--accent); padding-bottom: 4px; display: inline-block; margin-bottom: 12px;">What gets exported?</strong><br>
      A properly formatted <strong>Comma Separated Values (.csv)</strong> file using the <em>eBird Record Format</em>.<br>
      This file contains all detections for your selected date with <strong>&gt;75% confidence</strong>. To comply with eBird guidelines for automated recorders, data is automatically aggregated to a maximum of <strong>1 entry per species per hour</strong>.<br><br>
      
      <strong style="color: var(--text-heading); font-size: 1.15em; border-bottom: 2px solid var(--accent); padding-bottom: 4px; display: inline-block; margin-bottom: 12px; margin-top: 8px;">How to upload your data:</strong>
      <ol style="margin-top: 0; padding-left: 20px;">
        <li style="margin-bottom: 6px;">Click the button below to generate and download your <code>result_file.csv</code>.</li>
        <li style="margin-bottom: 6px;">Go to the <a href="https://ebird.org/submit" target="_blank" style="color: var(--accent); font-weight: bold; text-decoration: underline;">eBird Submit Data page</a> and choose <strong>Import Data</strong>.</li>
        <li style="margin-bottom: 6px;">Select <strong>eBird Record Format (Extended)</strong> as the format.</li>
        <li style="margin-bottom: 6px;">Upload your <code>.csv</code> file.</li>
        <li>Review your imported data in the "Cleaning up your imported data" step (you may need to match some species names to eBird's taxonomy, e.g., mapping Feral Pigeons).</li>
      </ol>
      
      <div style="background: #fef9c3; color: #854d0e; padding: 10px 14px; border-radius: 8px; margin-top: 16px; font-size: 0.9em;">
        <strong>Note:</strong> You cannot bulk upload audio recordings. Media files must be attached manually to their corresponding checklists <em>after</em> they have been imported.
      </div>
    </div>
    <button type="button" class="ebird-export-trigger" onclick="showDialog()">📥 Export as CSV for eBird</button>
</div>

<?php
echo "</div>";
if (get_included_files()[0] === __FILE__) {
	echo '</html>
</body>';
}
