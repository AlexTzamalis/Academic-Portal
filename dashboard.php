<?php
/**
 * Student Dashboard (Ξεχωριστό του Admin)
 * 
 * Σελίδα που βλέπουν οι φοιτητές μετά τη σύνδεση.
 * Εμφανίζει τα διαθέσιμα μαθήματα ανά ακαδημαϊκό έτος.
 * 
 * Χαρακτηριστικά:
 * - Επιλογή έτους (Α, Β, Γ, Δ)
 * - Διαχωρισμός ανά εξάμηνο (Χειμερινό/Εαρινό)
 * 
 * Αυτόματη συνδεση έαν ειναι loged in 
 * 
 * @Author AlexTzamalis
 * UEL : 2872177
 */

// INITIALIZATION & AUTHENTICATION
session_start();
require_once "config.php";

/**
 * Έλεγχος αν ο χρήστης είναι συνδεδεμένος
 * Αν δεν υπάρχει email στο session, redirect στο login
 */
if (!isset($_SESSION['email'])) {
    header("Location: login_register.php");
    exit();
}

/**
 * Αν ο χρήστης είναι admin (καθηγητής),
 * τον στέλνουμε στο admin dashboard
 * Για να μην υπάρχει θέμα εαν καποιοσ ειναι admin και μπει σε λάθος dashboard μέσω λάθος url
 */
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

/**
 * Παίρνουμε το επιλεγμένο έτος από το URL (?year=1)
 * Default: 1 (Α' έτος)
 * 
 * (int) μετατρέπει σε integer την μεταβλητη (Variable Casting)
 */
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : 1;

// Validation: το έτος πρέπει να είναι 1-4
if ($selectedYear < 1 || $selectedYear > 4) {
    $selectedYear = 1;
}

/**
 * Query για τα ΟΡΑΤΑ μαθήματα του επιλεγμένου έτους
 * is_visible = 1 σημαίνει ότι ο καθηγητής έχει ενεργοποιήσει το μάθημα
 * ORDER BY semester, course_code για σωστή σειρά
 */
$courses = [];
$stmt = $conn->prepare("SELECT * FROM courses WHERE year = ? AND is_visible = 1 ORDER BY semester, course_code");
$stmt->bind_param("i", $selectedYear);  // "i" = integer
$stmt->execute();
$result = $stmt->get_result();

// Αποθηκεύουμε τα μαθήματα σε array
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
$stmt->close();

/**
 * array_filter() Φιλτράρει τον πίνακα με βάση μια συνάρτηση
 * 
 * Χωρίζουμε τα μαθήματα σε:
 * - semester1 = Χειμερινό εξάμηνο
 * - semester2 = Εαρινό εξάμηνο
 */
$semester1 = array_filter($courses, fn($c) => $c['semester'] == 1);
$semester2 = array_filter($courses, fn($c) => $c['semester'] == 2);

/**
 * Παίρνουμε το πρώτο γράμμα του ονόματος για το avatar
 * strtoupper() = κεφαλαίο
 * substr($string, 0, 1) = πρώτος χαρακτήρας
 */
$userInitial = strtoupper(substr($_SESSION['name'], 0, 1));

/**
 * Ελληνικά ονόματα ετών
 * Χρησιμοποιούνται στα tabs και τους τίτλους
 */
$yearNames = [1 => 'Α\' Έτος', 2 => 'Β\' Έτος', 3 => 'Γ\' Έτος', 4 => 'Δ\' Έτος'];

?>


<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- Cache busting με ?v=2 -->
    <link rel="stylesheet" href="CSS/dashboard.css?v=2">
