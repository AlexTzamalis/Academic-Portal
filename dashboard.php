<?php
/**
 * Unified Dashboard - Κοινό Dashboard για Φοιτητές και Καθηγητές
 * 
 * Αυτό το αρχείο προσαρμόζεται δυναμικά ανάλογα με τον ρόλο του χρήστη:
 * - Student: Προβολή Μαθημάτων, Εργασιών, Υποβολή Εργασιών, Βαθμολογίες
 * - Professor: Διαχείριση Μαθημάτων, Ανάρτηση Εργασιών, Προβολή Υποβολών, Βαθμολόγηση
 * 
 * RBAC (Role-Based Access Control) ελέγχος για ασφάλεια.
 * 
 * @Author AlexTzamalis
 * UEL : 2872177
 */

session_start();
require_once "config.php";

// AUTHENTICATION CHECK
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: login_register.php");
    exit();
}

$userRole = $_SESSION['role']; // 'student' or 'admin'
$userId = $_SESSION['user_id'];
$userName = $_SESSION['name'];
$userInitial = strtoupper(substr($userName, 0, 1));

// HANDLE POST REQUESTS (Actions)
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// TOGGLE COURSE VISIBILITY (Professor only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'toggle_visibility' && $userRole === 'admin') {
    $courseId = (int)$_POST['course_id'];
    $isVisible = (int)$_POST['is_visible'];
    
    $stmt = $conn->prepare("UPDATE courses SET is_visible = ? WHERE id = ?");
    $stmt->bind_param("ii", $isVisible, $courseId);
    
    if ($stmt->execute()) {
        $_SESSION['dashboard_message'] = 'Η ορατότητα του μαθήματος ενημερώθηκε επιτυχώς.';
        $_SESSION['dashboard_message_type'] = 'success';
    } else {
        $_SESSION['dashboard_message'] = 'Σφάλμα κατά την ενημέρωση.';
        $_SESSION['dashboard_message_type'] = 'error';
    }
    $stmt->close();
    header("Location: dashboard.php?section=courses");
    exit();
}

// CREATE/EDIT COURSE (Professor only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'create_course' || $action === 'edit_course') && $userRole === 'admin') {
    $courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
    $courseCode = trim($_POST['course_code']);
    $courseName = trim($_POST['course_name']);
    $description = trim($_POST['description']);
    $year = (int)$_POST['year'];
    $semester = (int)$_POST['semester'];
    $isVisible = isset($_POST['is_visible']) ? 1 : 0;
    
    if ($action === 'create_course') {
        $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, description, year, semester, is_visible) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiii", $courseCode, $courseName, $description, $year, $semester, $isVisible);
        $stmt->execute();
        $newCourseId = $stmt->insert_id;
        $stmt->close();
        
        // Link professor to the course
        $linkStmt = $conn->prepare("INSERT INTO course_professors (course_id, professor_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE id=id");
        $linkStmt->bind_param("ii", $newCourseId, $userId);
        $linkStmt->execute();
        $linkStmt->close();
        
        if ($newCourseId) {
            $_SESSION['dashboard_message'] = 'Το μάθημα δημιουργήθηκε επιτυχώς.';
            $_SESSION['dashboard_message_type'] = 'success';
        } else {
            $_SESSION['dashboard_message'] = 'Σφάλμα κατά την αποθήκευση.';
            $_SESSION['dashboard_message_type'] = 'error';
        }
    } else {
        $stmt = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ?, description = ?, year = ?, semester = ?, is_visible = ? WHERE id = ?");
        $stmt->bind_param("sssiiii", $courseCode, $courseName, $description, $year, $semester, $isVisible, $courseId);
        
        if ($stmt->execute()) {
            $_SESSION['dashboard_message'] = 'Το μάθημα ενημερώθηκε επιτυχώς.';
            $_SESSION['dashboard_message_type'] = 'success';
        } else {
            $_SESSION['dashboard_message'] = 'Σφάλμα κατά την αποθήκευση.';
            $_SESSION['dashboard_message_type'] = 'error';
        }
    }
    $stmt->close();
    header("Location: dashboard.php?section=courses");
    exit();
}

