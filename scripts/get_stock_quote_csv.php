<?php
$homepage = file_get_contents('http://finance.yahoo.com/d/quotes.csv?s=MA&f=snd1l1yr');
echo $homepage;
$homepage = file_get_contents('http://finance.yahoo.com/d/quotes.csv?s=MA&f=snc6k1k2mll1a2vp2');
echo "<br>second line  sn c6 (change real time) k1 (last trade with time) k2 (change percent real time) m (day's range) l (last trade with time) l1 (last trade price only) a2 (average volume) v (volume) p2 (percent change)<br>";
$homepage = file_get_contents('http://finance.yahoo.com/d/quotes.csv?s=MA+V+GOOG&f=snlk2c6c');

echo $homepage;
/*
s  Symbol
n  Name
l (last trade with time)
k2 (change percent real time) 
c6 (change real time)
c Change & Percent Change
*/

?>