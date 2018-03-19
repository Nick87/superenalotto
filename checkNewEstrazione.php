<?php
require "functions.php";

libxml_use_internal_errors (true);

$dataUltimaEstrazione = getDataUltimaEstrazione();
$infoFromFile = getInfoFromFile();
$dataEstrazioneFromFile = $infoFromFile["dataEstrazione"];
$previousGiocatoreInfo = $infoFromFile["giocatoreInfo"];
$currentGiocatoreInfo = calcolaCurrentOrNextGiocatoreInfo($previousGiocatoreInfo, $giocatoriInfo);
$split = explode(":", $currentGiocatoreInfo);
$currentGiocatore = $split[0];
$nextGiocatoreInfo = calcolaCurrentOrNextGiocatoreInfo($currentGiocatoreInfo, $giocatoriInfo);
$split = explode(":", $nextGiocatoreInfo);
$nextGiocatore = $split[0];

if(strcasecmp($dataUltimaEstrazione, $dataEstrazioneFromFile) != 0)
{
	$mailTemplate = getMailTemplate();
	
	$giocati1 = array(7, 16, 29, 30, 81, 85);
	$giocati2 = array(32, 54, 61, 67, 69, 75);
	$storicoGiocate = array();
	$numbers = array();
	$jolly = "";
	$superstar = "";

	$doc = new DOMDocument();
	$page = file_get_contents('http://www.superenalotto.com/risultati-superenalotto.asp');

	$doc->loadHTML($page);
	$tables = $doc->getElementsByTagName('table');
	$table = $tables->item(0);
	$thead = $table->childNodes->item(0);
	$dataEstrazione = $thead->childNodes->item(0)->childNodes->item(0)->nodeValue;
	$tbody = $table->childNodes->item(1);
	$tr1 = $tbody->childNodes->item(0);
	$td1 = $tr1->childNodes->item(0);
	$table1 = $td1->childNodes->item(1);
	$tr2 = $table1->childNodes->item(0);
	$td2 = $tr2->childNodes->item(0);

	$i = 0;
	foreach($td2->childNodes as $child)
	{
		$item = $child->nodeValue;
		$str = trim($item);
		if(strlen($str) > 0)
		{
			if($i < 6)
				$numbers[$i] = $str;
			else if($i == 6)
				$jolly = $str;
			else
				$superstar = $str;
			$i++;
		}
	}

	$risGiocata1 = buildRisString(checkUltimaGiocata($giocati1));
	$risGiocata2 = buildRisString(checkUltimaGiocata($giocati2));
	buildStoricoGiocate($storicoGiocate, $giocati1, "1");
	buildStoricoGiocate($storicoGiocate, $giocati2, "2");
	$tableContent = generateTableContent($storicoGiocate);
	
	$doc2 = new DOMDocument();
	$page = file_get_contents('http://www.superenalotto.com/');
	$doc2->loadHTML($page);
	$dataProssimaEstrazione = $doc2->getElementsByTagName('section')->item(1)->childNodes->item(3)->childNodes->item(11)->childNodes->item(1)->childNodes->item(1)->childNodes->item(0)->childNodes->item(1)->childNodes->item(1)->nodeValue;
	$dataProssimaEstrazione = trim(str_replace("Prossima estrazione: ", "", $dataProssimaEstrazione));
	$dataProssimaEstrazione = parseProssimaEstrazioneDate($dataProssimaEstrazione);
	$dayOfWeekProssimaEstrazione = getDayOfWeek($dataProssimaEstrazione);
	
	$body = $mailTemplate;
	$body = str_replace("<!--dataEstrazione-->", $dataEstrazione, $body);
	$body = str_replace("<!--numbers-->", printNumbers($numbers), $body);
	$body = str_replace("<!--jolly-->", $jolly, $body);
	$body = str_replace("<!--superstar-->", $superstar, $body);
	$body = str_replace("<!--giocati1-->", printNumbers($giocati1), $body);
	$body = str_replace("<!--risGiocata1-->", $risGiocata1, $body);
	$body = str_replace("<!--giocati2-->", printNumbers($giocati2), $body);
	$body = str_replace("<!--risGiocata2-->", $risGiocata2, $body);
	$body = str_replace("<!--tableContent-->", $tableContent, $body);
	$body = str_replace("<!--currentGiocatore-->", $currentGiocatore, $body);
	$body = str_replace("<!--nextGiocatore-->", $nextGiocatore, $body);
	$body = str_replace("<!--dataProssimaEstrazione-->", $dataProssimaEstrazione, $body);
	$body = str_replace("<!--prossimaEstrazione-->", $dayOfWeekProssimaEstrazione . ", " . $dataProssimaEstrazione, $body);
	
	inviaMail($body);
	echo "INVIO MAIL PER ESTRAZIONE DEL " . $dataEstrazione;
	
	// Aggiorno la data dell'ultima estrazione e il giocatore
	updateFileInfo($dataUltimaEstrazione, $currentGiocatoreInfo);
}
else
	echo "NO INVIO MAIL";
?>