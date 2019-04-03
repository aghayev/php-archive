<?php

require_once('lib/Psr4Autoloader.php');
require_once('bootstrap.php');

$io = new Lib\IO("/path/to/folder");

echo $io->ls();

$io->mkdir("new-dir");

echo $io->ls();

$io->cd('new-dir');

echo $io->ls();

$io->fecho("my content", "my-new-file.txt");

$io->ls();

$io->cat("my-new-file.txt");

$io->rm("my-new-file.txt");

$io->ls();

