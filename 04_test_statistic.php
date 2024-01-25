<?php

$version = "";

require_once ("Daten/items.php");
require_once ('00_functions_2pl.php');

$irt_model = '2PL';

require_once ('Daten/responses ' . $irt_model . '.php');

// Lade alle Skalen und Items, Bereite Personen-Fähigkeitsmatrix vor
$sp = [];
$ip = [];
$pp = [];
$se = [];
$N_items = [];

$scale_root = "Sim";

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

$output_line = "Skala";
for ($theta=-5; $theta <= 5; $theta += 0.1) {
    $output_line .= ";" . round($theta, 2);
}
echo "<br><b>". $output_line."</b>";
foreach ($sp as $scale_id => $value) {
    
    $item_temp = array_filter($ip, function($v, $k) { global $scale_id; return substr($v['Scale'], 0, strlen($scale_id)) == $scale_id; }, ARRAY_FILTER_USE_BOTH);

    $output_line = $scale_id;
    for ($theta=-5; $theta <= 5; $theta += 0.1) {
        # $output_line .= ";" . round(ti_2pl($item_temp, $theta), 2);
        $output_line .= ";" . round(tp_2pl($item_temp, $theta, 5), 2);
    }
    
    echo "<br>". $output_line;
}

?>