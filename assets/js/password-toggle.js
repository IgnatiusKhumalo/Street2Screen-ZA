/**
 * Password Toggle Functionality
 * Shows/hides password in password fields
 */

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Auto-initialize password toggles on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add toggle icon to all password fields
    document.querySelectorAll('input[type="password"]').forEach(function(input) {
        // Skip if already has toggle
        if (input.nextElementSibling && input.nextElementSibling.classList.contains('password-toggle')) {
            return;
        }
        
        // Wrap input in relative container if not already
        if (!input.parentElement.classList.contains('password-wrapper')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'password-wrapper';
            wrapper.style.position = 'relative';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
        }
        
        // Add toggle icon
        const toggleIcon = document.createElement('i');
        toggleIcon.className = 'fas fa-eye password-toggle';
        toggleIcon.style.cssText = 'position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;color:#6c757d';
        toggleIcon.onclick = function() {
            togglePassword(input.id);
        };
        
        input.parentElement.appendChild(toggleIcon);
    });
});