// CREATE/EDIT ASSIGNMENT (Professor only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'create_assignment' || $action === 'edit_assignment') && $userRole === 'admin') {
    $assignmentId = isset($_POST['assignment_id']) ? (int)$_POST['assignment_id'] : 0;
    $courseId = (int)$_POST['course_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $dueDate = $_POST['due_date'];
    $maxGrade = (float)$_POST['max_grade'];
    
    if ($action === 'create_assignment') {
        $stmt = $conn->prepare("INSERT INTO assignments (course_id, professor_id, title, description, due_date, max_grade) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssd", $courseId, $userId, $title, $description, $dueDate, $maxGrade);
    } else {
        $stmt = $conn->prepare("UPDATE assignments SET course_id = ?, title = ?, description = ?, due_date = ?, max_grade = ? WHERE id = ? AND professor_id = ?");
        $stmt->bind_param("isssdii", $courseId, $title, $description, $dueDate, $maxGrade, $assignmentId, $userId);
    }
    
    if ($stmt->execute()) {
        $_SESSION['dashboard_message'] = $action === 'create_assignment' ? 'Η εργασία δημιουργήθηκε επιτυχώς.' : 'Η εργασία ενημερώθηκε επιτυχώς.';
        $_SESSION['dashboard_message_type'] = 'success';
    } else {
        $_SESSION['dashboard_message'] = 'Σφάλμα κατά την αποθήκευση.';
        $_SESSION['dashboard_message_type'] = 'error';
    }
    $stmt->close();
    header("Location: dashboard.php?section=assignments");
    exit();
}

// SUBMIT ASSIGNMENT (Student only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit_assignment' && $userRole === 'student') {
    $assignmentId = (int)$_POST['assignment_id'];
    $submissionText = trim($_POST['submission_text']);
    
    // Check if already submitted
    $stmt = $conn->prepare("SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $assignmentId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $submission = $result->fetch_assoc();
        $stmt = $conn->prepare("UPDATE submissions SET submission_text = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("si", $submissionText, $submission['id']);
    } else {
        $stmt = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, submission_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $assignmentId, $userId, $submissionText);
    }
    
    if ($stmt->execute()) {
        $_SESSION['dashboard_message'] = 'Η εργασία υποβλήθηκε επιτυχώς.';
        $_SESSION['dashboard_message_type'] = 'success';
    } else {
        $_SESSION['dashboard_message'] = 'Σφάλμα κατά την υποβολή.';
        $_SESSION['dashboard_message_type'] = 'error';
    }
    $stmt->close();
    header("Location: dashboard.php?section=assignments");
    exit();
}

// GRADE SUBMISSION (Professor only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'grade_submission' && $userRole === 'admin') {
    $submissionId = (int)$_POST['submission_id'];
    $assignmentId = (int)$_POST['assignment_id'];
    $studentId = (int)$_POST['student_id'];
    $grade = (float)$_POST['grade'];
    $feedback = trim($_POST['feedback'] ?? '');
    
    // Check if grade already exists
    $stmt = $conn->prepare("SELECT id FROM grades WHERE submission_id = ?");
    $stmt->bind_param("i", $submissionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $gradeRow = $result->fetch_assoc();
        $stmt = $conn->prepare("UPDATE grades SET grade = ?, feedback = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("dsi", $grade, $feedback, $gradeRow['id']);
    } else {
        $stmt = $conn->prepare("INSERT INTO grades (submission_id, assignment_id, student_id, professor_id, grade, feedback) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiids", $submissionId, $assignmentId, $studentId, $userId, $grade, $feedback);
    }
    
    if ($stmt->execute()) {
        $_SESSION['dashboard_message'] = 'Η βαθμολογία αποθηκεύτηκε επιτυχώς.';
        $_SESSION['dashboard_message_type'] = 'success';
    } else {
        $_SESSION['dashboard_message'] = 'Σφάλμα κατά την αποθήκευση της βαθμολογίας.';
        $_SESSION['dashboard_message_type'] = 'error';
    }
    $stmt->close();
    header("Location: dashboard.php?section=submissions");
    exit();
}

// HANDLE FORM DISPLAYS (Show create/edit forms)
$showForm = $_GET['form'] ?? '';

