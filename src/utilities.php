<?php
function timeDiff_Hours($from, $to) {
    $timeBegin = strtotime($from);
    $timeEnd = strtotime($to);
    return ($timeEnd - $timeBegin) / 3600;
}

function getCurrentTimestamp() {
    ini_set('date.timezone', 'UTC');
    $t = localtime(time(), true);
    return ($t["tm_year"] + 1900 . "-" . sprintf("%02d", ($t["tm_mon"] + 1)) . "-" . sprintf("%02d", $t["tm_mday"]) . " " . sprintf("%02d", $t["tm_hour"]) . ":" . sprintf("%02d", $t["tm_min"]) . ":" . sprintf("%02d", $t["tm_sec"]));
}

function carryOverAdder_Hours($a, $b) {
    if ($a == '0000-00-00 00:00:00') { return $a; }
    $date = new DateTime($a);
    if ($b < 0) {
        $b *= -1;
		$hours = floor($b);
        $date->sub(new DateInterval("PT" . $hours . "H"));
		$minutes = round(($b - $hours) * 60);
		$date->sub(new DateInterval("PT" . $minutes . "M"));
    } else {
		$hours = floor($b);
        $date->add(new DateInterval("PT" . $hours . "H"));
		$minutes = round(($b - $hours) * 60);
		$date->add(new DateInterval("PT" . $minutes . "M"));
    }
    return $date->format('Y-m-d H:i:s');
}

function isHoliday($ts) {
    global $conn;
    $result = $conn->query("SELECT * FROM holidays WHERE begin LIKE '" . substr($ts, 0, 10) . "%'"); //the sql %(§) comparison stopped working
    while($result && ($row = $result->fetch_assoc())){
        if(strpos($row['name'], '(§)')) return true;
    }
    return false;
}

function test_input($data, $strong = false) {
    if($strong){
        $data = preg_replace("/[^A-Za-z0-9]/", ' ', $data);
    } else {
        $data = preg_replace("~[^A-Za-z0-9\-?!=:.,/@€&§#$%()+*öäüÖÄÜß_\\n ]~", ' ', $data);
        //$regex_names = "/([^-_@A-Za-z0-9ąa̧ ɓçđɗɖęȩə̧ɛ̧ƒɠħɦįi̧ ɨɨ̧ƙłm̧ ɲǫo̧ øơɔ̧ɍşţŧųu̧ ưʉy̨ƴæɑðǝəɛɣıĳɩŋœɔʊĸßʃþʋƿȝʒʔáàȧâäǟǎăāãåǽǣćċĉčďḍḑḓéèėêëěĕēẽe̊ ẹġĝǧğg̃ ģĥḥíìiîïǐĭīĩịĵķǩĺļľŀḽm̂ m̄ ŉńn̂ ṅn̈ ňn̄ ñņṋóòôȯȱöȫǒŏōõȭőọǿơp̄ ŕřŗśŝṡšşṣťțṭṱúùûüǔŭūũűůụẃẁŵẅýỳŷÿȳỹźżžẓǯÁÀȦÂÄǞǍĂĀÃÅǼǢĆĊĈČĎḌḐḒÉÈĖÊËĚĔĒẼE̊ ẸĠĜǦĞG̃ ĢĤḤÍÌIÎÏǏĬĪĨỊĴĶǨĹĻĽĿḼM̂ M̄ ʼNŃN̂ ṄN̈ ŇN̄ ÑŅṊÓÒÔȮȰÖȪǑŎŌÕȬŐỌǾƠP̄ ŔŘŖŚŜṠŠŞṢŤȚṬṰÚÙÛÜǓŬŪŨŰŮỤẂẀŴẄÝỲŶŸȲỸŹŻŽẒǮĄA̧ ƁÇĐƊƉĘȨƏ̧Ɛ̧ƑƓĦꞪĮI̧ ƗƗ̧ƘŁM̧ ƝǪO̧ ØƠƆ̧ɌŞŢŦŲU̧ ƯɄY̨ƳÆⱭÐƎƏƐƔIĲƖŊŒƆƱĸƩÞƲȜƷʔ]+)/";
        //$data = preg_replace_callback($regex_names, function($m){ return convToUTF8($m[1]); }, $data);
    }
    $data = trim($data);
    return $data;
}

function test_Date($date, $format = "Y-m-d H:i:s") {
    $dt = DateTime::createFromFormat($format, $date);
	if($dt && $dt->format($format) === $date) return $date;
    return false;
}

function test_Time($time) {
    return preg_match("/^([01][0-9]|2[0-3]):([0-5][0-9])$/", $time);
}

function displayAsHoursMins($hour) {
    $hours = round($hour, 2); //trust issues
    $s = '';
    if ($hours < 0) {
        $s = '-';
        $hours = $hours * -1;
    }
    if ($hours >= 1) {
        $s .= floor($hours) . 'h ';
        $hours = $hours - floor($hours);
    }
    $s .= round($hours * 60) . 'min';
    return $s;
}

function redirect($url) {
    if (!headers_sent()) {
        header('Location: ' . $url);
    } else {
        echo '<script type="text/javascript">';
        echo 'window.location.href="' . $url . '";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
        echo '</noscript>';
    }
    exit;
}

