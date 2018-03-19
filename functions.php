<?php
date_default_timezone_set('Europe/Rome');

$giocatoriInfo = array("Antonino" => "antoninosireci@gmail.com", "Roberto" => "robertogullo88@gmail.com", "Walter" => "waltermazzei87@libero.it", "Nicola" => "nicola.domingo@gmail.com");
$nConcorsiPossibili = array(2, 3, 4, 5, 6, 9, 12, 15);

function buildPostParams($numbers)
{
	return "Switch=0&submit_checker=Verifica+schedina&" . 
		   "B0_1=" . $numbers[0] . "&" .
		   "B0_2=" . $numbers[1] . "&" .
		   "B0_3=" . $numbers[2] . "&" .
		   "B0_4=" . $numbers[3] . "&" .
		   "B0_5=" . $numbers[4] . "&" .
		   "B0_6=" . $numbers[5] . "&" .
		   "B0_7=&B0_8=&B0_9=&B0_10=&B0_11=&B0_12=&B0_13=&B0_14=&B0_15=&B0_16=&B0_17=&B0_18=&B0_19=&B1_1=&Check=ticket";
}

function getDayOfWeek($date)
{
	$dateSplit = explode("/", $date);
	$day = intval($dateSplit[0]);
	$month = intval($dateSplit[1]);
	$year = intval($dateSplit[2]);
	
	$dayOfWeek = strtoupper(date('l', mktime(0, 0, 0, $month, $day, $year)));
	
	switch($dayOfWeek)
	{
		case 'MONDAY':
			$dayOfWeek = 'Luned&igrave;';
			break;
		case 'TUESDAY':
			$dayOfWeek = 'Marted&igrave;';
			break;
		case 'WEDNESDAY':
			$dayOfWeek = 'Mercoled&igrave;';
			break;
		case 'THURSDAY':
			$dayOfWeek = 'Gioved&igrave;';
			break;
		case 'FRIDAY':
			$dayOfWeek = 'Venerd&igrave;';
			break;
		case 'SATURDAY':
			$dayOfWeek = 'Sabato';
			break;
		case 'SUNDAY':
			$dayOfWeek = 'Domenica';
			break;
	}
	
	return $dayOfWeek;
}

function parseProssimaEstrazioneDate($date)
{
	$date = trim(str_replace("Prossima estrazione: ", "", $date));
	$dateSplit = explode("/", $date);
	$day = $dateSplit[0];
	$month = $dateSplit[1];
	$year = $dateSplit[2];
	
	if(strlen($day) == 1)
		$day = "0" . $day;
	if(strlen($month) == 1)
		$month = "0" . $month;
		
	$date = $day . "/" . $month . "/". $year;
	
	return $date;
}

function checkUltimaGiocata($numbersToCheck)
{
	//ob_start();  
	//$out = fopen('C:\\xampp\\php\\logs\\curl.log', 'w+');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://www.superenalotto.com/verifica-schedina');
	curl_setopt($ch, CURLOPT_POST, 6);
	curl_setopt($ch, CURLOPT_POSTFIELDS, buildPostParams($numbersToCheck));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	/*curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_STDERR, $out);*/
	$result = curl_exec($ch);
	
	curl_close($ch);
	
	$doc = new DOMDocument();
	$doc->loadHTML($result);
	$table = $doc->getElementsByTagName('table')->item(0);
	$tbody = $table->childNodes->item(1);
	$tr = $tbody->childNodes->item(0);
	$td = $tr->childNodes->item(4);

	return $td->nodeValue;
}


function buildStoricoGiocate(&$storicoGiocate, $numbersToCheck, $numGiocata)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://www.superenalotto.com/verifica-schedina');
	curl_setopt($ch, CURLOPT_POST, 6);
	curl_setopt($ch, CURLOPT_POSTFIELDS, buildPostParams($numbersToCheck));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$result = curl_exec($ch);
	
	curl_close($ch);

	$doc = new DOMDocument();
	$doc->loadHTML($result);
	$table = $doc->getElementsByTagName('table')->item(0);
	$tbody = $table->childNodes->item(1);
	
	foreach($tbody->childNodes as $tr)
	{
		$dataEstrazione = buildDataEstrazione($tr->childNodes->item(0)->childNodes->item(0)->childNodes->item(0)->nodeValue);
		$numeri = "";
		$i = 0;
		foreach($tr->childNodes->item(2)->childNodes->item(0)->childNodes as $div)
		{
			if(strlen(trim($div->nodeValue)) > 0)
			{
				if($i== 0)
					$numeri = $div->nodeValue;
				else if($i < 6)
					$numeri .= " - " . $div->nodeValue;	
				else if($i == 6)
					$numeri .= "<br/>(Jolly: " . $div->nodeValue;
				else if($i == 7)
					$numeri .= " - Superstar: " . $div->nodeValue . ")";
				$i++;
			}
		}
		$risultato = buildRisString($tr->childNodes->item(4)->nodeValue);
		$storicoGiocate[$dataEstrazione][0] = $numeri;
		$storicoGiocate[$dataEstrazione][$numGiocata] = $risultato;
	}
}