</head>
<body>

    <header class="header">
        <nav class="nav-container">
            <!-- Logo-->
            <a href="index.php" class="logo">
                <div class="logo-icon">Π</div>
                <div class="logo-text">Πανεπιστήμιο <span>Portal</span></div>
            </a>
            
            <!-- User info & Logout -->
            <div class="nav-right">
                <div class="user-info">
                    <!-- Avatar με το πρώτο γράμμα του ονόματος -->
                    <div class="user-avatar"><?= $userInitial ?></div> <!-- php inline script-->
                    <!-- 
                        htmlspecialchars() = Προστασία από XSS
                        Μετατρέπει special characters σε HTML entities
                    -->
                    <span><?= htmlspecialchars($_SESSION['name']) ?></span>
                </div>
                <a href="logout.php" class="logout-btn">Αποσύνδεση</a>
            </div>
        </nav>
    </header>

    <!-- 
       Κύριο Περιεχόμενο
    -->
    <main class="main-content">
        
        <div class="welcome-section">
            <h1>Καλώς ήρθες, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>
            <p>Περιηγήσου στα μαθήματά σου και απόκτησε πρόσβαση στο εκπαιδευτικό υλικό του ακαδημαϊκού σου έτους.</p>
        </div>

        <!-- 
            Year Selector
            Κάθε tab είναι link με ?year=X
        -->
        <div class="year-selector">
            <h3>Επιλογή Ακαδημαϊκού Έτους</h3>
            <div class="year-tabs">
                <?php for ($y = 1; $y <= 4; $y++): ?>
                    <!-- 
                        Η class "active" προστίθεται στο τρέχον έτος
                        Ternary operator: condition ? true : false
                    -->
                    <a href="?year=<?= $y ?>" class="year-tab <?= $selectedYear === $y ? 'active' : '' ?>">
                        <?= $yearNames[$y] ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>

        <?php if (empty($courses)): ?>
            <!-- 
                Empty State - Όταν δεν υπάρχουν μαθήματα
            -->
            <div class="empty-state">
                <h3>Δεν υπάρχουν διαθέσιμα μαθήματα</h3>
                <p>Δεν υπάρχουν μαθήματα διαθέσιμα για το <?= $yearNames[$selectedYear] ?> αυτή τη στιγμή.</p>
            </div>
        <?php else: ?>

            <?php if (!empty($semester1)): ?>
            <!-- 
                SEMESTER 1 - Χειμερινό Εξάμηνο
            -->
            <section class="courses-section">
                <div class="section-header">
                    <h2 class="section-title">Μαθήματα <?= $yearNames[$selectedYear] ?></h2>
                    <span class="semester-badge">Χειμερινό Εξάμηνο</span>
                </div>
                <div class="courses-grid">
                    <?php foreach ($semester1 as $course): ?>
                    <!-- Course Card -->
                    <div class="course-card">
                        <div class="course-header">
                            <!-- Κωδικός μαθήματος (π.χ. CS101) -->
                            <span class="course-code"><?= htmlspecialchars($course['course_code']) ?></span>
                            <!-- ECTS credits -->
                            <span class="course-credits"><?= $course['credits'] ?> ECTS</span>
                        </div>
                        <!-- Τίτλος μαθήματος -->
                        <h3 class="course-name"><?= htmlspecialchars($course['course_name']) ?></h3>
                        <!-- Περιγραφή -->
                        <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>
                        <div class="course-footer">
                            <span class="course-semester">Εξάμηνο <?= $course['semester'] ?></span>
                            <!-- Κουμπί προβολής (placeholder) -->
                            <a href="#" class="course-action">Προβολή Μαθήματος</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php if (!empty($semester2)): ?>
            <!-- 
                SEMESTER 2 - Εαρινό Εξάμηνο
            -->
            <section class="courses-section">
                <div class="section-header">
                    <h2 class="section-title">Μαθήματα <?= $yearNames[$selectedYear] ?></h2>
                    <span class="semester-badge">Εαρινό Εξάμηνο</span>
                </div>
                <div class="courses-grid">
                    <?php foreach ($semester2 as $course): ?>
                    <div class="course-card">
                        <div class="course-header">
                            <span class="course-code"><?= htmlspecialchars($course['course_code']) ?></span>
                            <span class="course-credits"><?= $course['credits'] ?> ECTS</span>
                        </div>
                        <h3 class="course-name"><?= htmlspecialchars($course['course_name']) ?></h3>
                        <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>
                        <div class="course-footer">
                            <span class="course-semester">Εξάμηνο <?= $course['semester'] ?></span>
                            <a href="#" class="course-action">Προβολή Μαθήματος</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

        <?php endif; ?>

    </main>

</body>
</html>
