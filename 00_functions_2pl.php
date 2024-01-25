<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

function p_2pl ($ip, $pp, $k)
// berechnet die Likelihood eines Items nach dem 2PL-Modell bei gegebenen Item-Parametern ip, Personen-Parametern pp und Antwortkategorie k
// Output: Double (0 ... 1)
// Input:
// ip - Item-Parameter: array => [a - Schwierigkeit, b - Diskriminierung]
// pp - Personen-Parameter: Double
// k - Antwortkategorie: Integer 0 = falsch, 1 = wahr
{
	// hier Input prüfen, oder besser gleich objekorientiert umsetzen
	return (($k == 0)?
		(1 / (1 + exp($ip['b'] * ($pp - $ip['a'])))):
		(1 / (1 + exp($ip['b'] * ($ip['a'] - $pp)))));
}

function p_2pl_log ($ip, $pp, $k)
// berechnet die Log-Likelihood eines Items nach dem 2PL-Modell bei gegebenen Item-Parametern ip, Personen-Parametern pp und Antwortkategorie k
// Output: Double (0 ... 1)
// Input:
// ip - Item-Parameter: array => [a - Schwierigkeit, b - Diskriminierung]
// pp - Personen-Parameter: Double
// k - Antwortkategorie: Integer 0 = falsch, 1 = wahr
{
	if (!$pp) { return FALSE; }
	// hier Input prüfen, oder besser gleich objekorientiert umsetzen
	return log (p_2pl ($ip, $pp, $k));
}

function fi_2pl ($ip, $pp)
// berechnet die Fisher-Information eines Items nach dem 2PL-Modell bei gegebenen Item-Parametern ip, Personen-Parametern pp
// Output: Double (0 ... 1)
// Input:
// ip - Item-Parameter: array => [a - Schwierigkeit, b - Diskriminierung]
// pp - Personen-Parameter: Double
{
	return $ip['b']**2 * p_2pl ($ip, $pp, 0) * p_2pl ($ip, $pp, 1);
}

function p_2pl_log_der_pp1 ($ip, $pp = 0, $k = 0)
// berechnet die 1. Ableitung nach dem Personenparameter der Log-Likelihood eines Items nach dem 2PL-Modell bei gegebenen Item-Parametern ip, Personen-Parametern pp und Antwortkategorie k
// Output: Double (0 ... 1)
// Input:
// ip - Item-Parameter: array => [a - Schwierigkeit, b - Diskriminierung]
// pp - Personen-Parameter: Double
// k - Antwortkategorie: Integer 0 = falsch, 1 = wahr
{
	return (($k == 0)?
		(-$ip['b'] / (1 + exp($ip['b'] * ($ip['a'] - $pp)))):
		($ip['b'] / (1 + exp($ip['b'] * ($pp - $ip['a'] )))));
}

function p_2pl_log_der_pp2 ($ip, $pp = 0, $k = 0)
// berechnet die 2. Ableitung nach dem Personenparameter der Log-Likelihood eines Items nach dem 2PL-Modell bei gegebenen Item-Parametern ip, Personen-Parametern pp und Antwortkategorie k
// Output: Double (0 ... 1)
// Input:
// ip - Item-Parameter: array => [a - Schwierigkeit, b - Diskriminierung]
// pp - Personen-Parameter: Double
// k - Antwortkategorie: Integer 0 = falsch, 1 = wahr
{	
	$nenner = ((exp($ip['a'] * $ip['b']) + exp($ip['b'] * $pp))**2);
	
	if ($nenner == 0)
	{
		die("Division durch Null");
	} 
	return (($k == 0)?
		(-$ip['b']**2 * exp($ip['b'] * ($ip['a'] + $pp)) / $nenner):
		(-$ip['b']**2 * exp($ip['b'] * ($ip['a'] + $pp)) / $nenner));
}

