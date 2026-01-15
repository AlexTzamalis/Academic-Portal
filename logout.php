<?php
/**
 * Logout Script - Αποσύνδεση Χρήστη
 * 
 * Απλό script που:
 * 1. Καταστρέφει το session του χρήστη
 * 2. Τον κανει redirect στην αρχική σελίδα
 * 
 * Καλείται όταν ο χρήστης πατάει "Αποσύνδεση"
 * στο dashboard.
 * 
 * @Author AlexTzamalis
 * UEL : 2872177
 */

// SESSION DESTRUCTION - Καταστροφή Session

/**
 * session_start() - Πρέπει να καλεστεί πρώτα
 * για να έχουμε πρόσβαση στο session
 */
session_start();

/**
 * session_unset() - Διαγράφει ΟΛΕΣ τις session variables
 * Δηλαδή: $_SESSION['user_id'], $_SESSION['name'], κτλ.
 */
session_unset();

/**
 * session_destroy() - Καταστρέφει εντελώς το session
 * Διαγράφει και το session file από τον server
 */
session_destroy();

// REDIRECT - Ανακατεύθυνση

/**
 * header("Location: ...") - Redirect σε άλλη σελίδα
 * Ο browser θα πάει αυτόματα στο index.php
 */
header("Location: index.php");

/**
 * exit() - Σταματάει το script αμέσως
 * ΣΗΜΑΝΤΙΚΟ μετά από redirect για να μην εκτελεστεί
 * τυχόν κώδικας που ακολουθεί
 */
exit();
?>
