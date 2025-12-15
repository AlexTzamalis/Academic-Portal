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
        <link  rel="stylesheet" href="">

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

        <section>

        </section>

        <section>

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

        <footer>

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