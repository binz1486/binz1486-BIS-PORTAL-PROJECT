// Scroll to top on page load
window.scrollTo({ top: 0, behavior: 'smooth' });

// ================== Main Event Listener ==================
// All logic inside DOMContentLoaded to ensure elements exist
document.addEventListener('DOMContentLoaded', function() {

    // ------------- Login Form Handler -------------
    // Handles login form submission and redirects on success
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            showSpinner();

            // Detect admin mode
            const isAdmin = isAdminMode ? '1' : '0';
            const email = isAdminMode
                ? document.getElementById('adminEmail').value
                : document.getElementById('email').value;
            const password = document.getElementById('password').value;

            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('admin', isAdmin); // <-- Add this line

            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                hideSpinner();
                if (data.trim() === "admin_success") {
                    showSpinner();
                    setTimeout(() => {
                    window.location.href = "admin_dash.php"; // <--- admin dashboard
                    }, 2000);
                } else if (data.trim() === "user_success") {
                    showSpinner();
                    setTimeout(() => {
                    window.location.href = "dashboard.php"; // <--- user dashboard
                 }, 2000);
                } else {
                    alert("Invalid credentials, Please try again.");
                }
            })
            .catch(() => {
                hideSpinner();
                alert("An error occurred. Please try again.");
            });
        });
    }

    // ------------- Forgot Password Modal Logic -------------
    // Handles showing/hiding the forgot password modal and form submission
    const forgotLink = document.getElementById('forgotLink');
    const forgotModal = document.getElementById('forgotModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const okBtn = document.getElementById('okBtn');
    const recoveryForm = document.getElementById('recoveryForm');
    const successMsg = document.getElementById('successMsg');

    if (forgotLink && forgotModal) {
        forgotLink.addEventListener('click', (e) => {
            e.preventDefault();
            forgotModal.classList.remove('hidden');
        });
    }
    if (cancelBtn && forgotModal) {
        cancelBtn.addEventListener('click', () => {
            forgotModal.classList.add('hidden');
        });
    }
    if (okBtn && successMsg) {
        okBtn.addEventListener('click', () => {
            successMsg.classList.add('hidden');
        });
    }
    if (recoveryForm && forgotModal && successMsg) {
        recoveryForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('recoveryEmail').value;
            const userId = document.getElementById('userId').value;
            if (!email || !userId) {
                alert('Please fill all required fields');
                return;
            }
            forgotModal.classList.add('hidden');
            successMsg.classList.remove('hidden');
            // For demo only - replace with actual API call
            console.log('Recovery request:', { email, userId });
        });
    }

    // ------------- Registration Modal Logic -------------
    // Handles opening/closing the registration modal
    function openRegisterModal() {
        document.getElementById('registerModal').classList.remove('hidden');
        generateCaptcha();
    }
    function closeRegisterModal() {
        document.getElementById('registerModal').classList.add('hidden');
    }
    
    const registerLink = document.getElementById('registerLink');
    if (registerLink) {
        registerLink.addEventListener('click', function(e) {
            e.preventDefault();
            openRegisterModal();
        });
    }

    // ------------- CAPTCHA Logic -------------
    // Generates and validates simple math CAPTCHA for registration
    let captchaNum1 = 0, captchaNum2 = 0;
    function generateCaptcha() {
        captchaNum1 = Math.floor(Math.random() * 10) + 1;
        captchaNum2 = Math.floor(Math.random() * 10) + 1;
        document.getElementById('captchaNum1').textContent = captchaNum1;
        document.getElementById('captchaNum2').textContent = captchaNum2;
        document.getElementById('captchaAnswer').value = '';
        document.getElementById('captchaError').classList.add('hidden');
    }
    const refreshCaptchaBtn = document.getElementById('refreshCaptcha');
    if (refreshCaptchaBtn) {
        refreshCaptchaBtn.addEventListener('click', generateCaptcha);
    }

    // ------------- Register Form Logic -------------
    // Handles registration form submission and validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            // --- CAPTCHA VALIDATION ---
            const userSum = parseInt(document.getElementById('captchaAnswer').value, 10);
            if (userSum !== captchaNum1 + captchaNum2) {
                e.preventDefault();
                document.getElementById('captchaError').classList.remove('hidden');
                generateCaptcha();
                return false;
            }
            document.getElementById('captchaError').classList.add('hidden');

            e.preventDefault();
            const name = document.getElementById('fullName').value;
            const applicantId = document.getElementById('applicantId').value;
            const email = document.getElementById('regEmail').value;
            const password = document.getElementById('regPassword').value;
            if (!name || !applicantId || !email || !password) {
                alert('Please fill all fields');
                return;
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address');
                return;
            }

            // Prepare data for PHP
            const data = new URLSearchParams();
            data.append('fullName', name);
            data.append('applicantId', applicantId);
            data.append('regEmail', email);
            data.append('regPassword', password);

            // Optional: show spinner
            showSpinner();

            fetch('applicant.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: data
            })
            .then(res => res.text())
            .then(data => {
                hideSpinner();
                if (data.trim() === "success") {
                    closeRegisterModal();
                    const successMsg = document.getElementById('regSuccessMsg');
                    successMsg.classList.remove('hidden');
                    setTimeout(() => {
                        successMsg.classList.add('hidden');
                        registerForm.reset();
                    }, 4000);
                } else if (data.trim() === "duplicate") {
                    alert("Registration failed. Please try again.");
                } else {
                    alert("This account already exists. Try to login.");
                }
            })
            .catch(() => {
                hideSpinner();
                alert("An error occurred. Please try again.");
            });
        });
    }

    // ------------- Help Modal Logic -------------
    // Handles opening/closing the help modal and shake effect
    const helpBtn = document.getElementById('helpBtn');
    const helpModal = document.getElementById('helpModal');
    const closeHelpModalBtn = document.getElementById('closeHelpModal');
    if (helpBtn && helpModal) {
        helpBtn.addEventListener('click', function() {
            helpModal.classList.remove('hidden');
        });
    }
    if (closeHelpModalBtn && helpModal) {
        closeHelpModalBtn.addEventListener('click', function() {
            helpModal.classList.add('hidden');
        });
        helpModal.addEventListener('click', function(e) {
            if (e.target === helpModal) {
                closeHelpModalBtn.classList.add('shake');
                setTimeout(() => {
                    closeHelpModalBtn.classList.remove('shake');
                }, 500);
            }
        });
    }

    // ------------- Admin Login Switch Logic -------------
    // Switch to admin login mode
    let isAdminMode = false;
    const adminBtn = document.getElementById('adminLoginBtn');
    const adminBtnIcon = document.getElementById('adminBtnIcon');

    adminBtn.addEventListener('click', function() {
      isAdminMode = !isAdminMode;
      if (isAdminMode) {
        // Switch to admin login
        adminBtnIcon.classList.remove('fa-user-shield');
        adminBtnIcon.classList.add('fa-user');
        document.getElementById('userEmailField').classList.add('hidden');
        document.getElementById('adminEmailField').classList.remove('hidden');
        document.getElementById('userOptions').classList.add('hidden');
        document.getElementById('registerSection').classList.add('hidden');
        document.getElementById('adminRememberMe').classList.remove('hidden');
        document.getElementById('loginTitle').textContent = 'Admin Login';
        document.getElementById('loginBtn').innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i> Admin Sign In';
        document.getElementById('adminNotice').classList.remove('hidden');
        document.querySelector('.w-full.max-w-[27rem]').scrollIntoView({behavior: "smooth"});
      } else {
        // Switch back to user login
        adminBtnIcon.classList.remove('fa-user');
        adminBtnIcon.classList.add('fa-user-shield');
        document.getElementById('userEmailField').classList.remove('hidden');
        document.getElementById('adminEmailField').classList.add('hidden');
        document.getElementById('userOptions').classList.remove('hidden');
        document.getElementById('registerSection').classList.remove('hidden');
        document.getElementById('adminRememberMe').classList.add('hidden');
        document.getElementById('loginTitle').textContent = 'Binz Scholarship Portal';
        document.getElementById('loginBtn').innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i> Sign In';
        document.getElementById('adminNotice').classList.add('hidden');
        document.querySelector('.w-full.max-w-[27rem]').scrollIntoView({behavior: "smooth"});
      }
    });
});

