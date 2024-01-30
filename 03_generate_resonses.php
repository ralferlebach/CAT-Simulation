<?php

$vers = 2; 

ini_set("display_errors", "1");
error_reporting(E_ALL);
set_time_limit(0);


require_once ("00_functions.php");
require_once ("Daten/items V".$vers.".php");
require_once ("Daten/persons V".$vers.".php");

$irt_model = '2PL';
$irt_formula = ['1PL' => (fn($ip, $pp) => 1 / (1 + exp($ip['a'] - $pp))),
'2PL' => (fn($ip, $pp) => 1 / (1 + exp($ip['b'] * ($ip['a'] - $pp)))),
'3PL' => (fn($ip, $pp) => $ip['c'] + (1 - $ip['c'] ) / (1 +  exp($ip['b'] * ($ip['a'] - $pp))))];

$rp = [];

foreach ($persons as $id => $pp) {
    foreach ($items as $scale_id => $scale) {
        foreach ($scale as $item_id => $item) {
            $rp [$id][$item_id] = (lrand(0,1) < $irt_formula[$irt_model]($item, $pp[$scale_id])) ? 1 : 0;
        }
    }
}


$file_name = "Daten/responses " . $irt_model . " " . date("Y-m-d H-i-s");

$responses_php = "<?php\n\n\$responses = [\n".print_array($rp,1)."\n];\n?>";

file_put_contents($file_name.".php", $responses_php);

$responses_csv = "ID";

$response = $rp['P000000'];
foreach ($response as $item_id => $item_response) {
    $responses_csv .= ";$item_id";
}


foreach ($rp as $person_id => $response) {
    $responses_csv .= "\n$person_id";
    foreach ($response as $item_id => $item_response) {
        $responses_csv .= ";$item_response";
    }
}

file_put_contents($file_name.".csv", $responses_csv);

echo "finished";

?>