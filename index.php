<?php 
/**
 * Landing page, Αρχική σελίδα ιστοσελιδας
 * 
 * Η κυρια σελίδα που βλέπουν οι επισκέπτες
 * 
 * Με μια διαφορα!, Έαν ο χρήστης ειναι ήδη συνδεδεμένος τοτε τον στέλνουμε στο ανοίστιχο dashboard\
 * 
 * @Author Alextzamalis
 */

?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>University of larisa</title>

        <!-- Βασικο CSS Αρχείο -->
        <link  rel="stylesheet" href="homepage.css">

        <!--
           Leaflet CSS, Για τον διαδραστικο χάρτη
        -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    </head>

    <body>
        <!-- 
            HEADER

            Περιέχει το logo και τα navigation links.
            Χρησιμοποιεί position: sticky για να μένει στην κορυφή. (Css)
        -->
        <header class="header">
            <nav class="nav-container">
                <!-- Logo με link στην αρχική -->
                <a href="index.php" class="logo">
                    <div class="logo-icon">UL</div>
                    <div class="logo-text">University of<span> Larisa</span></div>
                </a>
                
                <!-- Navigation Links -->
                <div class="nav-links">
                    <!-- Anchor link στο footer -->
                    <a href="#about" class="nav-link">Σχετικά</a>
                    <!-- Link στη σελίδα επικοινωνίας -->
                    <a href="contact.php" class="nav-link">Επικοινωνία</a>
                    <!-- Κουμπί σύνδεσης -->
                    <a href="registration.php" class="nav-link login-btn">Σύνδεση</a>
                </div>
            </nav>
        </header>

        <!-- 
            Κεντρικό Banner Καλωσορίσματος Section
            Gradient background με τίτλο και περιγραφή.
        -->
        <section class="hero">
            <div class="hero-content">
                <h1>Καλώς ήρθατε στην Πανεπιστημιακή Πύλη</h1>
                <p>Αποκτήστε πρόσβαση στους ακαδημαϊκούς σας πόρους, το εκπαιδευτικό υλικό και παραμείνετε συνδεδεμένοι με την πανεπιστημιακή κοινότητα.</p>
            </div>
        </section>

        <!-- 
            Κάρτες με links σε διάφορες υπηρεσίες.
            id="quick-access" για anchor links.
        -->
        <section class="quick-links" id="quick-access">
            <div class="quick-links-container">
                <h2 class="section-title">Γρήγορη Πρόσβαση</h2>
                <div class="links-grid">
                    <!-- Κάρτα 1: Ακαδημαϊκό Ημερολόγιο -->
                    <a href="#" class="link-card">
                        <h3>Ακαδημαϊκό Ημερολόγιο</h3>
                        <p>Δείτε σημαντικές ημερομηνίες, προγράμματα εξετάσεων και προθεσμίες εξαμήνου.</p>
                    </a>
                    <!-- Κάρτα 2: Κατάλογος Μαθημάτων -->
                    <a href="#" class="link-card">
                        <h3>Κατάλογος Μαθημάτων</h3>
                        <p>Περιηγηθείτε στα διαθέσιμα μαθήματα και τις απαιτήσεις του προγράμματος.</p>
                    </a>
                    <!-- Κάρτα 3: Βιβλιοθήκη -->
                    <a href="#" class="link-card">
                        <h3>Βιβλιοθήκη</h3>
                        <p>Πρόσβαση σε ψηφιακή βιβλιοθήκη, επιστημονικά περιοδικά και βάσεις δεδομένων.</p>
                    </a>
                    <!-- Κάρτα 4: Φοιτητικές Υπηρεσίες -->
                    <a href="#" class="link-card">
                        <h3>Φοιτητικές Υπηρεσίες</h3>
                        <p>Υπηρεσίες υποστήριξης, συμβουλευτική και διοικητική βοήθεια.</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- 
            CAMPUS GALLERY Section
            Grid με εικόνες του πανεπιστημίου.
            Κάθε εικόνα έχει caption overlay.
        -->
        <section class="campus-gallery" id="campus">
            <div class="gallery-container">
                <h2 class="section-title">Το Campus μας</h2>
                <div class="gallery-grid">
                    <!-- Εικόνα 1 (Unsplash image) -->
                    <div class="gallery-item">
                        <img src="ASSETS/campus_unsplash.jpg" alt="Campus κτίριο">
                        <div class="gallery-caption">Κεντρικό Κτίριο</div>
                    </div>
                    <!-- Εικόνα 2 (Unsplash image) -->
                    <div class="gallery-item">
                        <img src="ASSETS/campus_unsplash_2.jpg" alt="Campus αίθουσα">
                        <div class="gallery-caption">Αίθουσες Διδασκαλίας</div>
                    </div>
                    <!-- Εικόνα 3 (Unsplash image) -->
                    <div class="gallery-item">
                        <img src="ASSETS/campus_unsplash_3.jpg" alt="Campus βιβλιοθήκη">
                        <div class="gallery-caption">Κεντρική Βιβλιοθήκη</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 
            Ανακοινώσεις Section
            Λίστα με πρόσφατες ανακοινώσεις του πανεπιστημίου.
        -->
        <section class="announcements" id="announcements">
            <div class="announcements-container">
                <h2 class="section-title">Ανακοινώσεις</h2>
                <div class="announcement-list">
                    <!-- Ανακοίνωση 1 -->
                    <div class="announcement-item">
                        <div class="announcement-date">5 Δεκεμβρίου 2025</div>
                        <h4>Έναρξη Εγγραφών Χειμερινού Εξαμήνου</h4>
                        <p>Οι εγγραφές για το χειμερινό εξάμηνο 2026 είναι ανοιχτές. Ελέγξτε την επιλεξιμότητά σας και εγγραφείτε πριν τη λήξη της προθεσμίας.</p>
                    </div>
                    <!-- Ανακοίνωση 2 -->
                    <div class="announcement-item">
                        <div class="announcement-date">2 Δεκεμβρίου 2025</div>
                        <h4>Διευρυμένο Ωράριο Βιβλιοθήκης</h4>
                        <p>Η κεντρική βιβλιοθήκη θα έχει διευρυμένο ωράριο κατά την εξεταστική περίοδο. Ανοιχτά μέχρι τις 23:00 τις καθημερινές.</p>
                    </div>
                    <!-- Ανακοίνωση 3 -->
                    <div class="announcement-item">
                        <div class="announcement-date">28 Νοεμβρίου 2025</div>
                        <h4>Ειδοποίηση Συντήρησης Campus</h4>
                        <p>Προγραμματισμένη συντήρηση στο Κτίριο Γ στις 10 Δεκεμβρίου. Ορισμένες εγκαταστάσεις ενδέχεται να μην είναι προσωρινά διαθέσιμες.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 
            MAP SECTION Χάρτης με Leaflet.js
            Διαδραστικός χάρτης που δείχνει την τοποθεσία του campus.
            id="contact" για anchor link από το navigation.
        -->
        <section class="map-section" id="contact">
            <div class="map-container">
                <h2 class="section-title">Τοποθεσία Campus</h2>
                <!-- 
                    Το div #map είναι το container για το Leaflet.
                -->
                <div id="map"></div>
            </div>
        </section>

        <!-- 
            Υποσέλιδο 
            Πληροφορίες επικοινωνίας, links και copyright.
        -->
        <footer class="footer" id="about">
            <div class="footer-content">
                <!-- Στήλη 1: Σχετικά -->
                <div class="footer-section">
                    <h4>University of Larisa Portal</h4>
                    <p>Παρέχουμε ποιοτική εκπαίδευση και ακαδημαϊκούς πόρους στους φοιτητές από το 1965.</p>
                </div>
                <!-- Στήλη 2: Επικοινωνία -->
                <div class="footer-section">
                    <h4>Επικοινωνία</h4>
                    <p>Παναγούλη 26<br>
                    Λαρισα, 41223<br>
                    Τηλ: +30 123-456-7899<br>
                    Email: info@larisaUniversity.gr</p>
                </div>
                <!-- Στήλη 3: Σύνδεσμοι -->
                <div class="footer-section">
                    <h4>Σύνδεσμοι</h4>
                    <a href="#">Εισαγωγή</a><br>
                    <a href="#">Υποτροφίες</a><br>
                    <a href="#">Χάρτης Campus</a><br>
                    <a href="#">Θέσεις Εργασίας</a>
                </div>
                <!-- Στήλη 4: Ωράριο -->
                <div class="footer-section">
                    <h4>Ωράριο</h4>
                    <p>Δευτέρα - Παρασκευή: 8πμ - 6μμ<br>
                    Σάββατο: 9πμ - 2μμ<br>
                    Κυριακή: Κλειστά</p>
                </div>
            </div>
            <!-- Copyright -->
            <div class="footer-bottom">
                &copy; 2025 University of Larisa. Με επιφύλαξη παντός δικαιώματος.
            </div>
        </footer>

        <!-- 
            LEAFLET.JS - Βιβλιοθήκη Χάρτη
            Φορτώνεται από CDN.
            Πρέπει να είναι ΠΡΙΝ τον κώδικα που χρησιμοποιεί το Leaflet.
        -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <script>
            /**
             * Αρχικοποίηση Χάρτη
             */
            
            /**
             * 
             * Τοποθεσία Λάρισας: 39.6365, 22.4183
             */
            var map = L.map('map').setView([39.6365, 22.4183], 15);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            /**
             * Βασικές λειτουργίες script για εμφάνιση του χάρτη
             * L.marker()
             * bindPopup()
             * openPopup()
             */
            L.marker([39.6365, 22.4183]).addTo(map)
                .bindPopup('<strong>University of Larisa</strong><br>Παναγούλη 26, Λάρισα 41223')
                .openPopup();
        </script>

        
    </body>
</html>