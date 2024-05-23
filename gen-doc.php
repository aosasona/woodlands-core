<?php

require __DIR__ . '/vendor/autoload.php';

use Woodlands\Core\Database\Connection;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$tables = Connection::getInstance()->getTables();

$doc = "";

foreach ($tables as $table) {
  if ($table["table_name"] == "__migrations") continue;

  $columns = Connection::getInstance()->query("DESCRIBE " . $table["table_name"])->fetchAll();
  $doc .= "Table: " . $table["table_name"] . "\nDescription:\n\n";
  foreach ($columns as $column) {
    $doc .= sprintf("- `%s` (%s): %s\n\n", $column["Field"], $column["Type"], $column["Comment"] ?? "");
  }

  $doc .= "\n\n";
}

echo $doc;