function pp_2pl_est($items, $pp = 0, $MW = 0, $SD = 1)
// schätzt die Personenfähigkeit auf Grundlage einer Liste mit Items
// Output: Double
//Input:
// items - Liste mit Items array('ID', array ip, k)
// pp - Double Personenfähigkeit (optional)
{
	// Setzt Personenparameter auf 0
	if (!isset($pp)) { $pp = 0.0; }
	
	// Teste darauf, ob Fragen sowohl korrekt als auch inkorrekt beantwortet wurden
	$use_gauss = 0;
	$k0 = false;
	$k1 = false;
	foreach ($items as $item)
	{
		if ($item['k'] == 0) {$k0 = true; }
		if ($item['k'] == 1) {$k1 = true; }
	}
	
	if (!($k0 && $k1)) { $use_gauss = 1; }
	
	// iteriere mindestens max_iter oder breche ab, wenn sich pp nicht um min_inc verändert
	$max_iter = 150;
	$min_inc = 0.0001;
	$max_inc = 0.1;

	for ($n = 1; $n < $max_iter; $n++)
	{
		// berechne p_2pl_log_der_pp1 und p_2pl_log_der_pp2 dp an der aktuellen Stelle pp
		$p_der1 = 0;
		$p_der2 = 0;
		
		// Reset und starte mit Gauß-Verteilung, when 
		if ((abs($pp) > 10)) 
		{ 
			//echo "<br><b>Warning</b>: Restart calculation using Gauss <br>";
			$use_gauss = 1;
			$pp = 0.0;
		}
		
		if ($use_gauss > 0)
		{
			$p_der1 = $use_gauss * (($MW - $pp) / ($SD**2));
			$p_der2 = $use_gauss * (- 1 / ($SD**2));
		}
		
		foreach ($items as $item)
		{
			$p_der1 += p_2pl_log_der_pp1($item['ip'], $pp, $item['k']);
			$p_der2 += p_2pl_log_der_pp2($item['ip'], $pp, $item['k']);
		}
		
		// aktualisiere pp
		if (abs($p_der1) == 0)
		{
			echo "<br><b>Warning</b>: 1st deravitive equals zero. Hit the maximum and quit. <br>";
			// $pp = -10;
			break;
		}
		if (abs($p_der2) == 0)
		{
			echo "<br><b>Warning</b>: 2nd deravitive equals zero. That's actually really bad. Quit. <br>";
			// $pp = -10;
			break;
		}
			
		$dp = - $p_der1 / $p_der2;
		
		if (abs($dp) > $max_inc) {
			$dp = $dp/abs($dp) * $max_inc;
		} else {
			$max_inc = abs($dp);
		}
				
		$pp += $dp;
		if (abs($dp) < $min_inc) { break; }
	}
	return $pp;
}

function ti_2pl ($items, $pp)
// berechnet die Test-Information einer Person mit Personen-Parameter pp auf Grundlage einer Liste mit Items
// Output: Double
//Input:
// items - Liste mit Items: Array('ID', array ip, k)
// pp - Personenparameter: Double
{
	$ti = 0;	
	foreach ($items as $item)
	{
		$ti += fi_2pl($item['ip'], $pp);
	}
	return $ti;
}


function tp_2pl ($items, $pp, $N = 0)
// berechnet das Test-Potential einer Person mit Personen-Parameter pp auf Grundlage einer Liste mit Items, es werden die N 
// Output: Double
//Input:
// items - Liste mit Items: Array('ID', array ip, k)
// pp - Personenparameter: Double
{
	$tp = 0;
	if ($N <= 0) {
		return $tp;
	}
	
	$fi = [];
	foreach ($items as $item)
	{
		$fi[] = fi_2pl($item['ip'], $pp);
	}
	rsort ($fi, SORT_NUMERIC);
	
	for ($i=0; $i < $N; $i++) {
		if (isset($fi[$i])) {
			$tp += $fi[$i];
		}
	}
	return $tp;
}

function se_2pl ($items, $pp)
// berechnet den Standardfehler eines Tests einer Person mit Personen-Parameter pp auf Grundlage einer Liste mit Items
// Output: Double
//Input:
// items - Liste mit Items: Array('ID', array ip, k)
// pp - Personenparameter: Double
{
	$ti = ti_2pl ($items, $pp);
	return (($ti <> 0)?(1/sqrt($ti)):(NULL));
}

function plot_pp_2pl_log ($items)
// zeichnet die Log-Likelyhood der PP als SVG
{
	$x_max = 5;
	$x_delta = 0.1;
	
	$y = array();
	
	for ($x = -$x_max; $x < $x_max; $x += $x_delta)
	{
		$y["$x"] = 0;
		foreach ($items as $item)
		{
			$y["$x"] += p_2pl_log($item['ip'], $x, $item['k']);
		}
	}
	$y_max = max($y);
	$y_min = min($y);
	
	$width = 1200;
	$height = 400 ;
	
	$output = '<svg width="'.$width.'" height="'.$height.'">';
	
	foreach ($y as $x_koord => $y_koord)
	{
		$x_koord = $width / (2 * $x_max) * $x_koord + $width / 2;
		$y_koord = $height / ($y_min - $y_max) * $y_koord;
		$output .= '<circle cx="'.$x_koord.'" cy="'.$y_koord.'" r="2" stroke="blue" stroke-width="2" fill="blue" />';	
	}
	$output .= '</svg>';	
	echo "<br>".$output;
}