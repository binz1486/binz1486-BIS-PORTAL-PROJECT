// ----------- Change Password Modal Logic -----------
const changePassBtn = document.getElementById('changePassBtn');
const changePassModal = document.getElementById('changePassModal');
const changePassCancel = document.getElementById('changePassCancel');
const changePassSuccess = document.getElementById('changePassSuccess');
const changePassForm = document.getElementById('changePassForm');
const changePassError = document.getElementById('changePassError');

// Show modal
if (changePassBtn && changePassModal) {
    changePassBtn.addEventListener('click', () => {
        changePassModal.classList.remove('hidden');
        // Hide floating message button and message modal
        const openMessageBtn = document.getElementById('openMessageBtn');
        const messageModal = document.getElementById('messageModal');
        if (openMessageBtn) openMessageBtn.classList.add('hidden');
        if (messageModal) messageModal.classList.add('hidden');
        // Hide dropdown menu
        const profileDropdown = document.getElementById('profileDropdown');
        if (profileDropdown) profileDropdown.classList.add('hidden');
    });
}
// Hide modal on cancel
if (changePassCancel && changePassModal) {
    changePassCancel.addEventListener('click', () => {
        changePassModal.classList.add('hidden');
        if (changePassError) changePassError.classList.add('hidden');
        if (changePassForm) changePassForm.reset();
        // Show floating message button again
        const openMessageBtn = document.getElementById('openMessageBtn');
        if (openMessageBtn) openMessageBtn.classList.remove('hidden');
        // Show dropdown menu again
        const profileDropdown = document.getElementById('profileDropdown');
        if (profileDropdown) profileDropdown.classList.remove('hidden');
    });
}

// Password show/hide toggle (must be global for inline onclick)
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === "password") {
        input.type = "text";
        btn.querySelector('i').classList.remove('fa-eye');
        btn.querySelector('i').classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        btn.querySelector('i').classList.remove('fa-eye-slash');
        btn.querySelector('i').classList.add('fa-eye');
    }
}

// Password strength checker
document.getElementById('newPassword')?.addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthBar = document.getElementById('changePasswordStrengthBar');
    const strengthText = document.getElementById('changePasswordStrengthText');
    if (!strengthBar || !strengthText) return;
    if (!password) {
        strengthBar.style.width = '0%';
        strengthBar.className = 'h-full transition-all duration-300 w-0';
        strengthText.textContent = '';
        return;
    }
    const hasLower = /[a-z]/.test(password);
    const hasUpper = /[A-Z]/.test(password);
    const hasNumber = /\d/.test(password);
    const hasSpecial = /[^a-zA-Z0-9]/.test(password);
    const isLongEnough = password.length >= 8;
    let width = 0, color = '', text = '';
    if (!isLongEnough) {
        width = 25; color = 'bg-red-500'; text = 'Too short';
    } else if (hasLower && !hasNumber && !hasSpecial && !hasUpper) {
        width = 33; color = 'bg-red-500'; text = 'Weak';
    } else if (hasLower && hasNumber && !hasSpecial && !hasUpper) {
        width = 50; color = 'bg-yellow-500'; text = 'Medium';
    } else if (hasLower && hasNumber && hasSpecial && !hasUpper) {
        width = 75; color = 'bg-blue-500'; text = 'Strong';
    } else if (hasLower && hasUpper && hasNumber && hasSpecial) {
        width = 100; color = 'bg-green-500'; text = 'Very Strong';
    } else {
        width = 50; color = 'bg-yellow-500'; text = 'Medium';
    }
    strengthBar.style.width = width + '%';
    strengthBar.className = 'h-full transition-all duration-300 ' + color;
    strengthText.textContent = text;
});

// Change Password Form Submit
if (changePassForm) {
    changePassForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (changePassError) changePassError.classList.add('hidden');
        const oldPassword = document.getElementById('oldPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        // Client-side validation
        if (newPassword.length < 8) {
            showChangePassError("New password must be at least 8 characters.");
            return;
        }
        if (newPassword !== confirmPassword) {
            showChangePassError("New password and confirm password do not match.");
            return;
        }
        if (oldPassword === newPassword) {
            showChangePassError("New password must be different from old password.");
            return;
        }

        fetch('change_pass.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ oldPassword, newPassword })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                changePassModal.classList.add('hidden');
                changePassSuccess.classList.remove('hidden');
                setTimeout(() => {
                    changePassSuccess.classList.add('hidden');
                    window.location.reload();
                }, 3000);
                changePassForm.reset();
            } else {
                showChangePassError(data.message || "Failed to change password.");
            }
        })
        .catch(() => {
            showChangePassError("An error occurred while changing your password.");
        });
    });
}

function showChangePassError(msg) {
    if (changePassError) {
        changePassError.textContent = msg;
        changePassError.classList.remove('hidden');
    } else {
        alert(msg);
    }
}

// ----------- Message Modal Logic -----------
const openMessageBtn = document.getElementById('openMessageBtn');
const messageModal = document.getElementById('messageModal');
const closeMessageModal = document.getElementById('closeMessageModal');
const cancelMessageBtn = document.getElementById('cancelMessageBtn');
const messageForm = document.getElementById('messageForm');
const messageSuccess = document.getElementById('messageSuccess');
const openMessageBtnProfile = document.getElementById('openMessageBtnProfile');

if (openMessageBtnProfile && messageModal) {
    openMessageBtnProfile.addEventListener('click', function(e) {
        e.preventDefault();
        messageModal.classList.remove('hidden');
    });
}
if (openMessageBtn && messageModal) {
    openMessageBtn.addEventListener('click', () => {
        messageModal.classList.remove('hidden');
    });
}
if (closeMessageModal && messageModal) {
    closeMessageModal.addEventListener('click', () => {
        messageModal.classList.add('hidden');
    });
}
if (cancelMessageBtn && messageModal) {
    cancelMessageBtn.addEventListener('click', () => {
        messageModal.classList.add('hidden');
    });
}
if (messageModal && closeMessageModal) {
    messageModal.addEventListener('click', function(e) {
        if (e.target === messageModal) {
            closeMessageModal.classList.add('shake');
            setTimeout(() => {
                closeMessageModal.classList.remove('shake');
            }, 500);
        }
    });
}
if (messageForm) {
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(messageForm);

        fetch('save_message.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (messageSuccess) messageSuccess.classList.remove('hidden');
                if (messageModal) messageModal.classList.add('hidden');
                setTimeout(() => {
                    if (messageSuccess) messageSuccess.classList.add('hidden');
                }, 4000);
                messageForm.reset();
            } else {
                alert(data.message || "Failed to send message.");
            }
        })
        .catch(() => {
            alert("An error occurred while sending your message.");
        });
    });
}