<?php // stats_module.php

function stat_mean ($data) {
// calculates mean
return (array_sum($data) / count($data));
}

function stat_median ($data) {
// calculates median
sort ($data);
$elements = count ($data);
if (($elements % 2) == 0) {
$i = $elements / 2;
return (($data[$i - 1] + $data[$i]) / 2);
} else {
$i = ($elements - 1) / 2;
return $data[$i];
}
}

function stat_range ($data) {
// calculates range
return (max($data) - min($data));
}

function stat_var ($data) {
// calculates sample variance
$n = count ($data);
$mean = stat_mean ($data);
$sum = 0;
foreach ($data as $element) {
$sum += pow (($element - $mean), 2);
}
return ($sum / ($n - 1));
}

function stat_varp ($data) {
// calculates population variance
$n = count ($data);
$mean = stat_mean ($data);
$sum = 0;
foreach ($data as $element) {
$sum += pow (($element - $mean), 2);
}
return ($sum / $n);
}

function stat_stdev ($data) {
// calculates sample standard deviation
return sqrt (stat_var($data));
}

function stat_stdevp ($data) {
// calculates population standard deviation
return sqrt (stat_varp($data));
}

function stat_simple_regression ($x, $y) {
// runs a simple linear regression on $x and $y
// returns an associative array containing the following fields:
// a - intercept
// b - slope
// s - standard error of estimate
// r - correlation coefficient
// r2 - coefficient of determination (r-squared)
// cov - covariation
// t - t-statistic
$output = array();
$output['a'] = 0;
$n = min (count($x), count($y));
$mean_x = stat_mean ($x);
$mean_y = stat_mean ($y);
$SS_x = 0;
foreach ($x as $element) {
$SS_x += pow (($element - $mean_x), 2);
}
$SS_y = 0;
foreach ($y as $element) {
$SS_y += pow (($element - $mean_y), 2);
}
$SS_xy = 0;
for ($i = 0; $i < $n; $i++) {
$SS_xy += ($x[$i] - $mean_x) * ($y[$i] - $mean_y);
}
$output['b'] = $SS_xy / $SS_x;
$output['a'] = $mean_y - $output['b'] * $mean_x;
$output['s'] = sqrt (($SS_y - $output['b'] * $SS_xy)/ ($n - 2));
$output['r'] = $SS_xy / sqrt ($SS_x * $SS_y);
$output['r2'] = pow ($output['r'], 2);
$output['cov'] = $SS_xy / ($n - 1);
$output['t'] = $output['r'] / sqrt ((1 - $output['r2']) / ($n - 2));

return $output;
}

function stat_cov ($data1, $data2) {
//calculate covariance between 2 arrays
// covariance = sum((data1-mean1) * (data2-mean2)) / samplesize -1
$data1_sum = 0;
$data2_sum = 0;

$len1=count($data1);
$len2=count($data2);
$mean1=stat_mean($data1);
$mean2=stat_mean($data2);
$count = 0;

for ($x = 0; $x<$len1; $x++) {
	$sum_array[$x] = ($data1[$x]-$mean1) * ($data2[$x]-$mean2);
}

return (array_sum($sum_array)/($len1-1));
}

function stat_covp ($data1, $data2) {
//calculate covariance between 2 arrays
// covariance = sum((data1-mean1) * (data2-mean2)) / samplesize -1
$data1_sum = 0;
$data2_sum = 0;

$len1=count($data1);
$len2=count($data2);
$mean1=stat_mean($data1);
$mean2=stat_mean($data2);
$count = 0;

for ($x = 0; $x<$len1; $x++) {
	$sum_array[$x] = ($data1[$x]-$mean1) * ($data2[$x]-$mean2);
}

return (array_sum($sum_array)/($len1));
}

function stat_corr_coef($data1, $data2) {	
	return (stat_cov($data1, $data2) / sqrt(stat_var ($data1) * stat_var ($data2)));
}

function stat_r_squared($data1, $data2) {
	return (pow(stat_corr_coef($data1, $data2) , 2));
}

function stat_beta($data1, $data2) {
	return (stat_cov($data1, $data2) / stat_var($data1));
}

function stat_portfolio_VaR($data){
	// VaR = sqrt (w1^2*std1^2 + w2^2*std2^2 = 2w1*w2*std1*std2*correlation-coefficient 1,2)
	print "first array 0 0: ";
	print $data[0][0]['symbol'] ;
	print "\n";
	print "first array 1 0: $data[1][0] \n";
	print "first array 2 0: $data[2][0] \n";
	print "first array 0 1: $data[0][1] \n";
	print "first array 0 2: $data[0][2] \n";


	
}

?>
