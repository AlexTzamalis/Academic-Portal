<?php
/**
 * Admin/Professor Dashboard 
 * 
 * Σελίδα διαχείρισης για καθηγητές.
 * Επιτρέπει τον έλεγχο της ορατότητας μαθημάτων.
 * 
 * 
 * @Author AlexTzamalis
 * UEL : 2872177
 */


// INITIALIZATION & AUTHENTICATION
session_start();
require_once "config.php";

/**
 * Έλεγχος σύνδεσης
 */
if (!isset($_SESSION['email'])) {
    header("Location: login_register.php");
    exit();
}

/**
 * Έλεγχος ρόλου - μόνο admin (καθηγητές) έχουν πρόσβαση
 * Αν δεν είναι admin, redirect στο student dashboard
 */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}


// Μεταβλητές Μηνυμάτων

$message = '';      // Το μήνυμα που θα εμφανιστεί
$messageType = '';  // 'success' ή 'error'


// Χειρισμός Αλλαγής Ορατότητας

/**
 * Όταν ο καθηγητής πατάει το toggle switch,
 * υποβάλλεται μια φόρμα με POST method.
 * 
 * $_POST['toggle_course'] = 1 (hidden input)
 * $_POST['course_id'] = ID του μαθήματος
 * $_POST['is_visible'] = νέα τιμή (0 ή 1)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_course'])) {
    $courseId = (int)$_POST['course_id'];
    $isVisible = (int)$_POST['is_visible'];
    
    // UPDATE query για αλλαγή της ορατότητας
    $stmt = $conn->prepare("UPDATE courses SET is_visible = ? WHERE id = ?");
    $stmt->bind_param("ii", $isVisible, $courseId);  // "ii" = 2 integers
    
    if ($stmt->execute()) {
        $message = 'Η ορατότητα του μαθήματος ενημερώθηκε επιτυχώς.';
        $messageType = 'success';
    } else {
        $message = 'Σφάλμα κατά την ενημέρωση της ορατότητας.';
        $messageType = 'error';
    }
    $stmt->close();
}


// YEAR SELECTION - Επιλογή Έτους

$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : 1;
if ($selectedYear < 1 || $selectedYear > 4) {
    $selectedYear = 1;
}

//Ανάκτηση ΟΛΩΝ των Μαθημάτων

/**
 * Διαφορά από student dashboard:
 * Εδώ παίρνουμε ΟΛΑ τα μαθήματα (και τα κρυφά)
 * για να μπορεί ο καθηγητής να τα διαχειριστεί
 */
$courses = [];
$stmt = $conn->prepare("SELECT * FROM courses WHERE year = ? ORDER BY semester, course_code");
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
$stmt->close();


// SEPARATE BY SEMESTER

$semester1 = array_filter($courses, fn($c) => $c['semester'] == 1);
$semester2 = array_filter($courses, fn($c) => $c['semester'] == 2);


// STATISTICS

/**
 * Queries για τα στατιστικά cards
 * COUNT(*) μετράει τις εγγραφές
 */
$totalCourses = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];
$visibleCourses = $conn->query("SELECT COUNT(*) as count FROM courses WHERE is_visible = 1")->fetch_assoc()['count'];
$hiddenCourses = $totalCourses - $visibleCourses;
$totalStudents = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];


// USER INFO

$userInitial = strtoupper(substr($_SESSION['name'], 0, 1));
$yearNames = [1 => 'Α\' Έτος', 2 => 'Β\' Έτος', 3 => 'Γ\' Έτος', 4 => 'Δ\' Έτος'];
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Πίνακας Καθηγητή - Πανεπιστημιακή Πύλη</title>
    <link rel="stylesheet" href="CSS/dashboard.css?v=2">
