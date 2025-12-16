<?php
/**
 * Registration/Login Σελίδα
 * 
 * Σελίδα με δύο φόρμες:
 * 1. Login -> Για υπάρχοντες χρήστες
 * 2. Register -> Για νέους χρήστες
 * 
 * Μόνο μία φόρμα εμφανίζεται κάθε φορά.
 * Το JavaScript αλλάζει ποια φόρμα φαίνεται.
 * 
 * ΕΙΔΙΚΟΙ ΚΩΔΙΚΟΙ ΕΓΓΡΑΦΗΣ:
 * - Φοιτητής: STUD2025
 * - Καθηγητής: PROF2025
 * 
 * @Author AlexTzamalis
 * UEL : 2872177
 */


// SESSION & ERROR HANDLING
session_start();
/**
 * Παίρνουμε τα error/success messages από το session
 * Το ?? είναι null coalescing operator:
 * αν η τιμή είναι null, επιστρέφει '' (κενό string)
 */
$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$success = $_SESSION['register_success'] ?? '';

/**
 * Ποια φόρμα να εμφανιστεί (login ή register)
 * Default: login
 */
$activeForm = $_SESSION['active_form'] ?? 'login';

/**
 * Καθαρισμός session messages
 * Τα διαγράφουμε αφού τα διαβάσαμε, για να μην εμφανιστούν
 * ξανά αν ο χρήστης κάνει refresh
 */
unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['register_success'], $_SESSION['active_form']);


// HELPER FUNCTIONS - Βοηθητικές Συναρτήσεις

/**
 * showError -> Εμφανίζει error message
 * @param string $error - Το μήνυμα σφάλματος
 * @return string -> HTML για το error message ή κενό string
 */
function showError($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

/**
 * showSuccess -> Εμφανίζει success message
 * @param string $message - Το μήνυμα επιτυχίας
 * @return string -> HTML για το success message ή κενό string
 */
function showSuccess($message) {
    return !empty($message) ? "<p class='success-message'>$message</p>" : '';
}

/**
 * isActiveForm -> Ελέγχει αν μια φόρμα είναι ενεργή
 * @param string $formName - Το όνομα της φόρμας ('login' ή 'register')
 * @param string $activeForm - Η τρέχουσα ενεργή φόρμα
 * @return string -> 'active' αν είναι ενεργή, αλλιώς ''
 */
function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}

?>

<!DOCTYPE html>
<html lang="el">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Σύνδεση / Εγγραφή - University of Larisa</title>
        <!-- 
            CSS Link με Cache Busting 
            Το ?v=5 αναγκάζει τον browser να φορτώσει το νέο CSS
            αντί να χρησιμοποιήσει cached version
        -->
        <link rel="stylesheet" href="CSS/login_register.css?v=5">
    </head>
    <body>

        <!-- 
            CONTAINER, Περιέχει και τις δύο φόρμες login & register
        -->
        <div class="container">
            
            <!-- 
                LOGIN FORM - Φόρμα Σύνδεσης
                Η class "active" προστίθεται δυναμικά με PHP
                για να δείξει τη σωστή φόρμα
            -->
            <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">

                <!-- action: στέλνει τα δεδομένα στο login_register.php -->
                <form action="login_register.php" method="post">
                    <h2>Σύνδεση</h2>
                    
                    <!-- Εμφάνιση success message (μετά από επιτυχή εγγραφή) -->
                    <?= showSuccess($success); ?>
                    
                    <!-- Εμφάνιση error message (αν υπάρχει) -->
                    <?= showError($errors['login']); ?>

                    <!-- Email input -->
                    <input type="email" name="email" placeholder="Email" required>
                    
                    <!-- Password input -->
                    <input type="password" name="password" placeholder="Κωδικός" required>
                    
                    <!-- Submit button - το name="login" χρησιμοποιείται στο PHP -->
                    <button type="submit" name="login">Σύνδεση</button>

                    <!-- Link για εναλλαγή στη φόρμα εγγραφής -->
                    <p>Δεν έχεις λογαριασμό; <a href="#" onclick="showForm('register-form')">Εγγραφή</a></p>
                </form>
            </div>

            <!-- 
                REGISTER FORM Φόρμα Εγγραφής
            -->
            <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">

                <form action="login_register.php" method="post">
                    <h2>Εγγραφή</h2>
                    
                    <!-- Εμφάνιση error message (αν υπάρχει) -->
                    <?= showError($errors['register']); ?>

                    <!-- Ονοματεπώνυμο -->
                    <input type="text" name="name" placeholder="Ονοματεπώνυμο" required>
                    
                    <!-- Email -->
                    <input type="email" name="email" placeholder="Email" required>
                    
                    <!-- Κωδικός -->
                    <input type="password" name="password" placeholder="Κωδικός" required>

                    <!-- 
                        Επιλογή Ρόλου (Dropdown)
                        - student = Φοιτητής
                        - admin = Καθηγητής (χρησιμοποιούμε 'admin' για το role)
                    -->
                    <select name="role" required>
                        <option value="">--Επιλέξτε Ρόλο--</option>
                        <option value="student">Φοιτητής</option>
                        <option value="admin">Καθηγητής</option>
                    </select>
                    
                    <!-- 
                        Ειδικός Κωδικός Εγγραφής
                        Απαιτείται για να ολοκληρωθεί η εγγραφή
                    -->
                    <input type="text" name="special-code" placeholder="Ειδικός Κωδικός Εγγραφής" required>
                    
                    <!-- Submit button -->
                    <button type="submit" name="register">Εγγραφή</button>
                    
                    <!-- Link για εναλλαγή στη φόρμα σύνδεσης -->
                    <p>Έχεις ήδη λογαριασμό; <a href="#" onclick="showForm('login-form')">Σύνδεση</a></p>
                </form>
            </div>
        </div>
        
        <!-- 
            JavaScript για εναλλαγή φορμών
            Η συνάρτηση showForm() ορίζεται στο LoginRegisterScript.js
        -->
        <script src="JS/LoginRegisterScript.js"></script>
    </body>
</html>
