<?php
require_once ("00_functions.php");

$vers = 2;

$scale = ['A' => [
1 => ['MN' => -4, 'SD' => 1],
2 => ['MN' => -3.8, 'SD' => 0.5],
3 => ['MN' => -3.5, 'SD' => 0.5],
4 => ['MN' => -2.9, 'SD' => 1.5],
5 => ['MN' => -2.5, 'SD' => 0.5],
6 => ['MN' => -2.1, 'SD' => 0.5],
7 => ['MN' => -1.8, 'SD' => 1],
8 => ['MN' => -1.7, 'SD' => 0.5],
9 => ['MN' => -1.4, 'SD' => 1],
10 => ['MN' => -1.1, 'SD' => 0.5],
11 => ['MN' => -0.8, 'SD' => 0.5],
12 => ['MN' => -0.5, 'SD' => 1.5],
13 => ['MN' => 0.5, 'SD' => 0.5]
],
'B' => [
1 => ['MN' => 0, 'SD' => 1.5],
2 => ['MN' => 0.5, 'SD' => 2],
3 => ['MN' => 1, 'SD' => 0.5],
4 => ['MN' => 1, 'SD' => 0.5]
],
'C' => [
1 => ['MN' => -0.5, 'SD' => 1],
2 => ['MN' => 0, 'SD' => 1],
3 => ['MN' => 0.5, 'SD' => 1],
4 => ['MN' => 1, 'SD' => 1],
5 => ['MN' => 1.5, 'SD' => 0.5],
6 => ['MN' => 2, 'SD' => 0.5],
7 => ['MN' => 2.5, 'SD' => 1],
8 => ['MN' => 3, 'SD' => 0.5],
9 => ['MN' => 3, 'SD' => 0.25],
10 => ['MN' => 3.5, 'SD' => 1.5]
],
'D' => [
1 => ['MN' => 0, 'SD' => 1],
2 => ['MN' => 0.5, 'SD' => 1],
3 => ['MN' => 0.5, 'SD' => 0.5],
4 => ['MN' => 1.5, 'SD' => 1],
5 => ['MN' => 1.5, 'SD' => 0.5],
6 => ['MN' => 2, 'SD' => 0.5],
7 => ['MN' => 2.5, 'SD' => 1],
8 => ['MN' => 2.5, 'SD' => 0.5],
9 => ['MN' => 3, 'SD' => 0.25],
10 => ['MN' => 3.5, 'SD' => 0.5]
],
'E' => [
1 => ['MN' => -0.5, 'SD' => 2],
2 => ['MN' => 0, 'SD' => 1],
3 => ['MN' => 0.5, 'SD' => 0.5],
4 => ['MN' => 1, 'SD' => 1],
5 => ['MN' => 1, 'SD' => 0.5],
6 => ['MN' => 1.5, 'SD' => 0.5],
7 => ['MN' => 2, 'SD' => 1],
8 => ['MN' => 2.5, 'SD' => 0.5],
9 => ['MN' => 3, 'SD' => 0.5],
10 => ['MN' => 3.5, 'SD' => 0.5],
9 => ['MN' => 4, 'SD' => 0.25],
10 => ['MN' => 4.5, 'SD' => 0.5]
],
'F' => [
1 => ['MN' => 0.5, 'SD' => 1],
2 => ['MN' => 1, 'SD' => 1],
3 => ['MN' => 1.5, 'SD' => 0.5],
4 => ['MN' => 2, 'SD' => 1],
5 => ['MN' => 2.5, 'SD' => 0.5],
6 => ['MN' => 3, 'SD' => 0.5],
7 => ['MN' => 3.5, 'SD' => 1]
],
'G' => [
1 => ['MN' => -1, 'SD' => 1],
2 => ['MN' => -0.5, 'SD' => 1],
3 => ['MN' => 0, 'SD' => 1],
4 => ['MN' => 1, 'SD' => 1],
5 => ['MN' => 1.5, 'SD' => 0.5],
6 => ['MN' => 2, 'SD' => 0.5],
7 => ['MN' => 2.5, 'SD' => 1],
8 => ['MN' => 3, 'SD' => 0.5],
9 => ['MN' => 3.5, 'SD' => 0.5]
],
'H' => [
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
],
'I' => [
1 => ['MN' => 1, 'SD' => 1],
2 => ['MN' => 1.5, 'SD' => 0.5],
3 => ['MN' => 1.5, 'SD' => 1],
4 => ['MN' => 1.5, 'SD' => 0.5],
5 => ['MN' => 2.5, 'SD' => 1],
6 => ['MN' => 2.5, 'SD' => 0.5],
7 => ['MN' => 3, 'SD' => 1],
8 => ['MN' => 3.5, 'SD' => 0.5]
],
'J' => [
1 => ['MN' => 1.5, 'SD' => 1],
2 => ['MN' => 2, 'SD' => 1],
3 => ['MN' => 2.5, 'SD' => 0.5],
4 => ['MN' => 2.5, 'SD' => 1],
5 => ['MN' => 3, 'SD' => 1],
6 => ['MN' => 3.5, 'SD' => 0.5],
7 => ['MN' => 3.5, 'SD' => 1],
8 => ['MN' => 4, 'SD' => 0.5]
],
'K' => [
1 => ['MN' => 2, 'SD' => 1],
2 => ['MN' => 2.5, 'SD' => 0.5],
3 => ['MN' => 2.5, 'SD' => 1],
4 => ['MN' => 3.5, 'SD' => 1],
5 => ['MN' => 3.5, 'SD' => 0.5],
6 => ['MN' => 4, 'SD' => 0.5],
7 => ['MN' => 4.5, 'SD' => 1],
8 => ['MN' => 4.5, 'SD' => 0.5]
],
'L' => [
1 => ['MN' => 3, 'SD' => 1],
2 => ['MN' => 3.5, 'SD' => 1],
3 => ['MN' => 3.5, 'SD' => 1],
4 => ['MN' => 4, 'SD' => 1],
5 => ['MN' => 4.5, 'SD' => 0.5]
]]; 

$ip = [];

$n_min = 5;
$n_max = 25;

$b_min = 0.2;
$b_max = 2.0;

$c_min = 0;
$c_max = 0.33;

foreach ($scale as $scale_nr => $subscale) {
    foreach ($subscale as $subscale_nr => $item) {
        $n_scale = rand ($n_min, $n_max);
        for ($n = 0; $n < $n_scale; $n++) {
            $ip[$scale_nr."/".$scale_nr.sprintf("%'.02d", $subscale_nr)][$scale_nr.sprintf("%'.02d", $subscale_nr) . "-" . sprintf("%'.02d", $n)] = ['a' => round(nrand($item['MN'],$item['SD']/2), 2), 'b' => round(lrand ($b_min, $b_max), 2), 'c' => round(lrand ($c_min, $c_max), 2)];
        }
    }
}

$file_name = "Daten/items ".date("Y-m-d H-i-s")." V".$vers;

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