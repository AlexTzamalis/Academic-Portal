<?php  
/**
 * Login & Registration Handler
 * 
 * Αρχείο που χειρίζεται τη σύνδεση και εγγραφή χρηστών.
 * Δέχεται POST requests από τη φόρμα στο login_register.php
 * και κάνει τους απαραίτητους ελέγχους.
 * 
 * ΕΙΔΙΚΟΙ ΚΩΔΙΚΟΙ ΕΓΓΡΑΦΗΣ:
 * - Φοιτητής: STUD2025
 * - Καθηγητής: PROF2025
 * 
 * Χωρίς σωστό κωδικό, η εγγραφή ακυρώνεται!
 * 
 * @Author AlexTzamalis
 * UEL : 2872177
 */

/**
 * session_start() -> Ξεκινάει ή συνεχίζει ένα session
 */
session_start();

/**
 * require_once -> Φορτώνει το config.php μία φορά
 * Αυτό μας δίνει πρόσβαση στο $conn object για queries
 */
require_once "config.php";


/**
 * CONSTANTS (Οπως ζηταει η εργασία.)
 * 
 * define() -> Ορίζει σταθερές τιμές
 * Οι σταθερές δεν αλλάζουν και είναι πιο ασφαλείς από variables
 */
define('STUDENT_CODE', 'STUD2025');    // Κωδικός για φοιτητές
define('PROFESSOR_CODE', 'PROF2025');  // Κωδικός για καθηγητές


// REGISTRATION HANDLER

/**
 * Έλεγχος αν υποβλήθηκε η φόρμα εγγραφής
 * isset($_POST['register']) = true αν πατήθηκε το κουμπί "Εγγραφή"
 */
