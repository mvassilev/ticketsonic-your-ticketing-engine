<?php

function parse_raw_recrypted_ticket($raw_decrypted_ticket) {
	$result = array();
	$checksum = 0;
	$checksumpos = 0;
	$i = 0;
	try {
		while ($i < strlen($raw_decrypted_ticket)) {
			$label = $raw_decrypted_ticket[$i++];
			$len = ord($raw_decrypted_ticket[$i++]);
			switch ($label) {
				case 'V':
					$result['version'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'H':
					$result['barcode'] = substr($raw_decrypted_ticket, $i, $len);
					break;

				case '$':
					$result['price'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'E':
					$result['event_id'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'S':
					$result['segment1'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'B':
					$result['segment2'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'R':
					$result['segment3'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'P':
					$result['segment4'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				// TODO: we do not really have event like this to implement this feature
				case 'T':
					$result['expiry'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'X':
					$temp = substr($raw_decrypted_ticket, $i, $len);
					$delimiter_pos = strpos($temp, '=');

					$result['extension.' . substr($temp, 0, $delimiter_pos)] = substr($temp, $delimiter_pos + 1);
					break;

				case 'C':
					$checksumpos = $i - 2;
					$checksum = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;
				
				default:
					$result[$label] = substr($raw_decrypted_ticket, $i, $len);
					$result['warning'] = "Unrecognized label";
					break;
			}
			$i += $len;
		}
	} catch (Exception $e) {
		$result["error"] = 1;
	}

	if ($checksumpos > 0) {
		$c = 0;
		for ($i = 0; $i < $checksumpos; $i++) {
			$c = ($c + ord($raw_decrypted_ticket[$i])) & 0xFFFF;
		}
		
		if ($c != $checksum) {
			$result["error"] = 1;
		}
	}

	return $result;
}

function bin_to_int_data($str) {
	$result = 0;
	for ($i = strlen($str) - 1; $i >= 0; $i--) {
		$result = ($result << 8) | ord($str[$i]);
	}

	return $result;
}

function qr_binary_to_html_table($raw_input) {
	$binary_input = QRcode::text($raw_input);
	$output = "<table><tbody>";
	foreach($binary_input as $line) {
		//die(gettype($line));
		$output .= "<tr>";
		$patterns = array();
		$patterns[0] = '/0/';
		$patterns[1] = '/1/';
		$replacements = array();
		$replacements[1] = '<td></td>';
		$replacements[0] = '<th></th>';
		
		$output .= preg_replace($patterns, $replacements, $line);
		$output .= "</tr>";
	}
	$output .= "</tbody></table>";
	return $output;
}

function qr_binary_to_binary($raw_input) {
	return QRcode::text($raw_input);
}

?>