function simple_encryption($message, $key) {
    $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
    $nonce = openssl_random_pseudo_bytes($nonceSize);
    $ciphertext = openssl_encrypt($message, 'aes-256-ctr', $key, OPENSSL_RAW_DATA, $nonce);
    return base64_encode($nonce . $ciphertext);
}

function simple_decryption($message, $key) {
    $message = base64_decode($message, true);
    if ($message === false) return $message;

    $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
    $nonce = mb_substr($message, 0, $nonceSize, '8bit');
    $ciphertext = mb_substr($message, $nonceSize, null, '8bit');
    $plaintext = openssl_decrypt($ciphertext, 'aes-256-ctr', $key, OPENSSL_RAW_DATA, $nonce);
    return $plaintext;
}

//if anything fails, it will return message as-is
function secure_data($module, $message, $mode = 'encrypt', $userID = 0, $privateKey = '', &$err = ''){
    global $conn;
    static $activeEncryption = null; //http://php.net/manual/en/language.variables.scope.php#language.variables.scope.static
    if($activeEncryption === null){
        $activeEncryption = true;
        $result = $conn->query("SELECT activeEncryption FROM configurationData WHERE activeEncryption = 'TRUE'");
        if(!$result || $result->num_rows < 1){ $activeEncryption = false; }
    }
    if(!$activeEncryption) return $message;
    $privateKey = base64_decode($privateKey);
    static $symmetric = false;
	static $usedModule = '';
    if((!$symmetric || $usedModule != $module) && $userID && $privateKey){
		if(is_array($module)){
			$optionalID = $module[1];
			$module = $module[0];
			$result = $conn->query("SELECT privateKey FROM security_access a INNER JOIN security_modules m ON a.module = m.module AND m.outdated = 'FALSE'
				WHERE userID = $userID AND a.module = '$module' AND optionalID = '$optionalID' AND a.outDated = 'FALSE' ORDER BY a.recentDate LIMIT 1");
		} else {
			$result = $conn->query("SELECT privateKey FROM security_access a INNER JOIN security_modules m ON a.module = m.module AND m.outdated = 'FALSE'
				WHERE userID = $userID AND a.module = '$module' AND a.outDated = 'FALSE' ORDER BY a.recentDate LIMIT 1");
        }
        if($result && ( $row=$result->fetch_assoc() )){
			//echo $row['privateKey'] .' --encrypted base64 private module key<br>';
            $cipher_private_module = base64_decode($row['privateKey']);
			//echo ($cipher_private_module) .' --private key module<br>';
            $result = $conn->query("SELECT publicKey, symmetricKey FROM security_modules WHERE module = '$module' AND outDated = 'FALSE'");
			if($module == 'PRIVATE_PROJECT') $result = $conn->query("SELECT publicKey, symmetricKey FROM security_projects WHERE projectID = $optionalID AND outDated = 'FALSE'");
            if($result && ( $row=$result->fetch_assoc() )){
				$public_module = base64_decode($row['publicKey']);
				$nonce = mb_substr($cipher_private_module, 0, 24, '8bit');
				$cipher_private_module = mb_substr($cipher_private_module, 24, null, '8bit');
				$private_module = sodium_crypto_box_open($cipher_private_module, $nonce, $privateKey.$public_module);
				//echo base64_encode($privateKey) .' --private <br> keypairsize: '.strlen($privateKey.$public_module ).'<br> public--';
				//echo $private_module .'-- decrypted private module<br>';
				if(strlen($private_module.$public_module) != 64){
					$err = 'module keys do not have the right size';
					return $message;
				}
				$cipher_symmetric = base64_decode($row['symmetricKey']);
				$nonce = mb_substr($cipher_symmetric, 0, 24, '8bit');
				$cipher_symmetric = mb_substr($cipher_symmetric, 24, null, '8bit');
				$symmetric = sodium_crypto_box_open($cipher_symmetric, $nonce, $private_module.$public_module);
				$usedModule = $module;

				//echo base64_encode($symmetric) .' -- plain symmetric<br>';
                if(!$symmetric){
					$err = 'Could not retrieve symmetric Key';
					return $message;
                }
            } elseif($result){
                $err = 'Module encryption not active';
				return $message;
            }
        } elseif($result){
            $err = 'User Access not found';
			return $message;
        } elseif($conn->error){
			$err = $conn->error;
			return $message;
		}
    }

	if($symmetric && $module == $usedModule) {
        if($mode == 'encrypt'){
            return simple_encryption($message, $symmetric);
        } else {
            return simple_decryption($message, $symmetric);
        }
    }
    $err = 'Something went wrong';
	$symmetric = false;
    return $message;
}

/*
* decrypt: $mode = public user key (not from registered users, that's why)
*/
function asymmetric_encryption($module, $message, $userID, $privateKey, $mode = 'encrypt', &$err = ''){
	global $conn;
	$privateKey = base64_decode($privateKey);
	static $activeEncryption = null;
    if($activeEncryption === null){
        $activeEncryption = true;
        $result = $conn->query("SELECT activeEncryption FROM configurationData WHERE activeEncryption = 'TRUE'");
        if(!$result || $result->num_rows < 1){ $activeEncryption = false; }
    }
    if(!$activeEncryption || !$mode) return $message;
	$result = $conn->query("SELECT publicKey FROM security_modules WHERE module = '$module' AND outDated = 'FALSE' LIMIT 1");
	if($result && ( $row=$result->fetch_assoc() )){
		$public_module = base64_decode($row['publicKey']);
		if($mode != 'encrypt'){ //decrypt - publicUser, privateModule
			$mode = base64_decode($mode);
			$result = $conn->query("SELECT privateKey FROM security_access WHERE userID = $userID AND module = '$module' AND outDated = 'FALSE' ORDER BY recentDate LIMIT 1");
			if($result && ( $row=$result->fetch_assoc() )){
				$cipher_private_module = base64_decode($row['privateKey']);
				$nonce = mb_substr($cipher_private_module, 0, 24, '8bit');
				$encrypted = mb_substr($cipher_private_module, 24, null, '8bit');
				try{
					$private_module = sodium_crypto_box_open($encrypted, $nonce, $privateKey.$public_module);

					if(strlen($private_module.$mode) != 64){
						$err = 'module keys do not have the right size: '.strlen($private_module.$mode);
						return $message;
					}
					$message_crypt = base64_decode($message);
					$nonce = mb_substr($message_crypt, 0, 24, '8bit');
					$encrypted = mb_substr($message_crypt, 24, null, '8bit');
					return sodium_crypto_box_open($encrypted, $nonce, $private_module.$mode);
				} catch(Exception $e){
					// echo '<br> public module: '.$public_module;
					// echo '<br> key:'.$privateKey;
					// echo '<br> private module: '.$private_module;
				}
			}
		} else { //encrypt - privateUser, publicModule
			$nonce = random_bytes(24);
			return base64_encode($nonce . sodium_crypto_box($message, $nonce, $privateKey.$public_module));
		}
	}
	$err = $conn->error;
	return $message;
}

//userID and privateKey are only required when decrypting
function asymmetric_seal($module, $message, $mode = 'encrypt', $userID ='', $privateKey='',  &$err = ''){
	global $conn;
	$privateKey = base64_decode($privateKey);
	static $activeEncryption = null;
    if($activeEncryption === null){
        $activeEncryption = true;
        $result = $conn->query("SELECT activeEncryption FROM configurationData WHERE activeEncryption = 'TRUE'");
        if(!$result || $result->num_rows < 1){ $activeEncryption = false; }
    }
    if(!$activeEncryption || !$mode) return $message;
	$result = $conn->query("SELECT publicKey FROM security_modules WHERE module = '$module' AND outDated = 'FALSE' LIMIT 1");
	if($result && ( $row=$result->fetch_assoc() )){
		$public_module = base64_decode($row['publicKey']);
		if($mode == 'encrypt'){
			return base64_encode(sodium_crypto_box_seal($message, $public_module));
		} else {
			$result = $conn->query("SELECT privateKey FROM security_access WHERE userID = $userID AND module = '$module' AND outDated = 'FALSE' ORDER BY recentDate LIMIT 1");
			if($result && ( $row=$result->fetch_assoc() )){
				$cipher_private_module = base64_decode($row['privateKey']);
				$nonce = mb_substr($cipher_private_module, 0, 24, '8bit');
				$encrypted = mb_substr($cipher_private_module, 24, null, '8bit');
				try{
					$private_module = sodium_crypto_box_open($encrypted, $nonce, $privateKey.$public_module);
					if(strlen($private_module.$public_module) != 64){
						$err = 'module keys do not have the right size: '.strlen($private_module.$public_module);
						return $message;
					}
					return sodium_crypto_box_seal_open(base64_decode($message), $private_module.$public_module);
				} catch(Exception $e){
					// echo '<br> public module: '.$public_module;
					// echo '<br> key:'.$privateKey;
					// echo '<br> private module: '.$private_module;
				}
			} else {
				$err = 'Missing access to decrypt module';
				return $message;
			}
		}
	} else {
		$err = 'No public key found for this module';
	}
	$err .= $conn->error;
	return $message;
}

function mc_status($module = '', $key = 1){
	global $conn;
    static $encrypt = null;
    if($encrypt === null){
        $encrypt = false;
        $result = $conn->query("SELECT activeEncryption FROM configurationData WHERE activeEncryption = 'TRUE'");
        if($result && $result->num_rows) $encrypt = true;
    }
	$active_icon = '<i class="fa fa-lock text-success" aria-hidden="true" title="Encryption Aktiv. Verwendeter Schlüssel: '.$module.'"></i>';
	$inactive_icon = '<i class="fa fa-unlock text-danger" aria-hidden="true" title="Encryption Inaktiv. Vorbereiteter Schlüssel: '.$module.'"></i>';
	if(!$key) return $inactive_icon;
	if($encrypt){
		if($module){
			$result = $conn->query("SELECT id FROM security_modules WHERE module = '$module' AND outDated = 'FALSE'");
			if($result && $result->num_rows){
				global $userID;
				$result = $conn->query("SELECT id FROM security_access WHERE module = '$module' AND outDated = 'FALSE' AND userID = $userID");
				if($result && $result->num_rows) return $active_icon;
			}
			return $inactive_icon;
		}
		return $active_icon;
    }
	return $inactive_icon;
}

/*
* low - at least x characters (x from policy table)
* medium - at least low and one capital letter and one number
* high - at least medium and one special character
*/
function match_passwordpolicy($p, &$out = '') {
    global $conn;
    $result = $conn->query("SELECT passwordLength, complexity FROM policyData LIMIT 1");
    $row = $result->fetch_assoc();

    if (strlen($p) < $row['passwordLength']) {
        $out = "Password must be at least " . $row['passwordLength'] . " Characters long.";
        return false;
    }
    if ($row['complexity'] === '0') {
        return true;
    } elseif ($row['complexity'] === '1') {
        if (!preg_match('/[A-Z]/', $p) || !preg_match('/[0-9]/', $p)) {
            $out = "Password must contain at least one captial letter and one number";
            return false;
        }
    } elseif ($row['complexity'] === '2') {
        if (!preg_match('/[A-Z]/', $p) || !preg_match('/[0-9]/', $p) || !preg_match('/[~\!@#\$%&\*_\-\+\.\?]/', $p)) {
            $out = "Password must contain at least one captial letter, one number and one special character (~ ! @ # $ % & * _ - + . ?)";
            return false;
        }
    }
    return true;
}

function getNextERP($identifier, $companyID, $offset = 0) {
    global $conn;
    $result = $conn->query("SELECT * FROM erp_settings WHERE companyID = $companyID");
    echo $conn->error;
    if ($row = $result->fetch_assoc()) {
        $offset = $row['erp_' . strtolower($identifier)];
        $offset--;
        if ($offset < 0) {
            $offset = 0;
        }
    }
    $vals = array($offset);
    $result = $conn->query("SELECT id_number FROM processHistory, proposals, clientData WHERE processID = proposals.id AND clientID = clientData.id AND companyID = $companyID AND id_number LIKE '$identifier%'");
    echo $conn->error;
    while ($result && ($row = $result->fetch_assoc())) {
        $vals[] = intval(substr($row['id_number'], strlen($identifier)));
    }
    return $identifier . sprintf('%0' . (10 - strlen($identifier)) . 'd', max($vals) + 1);
}

function randomPassword($length = 8) {
    $pool = array('abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', '1234567890', '!@#$*+?');
    shuffle($pool);
    $psw = array();
    for ($i = 0; $i < $length; $i++) {
        $psw[] = $pool[$i % 4][rand(0, strlen($pool[$i % 4]) - 1)];
        if ($i > 3) {
            shuffle($pool);
        }
    }
    return implode($psw);
}

/*
* requires Calculator/IntervalCalculator
* query must contain WHERE clause
*/
function getFilledOutTemplate($templateID, $bookingQuery = "") {
    set_time_limit(60);
    require "connection.php";
    require "language.php";

    $t = localtime(time(), true);
    $today = $t["tm_year"] + 1900 . "-" . sprintf("%02d", ($t["tm_mon"] + 1)) . "-" . sprintf("%02d", $t["tm_mday"]);

    //grab template
    $result = $conn->query("SELECT htmlCode, userIDs FROM $pdfTemplateTable WHERE id = $templateID");
    if ($result && ($row = $result->fetch_assoc())) {
        $html = $row['htmlCode'];
        $userIDs = $row['userIDs'];
    } else {
        die("Could not fetch template. Please make sure it exists. Contact support for further issues."); //We dont actually have a support.
    }

    if (empty($userIDs)) { //a template can define the user data it wants to display
        $userIDs_query = "";
    } else {
        $userIDs_query = "WHERE id IN ($userIDs)";
    }

    if (strpos($html, "[TIMESTAMPS]") !== false) { //0 = false, but 0 is valid position
        $html_bookings = "<h3>Anwesenheit:</h3><table><tr><th>Name</th><th>Status</th><th>Von</th><th>Bis</th><th>Bewertung</th><th>Differenz</th><th>Saldo (Stunden)</th></tr>";
        //select all users and select log from today if exists else log = null
        $result = $conn->query("SELECT * FROM $userTable LEFT JOIN $logTable ON $logTable.userID = $userTable.id AND $logTable.time LIKE '$today %' $userIDs_query");
        echo mysqli_error($conn);
        while ($result && ($row = $result->fetch_assoc())) {
            $html_bookings .= "<tr><td>" . $row['firstname'] . ' ' . $row['lastname'] . "</td>";
            //did he check out?
            if (!empty($row['timeEnd']) && $row['timeEnd'] != '0000-00-00 00:00:00') {
                $timeEnd_Cell = '<td>' . substr(carryOverAdder_Hours($row['timeEnd'], $row['timeToUTC']), 11, 5) . '</td>';
                $diff = displayAsHoursMins(timeDiff_Hours($row['time'], $row['timeEnd']));
            } else {
                $timeEnd_Cell = '<td style="color:gold;">00:00</td>';
                $diff = ' - ';
            }
            //if a user did not check in at all, mark him as absent.
            if (empty($row['time'])) {
                $row['status'] = '-1';
            } else {
                $time_Cell = '<td>' . substr(carryOverAdder_Hours($row['time'], $row['timeToUTC']), 11, 5) . '</td>';
            }
            //if a user did not >work< dont display times (no correct core times available)
            if ($row['status'] != 0) {
                $time_Cell = '<td> - </td>';
                $timeEnd_Cell = '<td> - </td>';
            }

            if ($diff > 10 && $diff != ' - ') { //user was checked in for over 10 hours
                $diff_Cell = '<td style="color:red;">' . $diff . '</td>';
            } else {
                $diff_Cell = "<td>$diff</td>";
            }

            //SALDO calculation:
            $curID = $row['id'];
            $logSums = new Interval_Calculator($curID);
            $saldo = sprintf('%.2f', $logSums->saldo);
            if ($saldo > 20 || $saldo < -5) {
                $saldo_Cell = "<td style=\"color:red;\">$saldo</td>";
            } else {
                $saldo_Cell = "<td>$saldo</td>";
            }

            if ($row['emoji']) {
                $emoji_Cell = '<td>' . $lang['EMOJI_TOSTRING'][$row['emoji']] . '</td>';
            } else {
                $emoji_Cell = "<td> - </td>";
            }

            $html_bookings .= '<td>' . $lang['ACTIVITY_TOSTRING'][$row['status']] . '</td>' . "$time_Cell $timeEnd_Cell $emoji_Cell $diff_Cell $saldo_Cell</tr>";
        }
        $html_bookings .= "</table>";
        //replace
        $html = str_replace("[TIMESTAMPS]", $html_bookings, $html);
    }

    if (strpos($html, "[BOOKINGS]") !== false) {
        if (empty($bookingQuery)) {
            $bookingQuery = "WHERE $projectBookingTable.start LIKE '$today %'";
        }
        if (empty($userIDs)) { //a template can define the user data it wants to display
            $userIDs_query = "";
        } else {
            $userIDs_query = "AND $userTable.id IN ($userIDs)";
        }

        $html_bookings = "<h3>Buchungen</h3>";
        //grab projectbookings
        $sql = "SELECT $projectTable.id AS projectID,
    $clientTable.id AS clientID,
    $clientTable.name AS clientName,
    $companyTable.name AS companyName,
    $projectTable.name AS projectName,
    $projectBookingTable.*,
    $projectBookingTable.id AS projectBookingID,
    $logTable.timeToUTC,
    $userTable.firstname, $userTable.lastname,
    $projectTable.hours,
    $projectTable.hourlyPrice,
    $projectTable.status
    FROM $projectBookingTable
    INNER JOIN $logTable ON  $projectBookingTable.timeStampID = $logTable.indexIM
    INNER JOIN $userTable ON $logTable.userID = $userTable.id
    LEFT JOIN $projectTable ON $projectBookingTable.projectID = $projectTable.id
    LEFT JOIN $clientTable ON $projectTable.clientID = $clientTable.id
    LEFT JOIN $companyTable ON $clientTable.companyID = $companyTable.id
    $bookingQuery $userIDs_query
    ORDER BY $userTable.firstname, $projectBookingTable.start ASC";

        $result = $conn->query($sql);
        $prevName = "";
        //for each booking
        while ($result && ($row = $result->fetch_assoc())) {
            if ($prevName != $row['firstname']) {
                if ($prevName != "") { //cant close a table if this is the first.
                    $html_bookings .= '</table>';
                }
                $html_bookings .= '<h4>' . $row['firstname'] . '</h4><table><tr><th>Kunde</th><th>Projekt</th><th>Datum</th><th>Von</th><th>Bis</th><th>Infotext</th></tr>';
            }

            $start = carryOverAdder_Hours($row['start'], $row['timeToUTC']);
            $end = carryOverAdder_Hours($row['end'], $row['timeToUTC']);

            $html_bookings .= '<tr><td>' . $row['companyName'].' - '.$row['clientName'] . '</td>'; //5acc434437ddf
            $html_bookings .= '<td>' . $row['projectName'] . '</td>';
            $html_bookings .= '<td>' . substr($start, 0, 10) . '</td>';
            $html_bookings .= '<td>' . substr($start, 11, 5) . '</td><td>' . substr($end, 11, 5) . '</td>';
            $html_bookings .= '<td>' . $row['infoText'] . '</td></tr>';

            $prevName = $row['firstname'];
        } //end while
        $html_bookings .= '</table>';
        //replace
        $html = str_replace("[BOOKINGS]", $html_bookings, $html);
    }
    return $html;
}

function uploadImage($file_field, $crop_square = false, $resize = true) {
    $max_size = 5000000; //5mb
    $whitelist_ext = array('jpeg', 'jpg', 'png', 'gif');
    $whitelist_type = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');

    //Validation
    $out = array('error' => array());
    //Make sure that there is a file
    if ((!empty($_FILES[$file_field])) && ($_FILES[$file_field]['error'] == 0)) {
        // Get filename
        $file_info = pathinfo($_FILES[$file_field]['name']);
        $ext = strtolower($file_info['extension']);

        //Check file has the right extension
        if (!in_array($ext, $whitelist_ext)) {
            $out['error'][] = "Invalid file Extension";
        }
        //Check that the file is of the right type
        if (!in_array($_FILES[$file_field]["type"], $whitelist_type)) {
            $out['error'][] = "Invalid file Type";
        }
        //Check that the file is not too big
        if ($_FILES[$file_field]["size"] > $max_size) {
            $out['error'][] = "File is too big";
        }
        if (!getimagesize($_FILES[$file_field]['tmp_name'])) {
            $out['error'][] = "Uploaded file is not a valid image";
        }
        if (count($out['error']) > 0) {
            return $out;
        }
        //remove interlacing bit
        $im = file_get_contents($_FILES[$file_field]['tmp_name']);
        $im = @imagecreatefromstring($im); //suppress the warning, since im handling it anyways
        if (!$im) {
            return file_get_contents($_FILES[$file_field]['tmp_name']);
        }
        $corx = imagesx($im);
        $cory = imagesy($im);
        if ($crop_square && $corx != $cory) {
            $size = min($corx, $cory);
            $im = imagecrop($im, ['x' => 0, 'y' => 0, 'width' => $size, 'height' => $size]);
        }
        if ($resize && ($corx > 350 || $cory > 200)) {
            $aspect_ratio = $corx / $cory;
            if ($aspect_ratio > 1) {
                $x = 350;
                $y = 350 / $aspect_ratio;
            } else {
                $x = 200 / ($cory / $corx);
                $y = 200;
            }
            $im2 = imagecreatetruecolor($x, $y);
            imagecopyresampled($im2, $im, 0, 0, 0, 0, $x, $y, $corx, $cory); //much better quality than copyresized
            $im = $im2;
        }
        imageinterlace($im, 0);
        if ($_FILES[$file_field]["type"] == $whitelist_type[0] || $_FILES[$file_field]["type"] == $whitelist_type[1]) {
            imagejpeg($im, $_FILES[$file_field]['tmp_name'], 90);
        } elseif ($_FILES[$file_field]["type"] == $whitelist_type[2]) {
            imagepng($im, $_FILES[$file_field]['tmp_name']);
        } else {
            imagegif($im, $_FILES[$file_field]['tmp_name']);
        }
        if (count($out['error']) > 0) {
            return $out;
        } else {
            return file_get_contents($_FILES[$file_field]['tmp_name']);
        }

    } else {
        $out['error'][] = "No file uploaded";
        return $out;
    }
}

function convToUTF8($text) {
    $max = strlen($text);
    $buf = "";
    for ($i = 0; $i < $max; $i++) {
        $c1 = $text{$i};
        if ($c1 >= "\xc0") { //Should be converted to UTF8, if it's not UTF8 already
            $c2 = $i + 1 >= $max ? "\x00" : $text{$i + 1};
            $c3 = $i + 2 >= $max ? "\x00" : $text{$i + 2};
            $c4 = $i + 3 >= $max ? "\x00" : $text{$i + 3};
            if ($c1 >= "\xc0" & $c1 <= "\xdf") { //looks like 2 bytes UTF8
                if ($c2 >= "\x80" && $c2 <= "\xbf") {
                    $buf .= $c1 . $c2;
                    $i++;
                } else {
                    $cc1 = (chr(ord($c1) / 64) | "\xc0");
                    $cc2 = ($c1 & "\x3f") | "\x80";
                    $buf .= $cc1 . $cc2;
                }
            } elseif ($c1 >= "\xe0" & $c1 <= "\xef") { //looks like 3 bytes UTF8
                if ($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf") {
                    $buf .= $c1 . $c2 . $c3;
                    $i = $i + 2;
                } else {
                    $cc1 = (chr(ord($c1) / 64) | "\xc0");
                    $cc2 = ($c1 & "\x3f") | "\x80";
                    $buf .= $cc1 . $cc2;
                }
            } elseif ($c1 >= "\xf0" & $c1 <= "\xf7") { //looks like 4 bytes UTF8
                if ($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf" && $c4 >= "\x80" && $c4 <= "\xbf") {
                    $buf .= $c1 . $c2 . $c3 . $c4;
                    $i = $i + 3;
                } else {
                    $cc1 = (chr(ord($c1) / 64) | "\xc0");
                    $cc2 = ($c1 & "\x3f") | "\x80";
                    $buf .= $cc1 . $cc2;
                }
            } else { //doesn't look like UTF8, but should be converted
                $cc1 = (chr(ord($c1) / 64) | "\xc0");
                $cc2 = (($c1 & "\x3f") | "\x80");
                $buf .= $cc1 . $cc2;
            }
        } elseif (($c1 & "\xc0") == "\x80") { // needs conversion
            $win1252ToUtf8 = array(
                128 => "\xe2\x82\xac",
                130 => "\xe2\x80\x9a",
                131 => "\xc6\x92",
                132 => "\xe2\x80\x9e",
                133 => "\xe2\x80\xa6",
                134 => "\xe2\x80\xa0",
                135 => "\xe2\x80\xa1",
                136 => "\xcb\x86",
                137 => "\xe2\x80\xb0",
                138 => "\xc5\xa0",
                139 => "\xe2\x80\xb9",
                140 => "\xc5\x92",
                142 => "\xc5\xbd",
                145 => "\xe2\x80\x98",
                146 => "\xe2\x80\x99",
                147 => "\xe2\x80\x9c",
                148 => "\xe2\x80\x9d",
                149 => "\xe2\x80\xa2",
                150 => "\xe2\x80\x93",
                151 => "\xe2\x80\x94",
                152 => "\xcb\x9c",
                153 => "\xe2\x84\xa2",
                154 => "\xc5\xa1",
                155 => "\xe2\x80\xba",
                156 => "\xc5\x93",
                158 => "\xc5\xbe",
                159 => "\xc5\xb8",
            );
            if (isset($win1252ToUtf8[ord($c1)])) { //found in Windows-1252 special cases
                $buf .= $win1252ToUtf8[ord($c1)];
            } else {
                $cc1 = (chr(ord($c1) / 64) | "\xc0");
                $cc2 = (($c1 & "\x3f") | "\x80");
                $buf .= $cc1 . $cc2;
            }
        } else { // it doesn't need conversion
            $buf .= $c1;
        }
    }
    return $buf;
}
//this is here because of reasons.
function insert_access_user($projectID, $userID, $privateKey, $external = false){
	global $conn;
	if($external) {
		$result = $conn->query("SELECT publicKey FROM external_users WHERE id = $userID");
	} else {
		$result = $conn->query("SELECT publicKey FROM security_users WHERE userID = $userID");
	}
	if($result && ($row = $result->fetch_assoc())){
		$user_public = base64_decode($row['publicKey']);
		$nonce = random_bytes(24);
		$private_encrypt = $nonce . sodium_crypto_box($privateKey, $nonce, $privateKey.$user_public);
		if($external){
			$conn->query("INSERT INTO security_external_access(externalID, module, privateKey, optionalID) VALUES ($userID, 'PRIVATE_PROJECT', '".base64_encode($private_encrypt)."', '$projectID')");
		} else {
			$conn->query("INSERT INTO security_access(userID, module, privateKey, optionalID) VALUES ($userID, 'PRIVATE_PROJECT', '".base64_encode($private_encrypt)."', '$projectID')");
		}
		if($conn->error){
			echo '<div class="alert alert-danger"><a href="#" data-dismiss="alert" class="close">&times;</a>'.$conn->error.__LINE__.'</div>';
		}
	} else {
		echo '<div class="alert alert-danger"><a href="#" data-dismiss="alert" class="close">&times;</a>'.$conn->error.__LINE__.'</div>';
	}
}

function util_strip_prefix($subject, $prefix) {
    if (substr($subject, 0, strlen($prefix)) == $prefix) {
        $subject = substr($subject, strlen($prefix));
    }
    return $subject;
}

function getS3Object($bucket = ''){
	global $conn;
	require dirname(__DIR__) . DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'aws'.DIRECTORY_SEPARATOR.'autoload.php';
	$result = $conn->query("SELECT endpoint, awskey, secret FROM archiveconfig WHERE isActive = 'TRUE' LIMIT 1");
	if($result && ($row = $result->fetch_assoc())){
		try{
			$s3 = new Aws\S3\S3Client(array(
				'version' => 'latest',
				'region' => '',
				'endpoint' => $row['endpoint'],
				'use_path_style_endpoint' => true,
				'credentials' => array('key' => $row['awskey'], 'secret' => $row['secret'])
			));
			if($bucket){
				if(!$s3->doesBucketExist($bucket)){
					$s3->createBucket(['Bucket' => $bucket]);
				}
			}
		} catch(Exception $e){
			echo $e->getMessage();
			return false;
		}
	} else {
		echo $conn->error;
		return false;
	}
	return $s3;
}

use PHPMailer\PHPMailer\PHPMailer;
/*
$options [
 subject
 teamid (pk teamData)
 senderid (pk UserData)
 reply{email}
 bcc{email}
 cc{email}
]
*/
function send_standard_email($recipient, $content, Array $options = ['subject' => '']){
	require dirname(__DIR__).'/plugins/phpMailer/autoload.php';
	global $conn;
	$mail = new PHPMailer();
	$mail->CharSet = 'UTF-8';
	$mail->Encoding = "base64";
	$mail->IsSMTP();

	$result = $conn->query("SELECT host, username, password, port, smtpSecure, sender, senderName FROM mailingOptions LIMIT 1");
	if(!$result || $result->num_rows < 1) return 'Keine E-Mail Einstellungen hinterlegt'; //5ac712bc31939
	$row = $result->fetch_assoc();

	if(!empty($row['username']) && !empty($row['password'])){
		$mail->SMTPAuth   = true;
		$mail->Username   = $row['username'];
		$mail->Password   = $row['password'];
	} else {
		$mail->SMTPAuth   = false;
	}
	if(empty($row['smptSecure'])){
		$mail->SMTPSecure = $row['smtpSecure'];
	}
	$signature = $companyID = '';
	$mail->Host       = $row['host'];
	$mail->Port       = $row['port'];
	if(isset($options['teamid'])){
		$result = $conn->query("SELECT emailName, email, emailSignature, companyID FROM teamData WHERE id = ".$options['teamid']." LIMIT 1");
		if($result && ($teamRow = $result->fetch_assoc())){
			$mail->setFrom($teamRow['email'], $teamRow['emailName']);
			$signature = $teamRow['emailSignature'];
			$companyID = $teamRow['companyID'];
		} else {
			echo 'Could not find teamid ', $conn->error;
		}
	} elseif(isset($options['senderid'])){
		$result = $conn->query("SELECT firstname, lastname, real_email, emailSignature, u.companyID FROM UserData u
			LEFT JOIN socialprofile ON u.id = UserID WHERE u.id = ".$options['senderid']);
		if($teamRow = $result->fetch_assoc()){
			$mail->setFrom($teamRow['real_email'], $teamRow['firstname'].' '.$teamRow['lastname']);
			$signature = $teamRow['emailSignature'];
			$companyID = $teamRow['companyID'];
		} else {
			echo 'Could not find senderid: ', $conn->error;
		}
	} else {
		$mail->setFrom($row['sender'], $row['senderName']);
	}
	if(isset($options['attachments'])){
		foreach($options['attachments'] as $filename => $file){
			$mail->addStringAttachment($file, $filename);
		}
	}
	if(!$signature && $companyID){
		$result = $conn->query("SELECT emailSignature FROM companyData WHERE id = $companyID");
		if($row_fc = $result->fetch_assoc()){
			$signature = $row_fc['emailSignature'];
		}
	}
	if(isset($options['reply'])) $mail->addReplyTo($options['reply']);
	if(!empty($options['bcc'])){
		foreach($options['bcc'] as $email => $name){
			$mail->AddBCC($email, $name);
		}
	}
	if(!empty($options['cc'])){
		foreach($options['cc'] as $email => $name){
			$mail->AddCC($email, $name);
		}
	}
	$mail->addAddress($recipient);
	$mail->isHTML(true);
	if($options['subject']) {
		$mail->Subject = $options['subject'];
	} else {
		$mail->Subject = 'Connect';
	}

	$mail->Body    =  $content .'<br><br>'. $signature;
	$mail->AltBody = 'Your e-mail provider does not support HTML. Use an Html Viewer to format this email. '. $content;
	if(!$mail->send()) return $mail->ErrorInfo;
}

//TODO: bad design, redo
function showError($message, $toString = false){
    if(!$message || strlen($message) == 0) return;
    $message = str_replace("'", "\\'", $message);
    if($toString){
        return "<script>$(document).ready(function(){showError('$message')})</script>";
    }
    echo "<script>$(document).ready(function(){showError('$message')})</script>";
}
function showWarning($message, $toString = false){
    if(!$message || strlen(trim($message)) == 0) return;
    $message = str_replace("'", "\\'", $message);
    if($toString){
        return "<script>$(document).ready(function(){showWarning('$message')})</script>";
    }
    echo "<script>$(document).ready(function(){showWarning('$message')})</script>";
}
function showInfo($message, $toString = false){
    if(!$message || strlen($message) == 0) return;
    $message = str_replace("'", "\\'", $message);
    if($toString){
        return "<script>$(document).ready(function(){showInfo('$message')})</script>";
    }
    echo "<script>$(document).ready(function(){showInfo('$message')})</script>";
}
function showSuccess($message, $toString = false){
    if(!$message || strlen($message) == 0) return;
    $message = str_replace("'", "\\'", $message);
    if($toString){
        return "<script>$(document).ready(function(){showSuccess('$message')})</script>";
    }
    echo "<script>$(document).ready(function(){showSuccess('$message')})</script>";
}

function validate_file(&$err, $extension, $filesize, $mime = ''){
	$err = '';
	if(!$extension) { $err = 'No file extension!'; }
	if(!in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'zip', 'msg','jpg', 'jpeg', 'png', 'gif'])){ $err .= "Invalid File extension $extension"; }
	if($filesize > 15000000){ $err .= "File too big, $filesize bytes detected"; }
	if($mime && !in_array($mime, ['application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'text/plain', 'application/pdf', 'application/zip',
	'application/x-zip-compressed', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'multipart/x-zip',
	'application/x-compressed', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-outlook'])){ $err .= "Invalid filetype $mime"; }

	return empty($err);
}

function getUnreadMessages($conversationID = 0){
	global $conn;
	global $userID;
	if($conversationID){
		$result = $conn->query("SELECT COUNT(*) AS unreadMessages FROM messenger_messages m
		INNER JOIN relationship_conversation_participant rcp ON (rcp.id = m.participantID AND (partType != 'USER' OR partID != '$userID'))
		WHERE rcp.conversationID = $conversationID
		AND m.sentTime >= (SELECT lastCheck FROM relationship_conversation_participant rcp2 WHERE rcp2.conversationID = rcp.conversationID
		AND rcp2.status != 'exited' AND rcp2.partType = 'USER' AND rcp2.partID = '$userID')");
	} else {
		$result = $conn->query("SELECT COUNT(*) AS unreadMessages FROM messenger_messages m
		INNER JOIN relationship_conversation_participant rcp ON (rcp.id = m.participantID AND (partType != 'USER' OR partID != '$userID'))
		WHERE m.sentTime >= (SELECT lastCheck FROM relationship_conversation_participant rcp2 WHERE rcp2.conversationID = rcp.conversationID
		AND rcp2.status != 'exited' AND rcp2.partType = 'USER' AND rcp2.partID = '$userID')");
	}

	if($result && ($row = $result->fetch_assoc())){
		return $row['unreadMessages'];
	} else {
		echo $conn->error;
	}
	return '';
}

function sendNotification($recipient, $subject, $message){
	if(!$recipient || !$message) return;
	global $conn;
	$identifier = uniqid();
	$conn->query("INSERT INTO messenger_conversations (subject, category, identifier) VALUES('$subject', 'notification', '$identifier')"); echo $conn->error;
	$conversationID = $conn->insert_id;
	$conn->query("INSERT INTO relationship_conversation_participant (conversationID, partType, partID, status) VALUES($conversationID, 'USER', $recipient, 'normal')");
	echo $conn->error;
	$rcpID = $conn->insert_id;
	if($rcpID){
		$conn->query("INSERT INTO messenger_messages (message, participantID) VALUES('$message', $rcpID)");
	} else {
		echo $conn->error;
	}
}

function str_starts_with($prefix, $subject){
    return substr($subject, 0, strlen($prefix)) === $prefix;
}
