<?php

// normalverteilte Zufallszahlen
function nrand($mean, $sd) {
    $x = mt_rand()/mt_getrandmax();
    $y = mt_rand()/mt_getrandmax();
    return sqrt(-2*log($x))*cos(2*pi()*$y)*$sd + $mean;
}

// linearverteilte Zufallszahlen
function lrand($min, $max) {
    return mt_rand()/mt_getrandmax() * ($max- $min) + $min;
}

function print_array (array $array, $indentation = 0) {
    $code = '';
    
    $n = 0;
    foreach ($array as $key => $value) {
        if ($n > 0) {
            $code .= ",\n";
        }
        $n++;
        
        if (is_array($value)) {
            $code .= str_repeat('  ', $indentation) .  ((is_int($key))? ($key) : ("'$key'")) . " => [\n";
            $code .= print_array($value, $indentation + 1);
            $code .= "\n" . str_repeat('  ', $indentation) . "]";
        } else {
            $code .= str_repeat('  ', $indentation) . ((is_int($key))? ($key) : ("'$key'")) . " => " . ((is_numeric($value))?($value) : ("'$value'"));
        }
    }
    return $code;
}

?>