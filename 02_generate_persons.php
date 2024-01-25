<?php

require_once ("00_functions.php");

require_once ("Daten/items.php");

$person_anz = 1000;

$pp = [];

for ($n = 0; $n < $person_anz; $n++) {
    $pp_MN = round(nrand(0, 3), 2);
    $pp_SD = round(lrand(0.1, 0.5), 2);
    $pp[sprintf("P%'.06d", $n)] = ['Gesamt' => $pp_MN];
    foreach ($items as $key => $value) {
        $pp[sprintf("P%'.06d", $n)][$key] = round(nrand($pp_MN, $pp_SD), 2);
    }
}

$file_name = "Daten/persons ".date("Y-m-d H-i-s");

$persons_php = "<?php\n\n\$persons = [\n".print_array($pp,1)."\n];\n?>";

file_put_contents($file_name.".php", $persons_php);

$persons_csv = "ID";
$person = $pp['P000000'];
foreach ($person as $scale => $person_pp) {
    $persons_csv .= ";$scale";
}


foreach ($pp as $id => $person) {
    $persons_csv .= "\n$id";
    foreach ($person as $scale => $person_pp) {
        $persons_csv .= ";$person_pp";
    }
}

file_put_contents($file_name.".csv", $persons_csv);

echo "finished";


?>