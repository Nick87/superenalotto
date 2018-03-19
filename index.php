<?php
require "functions.php";

function printSelectedIfEquals($str1, $str2)
{
	if(strcmp($str1, $str2) == 0)
		return "selected=\"selected\"";
	return "";
}

libxml_use_internal_errors (true);

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
$dataUltimaEstrazione = $thead->childNodes->item(0)->childNodes->item(0)->nodeValue;
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

$infoFromFile = getInfoFromFile();
$nomiGiocatori = array_keys($giocatoriInfo);
$dataEstrazioneFromFile = $infoFromFile["dataEstrazione"];
$currentGiocatoreInfo = $infoFromFile["giocatoreInfo"];
$split = explode(":", $currentGiocatoreInfo);
$currentGiocatore = $split[0];
$currentGiocata = $split[1];
$nConcorsi = $split[2];
$nextGiocatoreInfo = calcolaCurrentOrNextGiocatoreInfo($currentGiocatoreInfo, $giocatoriInfo, $nConcorsi);
$split = explode(":", $nextGiocatoreInfo);
$nextGiocatore = $split[0];

$risGiocata1 = buildRisString(checkUltimaGiocata($giocati1));
$risGiocata2 = buildRisString(checkUltimaGiocata($giocati2));
buildStoricoGiocate($storicoGiocate, $giocati1, "1");
buildStoricoGiocate($storicoGiocate, $giocati2, "2");

$doc2 = new DOMDocument();
$page = file_get_contents('http://www.superenalotto.com/');
$doc2->loadHTML($page);
$dataProssimaEstrazione = $doc2->getElementsByTagName('section')->item(1)->childNodes->item(3)->childNodes->item(11)->childNodes->item(1)->childNodes->item(1)->childNodes->item(0)->childNodes->item(1)->childNodes->item(1)->nodeValue;
$dataProssimaEstrazione = parseProssimaEstrazioneDate($dataProssimaEstrazione);
$dayOfWeekProssimaEstrazione = getDayOfWeek($dataProssimaEstrazione);
?>

<html>
<head>
<title>SUPERENALOTTO</title>
<!-- <script type="text/javascript" src="https://addthisevent.com/libs/1.6.0/ate.min.js"></script> -->
<style>
	table, table td, table th {
		border: 1px solid black;
	}
	th, td {
		padding:10px;
	}
	.h3Reponsive { display: inline; }
	@media screen and (max-width:1000px) {
		.applyResponsive {
			height: 70px;
			font-size: 40px;
			padding-left: 10px;
			padding-right: 10px;
		}
		.h3Reponsive { display: block; !important}
	}
</style>
</head>

<h1>Estrazione del <?php echo $dataUltimaEstrazione ?></h1>

<h3><span style="color:blue"><?php echo printNumbers($numbers); ?></span></h3>
<h3>Jolly: <span style="color:blue"><?php echo $jolly ?></span> - Superstar: <span style="color:blue"><?php echo $superstar ?></span></h3>

<hr>

<h1>Risultati ultima estrazione</h1>

<h3>Giocata 1</h3>
<h4><span style="color:green"><?php echo printNumbers($giocati1); ?></span>: <span style="color:red"><?php echo $risGiocata1 ?></span><h4>

<h3>Giocata 2</h3>
<h4><span style="color:green"><?php echo printNumbers($giocati2); ?></span>: <span style="color:red"><?php echo $risGiocata2 ?></span><h4>

<hr>

<h3>Giocatore attuale: <span style="color:red"><?php echo $currentGiocatore ?></span></h3>
<h3>Giocatore successivo: <span style="color:red"><?php echo $nextGiocatore ?></span></h3>
<h3>Data prossima estrazione: <span style="color:red"><?php echo $dayOfWeekProssimaEstrazione . ", " . $dataProssimaEstrazione ?></span></h3>
<form method="post" action="setNewCurrentInfo.php">
	<h3 class="h3Reponsive">Giocatore/Giocata/Concorsi:</h3>
	<select class="applyResponsive" name="newCurrentPlayer">
		<?php
		foreach($nomiGiocatori as $nome)
			echo "<option value=\"" . $nome. "\"" . printSelectedIfEquals($nome, $currentGiocatore) . ">" . $nome . "</option>";
		?>
	</select>
	<select class="applyResponsive" name="newCurrentGiocata">
		<?php
		for($i = 1; $i <= $nConcorsi; $i++)
			echo "<option value=\"" . $i. "\"" . printSelectedIfEquals($i, $currentGiocata) . ">" . $i . "</option>";
		?>
	</select>
	<select class="applyResponsive" name="newCurrentNConcorsi">
		<?php
		foreach($nConcorsiPossibili as $n)
			echo "<option value=\"" . $n. "\"" . printSelectedIfEquals($n, $nConcorsi) . ">" . $n . "</option>";
		?>
	</select>
	<input class="applyResponsive" type="submit" value="Imposta"/>
</form>
<!--
<div title="Add to Calendar" class="addthisevent">
    Aggiungi al Calendario
    <span class="start"><?php echo $dataProssimaEstrazione . " 00:00"; ?></span>
    <span class="end"><?php echo $dataProssimaEstrazione . " 00:00"; ?></span>
    <span class="timezone">Europe/Paris</span>
    <span class="title">SUPERENALOTTO</span>
    <span class="description">Giocare al Superenalotto</span>
    <span class="location">Via Monginevro 44, Torino</span>
    <span class="organizer">Nicola Domingo</span>
    <span class="all_day_event">true</span>
    <span class="date_format">DD/MM/YYYY</span>
</div>
-->
<hr>

<h1 style="text-align:center">Storico estrazioni</h1>
<table align="center" style="text-align:center">
	<thead>
		<tr>
			<th>Data Estrazione</th>
			<th>Numeri Estratti</th>
			<th>Risultato Giocata 1</th>
			<th>Risultato Giocata 2</th>
		</tr>
	</thead>
	<tbody>
	<?php
		foreach($storicoGiocate as $key => $value)
		{
			echo "<tr>";
			echo "<td>" . $key . "</td>";
			echo "<td>" . $value[0] . "</td>";
			echo "<td>" . $value[1] . "</td>";
			echo "<td>" . $value[2] . "</td>";
			echo "</tr>";
		}
	?>
	</tbody>
</table>
</html>