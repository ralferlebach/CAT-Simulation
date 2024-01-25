<?php

ini_set("display_errors", "1");
error_reporting(E_ALL);
set_time_limit(0);


require_once ("00_functions.php");
require_once ("Daten/items.php");
// Einlesen der Datei mit den Daten für die Fragen


$question_data = [];

foreach ($items as $scale_id => $scale) {
    foreach ($scale as $item_id => $item) {
        $question_data[] = ['ID' => $item_id, 'a' => $item['a'], 'b' => $item['b'], 'scale' => $scale_id];
    }
}

// Generiere Question-Export

// Einlesen des Grund-Templates
$question_export = file_get_contents("QType-Template/template.xml");

$question_output = "";

foreach ($question_data as $n => $question) {
    // Prüfe auf Vollständigkeit
    foreach ($question as $key => $value) {
  	  if ($value == "") {
  	      echo "<b>Fehler: </b>Angaben unvollständig zu '$key' in Zeile <b>". ($n+3) ."</b>. Eintrag wird nicht bearbeitet.<br>";
  		  continue(2);
  	  }
    }
    
	// Lese Template ein
	$question_template = file_get_contents("QType-Template/singlechoice.xml");
	
	// Erfasse alle grundlegenden Daten
	$search = [];
	$replace = [];
	$search[] = "{{Datum}}"; $replace[] = date("Y-m-d");
	
	foreach ($question as $key => $value) {
		if ($value !== "") {
			$search[] = "{{" . $key . "}}"; $replace[] = trim($value);
		}
	}
	$question_template = str_replace ($search, $replace, $question_template);
	$question_output .=  $question_template;
}

$question_export = str_replace(["{{output}}"], [$question_output], $question_export);

file_put_contents("question_export.xml", $question_export);

// Generiere Scale-Export

$scale_export = "componentid,componentname,contextid,status,qtype,model,difficulty,discrimination,guessing,label,catscalename,parentscalenames";

foreach ($question_data as $n => $question) {
    $scale_export .="\n0,question,1,4,Multiple-Choice,raschbirnbaumb,".$question['a'].",".$question['b'].",0.0000,SIM".$question['ID'].",Sim".substr($question['scale'], 2, 3).",Simulation|Sim".substr($question['scale'], 0,1);
}
file_put_contents("scale_export.csv", $scale_export);

echo "Fertig.";