// ------------- Password Strength Indicator -------------
// Shows password strength as user types in registration form
document.getElementById('regPassword')?.addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');
    if (!password) {
        strengthBar.style.width = '0%';
        strengthText.textContent = '';
        return;
    }
    const hasLower = /[a-z]/.test(password);
    const hasUpper = /[A-Z]/.test(password);
    const hasNumber = /\d/.test(password);
    const hasSpecial = /[^a-zA-Z0-9]/.test(password);
    const isLongEnough = password.length >= 8;
    let width = 0;
    let color = '';
    let text = '';
    if (!isLongEnough) {
        width = 25;
        color = 'bg-red-500';
        text = 'Too short';
    } else if (hasLower && !hasNumber && !hasSpecial && !hasUpper) {
        width = 33;
        color = 'bg-red-500';
        text = 'Weak';
    } else if (hasLower && hasNumber && !hasSpecial && !hasUpper) {
        width = 50;
        color = 'bg-yellow-500';
        text = 'Medium';
    } else if (hasLower && hasNumber && hasSpecial && !hasUpper) {
        width = 75;
        color = 'bg-blue-500';
        text = 'Strong';
    } else if (hasLower && hasUpper && hasNumber && hasSpecial) {
        width = 100;
        color = 'bg-green-500';
        text = 'Very Strong';
    } else {
        width = 50;
        color = 'bg-yellow-500';
        text = 'Medium';
    }
    strengthBar.style.width = width + '%';
    strengthBar.className = 'h-full transition-all duration-300 ' + color;
    strengthText.textContent = text;
});