if (isset($_POST['register'])) {
    
    // Παίρνουμε τα δεδομένα από τη φόρμα
    $role = $_POST['role'];                    // 'student' ή 'admin' (καθηγητής)
    $name = trim($_POST['name']);              // trim() αφαιρεί κενά από αρχή/τέλος
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $specialCode = trim($_POST['special-code']);
    
    
        // 1: Έλεγχος Ειδικού Κωδικού
    
    /**
     * Ελέγχουμε αν ο κωδικός ταιριάζει με τον ρόλο:
     * - student + STUD2025 = OK
     * - admin + PROF2025 = OK
     * - Οτιδήποτε άλλο = ERROR
     */
    $validCode = false;
    
    if ($role === 'student' && $specialCode === STUDENT_CODE) {
        $validCode = true;
    } elseif ($role === 'admin' && $specialCode === PROFESSOR_CODE) {
        $validCode = true;
    }
    
    // Αν ο κωδικός είναι λάθος, σταματάμε εδώ
    if (!$validCode) {
        $_SESSION['register_error'] = 'Λάθος ειδικός κωδικός για τον επιλεγμένο ρόλο!';
        $_SESSION['active_form'] = 'register';
        header("Location: login_register.php");  // Redirect πίσω στη φόρμα
        exit();  // ΝΟΤΕ! Σταματάει το script μετά το redirect
    }
    
    
        // 2: Έλεγχος αν το Email Υπάρχει Ήδη
    
    /**
     * Ψάχνουμε το email και στους δυθο πίνακες (students & professors)
     * για να σιγουρευτούμε ότι δεν υπάρχει ήδη
     */
    $table = ($role === 'student') ? 'students' : 'professors';
    $otherTable = ($role === 'student') ? 'professors' : 'students';
    
    /**
     * Prepared Statements - Ασφαλή Queries
     * 
     * Χρησιμοποιούμε prepare() και bind_param() αντι να βάζουμε
     * τις τιμές απευθειας στο query. Αυτό προστατεύει από SQL Injection.(προεραιτικο αλλα μπήκε)
     * 
     * "s" = string (ο τυπος της παραμέτρου)
     */
    $stmt = $conn->prepare("SELECT email FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $checkEmail = $stmt->get_result();
    
    // Έλεγχος και στον άλλο πίνακα
    $stmt2 = $conn->prepare("SELECT email FROM $otherTable WHERE email = ?");
    $stmt2->bind_param("s", $email);
    $stmt2->execute();
    $checkEmailOther = $stmt2->get_result();
    
    // Αν βρέθηκε το email σε οποιονδήποτε πίνακα
    if ($checkEmail->num_rows > 0 || $checkEmailOther->num_rows > 0) {
        $_SESSION['register_error'] = 'Το email είναι ήδη εγγεγραμμένο!';
        $_SESSION['active_form'] = 'register';
        header("Location: login_register.php");
        exit();
    }
    
    
        // 3: Κρυπτογράφηση Κωδικού
    
    /**
     * ΠΟΤΕ δεν αποθηκεύουμε κωδικούς σε plain text φυσικα οποτε καλύτερα με Κρυπτογράφηση!
     * και ας γίνεται πιο "περιπλοκόσ" ο κώδικας.
     * 
     * password_hash() -> Κρυπτογραφεί τον κωδικό
     * PASSWORD_DEFAULT = χρησιμοποιεί τον πιο ασφαλή αλγόριθμο
     * 
     */
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    
        // 4: Εισαγωγή στη Βάση
    
    /**
     * INSERT query στον κατάλληλο πίνακα
     * "sss" = 3 strings (name, email, password)
     */
    if ($role === 'student') {
        $stmt = $conn->prepare("INSERT INTO students (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);
    } else {
        $stmt = $conn->prepare("INSERT INTO professors (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);
    }
    
    // Εκτέλεση και έλεγχος αποτελέσματος
    if ($stmt->execute()) {
        $_SESSION['register_success'] = 'Επιτυχής εγγραφή! Μπορείτε να συνδεθείτε.';
        $_SESSION['active_form'] = 'login';  // Δείχνει τη φόρμα login
    } else {
        $_SESSION['register_error'] = 'Σφάλμα κατά την εγγραφή. Δοκιμάστε ξανά.';
        $_SESSION['active_form'] = 'register';
    }
    
    $stmt->close();  // Κλείνουμε το statement
    header("Location: login_register.php");
    exit();
}


// LOGIN HANDLER

/**
 * Έλεγχος αν υποβλήθηκε η φόρμα σύνδεσης
 */
if (isset($_POST['login'])) {
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $user = null;   // Θα αποθηκεύσει τα στοιχεία του χρήστη
    $role = null;   // Θα αποθηκεύσει τον ρόλο ('student' ή 'admin')
    
    
        // 1: Αναζήτηση στους Φοιτητές
    
    $stmt = $conn->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Βρέθηκε φοιτητής
        $user = $result->fetch_assoc();  // Παίρνει τα δεδομένα ως array
        $role = 'student';
    } else {
        
        // 2: Αν δεν βρέθηκε, ψάξε στους Καθηγητές
        
        $stmt2 = $conn->prepare("SELECT * FROM professors WHERE email = ?");
        $stmt2->bind_param("s", $email);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        if ($result2->num_rows > 0) {
            // Βρέθηκε καθηγητής
            $user = $result2->fetch_assoc();
            $role = 'admin';
        }
        $stmt2->close();
    }
    $stmt->close();
    
    
        // 3: Έλεγχος Κωδικού & Σύνδεση
    
    /**
     * password_verify() - Συγκρίνει τον κωδικό με το hash
     * Επιστρέφει true αν ταιριάζουν
     */
    if ($user && password_verify($password, $user['password'])) {
        
        // Επιτυχής σύνδεση - Αποθήκευση στοιχείων στο session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $role;
        
        /**  Redirect στο κατάλληλο dashboard
         * 
         * NOTE!, Χρειάζεται αλλαγή διότι θα μεταφερθούμε σε μονό αρχείο για dashboard αντι για admin και normal dashboard αρχεια
         * διοτι η εργασία αλλαξε!!!! νεο github version θα υπάρχει με την αλλαγή αυτην.!
         */
        if ($role === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    }
    
    // Αποτυχία σύνδεσης
    $_SESSION['login_error'] = 'Λάθος email ή κωδικός';
    $_SESSION['active_form'] = 'login';
    header("Location: login_register.php");
    exit();
}
?>
