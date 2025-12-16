<?php
/**
 * Σελίδα Επικοινωνίας
 * 
 * ΣΗΜΕΙΩΣΗ: Η φόρμα δεν στέλνει πραγματικά email.
 * Σε production θα χρειαστεί mail server ή API.
 * 
 * @Author AlexTzamalis
 * UEL : 2872177
 */

session_start();


// FORM HANDLING

$messageSent = false;  // Flag για επιτυχή αποστολή
$error = '';           // Μήνυμα σφάλματος

/**
 * Έλεγχος αν υποβλήθηκε η φόρμα
 * $_SERVER['REQUEST_METHOD'] = 'POST' όταν γίνεται form submit
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    /**
     * Παίρνουμε τα δεδομένα από τη φόρμα
     * trim() = αφαιρεί κενά από αρχή/τέλος
     * ?? '' = αν δεν υπάρχει, επιστρέφει κενό string
     */
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // VALIDATION
    /**
     * empty() επιστρέφει true αν η τιμή είναι:
     * κενό string ''
     */
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Παρακαλώ συμπληρώστε όλα τα πεδία.';
    } 

    /**
     * filter_var() με FILTER_VALIDATE_EMAIL
     * ελέγχει αν το email είναι έγκυρο
     */
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Παρακαλώ εισάγετε έγκυρο email.';
    } 
    else {
        /**
         * επειδη ειναι ακαδημαικο site, δεν θα στειλουμε πραγματικο email
         * Σε production εδώ θα στέλναμε το email με:
         * - mail() function της PHP
         * - Εξωτερικό API
         * 
         * Η με οποιον δίποτε άλλον επαγγελματικό τρόπο
         * Για τώρα απλά θεωρουμε πως σταλθηκε με επιτυχία!
         */
        $messageSent = true;
    }
}


?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Επικοινωνία - University of Larisa</title>
    
    <!-- 
        Φορτώνουμε ΚΑΙ το homepage.css για τα base styles (header, footer, variables)
        ΚΑΙ το contact.css για τα specific styles αυτής της σελίδας
    -->
    <link rel="stylesheet" href="CSS/homepage.css">
    <link rel="stylesheet" href="CSS/contact.css">
    
    <!-- Leaflet CSS για τον χάρτη -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
