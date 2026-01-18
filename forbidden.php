<?php
/**
 * Forbidden Page - 403 Error
 * 
 * Displays when user tries to access a page they don't have permission for
 * 
 * @Author AlexTzamalis
 * UEL : 2872177
 */
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forbidden Action - Πανεπιστημιακή Πύλη</title>
    <link rel="stylesheet" href="CSS/dashboard.css?v=3">
    <style>
        .forbidden-container {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .forbidden-icon {
            font-size: 72px;
            color: #8B1538;
            margin-bottom: 20px;
        }
        .forbidden-container h1 {
            color: #8B1538;
            margin-bottom: 15px;
        }
        .forbidden-container p {
            color: #4A4A4A;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="forbidden-container">
        <div class="forbidden-icon">🚫</div>
        <h1>Forbidden Action</h1>
        <p>Δεν έχετε δικαίωμα πρόσβασης σε αυτή τη σελίδα ή ενέργεια.</p>
        <p>Η πρόσβαση περιορίζεται ανάλογα με τον ρόλο σας στο σύστημα.</p>
        <div style="margin-top: 30px;">
            <a href="dashboard.php" class="btn-primary">Επιστροφή στο Dashboard</a>
            <a href="index.php" class="btn-secondary" style="margin-left: 10px;">Αρχική Σελίδα</a>
        </div>
    </div>
</body>
</html>

