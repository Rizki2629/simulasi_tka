<?php

// Simple database backup script
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'simulasi_tka';
$timestamp = date('Ymd_His');
$backupFile = __DIR__ . "/db/simulasi_tka_backup_{$timestamp}.sql";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all tables
    $tables = [];
    $result = $pdo->query('SHOW TABLES');
    foreach($result as $row) {
        $tables[] = $row[0];
    }
    
    $output = "-- Database Backup: $dbname\n";
    $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $output .= "SET time_zone = \"+00:00\";\n\n";
    
    // Loop through tables
    foreach($tables as $table) {
        echo "Backing up table: $table\n";
        
        // Drop table
        $output .= "\n-- Table structure for table `$table`\n";
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        
        // Create table
        $result = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch(PDO::FETCH_NUM);
        $output .= $row[1] . ";\n\n";
        
        // Table data
        $result = $pdo->query("SELECT * FROM `$table`");
        $rowCount = $result->rowCount();
        
        if($rowCount > 0) {
            $output .= "-- Dumping data for table `$table`\n";
            
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $output .= "INSERT INTO `$table` VALUES (";
                $values = [];
                foreach($row as $value) {
                    if($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = $pdo->quote($value);
                    }
                }
                $output .= implode(', ', $values);
                $output .= ");\n";
            }
            $output .= "\n";
        }
    }
    
    file_put_contents($backupFile, $output);
    echo "Backup completed successfully: $backupFile\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
