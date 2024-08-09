<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

$vers = 1;

require_once ("Daten/items V1.php");
require_once ('00_functions_2pl.php');

$irt_model = '2PL';
require_once ('Daten/responses ' . $irt_model . ' V1.php');

#print_r ($responses);
#die;

# $test_strategie = 'radCAT'; // radikaler CAT
 $test_strategie = 'classTest'; // klassischer Test
# $test_strategie = 'defCAT'; // Adaptive Test for Deficency
# $test_strategie = 'strenCAT'; // Adaptive Test for Strength
# $test_strategie = 'relScales'; // Adaptive Test for relevant Scales
 $test_strategie = 'allScales'; // Adaptive Test for all Scales

$pp_start = 0; #0.02;
$pp_start = 0.02;
$se_start = 1; #2.97;
$se_start = 2.97;
$pp_incmin = 0.1;
$se_min = 0.25;
$se_min = 0.35;
$se_max = 0.5;
$se_max = 1.5;
$N_total = 250;
$N_max = 10;
$N_min = 3;

if ($test_strategie === 'radCAT') {
    $N_max = $N_total;
}

$scale_root = "Sim";

// Lade alle Skalen und Items, Bereite Personen-Fähigkeitsmatrix vor
$sp = [];
$ip = [];
$pp = [];
$pp_prev = [];
$se = [];
$f = [];
$N_items = [];

foreach ($items as $scale_id => $value) {
    $scale_id = $scale_root."/".$scale_id;
    $offset = 0;

    do {
        $offset = strpos($scale_id, "/", $offset+1);
        $scale_temp = (($offset)?(substr($scale_id, 0, $offset)):($scale_id));

        if (($test_strategie === 'radCAT') || ($test_strategie === 'allScales')) {
            // Schalte bei (radikalen) CAT sowie bei "alle Skalen" gleich zu Beginn alle Skalen frei.

            $sp[$scale_temp] = $scale_temp;
        } else {
            $sp[$scale_temp] = FALSE;
        }
        // Wenn sp[$id] == $id, dann wird Skala verwendet, wenn sp[$id] == FALSE dann (vorerst) nicht
    } while ($offset);
}
$sp[$scale_root] = $scale_root;
//$sp = array_unique($sp);
//asort($sp);

 // print_r($sp);
# die();

foreach ($items as $scale_id => $scale) {
    foreach ($scale as $item_id => $item) {
        $ip[$item_id] = ['ID' => $item_id, 'Scale' => $scale_root."/".$scale_id, 'ip' => ['a' => $item['a'], 'b' => $item['b'], 'c' => $item['c']]];

        switch ($irt_model) {
            case '1PL':
                $ip[$item_id]['ip']['b'] = 1;
            case '2PL':
                $ip[$item_id]['ip']['c'] = 0;
        }
    }
}

if ($test_strategie === 'classTest') {
    $N_total = count($ip);
    $N_max = $N_total;
}

$out_step_header = "person;step;question;difficulty;discrimination;fraction";
$out_step_template = "";

foreach ($sp as $scale_id => $scale_value) {

    $out_step_header .= ";".$scale_id;
    $out_step_template .= ";{".$scale_id."}";

    $pp[$scale_id] = FALSE;
    $pp_prev[$scale_id] = FALSE;
    $se[$scale_id] = 0;
    $N_items[$scale_id] = 0;
    $N_normmax[$scale_id] = min($N_max, count(array_filter($ip, function($v, $k) { global $scale_id; return (substr($v['Scale'], 0, strlen($scale_id)) == $scale_id); }, ARRAY_FILTER_USE_BOTH)));

    $f[$scale_id] = 0.5;
}
$pp[$scale_root] = $pp_start;
$se[$scale_root] = $se_start;

// Starte Strategie-Test

$out_step_data = $out_step_header;
$time_start = time();
$out_step_data .= "\n\nTime:;".date("Y-m-d H-i-s", $time_start).";Mode:;".$test_strategie.";Model:;".$irt_model.";Dataset:;V".$vers;
$out_step_data .= "\nParameter;";
$out_step_data .= "pp_start:;$pp_start;se_start:;$se_start;se_min:;$se_min;se_max:;$se_max;N_total:;$N_total;N_max:;$N_max;N_min:;$N_min;pp_incmin:;$pp_incmin";