// ------------- Typing Quotes Effect -------------
// Animates motivational quotes on the homepage
document.addEventListener('DOMContentLoaded', function() {
    const quotes = [
        { text: '"Education is the passport to the future, for tomorrow belongs to those who prepare for it today."', author: 'Malcolm X', color: 'text-gray-700' },
        { text: '"Opportunities multiply as they are seized. So do not loose hope."', author: 'Sun Tzu', color: 'text-blue-700' },
        { text: '"The beautiful thing about learning is that no one can take it away from you."', author: 'B.B. King', color: 'text-purple-700' },
        { text: '"International education opens minds and builds bridges."', author: 'Mr Binz', color: 'text-orange-600' },
        { text: '"A dream you dream alone is only a dream. A dream you dream together is reality."', author: 'John Lennon', color: 'text-pink-700' }
    ];
    const quoteBox = document.getElementById('typedQuote');
    let quoteIdx = 0, charIdx = 0, typing = true;
    function typeQuote() {
        const { text, author, color } = quotes[quoteIdx];
        quoteBox.className = color;
        if (typing) {
            if (charIdx <= text.length) {
                quoteBox.textContent = text.slice(0, charIdx);
                charIdx++;
                setTimeout(typeQuote, 40);
            } else {
                quoteBox.innerHTML = text + '<br><span class="text-sm text-gray-500">— ' + author + '</span>';
                typing = false;
                setTimeout(typeQuote, 6000);
            }
        } else {
            if (charIdx > 0) {
                quoteBox.textContent = text.slice(0, charIdx - 1);
                charIdx--;
                setTimeout(typeQuote, 20);
            } else {
                typing = true;
                quoteIdx = (quoteIdx + 1) % quotes.length;
                setTimeout(typeQuote, 800);
            }
        }
    }
    if (quoteBox) typeQuote();
});

// ------------- Show/Hide Password Toggle -------------
// Allows toggling password visibility in login and registration forms
document.addEventListener('DOMContentLoaded', function() {
    function initPasswordToggle(inputId, toggleBtnId) {
        const input = document.getElementById(inputId);
        const toggleBtn = document.getElementById(toggleBtnId);
        if (input && toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                const icon = toggleBtn.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }
    }
    initPasswordToggle('password', 'togglePassword');
    const registerLink = document.getElementById('registerLink');
    if (registerLink) {
        registerLink.addEventListener('click', function() {
            setTimeout(() => {
                initPasswordToggle('regPassword', 'toggleRegPassword');
            }, 50);
        });
    }
});

// ------------- Spinner Utility Functions -------------
// Shows/hides the global loading spinner
function showSpinner() {
    document.getElementById('globalSpinner').classList.remove('hidden');
}
function hideSpinner() {
    document.getElementById('globalSpinner').classList.add('hidden');
}
//close registration modal by cancel click
function closeRegisterModal() {
    const registerModal = document.getElementById('registerModal');
    if (registerModal) {
        registerModal.classList.add('hidden');
    }
}

document.getElementById('recoveryForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('recoveryEmail').value.trim();
    const applicantId = document.getElementById('userId').value.trim();
    const spinner = document.getElementById('globalSpinner');
    if (spinner) spinner.classList.remove('hidden');
    fetch('reset_password.php', {
        method: 'POST',
        body: new URLSearchParams({ email, applicantId })
    })
    .then(res => res.json())
    .then(data => {
        if (spinner) spinner.classList.add('hidden');
        document.getElementById('forgotModal').classList.add('hidden');
        document.getElementById('successMsg').classList.remove('hidden');
    })
    .catch(() => {
        if (spinner) spinner.classList.add('hidden');
        document.getElementById('forgotModal').classList.add('hidden');
        document.getElementById('successMsg').classList.remove('hidden');
    });
});