</head>
<body>

    <!-- 
        HEADER
    -->
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">
                <div class="logo-icon">Π</div>
                <div class="logo-text">Πανεπιστήμιο <span>Portal</span></div>
            </a>
            <div class="nav-right">
                <div class="user-info">
                    <div class="user-avatar"><?= $userInitial ?></div>
                    <!-- Εμφάνιση "(Καθηγητής)" δίπλα στο όνομα -->
                    <span><?= htmlspecialchars($_SESSION['name']) ?> (Καθηγητής)</span>
                </div>
                <a href="logout.php" class="logout-btn">Αποσύνδεση</a>
            </div>
        </nav>
    </header>

    <!-- 
        MAIN CONTENT
    -->
    <main class="main-content">
        
        <div class="welcome-section">
            <h1>Πίνακας Ελέγχου Καθηγητή</h1>
            <p>Διαχειριστείτε την ορατότητα των μαθημάτων και ελέγξτε τι βλέπουν οι φοιτητές στην πύλη τους.</p>
        </div>

        <!-- 
            Μήνυμα επιτυχίας/σφάλματος μετά από toggle
        -->
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- 
            STATISTICS CARDS
        -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $totalCourses ?></div>
                <div class="stat-label">Σύνολο Μαθημάτων</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $visibleCourses ?></div>
                <div class="stat-label">Ορατά Μαθήματα</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $hiddenCourses ?></div>
                <div class="stat-label">Κρυφά Μαθήματα</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalStudents ?></div>
                <div class="stat-label">Εγγεγραμμένοι Φοιτητές</div>
            </div>
        </div>

        <!-- Year Selector -->
        <div class="year-selector">
            <h3>Διαχείριση Μαθημάτων ανά Έτος</h3>
            <div class="year-tabs">
                <?php for ($y = 1; $y <= 4; $y++): ?>
                    <a href="?year=<?= $y ?>" class="year-tab <?= $selectedYear === $y ? 'active' : '' ?>">
                        <?= $yearNames[$y] ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>

        <?php if (empty($courses)): ?>
            <div class="empty-state">
                <h3>Δεν βρέθηκαν μαθήματα</h3>
                <p>Δεν υπάρχουν μαθήματα διαμορφωμένα για το <?= $yearNames[$selectedYear] ?>.</p>
            </div>
        <?php else: ?>

            <?php if (!empty($semester1)): ?>
            <!-- 
                SEMESTER 1 COURSES
            -->
            <section class="courses-section">
                <div class="section-header">
                    <h2 class="section-title">Μαθήματα <?= $yearNames[$selectedYear] ?></h2>
                    <span class="semester-badge">Χειμερινό Εξάμηνο</span>
                </div>
                <div class="courses-grid">
                    <?php foreach ($semester1 as $course): ?>
                    <!-- 
                        Η class "hidden-course" προστίθεται στα κρυφά μαθήματα
                        για να φαίνονται με χαμηλό opacity
                    -->
                    <div class="course-card <?= !$course['is_visible'] ? 'hidden-course' : '' ?>">
                        <div class="course-header">
                            <span class="course-code"><?= htmlspecialchars($course['course_code']) ?></span>
                            <span class="course-credits"><?= $course['credits'] ?> ECTS</span>
                        </div>
                        <h3 class="course-name"><?= htmlspecialchars($course['course_name']) ?></h3>
                        <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>
                        <div class="course-footer">
                            <span class="course-semester">Εξάμηνο <?= $course['semester'] ?></span>
                            
                            <!-- 
                                TOGGLE FORM Φόρμα Αλλαγής Ορατότητας
                                Κάθε toggle είναι μια μικρή φόρμα που υποβάλλεται
                                αυτόματα όταν αλλάξει το checkbox (onchange).
                            -->
                            <form method="POST" class="toggle-container">
                                <!-- Hidden inputs για τα δεδομένα -->
                                <input type="hidden" name="toggle_course" value="1">
                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                <!-- 
                                    Η νέα τιμή είναι το αντίθετο της τρέχουσας:
                                    Αν είναι ορατό (1), στέλνουμε 0 για να γίνει κρυφό
                                    Αν είναι κρυφό (0), στέλνουμε 1 για να γίνει ορατό
                                -->
                                <input type="hidden" name="is_visible" value="<?= $course['is_visible'] ? 0 : 1 ?>">
                                
                                <!-- Label που δείχνει την τρέχουσα κατάσταση -->
                                <span class="toggle-label"><?= $course['is_visible'] ? 'Ορατό' : 'Κρυφό' ?></span>
                                
                                <!-- 
                                    Custom Toggle Switch
                                    onchange="this.form.submit()" = υποβάλλει τη φόρμα αυτόματα
                                -->
                                <label class="toggle-switch">
                                    <input type="checkbox" <?= $course['is_visible'] ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <span class="toggle-slider"></span>
                                </label>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php if (!empty($semester2)): ?>
            <!-- 
                SEMESTER 2 COURSES
            -->
            <section class="courses-section">
                <div class="section-header">
                    <h2 class="section-title">Μαθήματα <?= $yearNames[$selectedYear] ?></h2>
                    <span class="semester-badge">Εαρινό Εξάμηνο</span>
                </div>
                <div class="courses-grid">
                    <?php foreach ($semester2 as $course): ?>
                    <div class="course-card <?= !$course['is_visible'] ? 'hidden-course' : '' ?>">
                        <div class="course-header">
                            <span class="course-code"><?= htmlspecialchars($course['course_code']) ?></span>
                            <span class="course-credits"><?= $course['credits'] ?> ECTS</span>
                        </div>
                        <h3 class="course-name"><?= htmlspecialchars($course['course_name']) ?></h3>
                        <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>
                        <div class="course-footer">
                            <span class="course-semester">Εξάμηνο <?= $course['semester'] ?></span>
                            <form method="POST" class="toggle-container">
                                <input type="hidden" name="toggle_course" value="1">
                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                <input type="hidden" name="is_visible" value="<?= $course['is_visible'] ? 0 : 1 ?>">
                                <span class="toggle-label"><?= $course['is_visible'] ? 'Ορατό' : 'Κρυφό' ?></span>
                                <label class="toggle-switch">
                                    <input type="checkbox" <?= $course['is_visible'] ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <span class="toggle-slider"></span>
                                </label>
                            </form>
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
