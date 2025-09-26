<?php
$connection = new mysqli("localhost", "u242005246_urbandesign", "Urbandesign@123", "u242005246_urbandesign");

// Check connection
if ($connection->connect_errno) {
  echo "Failed to connect to MySQL: " . $connection->connect_error;
  exit();
}