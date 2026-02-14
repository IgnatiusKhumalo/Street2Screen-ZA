/**
 * ============================================
 * MAIN JAVASCRIPT FILE
 * ============================================
 * Project: Street2Screen ZA
 * Purpose: Core client-side functionality
 * Author: Ignatius Mayibongwe Khumalo
 * Date: February 2026
 * ============================================
 */

// ===== FORM VALIDATION =====
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    // South African phone: 0821234567 or +27821234567
    const cleaned = phone.replace(/[\s\-\(\)]/g, '');
    return /^(0[0-9]{9}|\+27[0-9]{9})$/.test(cleaned);
}

function validatePassword(password) {
    // Min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special
    return password.length >= 8 &&
           /[A-Z]/.test(password) &&
           /[a-z]/.test(password) &&
           /[0-9]/.test(password) &&
           /[^A-Za-z0-9]/.test(password);
}

// ===== FLASH MESSAGE AUTO-DISMISS =====
document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.opacity = '0';
            setTimeout(function() {
                message.remove();
            }, 300);
        }, 5000); // Dismiss after 5 seconds
    });
});

// ===== IMAGE PREVIEW =====
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ===== AJAX HELPER =====
function makeAjaxRequest(url, method, data, successCallback, errorCallback) {
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: method !== 'GET' ? JSON.stringify(data) : null
    })
    .then(response => response.json())
    .then(data => successCallback(data))
    .catch(error => errorCallback ? errorCallback(error) : console.error('Error:', error));
}

// ===== CONFIRM DELETE =====
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this?');
}

console.log('Street2Screen ZA - JavaScript loaded successfully');
