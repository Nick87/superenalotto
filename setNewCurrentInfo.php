<?php
require "functions.php";

$nomiGiocatori = array_keys($giocatoriInfo);

if(isset($_POST['newCurrentPlayer']) && !empty($_POST['newCurrentPlayer']) &&
   isset($_POST['newCurrentGiocata']) && !empty($_POST['newCurrentGiocata']) &&
   isset($_POST['newCurrentNConcorsi']) && !empty($_POST['newCurrentNConcorsi']))
{
	$newCurrentPlayer = $_POST['newCurrentPlayer'];
	$newCurrentGiocata = $_POST['newCurrentGiocata'];
	$newCurrentNConcorsi = $_POST['newCurrentNConcorsi'];
	
	if(!in_array($newCurrentPlayer, $nomiGiocatori, TRUE))
	{
		// echo "New current player non e' nella lista";
	}
	else
	{
		if(!is_numeric($newCurrentGiocata) || !is_numeric($newCurrentNConcorsi))
		{
			// echo "New current giocata e/o new current nConcorsi non e' number";
		}
		else
		{
			$newCurrentGiocata = intval($newCurrentGiocata);
			if($newCurrentGiocata <= 0 || $newCurrentGiocata > $newCurrentNConcorsi)
			{
				// echo "New current giocata e' number non valido";
			}
			else if($newCurrentNConcorsi <= 0 || $newCurrentNConcorsi > max($nConcorsiPossibili))
			{
				// echo "New current nConcorsi e' number non valido";
			}
			else
			{
				$infoFromFile = getInfoFromFile();
				$dataEstrazioneFromFile = $infoFromFile["dataEstrazione"];
				// La data di estrazione rimane invariata, il resto viene aggiornato
				updateFileInfo($dataEstrazioneFromFile, $newCurrentPlayer . ":" . $newCurrentGiocata . ":" . $newCurrentNConcorsi);
			}
		}
	}
}
else
{
	// echo "Variabili mancanti";
}

header("Location: index.php");