// Show course form
if (($showForm === 'create_course' || $showForm === 'edit_course') && $userRole === 'admin') {
    $course = null;
    if ($showForm === 'edit_course' && isset($_GET['id'])) {
        $courseId = (int)$_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $course = $result->fetch_assoc();
        $stmt->close();
    }
    ?>
    <!DOCTYPE html>
    <html lang="el">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $showForm === 'create_course' ? 'Νέο Μάθημα' : 'Επεξεργασία Μαθήματος' ?></title>
        <link rel="stylesheet" href="CSS/dashboard.css?v=3">
    </head>
    <body>
        <div class="main-content">
            <h1><?= $showForm === 'create_course' ? 'Δημιουργία Νέου Μαθήματος' : 'Επεξεργασία Μαθήματος' ?></h1>
            <form method="POST" action="dashboard.php" class="form-container">
                <input type="hidden" name="action" value="<?= $showForm ?>">
                <?php if ($course): ?>
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                <?php endif; ?>
                
                <label>Κωδικός Μαθήματος *</label>
                <input type="text" name="course_code" value="<?= $course ? htmlspecialchars($course['course_code']) : '' ?>" required>
                
                <label>Όνομα Μαθήματος *</label>
                <input type="text" name="course_name" value="<?= $course ? htmlspecialchars($course['course_name']) : '' ?>" required>
                
                <label>Περιγραφή *</label>
                <textarea name="description" rows="4" required><?= $course ? htmlspecialchars($course['description']) : '' ?></textarea>
                
                <label>Έτος *</label>
                <select name="year" required>
                    <?php for ($y = 1; $y <= 4; $y++): ?>
                        <option value="<?= $y ?>" <?= $course && $course['year'] == $y ? 'selected' : '' ?>>
                            <?= ['Α\'', 'Β\'', 'Γ\'', 'Δ\''][$y-1] ?> Έτος
                        </option>
                    <?php endfor; ?>
                </select>
                
                <label>Εξάμηνο *</label>
                <select name="semester" required>
                    <option value="1" <?= $course && $course['semester'] == 1 ? 'selected' : '' ?>>Χειμερινό</option>
                    <option value="2" <?= $course && $course['semester'] == 2 ? 'selected' : '' ?>>Εαρινό</option>
                </select>
                
                <label>
                    <input type="checkbox" name="is_visible" <?= $course && $course['is_visible'] ? 'checked' : '' ?>>
                    Ορατό στους φοιτητές
                </label>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Αποθήκευση</button>
                    <a href="dashboard.php?section=courses" class="btn-secondary">Ακύρωση</a>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Show assignment form
if (($showForm === 'create_assignment' || $showForm === 'edit_assignment') && $userRole === 'admin') {
    $assignment = null;
    if ($showForm === 'edit_assignment' && isset($_GET['id'])) {
        $assignmentId = (int)$_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM assignments WHERE id = ? AND professor_id = ?");
        $stmt->bind_param("ii", $assignmentId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $assignment = $result->fetch_assoc();
        $stmt->close();
    }
    
    // Get professor's courses
    $stmt = $conn->prepare("SELECT DISTINCT c.* FROM courses c 
                            LEFT JOIN course_professors cp ON c.id = cp.course_id 
                            WHERE cp.professor_id = ?
                            ORDER BY c.year, c.semester");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    $stmt->close();
    
    if (empty($courses)) {
        $stmt = $conn->prepare("SELECT * FROM courses ORDER BY year, semester");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        $stmt->close();
    }
    ?>
    <!DOCTYPE html>
    <html lang="el">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $showForm === 'create_assignment' ? 'Νέα Εργασία' : 'Επεξεργασία Εργασίας' ?></title>
        <link rel="stylesheet" href="CSS/dashboard.css?v=3">
    </head>
    <body>
        <div class="main-content">
            <h1><?= $showForm === 'create_assignment' ? 'Δημιουργία Νέας Εργασίας' : 'Επεξεργασία Εργασίας' ?></h1>
            <form method="POST" action="dashboard.php" class="form-container">
                <input type="hidden" name="action" value="<?= $showForm ?>">
                <?php if ($assignment): ?>
                    <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                <?php endif; ?>
                
                <label>Μάθημα *</label>
                <select name="course_id" required>
                    <option value="">--Επιλέξτε Μάθημα--</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id'] ?>" <?= $assignment && $assignment['course_id'] == $course['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label>Τίτλος *</label>
                <input type="text" name="title" value="<?= $assignment ? htmlspecialchars($assignment['title']) : '' ?>" required>
                
                <label>Περιγραφή *</label>
                <textarea name="description" rows="6" required><?= $assignment ? htmlspecialchars($assignment['description']) : '' ?></textarea>
                
                <label>Ημερομηνία Λήξης *</label>
                <input type="datetime-local" name="due_date" value="<?= $assignment ? date('Y-m-d\TH:i', strtotime($assignment['due_date'])) : '' ?>" required>
                
                <label>Μέγιστος Βαθμός *</label>
                <input type="number" name="max_grade" value="<?= $assignment ? $assignment['max_grade'] : 100 ?>" step="0.01" min="0" required>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Αποθήκευση</button>
                    <a href="dashboard.php?section=assignments" class="btn-secondary">Ακύρωση</a>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Show submission form (Student only)
if ($showForm === 'submit_assignment' && $userRole === 'student' && isset($_GET['assignment_id'])) {
    $assignmentId = (int)$_GET['assignment_id'];
    
    $stmt = $conn->prepare("SELECT a.*, c.course_name, c.course_code FROM assignments a 
                            JOIN courses c ON a.course_id = c.id 
                            WHERE a.id = ?");
    $stmt->bind_param("i", $assignmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignment = $result->fetch_assoc();
    $stmt->close();
    
    if (!$assignment) {
        header("Location: dashboard.php?section=assignments");
        exit();
    }
    
    // Check if already submitted
    $stmt = $conn->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $assignmentId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingSubmission = $result->num_rows > 0 ? $result->fetch_assoc() : null;
    $stmt->close();
    ?>
    <!DOCTYPE html>
    <html lang="el">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Υποβολή Εργασίας</title>
        <link rel="stylesheet" href="CSS/dashboard.css?v=3">
    </head>
    <body>
        <div class="main-content">
            <h1>Υποβολή Εργασίας</h1>
            <div class="assignment-info">
                <h2><?= htmlspecialchars($assignment['title']) ?></h2>
                <p><strong>Μάθημα:</strong> <?= htmlspecialchars($assignment['course_code']) ?> - <?= htmlspecialchars($assignment['course_name']) ?></p>
                <p><strong>Λήξη:</strong> <?= date('d/m/Y H:i', strtotime($assignment['due_date'])) ?></p>
                <p><strong>Περιγραφή:</strong></p>
                <p><?= nl2br(htmlspecialchars($assignment['description'])) ?></p>
            </div>
            
            <form method="POST" action="dashboard.php" class="form-container">
                <input type="hidden" name="action" value="submit_assignment">
                <input type="hidden" name="assignment_id" value="<?= $assignmentId ?>">
                
                <label>Υποβολή *</label>
                <textarea name="submission_text" rows="10" required placeholder="Γράψτε την απάντησή σας εδώ..."><?= $existingSubmission ? htmlspecialchars($existingSubmission['submission_text']) : '' ?></textarea>
                <?php if ($existingSubmission): ?>
                    <p class="info-text">Υπάρχει ήδη υποβολή. Η νέα υποβολή θα αντικαταστήσει την προηγούμενη.</p>
                <?php endif; ?>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Υποβολή</button>
                    <a href="dashboard.php?section=assignments" class="btn-secondary">Ακύρωση</a>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// MAIN DASHBOARD VIEW
// Get active section from URL (default based on role)
$activeSection = isset($_GET['section']) ? $_GET['section'] : ($userRole === 'admin' ? 'courses' : 'my-courses');
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : 1;
if ($selectedYear < 1 || $selectedYear > 4) $selectedYear = 1;

$yearNames = [1 => 'Α\' Έτος', 2 => 'Β\' Έτος', 3 => 'Γ\' Έτος', 4 => 'Δ\' Έτος'];

// MESSAGES
$message = $_SESSION['dashboard_message'] ?? '';
$messageType = $_SESSION['dashboard_message_type'] ?? '';
unset($_SESSION['dashboard_message'], $_SESSION['dashboard_message_type']);

// STUDENT DATA
$myCourses = [];
$myAssignments = [];
$myGrades = [];

if ($userRole === 'student') {
    // Get courses visible to students
    $stmt = $conn->prepare("SELECT * FROM courses WHERE year = ? AND is_visible = 1 ORDER BY semester, course_code");
    $stmt->bind_param("i", $selectedYear);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $myCourses[] = $row;
    }
    $stmt->close();
    
    // Get assignments for student's courses
    if (!empty($myCourses)) {
        $courseIds = array_column($myCourses, 'id');
        $placeholders = str_repeat('?,', count($courseIds) - 1) . '?';
        $stmt = $conn->prepare("SELECT a.*, c.course_name, c.course_code 
                                FROM assignments a 
                                JOIN courses c ON a.course_id = c.id 
                                WHERE a.course_id IN ($placeholders) 
                                ORDER BY a.due_date DESC");
        $stmt->bind_param(str_repeat('i', count($courseIds)), ...$courseIds);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            // Check if student has submitted
            $subStmt = $conn->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
            $subStmt->bind_param("ii", $row['id'], $userId);
            $subStmt->execute();
            $subResult = $subStmt->get_result();
            $row['submitted'] = $subResult->num_rows > 0;
            $row['submission'] = $subResult->num_rows > 0 ? $subResult->fetch_assoc() : null;
            
            // Check if graded
            if ($row['submitted']) {
                $gradeStmt = $conn->prepare("SELECT * FROM grades WHERE submission_id = ?");
                $gradeStmt->bind_param("i", $row['submission']['id']);
                $gradeStmt->execute();
                $gradeResult = $gradeStmt->get_result();
                $row['grade'] = $gradeResult->num_rows > 0 ? $gradeResult->fetch_assoc() : null;
                $gradeStmt->close();
            }
            
            $subStmt->close();
            $myAssignments[] = $row;
        }
        $stmt->close();
    }
    
    // Get all grades for student
    $stmt = $conn->prepare("SELECT g.*, a.title as assignment_title, c.course_name, c.course_code 
                            FROM grades g 
                            JOIN submissions s ON g.submission_id = s.id 
                            JOIN assignments a ON g.assignment_id = a.id 
                            JOIN courses c ON a.course_id = c.id 
                            WHERE g.student_id = ? 
                            ORDER BY g.graded_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $myGrades[] = $row;
    }
    $stmt->close();
}

// PROFESSOR DATA
$professorCourses = [];
$professorAssignments = [];
$pendingSubmissions = [];

if ($userRole === 'admin') {
    // Get courses taught by this professor
    $stmt = $conn->prepare("SELECT DISTINCT c.* FROM courses c 
                            LEFT JOIN course_professors cp ON c.id = cp.course_id 
                            WHERE cp.professor_id = ? AND c.year = ?
                            ORDER BY c.semester, c.course_code");
    $stmt->bind_param("ii", $userId, $selectedYear);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $professorCourses[] = $row;
    }
    $stmt->close();
    
    // If no courses via course_professors, get all courses (for initial setup)
    if (empty($professorCourses)) {
        $stmt = $conn->prepare("SELECT * FROM courses WHERE year = ? ORDER BY semester, course_code");
        $stmt->bind_param("i", $selectedYear);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $professorCourses[] = $row;
        }
        $stmt->close();
    }
    
    // Get assignments created by this professor
    if (!empty($professorCourses)) {
        $courseIds = array_column($professorCourses, 'id');
        $placeholders = str_repeat('?,', count($courseIds) - 1) . '?';
        $stmt = $conn->prepare("SELECT a.*, c.course_name, c.course_code,
                                (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) as submission_count
                                FROM assignments a 
                                JOIN courses c ON a.course_id = c.id 
                                WHERE a.course_id IN ($placeholders) AND a.professor_id = ?
                                ORDER BY a.created_at DESC");
        $params = array_merge($courseIds, [$userId]);
        $stmt->bind_param(str_repeat('i', count($courseIds)) . 'i', ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $professorAssignments[] = $row;
        }
        $stmt->close();
    }
    
    // Get pending submissions (not yet graded)
    $stmt = $conn->prepare("SELECT s.*, a.title as assignment_title, a.max_grade, 
                            c.course_name, c.course_code, st.name as student_name, st.email as student_email
                            FROM submissions s
                            JOIN assignments a ON s.assignment_id = a.id
                            JOIN courses c ON a.course_id = c.id
                            JOIN students st ON s.student_id = st.id
                            LEFT JOIN grades g ON s.id = g.submission_id
                            WHERE a.professor_id = ? AND g.id IS NULL
                            ORDER BY s.submitted_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pendingSubmissions[] = $row;
    }
    $stmt->close();
}

// Separate courses by semester
$semester1 = array_filter($userRole === 'admin' ? $professorCourses : $myCourses, fn($c) => $c['semester'] == 1);
$semester2 = array_filter($userRole === 'admin' ? $professorCourses : $myCourses, fn($c) => $c['semester'] == 2);
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $userRole === 'admin' ? 'Πίνακας Καθηγητή' : 'Πίνακας Φοιτητή' ?> - Πανεπιστημιακή Πύλη</title>
    <link rel="stylesheet" href="CSS/dashboard.css?v=3">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">
                <div class="logo-icon">Π</div>
                <div class="logo-text">Πανεπιστήμιο <span>Portal</span></div>
            </a>
            <div class="nav-right">
                <div class="user-info">
                    <div class="user-avatar"><?= $userInitial ?></div>
                    <span><?= htmlspecialchars($userName) ?> <?= $userRole === 'admin' ? '(Καθηγητής)' : '(Φοιτητής)' ?></span>
                </div>
                <a href="logout.php" class="logout-btn">Αποσύνδεση</a>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="welcome-section">
            <h1>Καλώς ήρθες, <?= htmlspecialchars($userName) ?>!</h1>
            <p><?= $userRole === 'admin' 
                ? 'Διαχειριστείτε τα μαθήματά σας, ανεβάστε εργασίες και βαθμολογήστε τους φοιτητές.' 
                : 'Περιηγηθείτε στα μαθήματά σας, δείτε τις εργασίες και τις βαθμολογίες σας.' ?></p>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Navigation Tabs -->
        <div class="dashboard-nav">
            <?php if ($userRole === 'admin'): ?>
                <a href="?section=courses&year=<?= $selectedYear ?>" class="nav-tab <?= $activeSection === 'courses' ? 'active' : '' ?>">
                    Μαθήματα
                </a>
                <a href="?section=assignments&year=<?= $selectedYear ?>" class="nav-tab <?= $activeSection === 'assignments' ? 'active' : '' ?>">
                    Εργασίες
                </a>
                <a href="?section=submissions&year=<?= $selectedYear ?>" class="nav-tab <?= $activeSection === 'submissions' ? 'active' : '' ?>">
                    Υποβολές <span class="badge"><?= count($pendingSubmissions) ?></span>
                </a>
            <?php else: ?>
                <a href="?section=my-courses&year=<?= $selectedYear ?>" class="nav-tab <?= $activeSection === 'my-courses' ? 'active' : '' ?>">
                    Μαθήματα
                </a>
                <a href="?section=assignments&year=<?= $selectedYear ?>" class="nav-tab <?= $activeSection === 'assignments' ? 'active' : '' ?>">
                    Εργασίες
                </a>
                <a href="?section=grades&year=<?= $selectedYear ?>" class="nav-tab <?= $activeSection === 'grades' ? 'active' : '' ?>">
                    Βαθμολογίες
                </a>
            <?php endif; ?>
        </div>

        <!-- Year Selector -->
        <div class="year-selector">
            <h3>Ακαδημαϊκό Έτος</h3>
            <div class="year-tabs">
                <?php for ($y = 1; $y <= 4; $y++): ?>
                    <a href="?section=<?= $activeSection ?>&year=<?= $y ?>" class="year-tab <?= $selectedYear === $y ? 'active' : '' ?>">
                        <?= $yearNames[$y] ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>

        <!-- CONTENT SECTIONS -->
        
        <?php if ($userRole === 'admin' && $activeSection === 'courses'): ?>
            <!-- PROFESSOR: Courses Management -->
            <div class="section-header">
                <h2 class="section-title">Διαχείριση Μαθημάτων</h2>
                <a href="?form=create_course" class="btn-primary">+ Νέο Μάθημα</a>
            </div>

            <?php if (empty($professorCourses)): ?>
                <div class="empty-state">
                    <h3>Δεν υπάρχουν μαθήματα</h3>
                    <p>Δημιουργήστε ένα νέο μάθημα για να ξεκινήσετε.</p>
                </div>
            <?php else: ?>
                <?php if (!empty($semester1)): ?>
                <section class="courses-section">
                    <div class="section-header">
                        <h2 class="section-title">Μαθήματα <?= $yearNames[$selectedYear] ?></h2>
                        <span class="semester-badge">Χειμερινό Εξάμηνο</span>
                    </div>
                    <div class="courses-grid">
                        <?php foreach ($semester1 as $course): ?>
                        <div class="course-card <?= !$course['is_visible'] ? 'hidden-course' : '' ?>">
                            <div class="course-header">
                                <span class="course-code"><?= htmlspecialchars($course['course_code']) ?></span>
                            </div>
                            <h3 class="course-name"><?= htmlspecialchars($course['course_name']) ?></h3>
                            <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>
                            <div class="course-footer">
                                <span class="course-semester">Εξάμηνο <?= $course['semester'] ?></span>
                                <div class="course-actions">
                                    <a href="?form=edit_course&id=<?= $course['id'] ?>" class="btn-small">Επεξεργασία</a>
                                    <a href="?section=assignments&course_id=<?= $course['id'] ?>" class="btn-small">Εργασίες</a>
                                    <form method="POST" action="dashboard.php" class="toggle-container">
                                        <input type="hidden" name="action" value="toggle_visibility">
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
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <?php if (!empty($semester2)): ?>
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
                            </div>
                            <h3 class="course-name"><?= htmlspecialchars($course['course_name']) ?></h3>
                            <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>
                            <div class="course-footer">
                                <span class="course-semester">Εξάμηνο <?= $course['semester'] ?></span>
                                <div class="course-actions">
                                    <a href="?form=edit_course&id=<?= $course['id'] ?>" class="btn-small">Επεξεργασία</a>
                                    <a href="?section=assignments&course_id=<?= $course['id'] ?>" class="btn-small">Εργασίες</a>
                                    <form method="POST" action="dashboard.php" class="toggle-container">
                                        <input type="hidden" name="action" value="toggle_visibility">
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
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
            <?php endif; ?>
            
        <?php elseif ($userRole === 'admin' && $activeSection === 'assignments'): ?>
            <!-- PROFESSOR: Assignments Management -->
            <div class="section-header">
                <h2 class="section-title">Διαχείριση Εργασιών</h2>
                <a href="?form=create_assignment" class="btn-primary">+ Νέα Εργασία</a>
            </div>

            <?php if (empty($professorAssignments)): ?>
                <div class="empty-state">
                    <h3>Δεν υπάρχουν εργασίες</h3>
                    <p>Δημιουργήστε μια νέα εργασία για να ξεκινήσετε.</p>
                </div>
            <?php else: ?>
                <div class="assignments-list">
                    <?php foreach ($professorAssignments as $assignment): ?>
                    <div class="assignment-card">
                        <div class="assignment-header">
                            <div>
                                <h3><?= htmlspecialchars($assignment['title']) ?></h3>
                                <p class="assignment-course"><?= htmlspecialchars($assignment['course_code']) ?> - <?= htmlspecialchars($assignment['course_name']) ?></p>
                            </div>
                            <div class="assignment-meta">
                                <span class="badge"><?= $assignment['submission_count'] ?> υποβολές</span>
                            </div>
                        </div>
                        <p class="assignment-description"><?= htmlspecialchars($assignment['description']) ?></p>
                        <div class="assignment-footer">
                            <div>
                                <span class="assignment-due">Λήξη: <?= date('d/m/Y H:i', strtotime($assignment['due_date'])) ?></span>
                                <span class="assignment-max-grade">Μέγιστος Βαθμός: <?= $assignment['max_grade'] ?></span>
                            </div>
                            <div class="assignment-actions">
                                <a href="?form=edit_assignment&id=<?= $assignment['id'] ?>" class="btn-small">Επεξεργασία</a>
                                <a href="?section=submissions&assignment_id=<?= $assignment['id'] ?>" class="btn-small">Υποβολές</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php elseif ($userRole === 'admin' && $activeSection === 'submissions'): ?>
            <!-- PROFESSOR: View Submissions -->
            <div class="section-header">
                <h2 class="section-title">Υποβολές Φοιτητών</h2>
            </div>

            <?php if (empty($pendingSubmissions)): ?>
                <div class="empty-state">
                    <h3>Δεν υπάρχουν εκκρεμείς υποβολές</h3>
                    <p>Όλες οι υποβολές έχουν βαθμολογηθεί.</p>
                </div>
            <?php else: ?>
                <div class="submissions-list">
                    <?php foreach ($pendingSubmissions as $submission): ?>
                    <div class="submission-card">
                        <div class="submission-header">
                            <div>
                                <h3><?= htmlspecialchars($submission['assignment_title']) ?></h3>
                                <p class="submission-student">Φοιτητής: <?= htmlspecialchars($submission['student_name']) ?> (<?= htmlspecialchars($submission['student_email']) ?>)</p>
                                <p class="submission-course"><?= htmlspecialchars($submission['course_code']) ?> - <?= htmlspecialchars($submission['course_name']) ?></p>
                            </div>
                            <div class="submission-meta">
                                <span class="submission-date">Υποβλήθηκε: <?= date('d/m/Y H:i', strtotime($submission['submitted_at'])) ?></span>
                            </div>
                        </div>
                        <?php if ($submission['submission_text']): ?>
                            <div class="submission-content">
                                <h4>Περιεχόμενο:</h4>
                                <p><?= nl2br(htmlspecialchars($submission['submission_text'])) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if ($submission['file_path']): ?>
                            <div class="submission-file">
                                <a href="<?= htmlspecialchars($submission['file_path']) ?>" target="_blank" class="btn-small">Λήψη Αρχείου</a>
                            </div>
                        <?php endif; ?>
                        <div class="submission-footer">
                            <form method="POST" action="dashboard.php" class="grade-form">
                                <input type="hidden" name="action" value="grade_submission">
                                <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                                <input type="hidden" name="assignment_id" value="<?= $submission['assignment_id'] ?>">
                                <input type="hidden" name="student_id" value="<?= $submission['student_id'] ?>">
                                <div class="grade-inputs">
                                    <label>
                                        Βαθμός (0-<?= $submission['max_grade'] ?>):
                                        <input type="number" name="grade" step="0.01" min="0" max="<?= $submission['max_grade'] ?>" required>
                                    </label>
                                    <label>
                                        Σχόλια:
                                        <textarea name="feedback" rows="3" placeholder="Προαιρετικά σχόλια..."></textarea>
                                    </label>
                                </div>
                                <button type="submit" class="btn-primary">Αποθήκευση Βαθμού</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php elseif ($userRole === 'student' && $activeSection === 'my-courses'): ?>
            <!-- STUDENT: View Courses -->
            <div class="section-header">
                <h2 class="section-title">Τα Μαθήματά Μου</h2>
            </div>

            <?php if (empty($myCourses)): ?>
                <div class="empty-state">
                    <h3>Δεν υπάρχουν διαθέσιμα μαθήματα</h3>
                    <p>Δεν υπάρχουν μαθήματα διαθέσιμα για το <?= $yearNames[$selectedYear] ?> αυτή τη στιγμή.</p>
                </div>
            <?php else: ?>
                <?php if (!empty($semester1)): ?>
                <section class="courses-section">
                    <div class="section-header">
                        <h2 class="section-title">Μαθήματα <?= $yearNames[$selectedYear] ?></h2>
                        <span class="semester-badge">Χειμερινό Εξάμηνο</span>
                    </div>
                    <div class="courses-grid">
                        <?php foreach ($semester1 as $course): ?>
                        <div class="course-card">
                            <div class="course-header">
                                <span class="course-code"><?= htmlspecialchars($course['course_code']) ?></span>
                            </div>
                            <h3 class="course-name"><?= htmlspecialchars($course['course_name']) ?></h3>
                            <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>
                            <div class="course-footer">
                                <span class="course-semester">Εξάμηνο <?= $course['semester'] ?></span>
                                <a href="?section=assignments&course_id=<?= $course['id'] ?>" class="course-action">Προβολή Μαθήματος</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <?php if (!empty($semester2)): ?>
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
                            </div>
                            <h3 class="course-name"><?= htmlspecialchars($course['course_name']) ?></h3>
                            <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>
                            <div class="course-footer">
                                <span class="course-semester">Εξάμηνο <?= $course['semester'] ?></span>
                                <a href="?section=assignments&course_id=<?= $course['id'] ?>" class="course-action">Προβολή Μαθήματος</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
            <?php endif; ?>
            
        <?php elseif ($userRole === 'student' && $activeSection === 'assignments'): ?>
            <!-- STUDENT: View & Submit Assignments -->
            <?php
            $selectedCourseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
            $filteredAssignments = $selectedCourseId > 0 
                ? array_filter($myAssignments, fn($a) => $a['course_id'] == $selectedCourseId)
                : $myAssignments;
            ?>
            <div class="section-header">
                <h2 class="section-title">Εργασίες</h2>
            </div>

            <?php if (empty($filteredAssignments)): ?>
                <div class="empty-state">
                    <h3>Δεν υπάρχουν διαθέσιμες εργασίες</h3>
                    <p>Δεν υπάρχουν εργασίες για τα μαθήματά σας αυτή τη στιγμή.</p>
                </div>
            <?php else: ?>
                <div class="assignments-list">
                    <?php foreach ($filteredAssignments as $assignment): ?>
                    <div class="assignment-card <?= $assignment['submitted'] ? 'submitted' : '' ?>">
                        <div class="assignment-header">
                            <div>
                                <h3><?= htmlspecialchars($assignment['title']) ?></h3>
                                <p class="assignment-course"><?= htmlspecialchars($assignment['course_code']) ?> - <?= htmlspecialchars($assignment['course_name']) ?></p>
                            </div>
                            <div class="assignment-meta">
                                <?php if ($assignment['submitted']): ?>
                                    <span class="badge success">Υποβλήθηκε</span>
                                    <?php if ($assignment['grade']): ?>
                                        <span class="badge">Βαθμός: <?= $assignment['grade']['grade'] ?>/<?= $assignment['max_grade'] ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge warning">Εκκρεμής</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="assignment-description"><?= htmlspecialchars($assignment['description']) ?></p>
                        <div class="assignment-footer">
                            <div>
                                <span class="assignment-due">Λήξη: <?= date('d/m/Y H:i', strtotime($assignment['due_date'])) ?></span>
                                <span class="assignment-max-grade">Μέγιστος Βαθμός: <?= $assignment['max_grade'] ?></span>
                            </div>
                            <div class="assignment-actions">
                                <?php if ($assignment['submitted']): ?>
                                    <span class="btn-small">Υποβλήθηκε</span>
                                <?php else: ?>
                                    <a href="?form=submit_assignment&assignment_id=<?= $assignment['id'] ?>" class="btn-primary">Υποβολή Εργασίας</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php elseif ($userRole === 'student' && $activeSection === 'grades'): ?>
            <!-- STUDENT: View Grades -->
            <div class="section-header">
                <h2 class="section-title">Βαθμολογίες</h2>
            </div>

            <?php if (empty($myGrades)): ?>
                <div class="empty-state">
                    <h3>Δεν υπάρχουν βαθμολογίες</h3>
                    <p>Δεν έχετε βαθμολογηθεί ακόμα σε καμία εργασία.</p>
                </div>
            <?php else: ?>
                <div class="grades-table-container">
                    <table class="grades-table">
                        <thead>
                            <tr>
                                <th>Μάθημα</th>
                                <th>Εργασία</th>
                                <th>Βαθμός</th>
                                <th>Σχόλια</th>
                                <th>Ημερομηνία</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myGrades as $grade): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($grade['course_code']) ?></strong><br>
                                    <small><?= htmlspecialchars($grade['course_name']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($grade['assignment_title']) ?></td>
                                <td>
                                    <span class="grade-value"><?= $grade['grade'] ?></span>
                                </td>
                                <td><?= $grade['feedback'] ? nl2br(htmlspecialchars($grade['feedback'])) : '-' ?></td>
                                <td><?= date('d/m/Y', strtotime($grade['graded_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </main>

    <script src="JS/dashboard.js?v=1"></script>
</body>
</html>

