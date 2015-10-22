#!/usr/local/bin/perl

use DBI;
use Getopt::Std;
use Math::Business::SMA;
use List::Util max;
use List::Util min;

require ("functions.pl");

init();

#my $my_sql = "truncate turtle_s2_system";
#&sql_query($my_sql);

#@mySymbol =('lulu');

system("date");

foreach $s (@mySymbol)
{
## reset portfio
my $my_sql = "update portfolio set num_shares = 100000,value = 100000";
&sql_query($my_sql);
my $my_sql = "delete from portfolio where symbol != 'cash'";
&sql_query($my_sql);

get_detail_quote("$s");

system("date");
print "update price history \n";
update_today_price_history("$s");
}

system("date");

sub init()
{
        if (@ARGV > 0) {
                getopt ('hf:s:');

                if ($opt_f) {
                        $filename = $opt_f ;
                        @mySymbol =readFile ();

print "my symbol:@mySymbol \n";
                } elsif ($opt_s) {
                        @mySymbol = uc $opt_s ;
                } else {
                        usage();
                }
        } else {
                usage();
                ##print "yahooQuote.pl [-S single ticker] [-F file with multiple ticker] \n";
        }


}

#
# Message about this program and how to use it
#
sub usage()
{
    print STDERR << "EOF";

This program does...

usage: $0 [-fsv] 

-h        : this (help) message
-f        : filename, one ticker per line 
-s        : single ticker

example: $0 -f file

EOF
    exit;
}

