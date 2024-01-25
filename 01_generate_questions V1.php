<?php
require_once ("00_functions.php");

$scale = ['A' => [
1 => ['MN' => -4, 'SD' => 1],
2 => ['MN' => -1, 'SD' => 2],
3 => ['MN' => -4, 'SD' => 0.5],
4 => ['MN' => -0.5, 'SD' => 1.5],
5 => ['MN' => -2.5, 'SD' => 0.5],
6 => ['MN' => -1, 'SD' => 0.5],
7 => ['MN' => -0.5, 'SD' => 0.5]
],
'B' => [
1 => ['MN' => 0, 'SD' => 1.5],
2 => ['MN' => 1, 'SD' => 2],
3 => ['MN' => 1, 'SD' => 0.5],
4 => ['MN' => 3, 'SD' => 0.5]
],
'C' => [
1 => ['MN' => -0.5, 'SD' => 1],
2 => ['MN' => 0, 'SD' => 1],
3 => ['MN' => 0.5, 'SD' => 1],
4 => ['MN' => 3.5, 'SD' => 1],
5 => ['MN' => 3.5, 'SD' => 0.5],
6 => ['MN' => 4, 'SD' => 0.5],
7 => ['MN' => 3.5, 'SD' => 1],
8 => ['MN' => 4.5, 'SD' => 0.5],
9 => ['MN' => 4.5, 'SD' => 0.25],
10 => ['MN' => 3.5, 'SD' => 0.5]
]]; 

$ip = [];

$n_max = 20;

foreach ($scale as $scale_nr => $subscale) {
    foreach ($subscale as $subscale_nr => $item) {
        for ($n = 0; $n < $n_max; $n++) {
            $ip[$scale_nr."/".$scale_nr.sprintf("%'.02d", $subscale_nr)][$scale_nr.sprintf("%'.02d", $subscale_nr) . "-" . sprintf("%'.02d", $n)] = ['a' => round(nrand($item['MN'],$item['SD']/2), 2), 'b' => round(lrand (0.3, 6), 2), 'c' => round(lrand (0, 0.33), 2)];
        }
    }
}

$file_name = "Daten/items ".date("Y-m-d H-i-s");

$item_php = "<?php\n\n\$items = [\n".print_array($ip,1)."\n];\n?>";

file_put_contents($file_name.".php", $item_php);

$item_csv = "label;status;category;difficulty;discrimination;guessing";

foreach ($ip as $category => $subscale) {
    foreach ($subscale as $label => $item) {
        $item_csv .= "\n$label;0;$category;" . $item['a'] . ";" . $item['b'] . ";" . $item['c'];
    }
}

file_put_contents($file_name.".csv", $item_csv);

echo "finished";
?>