</head>
<body>

    <!-- 
        HEADER -> Ίδιο με το index.php
    -->
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">
                <div class="logo-icon">UL</div>
                <div class="logo-text">University of<span> Larisa</span></div>
            </a>
            <div class="nav-links">
                <!-- Link με anchor στο about section του index.php -->
                <a href="index.php#about" class="nav-link">Σχετικά</a>
                <a href="contact.php" class="nav-link">Επικοινωνία</a>
                <a href="login_register.php" class="nav-link login-btn">Σύνδεση</a>
            </div>
        </nav>
    </header>

    <!-- 
        CONTACT HERO
    -->
    <section class="contact-hero">
        <div class="hero-content">
            <h1>Επικοινωνήστε μαζί μας</h1>
            <p>Είμαστε εδώ για να απαντήσουμε στις ερωτήσεις σας και να σας βοηθήσουμε.</p>
        </div>
    </section>

    <!-- 
        CONTACT SECTION - Κύριο Περιεχόμενο
    -->
    <section class="contact-section">
        <div class="contact-container">
            
            <!-- 
                CONTACT INFO
            -->
            <div class="contact-info">
                <h2>Στοιχεία Επικοινωνίας</h2>
                
                <!-- Διεύθυνση -->
                <div class="info-item">
                    <div class="info-icon"></div>
                    <div class="info-content">
                        <h4>Διεύθυνση</h4>
                        <p>Παναγούλη 26<br>Λάρισα, 41223<br>Ελλάδα</p>
                    </div>
                </div>
                
                <!-- Τηλέφωνο -->
                <div class="info-item">
                    <div class="info-icon"></div>
                    <div class="info-content">
                        <h4>Τηλέφωνο</h4>
                        <p>+30 123-456-7899</p>
                    </div>
                </div>
                
                <!-- Email -->
                <div class="info-item">
                    <div class="info-icon"></div>
                    <div class="info-content">
                        <h4>Email</h4>
                        <p>info@larisaUniversity.gr</p>
                    </div>
                </div>
                
                <!-- Ωράριο -->
                <div class="info-item">
                    <div class="info-icon"></div>
                    <div class="info-content">
                        <h4>Ωράριο Γραμματείας</h4>
                        <p>Δευτέρα - Παρασκευή: 8πμ - 6μμ<br>
                        Σάββατο: 9πμ - 2μμ<br>
                        Κυριακή: Κλειστά</p>
                    </div>
                </div>
            </div>
            
            <!-- 
                CONTACT FORM
            -->
            <div class="contact-form-wrapper">
                <h2>Φόρμα Επικοινωνίας</h2>
                
                <!-- Success Message -->
                <?php if ($messageSent): ?>
                    <div class="success-message">
                        Το μήνυμά σας στάλθηκε επιτυχώς! Θα επικοινωνήσουμε μαζί σας σύντομα.
                    </div>
                <?php endif; ?>
                
                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <!-- 
                    Η φόρμα υποβάλλεται στον εαυτό της (contact.php)
                    method="POST" για ασφαλή μετάδοση δεδομένων
                -->
                <form action="contact.php" method="POST" class="contact-form">
                    <!-- Ονοματεπώνυμο -->
                    <div class="form-group">
                        <label for="name">Ονοματεπώνυμο *</label>
                        <input type="text" id="name" name="name" required placeholder="Το όνομά σας">
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required placeholder="Το email σας">
                    </div>
                    
                    <!-- Θέμα -->
                    <div class="form-group">
                        <label for="subject">Θέμα *</label>
                        <input type="text" id="subject" name="subject" required placeholder="Θέμα μηνύματος">
                    </div>
                    
                    <!-- Μήνυμα <textarea> -->
                    <div class="form-group">
                        <label for="message">Μήνυμα *</label>
                        <textarea id="message" name="message" rows="5" required placeholder="Γράψτε το μήνυμά σας..."></textarea>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="submit-btn">Αποστολή Μηνύματος</button>
                </form>
            </div>
            
        </div>
    </section>

    <!-- 
        MAP SECTION
    -->
    <section class="map-section">
        <div class="map-container">
            <h2 class="section-title">Βρείτε μας στον Χάρτη</h2>
            <div id="map"></div>
        </div>
    </section>

    <!-- 
        FOOTER ->$_COOKIE Ίδιο με το index.php
    -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>University of Larisa Portal</h4>
                <p>Παρέχουμε ποιοτική εκπαίδευση και ακαδημαϊκούς πόρους στους φοιτητές από το 1965.</p>
            </div>
            <div class="footer-section">
                <h4>Επικοινωνία</h4>
                <p>Παναγούλη 26<br>
                Λαρισα, 41223<br>
                Τηλ: +30 123-456-7899<br>
                Email: info@larisaUniversity.gr</p>
            </div>
            <div class="footer-section">
                <h4>Σύνδεσμοι</h4>
                <a href="index.php">Αρχική</a><br>
                <a href="registration.php">Σύνδεση</a><br>
                <a href="contact.php">Επικοινωνία</a>
            </div>
            <div class="footer-section">
                <h4>Ωράριο</h4>
                <p>Δευτέρα - Παρασκευή: 8πμ - 6μμ<br>
                Σάββατο: 9πμ - 2μμ<br>
                Κυριακή: Κλειστά</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2025 University of Larisa. Με επιφύλαξη παντός δικαιώματος.
        </div>
    </footer>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        /**
         * Αρχικοποίηση χάρτη -> ίδιο με το index.php
         */
        var map = L.map('map').setView([39.6365, 22.4183], 15);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        L.marker([39.6365, 22.4183]).addTo(map)
            .bindPopup('<strong>University of Larisa</strong><br>Παναγούλη 26, Λάρισα 41223')
            .openPopup();
    </script>
</body>
</html>