$output = "ID;PP global;SE global;N global;f global;Scala Diagnose;PP Diagnose;SE Diagnose;N Diagnose;f Diagnose;alle Skalen;alle PP";

$itemselection = [];
foreach ($responses as $person_id => $response_pattern) {
    $out_step_data .= "\n\n".$person_id;

    set_time_limit(180);

    $item_calc = $ip;
    $item_played = [];

    echo "<br><br><b>Person: ".$person_id."</b>";

    $sp_calc = $sp;
    $pp_calc = $pp;
    $se_calc = $se;
    $N_calc = $N_items;
    $f_calc = $f;

    for ($n=0; (($n < $N_total) && (count($N_calc) > 0)) || (( $test_strategie == 'classTest') && ($n < count($ip))); $n++) {

        // Sperre alle Skalen, die nicht (mehr) genügend Test-Information haben
        foreach ($pp_calc as $scale_temp => $pp_value) {
            if (!array_key_exists($scale_temp, $sp_calc)) { continue; }
            if (($test_strategie === 'radCAT') || ($test_strategie === 'classTest'))  { break; }
            if (($test_strategie === 'allScales') && ($N_calc[$scale_temp] < $N_min)) { break; }

            if (!$pp_value) { continue; }

            $item_temp = array_filter($item_calc, function($v, $k) { global $scale_temp; return substr($v['Scale'], 0, strlen($scale_temp)) == $scale_temp; }, ARRAY_FILTER_USE_BOTH);

            $tp_temp = tp_2pl($item_temp, $pp_value, max(0 ,$N_max - $N_calc[$scale_temp]));

            $item_temp = array_filter($item_played, function($v, $k) { global $scale_temp; return (substr($v['Scale'], 0, strlen($scale_temp)) == $scale_temp); }, ARRAY_FILTER_USE_BOTH);

            $ti_temp = ti_2pl($item_temp, $pp_value);

            if ($tp_temp + $ti_temp < (1/$se_max ** 2)) {
                if ($N_calc[$scale_temp] >= $N_min) {
                    if ($sp_calc[$scale_temp]) {
                        $out_step_data .= "\n".$person_id.";deact;".$scale_temp.";TP+TI:;".($tp_temp + $ti_temp)."; <".(1/$se_max ** 2);
                    }
                $sp_calc[$scale_temp] = FALSE;
                }
            } else {
                if (!$sp_calc[$scale_temp]) {
                        $out_step_data .= "\n".$person_id.";enact;".$scale_temp.";TP+TI:;".($tp_temp + $ti_temp)."; >=".(1/$se_max ** 2);
                }
                $sp_calc[$scale_temp] = $scale_temp;
            }
        }

    // Berechne in allen (verbliebenen Skalen) die gewichteten Fisher-Informationen und wähle Item mit höchster weighted_FI

    $items_FI = [];
    foreach ($item_calc as $item_id => $item) {
        if ($test_strategie === 'classTest') { break; }

        $offset = 0;
        $scale_id = $item['Scale'];
        $weighted_FI = 0;
        do {
            $offset = strpos($scale_id, "/", $offset+1);
            $scale = (($offset)?(substr($scale_id, 0, $offset)):($scale_id));

            if (in_array($scale, $sp_calc) &&  ($pp_calc[$scale] !== FALSE)) {
                $ti_temp = ($se_calc[$scale] > 0)?((1/$se_calc[$scale]) ** 2):(0);
                $temp_FI = fi_2pl($item['ip'], $pp_calc[$scale])
                    // Hier die Penalty-Funktion, jetzt gesetzt auf 1
                     * 1;
                // Hier die strategie-spezifische Selektions-Funktion
                if ($test_strategie === 'defCAT')  {
                    $temp_FI *= max(0.1, $ti_temp) / max(1, $N_calc[$scale]); // Prozess-Term
                    $temp_FI *= 1 / (1 + exp($ti_temp * ($pp_calc[$scale] - $pp_calc[$scale_root]))); // Skalen-Term
                    $temp_FI *= (1 / (1 + exp($ti_temp * 2 * (0.5 - $f_calc[$scale]) * ($item['ip']['a'] - $pp_calc[$scale])))) ** max(1, $N_calc[$scale] - $N_min + 1); // Item-Term
                }
                if ($test_strategie === 'strenCAT')  {
                    $temp_FI *= max(0.1, $ti_temp) / max(1, $N_calc[$scale]); // Prozess-Term
                    $temp_FI *= 1 / (1 + exp(- $ti_temp * ($pp_calc[$scale] - $pp_calc[$scale_root]))); // Skalen-Term
                    $temp_FI *= (1 / (1 + exp($ti_temp * 2 * (0.5 - $f_calc[$scale]) * ($item['ip']['a'] - $pp_calc[$scale])))) ** max(1, $N_calc[$scale] - $N_min + 1); // Item-Term
                }
                if ($test_strategie === 'allScales' || $test_strategie === 'relScales')  {
                    $temp_FI *= max(0.1, $ti_temp) / max(1, $N_calc[$scale]); // Prozess-Term
                    $temp_FI *= 1; // Skalen-Term
                    $temp_FI *= (1 / (1 + exp($ti_temp * 2 * (0.5 - $f_calc[$scale]) * ($item['ip']['a'] - $pp_calc[$scale])))) ** max(1, $N_calc[$scale] - $N_min + 1); // Item-Term
                }

                $weighted_FI = (($temp_FI > $weighted_FI)?($temp_FI) : ($weighted_FI));
            }
            if ($test_strategie === 'radCAT')  { break; }
        } while ($offset);
        if ($weighted_FI > 0) {
            $items_FI[$item_id] = $weighted_FI;
        }
    }

    if ( $test_strategie !== 'classTest') {
        arsort($items_FI);
        if (array_keys($items_FI)) {
            $item_id = array_keys($items_FI)[0];
        } else {
            $out_step_data .= "\n".$person_id.";end;kein Item übrig";
            # echo "nichts mehr zum Ausspielen";
            break;
        }
    }

    if (($test_strategie === 'classTest') && (count($item_calc) == 0)) {
        $out_step_data .= "\n".$person_id.";end;kein Item übrig";
        # echo "nichts mehr zum Ausspielen"; die();
        break;
    }

    $out_step_data .= "\n".$person_id.";".($n+1);

    // Entferne gezeigtes Item aus der Item-Liste und füge es der zu berechenden Listen hinzu.
    $item_calc[$item_id]['k'] = $response_pattern[$item_id];
    $scale =  $item_calc[$item_id]['Scale'];
    $item_played[] = $item_calc[$item_id];

    $out_step_data .= ";". $item_id . ";". '"' . sprintf("%01.2f", round($item_calc[$item_id]['ip']['a'], 2)). '"'.";".'"'. sprintf("%01.2f",round($item_calc[$item_id]['ip']['b'], 2)).'"'.";".'"'. $item_calc[$item_id]['k'].'"';
    $out_step_data_tmp =  $out_step_template;

    unset ($item_calc[$item_id]);

    // Berechne für alle assozierten Skalen die PP, SE und N
    $offset = 0;
    $pp_parent = $pp_start; # $pp_calc[$scale_root];
    $se_parent = $se_start; # $se_calc[$scale_root];

    do {
        $offset = strpos($scale, "/", $offset+1);
        $scale_temp = (($offset)?(substr($scale, 0, $offset)):($scale));
        $item_temp = [];
        $item_temp = array_filter($item_played, function($v, $k) { global $scale_temp; return (substr($v['Scale'], 0, strlen($scale_temp)) == $scale_temp); }, ARRAY_FILTER_USE_BOTH);

        $N_calc[$scale_temp] = count($item_temp);
        if (($N_calc[$scale_temp] > 0) || ($scale_temp == $scale_root)) {
            $pp_prev[$scale_temp] = $pp_calc[$scale_temp];
            $pp_calc[$scale_temp] = pp_2pl_est($item_temp, ($pp_calc[$scale_temp] !== FALSE)?($pp_calc[$scale_temp]):($pp_parent), $pp_parent, $se_parent);
            $se_calc[$scale_temp] = se_2pl($item_temp, $pp_calc[$scale_temp]);

            $frac_temp = array_map(function ($v) { return $v['k']; } , $item_temp);
            $frac_sum = array_sum($frac_temp);
            $f_calc[$scale_temp] = $frac_sum / $N_calc[$scale_temp];

            $out_step_data_tmp = str_replace("{".$scale_temp."}", '"'.round($pp_calc[$scale_temp], 2)." (SE ".round($se_calc[$scale_temp], 2)." bei ".$N_calc[$scale_temp]." Fragen mit R/W-Rate ".round($f_calc[$scale_temp], 2).")".'"', $out_step_data_tmp);

            if (round($f_calc[$scale_temp], 0) != $f_calc[$scale_temp] ) {
                $pp_parent = $pp_calc[$scale_temp];
                $se_parent = $se_calc[$scale_temp];

                # Alle unterliegenden Skalen mit round($f_calc, 0) == $f_calc nachberechnen (evtl. kommt es dadurch zu Doppelberechnungen!)
                foreach ($sp as $scale_id => $scale_val) {
                    # if ($scale_id != $scale_val) {continue;} // Skippe alle ungenutzten oder ausgeschlossenen Skalen
                    if (substr($scale_id, 0, strlen($scale_temp)) != $scale_temp) {continue;} // Skippe Skalen, die nicht unter $scale_temp liegen
                    if (substr($scale, 0, strlen($scale_id)) == $scale_id) {continue;} // Skippe Skalen, die innerhalb des Zweiges des aktuellen items liegen - diese werden ohnehin gleich berechnet.
                    if (round($f_calc[$scale_id], 0) != $f_calc[$scale_id] ){continue; } // Skippe alle untergeordneten Skalen mit echter Berechnung
                    $item_temp = array_filter($item_played, function($v, $k) { global $scale_id; return (substr($v['Scale'], 0, strlen($scale_id)) == $scale_id); }, ARRAY_FILTER_USE_BOTH);
                    $pp_calc[$scale_id] = pp_2pl_est($item_temp, $pp_calc[$scale_id], $pp_parent, $se_parent);
                    $se_calc[$scale_id] = se_2pl($item_temp, $pp_calc[$scale_id]);
                # reestimate ($item_temp, $scale_temp, $pp_calc, $se_calc);
                $out_step_data_tmp = str_replace("{".$scale_temp."}", '"'.round($pp_calc[$scale_temp], 2)." (SE ".round($se_calc[$scale_temp], 2)." bei ".$N_calc[$scale_temp]." Fragen mit R/W-Rate ".round($f_calc[$scale_temp], 2).")".'"', $out_step_data_tmp);
                # echo "Nachberechnen: $scale_id: ".$pp_calc[$scale_id]." bei ".$N_calc[$scale_id]." Fragen mit ".$f_calc[$scale_id]." R/W-Rate<br>\n";
                }
            }
        }
    } while ($offset);

    $out_step_data .= $out_step_data_tmp;

    // Lege Skalen still, die Höchst-Kriterien erreichen
    $offset = 0;
    do {
        if ( $test_strategie === 'classTest') { break; }

        $offset = strpos($scale, "/", $offset+1);
        $scale_temp = (($offset)?(substr($scale, 0, $offset)):($scale));
        if ((in_array($scale_temp, $sp_calc))) {

        if (($N_calc[$scale_temp] >= $N_max) || (($se_calc[$scale_temp] <= $se_min) && abs($pp_prev[$scale_temp]-$pp_calc[$scale_temp]) <= $pp_incmin )) {
            unset ($sp_calc[array_keys($sp_calc, $scale_temp)[0]]);
            unset ($sp_calc[$scale_temp]);

            if ($N_calc[$scale_temp] >= $N_max) { $out_step_data .= "\n".$person_id.";drop;".$scale_temp.";N_scale:;".$N_calc[$scale_temp]."; >=".$N_max; }
            if (($se_calc[$scale_temp] <= $se_min) && abs($pp_prev[$scale_temp]-$pp_calc[$scale_temp]) <= $pp_incmin ) { $out_step_data .= "\n".$person_id.";drop;".$scale_temp.";se_scale:;".$se_calc[$scale_temp]."; <=".$se_min.";delta (pp):".abs($pp_prev[$scale_temp]-$pp_calc[$scale_temp])."; <=".$pp_incmin; }

            // Vererbe alle PP (zu- bzw. abzüglich eines SE) den noch unberechneten Sub-Skalen
            $pp_temp = array_filter($pp_calc, function($v, $k) { global $scale_temp; return (substr($k, 0, strlen($scale_temp)) == $scale_temp) && (substr_count($k, "/") == substr_count($scale_temp, "/") + 1) && (!$v); }, ARRAY_FILTER_USE_BOTH);

            $pp_inhere = $pp_calc[$scale_temp];
            if ($test_strategie === 'defCAT')  {
                $pp_inhere = $pp_calc[$scale_temp] - $se_calc[$scale_temp];
            }
             if ($test_strategie === 'strenCAT')  {
                $pp_inhere = $pp_calc[$scale_temp] + $se_calc[$scale_temp];
            }

            foreach ($pp_temp as $pp_scale => $pp_value) {

                $out_step_data .= "\n".$person_id.";inhere;".$pp_scale.";pp:;".$pp_inhere.";from:;".$scale_temp.":;".$pp_calc[$scale_temp];

                $pp_calc[$pp_scale] = $pp_inhere;
            }
        }}
    } while ($offset);

    // Prüfe, ob noch Skalen oder Items übrig sind
    $scale_temp = array_filter($sp_calc, function($v, $k) { return ($v == $k); }, ARRAY_FILTER_USE_BOTH);
    if (count($scale_temp) == 0) {
        $out_step_data .= "\n".$person_id.";end;keine Skala übrig";
        break;
    }
    if (count($N_calc) <= 0) {
        $out_step_data .= "\n".$person_id.";end;keine Items übrig";
        break;
    };

    /*
    if ($se_calc[$scale_root] < $se_min) { break; }
    */
    }


    echo "<br><br> <b>Ergebnis:</b>";

    echo "<br> Globalskala PP: ".round($pp_calc[$scale_root],2)." (".round($se_calc[$scale_root],2).") mit ".$N_calc[$scale_root]." Items und ".round($f_calc[$scale_root], 2)." mittlerer Punktzahl<br>";

    if ($test_strategie === 'defCAT')  {
        // Sortiere nach schwächster Skala zuerst
        asort ($pp_calc);
    }
    if ($test_strategie === 'classTest')  {
        asort ($pp_calc);
    }
    if ($test_strategie == 'strenCAT')  {
        // Sortiere nach stärkster Skala zuerst
        arsort ($pp_calc);
    }
    if ($test_strategie == 'relScales' || $test_strategie === 'allScales') {
        // Sortiere nach Skalen-Name (bzw. Anordnung)
        ksort ($pp_calc);
    }

    $output_diag_scale = "";
    $output_diag_pp = "";

    $n = true;
    foreach ($pp_calc as $scale => $value) {

        $valid_result = false;
        // Teste, ob das Ergebnis das valide Diagnose-Resultat ist
        if ($value && (strlen($scale)>strlen($scale_root) + 2) && ($N_calc[$scale] >= $N_min) && ($se_calc[$scale] <= $se_max)) {
            // für gewöhnlich (radCAT, strenCAT, defCAT, relScales): Mindestanzahl an Fragen und unter max. SE

            if ($test_strategie === 'defCAT') {
                // NOTE: Bei Defizit-CAT muss Skalen-PP kleiner gleich als Global-PP sein und mittlere Fraction < 1,
                // akzeptiere nur die erste Skala
                $valid_result = ($n && ($f_calc[$scale] < 1) && ($value <= $pp_calc[$scale_root]));
            }

            elseif ($test_strategie === 'strenCAT') {
                // NOTE: Bei Stärken-CAT muss Skalen-PP größer gleich als Global-PP sein und mittlere Fraction > 0,
                // akzeptiere nur die erste Skala
                $valid_result = ($n && ($f_calc[$scale] > 0) && ($value >= $pp_calc[$scale_root]));
            }

            else {
                // NOTE: Ansonsten akzeptiere nur Skalen, die gemischt beantwortet wurden (mittlere Fraction nicht 0 oder 1)
                $valid_result = (round($f_calc[$scale],0) !== $f_calc[$scale]);
            }
        }
        if ($test_strategie === 'classTest' || $test_strategie === 'allScales') {
            // NOTE: Bei klassischem Test und Alle Skalen werden alle Skalen bedingungslos angegeben
            $valid_result = true;
        }

        if ($valid_result) {
            echo "<br> Skala ".$scale." PP: ".round($value, 2)." (".round($se_calc[$scale], 2).") mit ".$N_calc[$scale]." Items und ".round($f_calc[$scale], 2)." mittlerer Punktzahl";

            $output .= "\n".$person_id;
            $output .= ";".round($pp_calc[$scale_root], 2).";".round($se_calc[$scale_root], 2).";".$N_calc[$scale_root].";".round($f_calc[$scale_root], 2);
            $output .= ";".$scale.";".round($value, 2).";".round($se_calc[$scale], 2).";".$N_calc[$scale].";".round($f_calc[$scale], 2);

            $n = false;
            echo "<b> &lArr; DAS IST DIAGNOSE-ERGEBNIS</b>";
        }
        # if (($value && (strlen($scale)>strlen($scale_root) + 2)) && ($f_calc[$scale] < 1) && ($value <= $pp_calc[$scale_root]) && ($N_calc[$scale] > 0) && ($f_calc[$scale] > 0)) {
        if (($value && (strlen($scale)>strlen($scale_root) + 2)) && ($f_calc[$scale] < 1) && ($value <= $pp_calc[$scale_root]) && ($N_calc[$scale] > 0)) {
            $output_diag_scale .= $scale." ";
            $output_diag_pp .= round($value, 2). " (".round($se_calc[$scale], 2). " | ".$N_calc[$scale]." | ".round($f_calc[$scale], 2).") ";
        }
    }

    if ( $n ) {
        // Schreibe ersatzweise den Zeilenanfang, wenn kein eindeutiges Diagnoseergebnis ermittelt wurde
        $output .= "\n".$person_id;
        $output .= ";".round($pp_calc[$scale_root], 2).";".round($se_calc[$scale_root], 2).";".$N_calc[$scale_root].";".round($f_calc[$scale_root], 2). ";;;;;";
    }
    $output .= ";". $output_diag_scale. ";". $output_diag_pp;

    $out_step_data_tmp_pp = "\n".$person_id.";RESULT;;;;pp". $out_step_template;
    $out_step_data_tmp_se =  "\n;;;;;se".$out_step_template;
    $out_step_data_tmp_f =  "\n;;;;;frac".$out_step_template;
    $out_step_data_tmp_N =  "\n;;;;;N".$out_step_template;
    foreach ($sp as $scale_temp => $scale_value) {
        if ($N_calc[$scale_temp] > 0) {
            $out_step_data_tmp_pp = str_replace("{".$scale_temp."}", round($pp_calc[$scale_temp], 2), $out_step_data_tmp_pp);
            $out_step_data_tmp_se = str_replace("{".$scale_temp."}", round($se_calc[$scale_temp], 2), $out_step_data_tmp_se);
            $out_step_data_tmp_f = str_replace("{".$scale_temp."}", round($f_calc[$scale_temp], 2), $out_step_data_tmp_f);
            $out_step_data_tmp_N = str_replace("{".$scale_temp."}", round($N_calc[$scale_temp], 2), $out_step_data_tmp_N);
        }
    }
    $out_step_data .= $out_step_data_tmp_pp;
    $out_step_data .= $out_step_data_tmp_se;
    $out_step_data .= $out_step_data_tmp_f;
    $out_step_data .= $out_step_data_tmp_N;
}
$time_end = time();
$out_step_data .= "\n\nTime:;".date("Y-m-d H-i-s", $time_end).";Dauer:;".date("H-i-s", $time_end-$time_start);
$out_step_data = preg_replace("/\{.*?\}/", "\"\"", $out_step_data);
$out_step_data = str_replace(".", ",", $out_step_data);

file_put_contents("Ergebnisse/SimulationSteps ".$test_strategie . " " . date("Y-m-d H-i-s", $time_end).".csv", $out_step_data);

$output = str_replace(".", ",", $output);
file_put_contents("Ergebnisse/ErgebnisSimulation ". $test_strategie . " " . date("Y-m-d H-i-s", $time_end).".csv", $output);
?><br><br><b>finished</b><br>