function buildDataEstrazione($data)
{
	return  ucfirst(htmlentities($data, ENT_COMPAT, 'UTF-8'));
}

function printNumbers($numbers)
{
	$str = $numbers[0];
	for($i = 1; $i < count($numbers); $i++)
		$str .= " - " . $numbers[$i];
	return $str;
}

function buildRisString($str)
{
	$str = trim($str);
	
	if(strstr($str, "EUR"))
	{
		$str = str_replace("Punti 1", "Punti 1 - ", $str);
		$str = str_replace("Punti 2", "Punti 2 - ", $str);
		$str = str_replace("Punti 3", "Punti 3 - ", $str);
		$str = str_replace("Punti 4", "Punti 4 - ", $str);
		$str = str_replace("Punti 5", "Punti 5 - ", $str);
		$str = str_replace("Punti 6", "Punti 6 - ", $str);
		$str = str_replace("Jolly", "Jolly - ", $str);
		$str = str_replace("EUR", "€", $str);
	}
	
	return $str;
}

function getDataUltimaEstrazione()
{
	$doc = new DOMDocument();
	$page = file_get_contents('http://www.superenalotto.com/risultati-superenalotto.asp');
	
	$doc->loadHTML($page);
	$tables = $doc->getElementsByTagName('table');
	$table = $tables->item(0);
	$thead = $table->childNodes->item(0);
	$dataEstrazione = $thead->childNodes->item(0)->childNodes->item(0)->nodeValue;
	
	return $dataEstrazione;
}

function getInfoFromFile()
{
	$dataEstrazione = "";
	$giocatoreInfo = "";
	
	$fp = @fopen("infoFile.txt", "r");
	if ($fp)
	{
		$dataEstrazione = trim(fgets($fp));
		$giocatoreInfo = trim(fgets($fp));
		fclose($fp);
	}
	
	return array("dataEstrazione" => $dataEstrazione, "giocatoreInfo" => $giocatoreInfo);
}

function inviaMail($body)
{
	$to = "";
	$subject = "NUOVA ESTRAZIONE SUPERENALOTTO";
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	
	global $giocatoriInfo;
	$values = array_values($giocatoriInfo);
	$to = $values[0];
	for($i = 1; $i < count($values); $i++)
		if(strlen(trim($values[$i])) > 0)
			$to .= "," . $values[$i];
	// $to = "nicola.domingo@gmail.com";
	@mail($to, $subject, $body, $headers);
}

function getMailTemplate()
{
	$mailTemplate = "";
	
	$fp = @fopen("mailTemplate.html", "r");
	if ($fp)
	{
		while (($line = fgets($fp)) !== false) {
			$mailTemplate .= $line;
		}
		fclose($fp);
	}
	
	return $mailTemplate;
}

function generateTableContent($storicoGiocate)
{
	$tableContent = "";
	foreach($storicoGiocate as $key => $value)
	{
		$tableContent .= "<tr>";
		$tableContent .= "<td>" . $key . "</td>";
		$tableContent .= "<td>" . $value[0] . "</td>";
		$tableContent .= "<td>" . $value[1] . "</td>";
		$tableContent .= "<td>" . $value[2] . "</td>";
		$tableContent .= "</tr>";
	}
	return $tableContent;
}

function calcolaCurrentOrNextGiocatoreInfo($giocatoreInfo, $giocatoriInfo, $nConcorsi)
{
	$split = explode(":", $giocatoreInfo);
	$giocatore = $split[0];
	$nTurno = $split[1];
	$arr = array_keys($giocatoriInfo);
	$giocatoreIndex = array_search($giocatore, $arr);
	
	if($split[1] < $nConcorsi)
		return $giocatore . ":" . ($nTurno + 1);
	else
		return $arr[($giocatoreIndex + 1) % count($giocatoriInfo)] . ":1";
}

function updateFileInfo($dataUltimaEstrazione, $currentGiocatore)
{
	$fp = fopen("infoFile.txt", "w");
	fwrite($fp, trim($dataUltimaEstrazione) . "\r\n");
	fwrite($fp, trim($currentGiocatore));
	fclose($fp);
}
?>