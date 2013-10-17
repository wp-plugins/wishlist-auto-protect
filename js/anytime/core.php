<?php
function ATCKey()
{
	?>
<div class="wrap">
	<h2>Automatic Content Protector</h2>
	<form method="post">
		<table class="form-table">
			<tr valign="top">
				<td colspan="3" style="border: none"><?php
				_e('Please enter your Automatic Content Protector Key and Email below to activate this plugin', 'automatic-content-protect');
				?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="border: none; white-space: nowrap;"
					class="WLRequired"><?php
					_e('Automatic Product Protector Key', 'automatic-content-protect');
					?>
				</th>
				<td style="border: none"><input type="text"
					name="<?php
            $this->Option('LicenseKey', true);
?>"
					value="<?php
            $this->OptionValue();
?>" size="32" /></td>
				<td style="border: none"><?php
				_e('(This was sent to the email you used during your purchase)', 'automatic-content-protect');
				?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="border: none; white-space: nowrap"
					class="WLRequired"><?php
					_e('WishList Products Email', 'automatic-content-protect');
					?>
				</th>
				<td style="border: none"><input type="text"
					name="<?php
            $this->Option('LicenseEmail', true);
?>"
					value="<?php
            $this->OptionValue();
?>" size="32" /></td>
				<td style="border: none"><?php
				_e('(Please enter the email you used during your registration/purchase)', 'automatic-content-protect');
				?>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="hidden" value="0"
				name="<?php
            $this->Option('LicenseLastCheck');
?>" />
<?php
$this->Options();
$this->RequiredOptions();
?>
			<input type="hidden"
				value="<strong>License Information Saved</strong>"
				name="WLSaveMessage" /> <input type="hidden" value="Save"
				name="WishListMemberAction" /> <input type="submit"
				value="Save Automatic Content Protect Product Key" name="Submit" />
		</p>
	</form>
</div>
<?php
} // End Function ATCKey 


function ATCKeyProcess()
{
	
	$ATCKey    = $this->GetOption('LicenseKey'); // Product Key 
	$ATCEmail  = $this->GetOption('LicenseEmail'); // Email 
	$ATCLast   = $this->GetOption('LicenseLastCheck'); // Time last check string 
	$ATCPID    = $this->ProductSKU; // Product ID 
	
	
	$ATCCheck     = md5("{$ATCKey}_{$ATCPID}_" . ($ATCURL = strtolower(get_bloginfo('url'))));

	// Set Key Action ID, True = Deactivated , False = Activate 
	$ATCKeyAction = $_POST['wordpress_wishlist_deactivate'] == $ATCPID ? 'deactivate' : 'activate';
	
	
	// Get Current Time Strinf 
	$ATCTime      = time();
	
	// Check if the the from is last check is past or the action is decativate
	if ($ATCTime - 43200 > $ATCLast || $ATCKeyAction == 'deactivate') {
		
		$url        = 'http://wishlista3ctivation.com/activ8.php?key=' . urlencode($ATCKey) . '&pid=' . urlencode($ATCPID) . '&check=' . urlencode($ATCCheck) . '&email=' . urlencode($ATCEmail) . '&url=' . urlencode($ATCURL) . '&' . $ATCKeyAction . '=1&ver=' . urlencode($this->Version);
		
		$ATCStatus = $ATCCheckResponse = $this->ReadURL($url);
		
		
		if ($ATCStatus === false) {

			exec('wget -q -O - "' . $url . '"', $output, $error);
			if (!$error) {
				$ATCStatus = $ATCCheckResponse = trim(implode("\n", $output));
			} else {
				$ATCStatus = $ATCCheckResponse = 'Unable to contact License Activation Server.<br />Your WL Member License cannot be activated.';
			} // end if 
		} else if (trim($ATCStatus) == '') {
			$ATCStatus = $ATCCheckResponse = 'Theres a problem with your connection to our License Activation Server.<br />Your WL Member License cannot be activated.';
		} // end elseif 
		
		$ATCStatus = trim($ATCStatus);
		SaveOption('LicenseLastCheck', $ATCTime);
		SaveOption('LicenseStatus', $ATCStatus);
		
		if ($ATCKeyAction == 'deactivate') {
			$this->DeleteOption('LicenseKey', 'LicenseEmail');
		} // end if 
	} // end check time and deactivated action 
	
	$this->ATCCheckResponse = $ATCCheckResponse;
	if ($this->GetOption('LicenseStatus') != '1') {
		add_action('admin_notices', array(
		&$this,
                    'ATCKeyResponse'
                    ), 1);
                    $this->DeleteOption('LicenseLastCheck');
	}
} // End function ATCKeyProcess


function ATCKeyResponse()
{
	if (strlen($this->ATCCheckResponse) > 1)
	echo '<div class="updated fade" id="message"><p style="color:#f00"><strong>' 
		 . $this->ATCCheckResponse . '</strong></p></div>';
} // End Function ATCKeyResponse 


function ReadURL($url, $timeout = null, $file_get_contents_fallback = null)
{
	$urls = (array) $url;

	if (is_null($timeout))
	$timeout = 30;
	
	if (is_null($file_get_contents_fallback))
	$file_get_contents_fallback = false;
	
	$x = false;
	
	foreach ($urls AS $url) {
		
		if (class_exists('WP_Http')) {
			$http = new WP_Http;
			$req  = $http->request($url, array(
	                        'timeout' => $timeout
			));
			$x    = (is_wp_error($req) OR is_null($req) OR $req === false) ? false : ($req['response']['code'] == '200' ? $req['body'] . '' : false);
		} else {
			$file_get_contents_fallback = true;
		}
		
		if ($x === false && ini_get('allow_url_fopen') && $file_get_contents_fallback) {
			$x = file_get_contents($url);
		}
		if ($x !== false) {
			return $x;
		}
	}
	return $x;
} // End Function ReadURL