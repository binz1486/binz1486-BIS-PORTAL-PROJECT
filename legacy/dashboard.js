// ================== DASHBOARD.JS ===========================================
// Handles all dashboard page logic: modals, forms, profile, award letter, etc.
// ===========================================================================
// ------------- My Applications Box Logic -------------
function hideMyApplicationsBox() {
    const box = document.getElementById('myApplicationsBox');
    if (box) box.classList.add('hidden');
}
function showMyApplicationsBox() {
    const box = document.getElementById('myApplicationsBox');
    if (box) box.classList.remove('hidden');
}
// ------------- Logout Button Handler -------------
// Clears session/local storage and redirects to login
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', function() {
        sessionStorage.clear();
        localStorage.clear();
        window.location.href = 'index.html';
    });
}

// ------------- Personal Info Section Logic -------------
document.addEventListener('DOMContentLoaded', function() {

    // Handles opening, auto-filling, and submitting the personal info section
    const openPersonalInfoBtn = document.getElementById('openPersonalInfoBtn');
    const personalInfoSection = document.getElementById('personalInfoSection');
    const personalInfoForm = document.getElementById('personalInfoForm');
    const personalInfoSuccess = document.getElementById('personalInfoSuccess');

    if (openPersonalInfoBtn && personalInfoSection) {
        openPersonalInfoBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetch('get_profile_info.php')
                .then(res => res.json())
                .then(data => {
                    if (data.Name && data.Date_of_Birth && data.Gender) {
                        document.getElementById('personalInfoCompletedModal').classList.remove('hidden');
                        personalInfoSection.classList.add('hidden');
                    } else {
                        // Autofill if needed
                        fetch('get_user_info.php')
                            .then(res => res.json())
                            .then(user => {
                                if (user.success) {
                                    document.getElementById('piFullName').value = user.name;
                                    document.getElementById('piApplicantId').value = user.applicant_id;
                            }
                        });
                    personalInfoSection.classList.remove('hidden');
                    window.scrollTo({ top: personalInfoSection.offsetTop - 40, behavior: 'smooth' });
                    hideMyApplicationsBox();
                }
            });
        });
    }

    if (personalInfoForm && personalInfoSection && personalInfoSuccess) {
        personalInfoForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Collect all form data
            const formData = new FormData(personalInfoForm);

            fetch('personal_info.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    personalInfoSuccess.classList.remove('hidden');
                    Array.from(personalInfoForm.elements).forEach(el => {
                        if (el.tagName === "INPUT") el.readOnly = true;
                        if (el.tagName === "SELECT") el.disabled = true;
                    });
                    setTimeout(() => {
                        personalInfoSuccess.classList.add('hidden');
                        window.location.href = 'dashboard.php';
                    }, 4000);
                } else {
                    alert(data.message || "Failed to save personal information.");
                }
            })
            .catch(() => {
                alert("An error occurred while saving your information.");
            });
        });
    }

    // ------------- Scholarship Section Logic -------------
    // Handles eligibility check, opening, and submitting the scholarship section
    const openScholarshipBtn = document.getElementById('openScholarshipBtn');
    const scholarshipSection = document.getElementById('scholarshipSection');
    const scholarshipForm = document.getElementById('scholarshipForm');
    const scholarshipSuccess = document.getElementById('scholarshipSuccess');
    const eligibilitySection = document.getElementById('eligibilitySection');
    const eligibilityCheck = document.getElementById('eligibilityCheck');
    const admitBtn = document.getElementById('admitBtn');
    const eligibilityBackBtn = document.getElementById('eligibilityBackBtn');

    if (scholarshipForm && scholarshipSection && scholarshipSuccess) {
        scholarshipForm.addEventListener('submit', function(e) {
            // Validate choices
            if (!validateChoices()) {
                e.preventDefault();
                choice2.focus();
                return false;
            }
            // Validate profile pic size (<= 1MB, only jpg/png/jpeg)
            const profilePic = scholarshipForm.querySelector('input[name="profilePic"]');
            if (profilePic && profilePic.files.length > 0) {
                const file = profilePic.files[0];
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Profile picture must be JPG, JPEG, or PNG.');
                    profilePic.value = "";
                    return false;
                }
                if (file.size > 1 * 1024 * 1024) {
                    alert('Profile picture must be 1MB or less.');
                    profilePic.value = "";
                    return false;
                }
            }
            // Validate PDF file size (<= 5MB, only pdf)
            const pdfFile = scholarshipForm.querySelector('input[name="doxfile"]');
            if (pdfFile && pdfFile.files.length > 0) {
                const file = pdfFile.files[0];
                if (file.type !== 'application/pdf') {
                    alert('Only PDF files are allowed.');
                    pdfFile.value = "";
                    return false;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('PDF file must be 5MB or less.');
                    pdfFile.value = "";
                    return false;
                }
            }
            // If all validations pass, show success and redirect
            e.preventDefault();
            scholarshipSuccess.classList.remove('hidden');
            setTimeout(() => {
                scholarshipSuccess.classList.add('hidden');
                window.location.href = 'dashboard.php';
            }, 4000);
        });
    }
    // Scholarship Card Open
    if (openScholarshipBtn && scholarshipSection) {
        openScholarshipBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Generate 3 random uppercase letters
            const letters = Array.from({length: 3}, () =>
                String.fromCharCode(65 + Math.floor(Math.random() * 26))
            ).join('');
            // Generate 3 random digits (between 101 and 999)
            const digits = Math.floor(Math.random() * (999 - 101 + 1)) + 101;
            const randomId = `${letters}-${digits}`;
            const applicationIdInput = document.getElementById('applicationId');
            if (applicationIdInput) {
                applicationIdInput.value = randomId;
                applicationIdInput.readOnly = true;
            }
            scholarshipSection.classList.remove('hidden');
            window.scrollTo({ top: scholarshipSection.offsetTop - 40, behavior: 'smooth' });
            hideMyApplicationsBox();
        });
    }
    if (eligibilitySection) eligibilitySection.classList.add('hidden');
    if (scholarshipSection) scholarshipSection.classList.add('hidden');
    if (openScholarshipBtn && eligibilitySection && scholarshipSection) {
        openScholarshipBtn.addEventListener('click', function() {
            eligibilitySection.classList.remove('hidden');
            scholarshipSection.classList.add('hidden');
        });
    }
    if (eligibilityBackBtn && eligibilitySection) {
        eligibilityBackBtn.addEventListener('click', function() {
            eligibilitySection.classList.add('hidden');
            showMyApplicationsBox(); 
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    if (eligibilityCheck && admitBtn) {
        eligibilityCheck.addEventListener('change', function() {
            admitBtn.disabled = !eligibilityCheck.checked;
        });
        admitBtn.addEventListener('click', function() {
            if (eligibilityCheck.checked) {
                // Check if already applied by sending a POST to scholarship.php with a dummy field
                const formData = new FormData();
                formData.append('checkOnly', '1');
                fetch('scholarship.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    const errorMsg = document.getElementById('scholarshipErrorMsg');
                    if (data.trim() === "You have already applied for a scholarship.") {
                        // Hide scholarship form if visible
                        if (scholarshipSection) scholarshipSection.classList.add('hidden');
                        // Show red-bordered message
                        if (errorMsg) {
                            errorMsg.textContent = "You have already applied for a scholarship. You cannot submit another application.";
                            errorMsg.classList.remove('hidden');
                        }
                        // Redirect after 2 seconds
                        setTimeout(() => {
                            window.location.href = 'dashboard.php';
                        }, 2000);
                    } else {
                        // Hide error, show scholarship form
                        if (errorMsg) errorMsg.classList.add('hidden');
                        fetch('get_profile_info.php')
                            .then(res => res.json())
                            .then(data => {
                                if (data.Name && data.Date_of_Birth && data.Gender) {
                                    eligibilitySection.classList.add('hidden');
                                    scholarshipSection.classList.remove('hidden');
                                    window.scrollTo({ top: scholarshipSection.offsetTop - 40, behavior: 'smooth' });
                                } else {
                                    const personalInfoRequiredModal = document.getElementById('personalInfoRequiredModal');
                                    if (personalInfoRequiredModal) {
                                        personalInfoRequiredModal.classList.remove('hidden');
                                    } else {
                                        alert("Please fill your personal information before applying for a scholarship.");
                                    }
                                }
                            })
                            .catch(() => {
                                alert("Could not verify personal information. Please try again.");
                            });
                    }
                })
                .catch(() => {
                    alert("Could not check scholarship application status. Please try again.");
                });
            }
        });
    }

    // ------------- Payment Section Logic -------------
    // Handles opening, submitting, and generating the payment challan
    const openPaymentBtn = document.getElementById('openPaymentBtn');
    const paymentSection = document.getElementById('paymentSection');
    const paymentForm = document.getElementById('paymentForm');
    const challanModal = document.getElementById('challanModal');
    const challanDetails = document.getElementById('challanDetails');
    const saveChallanBtn = document.getElementById('saveChallanBtn');
    const cancelChallanBtn = document.getElementById('cancelChallanBtn');

    if (openPaymentBtn && paymentSection) {
        openPaymentBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('payChallanDate').value = today;
            const psid = '25' + Math.floor(Math.random() * (99999999 - 10000000 + 1) + 10000000);
            document.getElementById('payPSID').value = psid;
            paymentSection.classList.remove('hidden');
            window.scrollTo({ top: paymentSection.offsetTop - 40, behavior: 'smooth' });
            hideMyApplicationsBox();
        });
    }
    if (paymentForm && challanModal && challanDetails) {
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const sId = document.getElementById('payapplicationId').value;
            const aId = document.getElementById('payApplicantId').value;
            const date = document.getElementById('payChallanDate').value;
            const contact = document.getElementById('payContact').value;
            const psid = document.getElementById('payPSID').value;
            const fee = document.getElementById('payFee').value;
            challanDetails.innerHTML = `
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;"><strong>Application ID:</strong><span>${sId}</span></div>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;"><strong>Applicant ID:</strong><span>${aId}</span></div>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;"><strong>Challan Date:</strong><span>${date}</span></div>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;"><strong>Contact Number:</strong><span>${contact}</span></div>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;"><strong>PSID:</strong><span>${psid}</span></div>
            `;
            const challanFee = document.getElementById('challanFee');
            if (challanFee) challanFee.textContent = fee;
            challanModal.classList.remove('hidden');
        });
    }
    if (cancelChallanBtn && challanModal) {
        cancelChallanBtn.addEventListener('click', function() {
            challanModal.classList.add('hidden');
        });
    }
    if (saveChallanBtn && challanModal) {
        saveChallanBtn.addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const sId = document.getElementById('payapplicationId').value;
            const aId = document.getElementById('payApplicantId').value;
            const date = document.getElementById('payChallanDate').value;
            const contact = document.getElementById('payContact').value;
            const psid = document.getElementById('payPSID').value;
            const fee = document.getElementById('payFee').value;
            doc.setFontSize(18);
            doc.setTextColor(200, 0, 0);
            doc.text('Binz International Scholarship (BIS)', 105, 20, { align: 'center' });
            doc.setFontSize(14);
            doc.setTextColor(0, 0, 0);
            doc.text('Official Scholarship Challan', 105, 30, { align: 'center' });
            doc.setDrawColor(200, 0, 0);
            doc.rect(10, 10, 190, 120);
            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);
            let y = 45;
            doc.text(`Application ID:`, 20, y); doc.text(`${sId}`, 70, y); y += 10;
            doc.text(`Applicant ID:`, 20, y); doc.text(`${aId}`, 70, y); y += 10;
            doc.text(`Challan Date:`, 20, y); doc.text(`${date}`, 70, y); y += 10;
            doc.text(`Contact Number:`, 20, y); doc.text(`${contact}`, 70, y); y += 10;
            doc.text(`PSID:`, 20, y); doc.text(`${psid}`, 70, y); y += 10;
            doc.text(`Due Date:`, 20, y); doc.text(`20/06/2025 (Mon)`, 70, y);
            y += 15;
            doc.setFontSize(14); doc.setTextColor(0, 128, 0);
            doc.text(`Application Fee:`, 20, y); doc.text(`${fee}`, 70, y);
            doc.setDrawColor(0, 0, 0);
            doc.line(120, y + 10, 190, y + 10);
            doc.setFontSize(10); doc.setTextColor(0, 0, 0);
            doc.text('Authorized Stamp & Signature', 125, y + 15);
            doc.setFontSize(10); doc.setTextColor(150, 150, 150);
            doc.text('This is a system-generated challan. Please pay at any designated bank branch.', 105, 135, { align: 'center' });
            doc.save('challan.pdf');
            challanModal.classList.add('hidden');
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1000);
        });
    }

    // ------------- Interviewer Modal Logic -------------
    // Handles opening the interviewer modal and displaying interviewer info
    const openInterviewerBtn = document.getElementById('openInterviewerBtn');
    const interviewerModal = document.getElementById('interviewerModal');
    const closeInterviewerModal = document.getElementById('closeInterviewerModal');
    const programSelect = document.getElementById('programSelect');
    const interviewerTable = document.getElementById('interviewerTable');
    const interviewerTableBody = document.getElementById('interviewerTableBody');

    if (openInterviewerBtn && interviewerModal) {
        openInterviewerBtn.addEventListener('click', function() {
            interviewerModal.classList.remove('hidden');
            interviewerTable.classList.add('hidden');
            programSelect.value = "";
            hideMyApplicationsBox();
        });
    }
    if (closeInterviewerModal && interviewerModal) {
        closeInterviewerModal.addEventListener('click', function() {
            showMyApplicationsBox();
            interviewerModal.classList.add('hidden');
        });
    }
    if (programSelect && interviewerTable && interviewerTableBody) {
        programSelect.addEventListener('change', function() {
            let data = null;
            if (this.value === "bachelor") {
                data = { name: "Ali", email: "ali@example.com", id: "SR-101", date: "20/08/2025" };
            } else if (this.value === "master") {
                data = { name: "Zubi", email: "zubi@example.com", id: "SR-180", date: "20/07/2025" };
            } else if (this.value === "phd") {
                data = { name: "Sara", email: "sara@example.com", id: "MS-156", date: "20/09/2025" };
            }
            if (data) {
                interviewerTableBody.innerHTML = `
                    <tr>
                        <td class="border px-4 py-2">${data.name}</td>
                        <td class="border px-4 py-2">${data.email}</td>
                        <td class="border px-4 py-2">${data.id}</td>
                        <td class="border px-4 py-2">${data.date}</td>
                    </tr>
                `;
                interviewerTable.classList.remove('hidden');
            } else {
                interviewerTable.classList.add('hidden');
                interviewerTableBody.innerHTML = "";
            }
        });
    }

   
    // ------------- Profile Box Logic -------------
    // Handles showing/hiding the profile box and loading profile data
    const myProfileBox = document.getElementById('myProfileBox');
    const myProfileBtn = document.getElementById('myProfileBtn');
    const dashboardMain = document.querySelector('main');
    const closeProfileBoxBtn = document.getElementById('closeProfileBoxBtn');

    if (myProfileBtn && myProfileBox && dashboardMain) {
        myProfileBtn.addEventListener('click', function() {
            dashboardMain.classList.add('hidden');
            myProfileBox.classList.remove('hidden');
            myProfileBox.scrollIntoView({ behavior: 'smooth' });
            loadProfileData();
        });
    }
    if (closeProfileBoxBtn && myProfileBox && dashboardMain) {
        closeProfileBoxBtn.addEventListener('click', function() {
            myProfileBox.classList.add('hidden');
            dashboardMain.classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ------------- Profile Picture Upload -------------
    // Handles uploading and previewing the profile picture
    const uploadPicBtn = document.getElementById('uploadPicBtn');
    const profilePicInput = document.getElementById('profilePicInput');
    const profilePicPreview = document.getElementById('profilePicPreview');

    if (uploadPicBtn && profilePicInput && profilePicPreview) {
        uploadPicBtn.addEventListener('click', function() {
            profilePicInput.click();
        });
        profilePicInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePicPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ------------- Logout Button Handler -------------
    // Handles user logout from dashboard
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            sessionStorage.clear();
            localStorage.clear();
            window.location.href = 'index.html';
        });
    }

    // Check if personal info is already filled (when user clicking again on the button)
    fetch('get_profile_info.php')
        .then(res => res.json())
        .then(data => {
            const btn = document.getElementById('openPersonalInfoBtn');
            if (btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (data.Name && data.Date_of_Birth && data.Gender) {
                        // Profile complete: show modal, hide form
                        document.getElementById('personalInfoCompletedModal').classList.remove('hidden');
                        document.getElementById('personalInfoSection').classList.add('hidden');
                    } else {
                        // Profile not complete: show form
                        document.getElementById('personalInfoSection').classList.remove('hidden');
                    }
                });
            }
        });

    // Modal close button logic
    const closeModalBtn = document.getElementById('closePersonalInfoCompletedModal');
    const completedModal = document.getElementById('personalInfoCompletedModal');
    if (closeModalBtn && completedModal) {
        closeModalBtn.addEventListener('click', function() {
            completedModal.classList.add('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    const closePersonalInfoRequiredModal = document.getElementById('closePersonalInfoRequiredModal');
    const personalInfoRequiredModal = document.getElementById('personalInfoRequiredModal');
    if (closePersonalInfoRequiredModal && personalInfoRequiredModal) {
        closePersonalInfoRequiredModal.addEventListener('click', function() {
            personalInfoRequiredModal.classList.add('hidden');
            window.location.href = 'dashboard.php'; // Redirect to dashboard
        });
    }

    const choice1 = document.getElementById('scholarshipName');
    const choice2 = document.getElementById('scholarshipName2');
    const error = document.getElementById('choiceError');

    function validateChoices() {
        if (choice1.value === choice2.value) {
            error.textContent = 'Both choices must be different.';
            error.classList.remove('hidden');
            return false;
        }
        error.classList.add('hidden');
        return true;
    }

    if (choice1) choice1.addEventListener('change', validateChoices);
    if (choice2) choice2.addEventListener('change', validateChoices);
});

// ------------- Load Profile Data Function -------------
// Fetches and displays user profile data in the profile box
function loadProfileData() {
    console.log("LoadProfileData called");
    // Fetch profile data from the server
    fetch('get_profile_info.php')
        .then(res => res.json())
        .then(data => {
            document.getElementById('profileName').textContent = data.Name || '';
            document.getElementById('profileApplicantId').textContent = data.Applicant_Id || '';
            document.getElementById('profileEmail').textContent = data.email || '';
            document.getElementById('profileReligion').textContent = data.Religion || '';
            document.getElementById('profileDob').textContent = data.Date_of_Birth || '';
            document.getElementById('profileGender').textContent = data.Gender || '';
            document.getElementById('profileAddress').textContent = data.Address || '';
            document.getElementById('profileNationality').textContent = data.Nationality || '';
            document.getElementById('profileEduLevel').textContent = data.Qualification || '';
            document.getElementById('profileEduGrade').textContent = data.Grade_Percent || '';
            document.getElementById('profileContact').textContent = data.Contact_Number || '';
        });
}

// ------------- Section Close Functions -------------
// Functions to close personal info, scholarship, payment, and award letter sections
//personalInfoModal-dashboard.php
function closePersonalInfoSection() {
    const section = document.getElementById('personalInfoSection');
    if (section) section.classList.add('hidden');
    showMyApplicationsBox();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
//scholarshipSection-dashboard.php
function closeScholarshipSection() {
    const section = document.getElementById('scholarshipSection');
    if (section) section.classList.add('hidden');
    showMyApplicationsBox();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
//paymentSection-dashboard.php
function closePaymentSection() {
    const section = document.getElementById('paymentSection');
    if (section) section.classList.add('hidden');
    showMyApplicationsBox();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
// Award Letter Section Logic
const openAwardLetterBtn = document.getElementById('openAwardLetterBtn');
const awardLetterSection = document.getElementById('awardLetterSection');
const downloadAwardLetterBtn = document.getElementById('downloadAwardLetterBtn');

if (openAwardLetterBtn && awardLetterSection) {
    openAwardLetterBtn.addEventListener('click', function() {
        awardLetterSection.classList.remove('hidden');
        window.scrollTo({ top: awardLetterSection.offsetTop - 40, behavior: 'smooth' });
    });
}

function closeAwardLetterSection() {
    if (awardLetterSection) awardLetterSection.classList.add('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Download Award Letter as PDF
if (downloadAwardLetterBtn) {
    downloadAwardLetterBtn.addEventListener('click', () => {
        showSpinner();
        setTimeout(() => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text("Award Letter Details", 14, 16);
        doc.autoTable({ html: '#awardLetterTable', startY: 22 });
        doc.save('award_letter.pdf');
        hideSpinner();
        }, 500);
    });
}

// ------------- Spinner Utility Functions -------------
// Shows/hides the global loading spinner
function showSpinner() {
    document.getElementById('globalSpinner').classList.remove('hidden');
}
function hideSpinner() {
    document.getElementById('globalSpinner').classList.add('hidden');
}
// Fetch and display email (simple version)
fetch('get_profile_info.php')
    .then(res => res.json())
    .then(data => {
        document.getElementById('userEmail').textContent = data.email || "user@example.com";
    })
    .catch(err => {
        console.error("Couldn't load email:", err);
        document.getElementById('userEmail').textContent = "user@example.com"; // Fallback
    });
// ================== IDLE TIMEOUT LOGIC =========================

// Timeout settings (in milliseconds)
const IDLE_WARNING_TIME = 6 * 60 * 1000; // 6 minutes
const IDLE_LOGOUT_TIME = 8 * 60 * 1000; // 8 minutes (6 + 2)
const IDLE_WARNING_AUTOHIDE = 8000; // 8 seconds

let idleWarningTimer, idleLogoutTimer, idleWarningAutoHideTimer;
let isWarningShowing = false;

// Show warning modal at top middle
function showIdleWarning() {
    isWarningShowing = true;
    let modal = document.getElementById('idleWarningModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'idleWarningModal';
        modal.style.position = 'fixed';
        modal.style.top = '5px';
        modal.style.left = '50%';
        modal.style.transform = 'translateX(-50%)';
        modal.style.width = 'auto';
        modal.style.maxWidth = '90vw';
        modal.style.background = '#fff';
        modal.style.padding = '16px 24px';
        modal.style.borderRadius = '8px';
        modal.style.boxShadow = '0 2px 16px #0002';
        modal.style.textAlign = 'center';
        modal.style.zIndex = '9999';
        modal.style.border = '2px solid #e3342f';
        modal.innerHTML = `
            <div style="margin-right:16px;font-size:32px;color:#e3342f;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h3 style="margin:0 0 8px 0;color:#c00;font-size:18px;">Session Timeout Warning</h3>
                <p style="margin:0 0 16px 0;font-size:14px;line-height:1.4;">
                    You've been idle for 8 minutes. Your session will expire in 2 minutes.
                </p>
                <button id="idleContinueBtn" style="padding:6px 16px;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:14px;">
                    Continue Session
                </button>
                <style>
            #idleContinueBtn:hover {
                background: #0069d9;
                transform: translateY(-1.5px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
        </style>
            </div>
             `;
        document.body.appendChild(modal);
        document.getElementById('idleContinueBtn').onclick = function() {
            hideIdleWarning();
            resetIdleTimers();
        };
    }
    modal.classList.remove('hidden');

    // Auto-hide after 8 seconds if not clicked
    if (idleWarningAutoHideTimer) clearTimeout(idleWarningAutoHideTimer);
    idleWarningAutoHideTimer = setTimeout(() => {
        hideIdleWarning();
    }, IDLE_WARNING_AUTOHIDE);
}

// Hide warning modal
function hideIdleWarning() {
    isWarningShowing = false;
    const modal = document.getElementById('idleWarningModal');
    if (modal) modal.classList.add('hidden');
    if (idleWarningAutoHideTimer) clearTimeout(idleWarningAutoHideTimer);
}

// Logout function
function idleLogout() {
    window.location.href = 'index.html'; 
}

// Reset timers on user activity
function resetIdleTimers() {
    // Don't reset if warning is showing (except for continue button click which is handled separately)
    if (isWarningShowing) return;
    
    clearTimeout(idleWarningTimer);
    clearTimeout(idleLogoutTimer);
    idleWarningTimer = setTimeout(showIdleWarning, IDLE_WARNING_TIME);
    idleLogoutTimer = setTimeout(idleLogout, IDLE_LOGOUT_TIME);
}

// Listen for user activity
['mousemove', 'keydown', 'mousedown', 'touchstart'].forEach(evt => {
    document.addEventListener(evt, resetIdleTimers, { passive: true });
});

// Start timers when page loads
resetIdleTimers();

document.addEventListener('DOMContentLoaded', function() {
  const scholarshipForm = document.getElementById('scholarshipForm');
  const choice1 = document.getElementById('scholarshipName');
  const choice2 = document.getElementById('scholarshipName2');
  const error = document.getElementById('choiceError');
  const scholarshipSuccess = document.getElementById('scholarshipSuccess');

  function validateChoices() {
      if (choice1.value === choice2.value) {
          error.textContent = 'Both choices must be different.';
          error.classList.remove('hidden');
          return false;
      }
      error.classList.add('hidden');
      return true;
  }

  if (scholarshipForm) {
    scholarshipForm.addEventListener('submit', function(e) {
      e.preventDefault();
      // Validate choices before AJAX submit
      if (!validateChoices()) {
        choice2.focus();
        return false;
      }

      const formData = new FormData(scholarshipForm);

      fetch('scholarship.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.text())
      .then(data => {
        const errorMsg = document.getElementById('scholarshipErrorMsg');
        if (data.trim() === "success") {
          if (errorMsg) errorMsg.classList.add('hidden');
          scholarshipSuccess.classList.remove('hidden');
          scholarshipForm.reset();
          setTimeout(() => {
            scholarshipSuccess.classList.add('hidden');
            window.location.href = 'dashboard.php';
          }, 4000);
        } else if (data.trim() === "You have already applied for a scholarship.") {
          if (errorMsg) {
            errorMsg.textContent = data.trim();
            errorMsg.classList.remove('hidden');
          }
          setTimeout(() => {
            window.location.href = 'dashboard.php';
          }, 2000);
        } else {
          if (errorMsg) {
            errorMsg.textContent = data.trim();
            errorMsg.classList.remove('hidden');
          } else {
            alert(data);
          }
        }
      })
      .catch(() => {
        alert("An error occurred while submitting your application.");
      });
    });
  }

  if (choice1) choice1.addEventListener('change', validateChoices);
  if (choice2) choice2.addEventListener('change', validateChoices);
});

// Example for scholarship form
const profilePicInput = document.getElementById('scholarshipProfilePic');
if (profilePicInput) {
  profilePicInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file && !['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
      alert('Only JPG, JPEG, or PNG images are allowed.');
      this.value = '';
    }
  });
}

const doxfileInput = document.querySelector('input[name="doxfile"]');
if (doxfileInput) {
  doxfileInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file && file.type !== "application/pdf") {
      alert('Only PDF files are allowed.');
      this.value = '';
    }
  });
}


document.addEventListener('DOMContentLoaded', function() {
  // Expand/collapse application rows
  document.querySelectorAll('.expand-row-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const tr = btn.closest('tr');
      const next = tr.nextElementSibling;
      if (next && next.classList.contains('expandable-row')) {
        next.classList.toggle('hidden');
        // Optionally scroll into view
        if (!next.classList.contains('hidden')) {
          next.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }
    });
  });
  document.querySelectorAll('.collapse-row-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const tr = btn.closest('.expandable-row');
      if (tr) tr.classList.add('hidden');
    });
  });
});
// ------------- My Profile Button Logic -------------
document.addEventListener('DOMContentLoaded', function() {
    const myProfileBtn = document.getElementById('myProfileBtn');
    const myProfileBox = document.getElementById('myProfileBox');
    const myApplicationsBox = document.getElementById('myApplicationsBox');
    const dashboardMain = document.querySelector('main');
    const closeProfileBoxBtn = document.getElementById('closeProfileBoxBtn');

    if (myProfileBtn && myProfileBox && myApplicationsBox && dashboardMain) {
        myProfileBtn.addEventListener('click', function() {
            // Hide dashboard and applications, show profile
            dashboardMain.classList.add('hidden');
            myApplicationsBox.classList.add('hidden');
            myProfileBox.classList.remove('hidden');
            myProfileBox.scrollIntoView({ behavior: 'smooth' });
        });
    }
    if (closeProfileBoxBtn && myProfileBox && myApplicationsBox && dashboardMain) {
        closeProfileBoxBtn.addEventListener('click', function() {
            // Show dashboard and applications, hide profile
            myProfileBox.classList.add('hidden');
            dashboardMain.classList.remove('hidden');
            myApplicationsBox.classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});