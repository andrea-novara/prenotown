#!/usr/bin/perl

use strict;
use warnings;

my $original = shift() || die "No original file provided";
my $previous = shift() || "";

if (open(FH, $original)) {
	if (!open(TRANS, ">$original.trans")) {
		die "Can't open $original.trans\n";
	}

	while (<FH>) {
		chomp;
		my ($key, $value) = split("=", $_);

		$value = lookup($key);
		if (!length($value)) {
			$value = translate($key);
		}

		print TRANS "$key=$value\n";
	}
	close FH;
	close TRANS;
}

sub lookup {
	my $key = shift() || return "";
	my $value = "";

	if (!length($previous)) {
		return "";
	}

	if (!open(PRE, $previous)) {
		$previous = "";
		return "";
	}

	while (<PRE>) {
		chomp;
		my ($oldkey, $oldvalue) = split("=", $_);
		if ($key eq $oldkey) {
			$value = $oldvalue;
			last;
		}
	}

	close PRE;
	return $value;
}

sub translate {
	my $key = shift() || return "";
	my $value = "";

	print STDERR "\n", $key, "=";
	$value = <STDIN>;
	chomp $value;

	print "\n   ORIGINAL: $key\n   TRANSLATION: $value\n   ACCEPT? [y/n] ";
	my $valid = <STDIN>;
	chomp $valid;

	if ($valid !~ /^y$/i) {
		return translate($key);
	}
	return $value;
}
