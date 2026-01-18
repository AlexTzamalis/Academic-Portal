/**
 * Dashboard JavaScript
 * 
 * Handles client-side interactions for the unified dashboard
 * 
 * @Author AlexTzamalis
 * UEL : 2872177
 */

// Auto-hide messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const messages = document.querySelectorAll('.message');
    messages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'opacity 0.5s ease';
            message.style.opacity = '0';
            setTimeout(function() {
                message.remove();
            }, 500);
        }, 5000);
    });
    
    // Confirm before submitting forms
    const deleteForms = document.querySelectorAll('form[data-confirm]');
    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm(form.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
    
    // Validate grade input
    const gradeInputs = document.querySelectorAll('input[name="grade"]');
    gradeInputs.forEach(function(input) {
        const maxGrade = parseFloat(input.getAttribute('max'));
        input.addEventListener('input', function() {
            if (parseFloat(this.value) > maxGrade) {
                this.value = maxGrade;
            }
            if (parseFloat(this.value) < 0) {
                this.value = 0;
            }
        });
    });
});


