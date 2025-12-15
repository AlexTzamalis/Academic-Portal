/**
 * Βασικό JavaScript Αρχειο για ενναλαγή login και register container
 * 
 * Χρησιμοποιείται κυρίως στη σελίδα login_register.php
 * για την εναλλαγή μεταξύ φορμών login/register.

 * 
 * @Author AlexTzamalis
 * UEL : 2872177
 */


/**
 * showForm() -> Εμφανίζει τη σωστή φόρμα (login ή register)
 * 
 * Αυτή η συνάρτηση κρύβει όλες τις φόρμες και εμφανίζει
 * μόνο αυτή που ζητήθηκε.
 * 
 * Πώς λειτουργεί:
 * 1. Βρίσκει ΟΛΑ τα elements με class "form-box"
 * 2. Αφαιρεί την class "active" από όλα (τα κρύβει)
 * 3. Προσθέτει την class "active" στη φόρμα με το formId
 * 
 * @param {string} formId - Το ID της φόρμας που θέλουμε να εμφανίσουμε
 *                          Παραδειγμα 'login-form' ή 'register-form'
 * 
 * @example
 * // Εμφάνιση φόρμας εγγραφής
 * showForm('register-form');
 * 
 * @example
 * // Εμφάνιση φόρμας σύνδεσης
 * showForm('login-form');
 */
function showForm(formId) {
    /**
     * document.querySelectorAll(".form-box")
     * Επιστρέφει ΟΛΑ τα elements που έχουν την class "form-box".
     * Στην περίπτωσή αθτην είναι 2: login-form και register-form.
     * 
     * .forEach(form => ...)
     * Κάνει loop σε κάθε element και εκτελεί τον κώδικα.
     * 
     * form.classList.remove("active")
     * Αφαιρεί την class "active" από κάθε φόρμα.
     * Χωρίς την "active" class, το CSS (display: none) τις κρύβει.
     */
    document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));

    /**
     * document.getElementById(formId)
     * Βρίσκει το element με το συγκεκριμένο ID.
     * 
     * .classList.add("active")
     * Προσθέτει την class "active" στη φόρμα.
     * Με την "active" class, το CSS (display: block) την εμφανίζει.
     */
    document.getElementById(formId).classList.add("active");
}
