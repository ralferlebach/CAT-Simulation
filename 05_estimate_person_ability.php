<?php

$vers = 1;

require_once ("Daten/items V".$vers.".php");
require_once ('00_functions_2pl.php');
require_once ('00_functions.php');

$irt_model = '2PL';
require_once ('Daten/responses ' . $irt_model . ' V'.$vers.'.php');

#print_r ($responses);
#die;

$pp_start = 0;

$scale_root = "Sim";

// Lade alle Skalen und Items
$sp = [];
$ip = [];
$pp = [];
$se = [];
$N_items = [];

foreach ($items as $scale_id => $value) {
    $scale_id = $scale_root."/".$scale_id;
    $offset = 0;
    do {
        $offset = strpos($scale_id, "/", $offset+1);
        $scale_temp = (($offset)?(substr($scale_id, 0, $offset)):($scale_id));
        $sp[$scale_temp] = FALSE; // $scale_temp;
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

$pp_output = "ID";

foreach ($sp as $scale_id => $scale_value) {
    $pp[$scale_id] = FALSE;
    $se[$scale_id] = 0;
    $N_items[$scale_id] = 0;
    
    $pp_output .= ";$scale_id";
} 
$pp[$scale_root] = 0;
$se[$scale_root] = 1;
// print_r($pp);
// die();
// Starte Strategie-Test

$pp_php = [];
$se_php = [];
$output = "ID";

$itemselection = [];
foreach ($responses as $person_id => $response_pattern) {

    set_time_limit(60);

    echo "<br><br><b>Person: ".$person_id."</b>";
    $pp_output .= "\n". $person_id;
    
    $sp_calc = $sp;
    $pp_calc = [];
    $se_calc = [];
    $N_calc = [];
    
    foreach ($sp as $scale_id => $scale_value) { # Berechne PP für jede Skala
        
        $item_temp = array_filter($ip, function($v, $k) { global $scale_id; return (substr($v['Scale'], 0, strlen($scale_id)) == $scale_id); }, ARRAY_FILTER_USE_BOTH);
        
        $N_calc[$scale_id] = count($item_temp);
        
        foreach( $item_temp as $item_id => $item_value) {
            $item_temp[$item_id ]["k"] = $response_pattern[$item_id];
        }
        
        $pp_calc[$scale_id] = pp_2pl_est($item_temp);
        $se_calc[$scale_id] = se_2pl($item_temp, $pp_calc[$scale_id]);
        
        $pp_output .= ";".round($pp_calc[$scale_id], 2)." (".round($se_calc[$scale_id], 2).", ".$N_calc[$scale_id].")";
    }   
    // die();
    $pp_php[$person_id] = $pp_calc;
    $se_php[$person_id] = $se_calc;
    
}
$file_name = "Daten/persons ".$irt_model." V".$vers;

file_put_contents($file_name.".csv", $pp_output);

$pp_php = "<?php\n\n\$items = [\n".print_array($pp_php,1)."\n];\n?>";

file_put_contents($file_name.".php", $pp_php);


?><br>
finished