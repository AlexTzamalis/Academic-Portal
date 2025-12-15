<?php 
/**
 * Ρυθμίσεις Βάσης Δεδομένων (Configuration file)
 * 
 * Αρχείο σύνδεσης με την MySQL βάση δεδομένων.
 * Χρησιμοποιείται από όλα τα PHP αρχεία που χρειάζονται
 * πρόσβαση στη βάση (login, register, dashboards).
 * 
 * @Author AlexTzamalis
 * UEL : 2872177
 */

// DATABASE CREDENTIALS,Στοιχεία Σύνδεσης

$db_host = "localhost";                     // Server της βάσης (localhost για XAMPP)
$db_user = "root";                          // Username (default για XAMPP)
$db_password = "";                          // Password (κενό για XAMPP)
$database_name = "university_larisa_db";    // Όνομα βάσης δεδομένων

/**
 * DATABASE CONNECTION - Σύνδεση με τη Βάση
 * 
 * Δημιουργία σύνδεσης με mysqli object
 * Το mysqli είναι η βιβλιοθήκη της PHP για σύνδεση με MySQL.
 * Δημιουργούμε ένα object $conn που θα χρησιμοποιούμε
 * για queries σε όλα τα αρχεία.
 */
$conn = new mysqli($db_host, $db_user, $db_password, $database_name);

/**
 * CONNECTION ERROR CHECK, Έλεγχος Σύνδεσης
 * 
 * Αν η σύνδεση αποτύχει, σταματάμε την εκτέλεση
 * και εμφανίζουμε το error message.
 * 
 * die() = σταματάει το script αμέσως
 * $conn->connect_error = το μήνυμα σφάλματος από τη MySQL
 */
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

?>