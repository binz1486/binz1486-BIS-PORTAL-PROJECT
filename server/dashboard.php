<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$timeout_duration = 8 * 60; // 8 minutes
//session timeout logic 
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: index.html?timeout=1');
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['applicant_id']) || $_SESSION['applicant_id'] === 'admin') {
    header('Location: index.html');
    exit();}

    //application status card logic
include 'db_connect.php';
$applicantId = $_SESSION['applicant_id'];

// Fetch user name from applicant table
$stmtUser = $conn->prepare("SELECT Name FROM applicant WHERE Applicant_Id = ?");
$stmtUser->execute([$applicantId]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);
$name = $user['Name'] ?? 'User';
// Fetch all applications by user
$stmtApps = $conn->prepare("SELECT Application_Id, Degree_Level, Status, Submitted_At FROM scholarship_applications WHERE Applicant_Id = ? ORDER BY Submitted_At DESC");
$stmtApps->execute([$applicantId]);
$userApplications = $stmtApps->fetchAll(PDO::FETCH_ASSOC);

// Check if personal info is filled
$stmtProfile = $conn->prepare("SELECT 1 FROM personal_info WHERE Applicant_Id = ?");
$stmtProfile->execute([$applicantId]);
$profileCompleted = $stmtProfile->fetch() ? true : false;
$profilePercent = $profileCompleted ? 100 : 20;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Binz Scholarship</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
<style>@keyframes scrollToAfterLabel {
  0%   { transform: translateX(-100%); }
  30%  { transform: translateX(0); }
  70%  { transform: translateX(0); }
  100% { transform: translateX(100%); }
}
#scrollingBar {
  margin-left: 11rem; 
  animation: scrollToAfterLabel 12s linear infinite;
}

</style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Top Navigation Bar -->
<header class="multi-green-gradient text-white p-4 md:p-10 shadow-md">
  <div class="mx-auto max-w-6xl flex justify-between items-center gap-x-8">
    <div class="flex items-center">
      <img src="logo.png" alt="Binz Logo" class="h-12 w-12 mr-4 rounded-full shadow filter brightness-0 invert" />
      <h1 class="text-2xl font-bold">
        <span style="font-family: 'Cinzel Decorative', cursive; letter-spacing:2px;">Welcome!</span>
        to Binz International Scholarship Portal
      </h1>
    </div>
    <div class="relative group" id="profileContainer">
      <div class="flex items-center space-x-2 cursor-pointer" id="profileBtn">
       <span id="userEmail" class="text-white font-semibold mr-1"></span>
        <div class="w-12 h-12 rounded-full bg-gray-300 overflow-hidden border-2 border-white hover:scale-110">
          <img id="navbarProfilePic" src="binz-pic.jpg" alt="Profile" class="w-full h-full object-cover">
        </div>
      </div>
      <!-- Dropdown menu -->
        <div id="profileDropdown"
              class="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg py-2 z-50 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition border border-blue-300">
             <button id="myProfileBtn" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-200 hover:text-gray-800"><i class="fas fa-user-circle text-gray-600"></i>
          My Profile</button>
          
              <button id="changePassBtn" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-200 hover:text-blue-700"><i class="fas fa-key text-blue-600"></i>
        Change Password</button>
            <a href="logout.php" id="logoutBtn" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-200 hover:text-red-700"><i class="fas fa-sign-out-alt text-red-600"></i>
        Logout</a>
       </div>
                <!-- Change Password Modal -->
<div id="changePassModal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-lg text-center">
    <h2 class="text-xl font-bold mb-4 text-gray-800"><i class="fas fa-key text-blue-600"></i> Change Password</h2>
    <form id="changePassForm" class="space-y-4">
      <div class="relative">
        <input type="password" id="oldPassword" name="oldPassword" class="w-full p-2 border rounded pr-10 text-black" placeholder="Old Password" required minlength="5">
        <button type="button" class="absolute right-2 top-2 text-gray-500" onclick="togglePassword('oldPassword', this)" tabindex="-1">
          <i class="fa fa-eye"></i>
        </button>
      </div>
      <div class="relative">
        <input type="password" id="newPassword" name="newPassword" class="w-full p-2 border rounded pr-10 text-black" placeholder="New Password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$" required minlength="8">
        <button type="button" class="absolute right-2 top-2 text-gray-500" onclick="togglePassword('newPassword', this)" tabindex="-1">
          <i class="fa fa-eye"></i>
        </button>
        <!-- Password strength indicator -->
        <div class="mt-1 flex items-center gap-2">
          <div class="flex-1 h-1 bg-gray-200 rounded-full overflow-hidden">
            <div id="changePasswordStrengthBar" class="h-full transition-all duration-300 w-0"></div>
          </div>
          <span id="changePasswordStrengthText" class="text-xs text-gray-500"></span>
        </div>
      </div>
      <div class="relative">
        <input type="password" id="confirmPassword" name="confirmPassword" class="w-full p-2 border rounded pr-10 text-black" placeholder="Confirm New Password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$" required minlength="8">
        <button type="button" class="absolute right-2 top-2 text-gray-500" onclick="togglePassword('confirmPassword', this)" tabindex="-1">
          <i class="fa fa-eye"></i>
        </button>
      </div>
      <div id="changePassError" class="text-red-600 text-sm hidden"></div>
      <div class="flex justify-center gap-4">
        <button type="button" id="changePassCancel" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Change</button>
      </div>
    </form>
  </div>
</div>

<!-- Success Modal -->
<div id="changePassSuccess" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-sm text-center">
    <div class="text-green-500 text-4xl mb-2">✓</div>
    <h2 class="text-xl font-bold mb-4 text-gray-800">Password Changed!</h2>
    <p class="mb-4 text-gray-800">Your password has been updated successfully.</p>
    </div>
  </div>
         </div>
        </div>
    </header>

    <!-- Modern Scrolling Announcement Bar -->
<div class="bg-gradient-to-r from-yellow-400 via-yellow-300 to-yellow-400 text-black py-2 overflow-hidden flex items-center relative shadow-lg border-b-2 border-yellow-500" style="height: 40px;">
  <div class="bg-yellow-700 text-white px-4 py-1 rounded-r-full font-semibold mr-4 text-lg shadow z-10 flex items-center gap-2" style="position: relative;">
    <i class="fa fa-bullhorn animate-pulse"></i>
    Announcement
  </div>
  <div id="scrollingBar" class="absolute left-0 top-0 w-full h-full flex items-center justify-start font-semibold text-yellow-900 text-base tracking-wide px-4"
       style="z-index: 0; white-space: nowrap;">
    Dear User! Apply for Binz International Scholarship till Sep 20, 2025 • Hurry up! Limited seats available 
  </div>
</div>

    <!-- Main Dashboard Sections -->
  <main class="container mx-auto p-2 md:p-6 mt-2">
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
    <!-- Left Column 1 -->
    <div class="flex flex-col gap-6">
      <!-- Personal Info Card -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition w-full min-w-0 border border-blue-300">
            <div class="bg-gradient-to-r from-blue-700 to-blue-400 p-4 text-white flex items-center">
                <i class="fas fa-user-circle text-2xl mr-3"></i>
                <h2 class="text-xl font-semibold">Personal Information</h2>
            </div>
            <div class="p-4">
                <p class="text-gray-600">View and update your personal details</p>
                <button id="openPersonalInfoBtn" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    Enter Details
                </button>
            </div>
        </div>

        <!-- Payment Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition w-full min-w-0 border border-red-300">
            <div class="bg-gradient-to-r from-red-700 to-red-400 p-4 text-white flex items-center">
                <i class="fas fa-credit-card text-2xl mr-3"></i>
                <h2 class="text-xl font-semibold">Payment</h2>
            </div>
            <div class="p-4">
                <p class="text-gray-600">Generate & pay your challan</p>
                <button id="openPaymentBtn" class="mt-4 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                    Payment Options
                </button>
            </div>
        </div>
    </div>
    <!-- Left Column 2 -->
    <div class="flex flex-col gap-6">
      <!-- Scholarship Card -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition w-full min-w-0 border border-green-300">
            <div class="bg-gradient-to-r from-emerald-700 to-emerald-400 p-4 text-white flex items-center">
                <i class="fas fa-graduation-cap text-2xl mr-3"></i>
                <h2 class="text-xl font-semibold">Scholarships</h2>
            </div>
            <div class="p-4">
                <p class="text-gray-600">Apply for scholarship programs!</p>
                <button id="openScholarshipBtn" class="mt-4 bg-emerald-700 text-white px-4 py-2 rounded hover:bg-emerald-800 transition">
                    Apply Now
                </button>
            </div>
        </div>

        <!-- Interviewer Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition w-full min-w-0 border border-yellow-300">
            <div class="bg-gradient-to-r from-yellow-700 to-yellow-400 p-4 text-white flex items-center">
                <i class="fas fa-users text-2xl mr-3"></i>
                <h2 class="text-xl font-semibold">Interviewer Details</h2>
            </div>
            <div class="p-4">
                <p class="text-gray-600">View your assigned interviewer</p>
                <button id="openInterviewerBtn" class="mt-4 bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 transition">
                    View Details
                </button>
            </div>
        </div>
    </div>
  <!-- Right Column (Summary Box) -->
<div class="flex flex-col h-full">
  <div class="bg-gradient-to-br from-emerald-50 via-white to-emerald-100 rounded-xl shadow-xl p-8 flex-1 flex flex-col justify-between min-h-[370px] border border-emerald-200">
    <div class="flex items-center mb-6">
      <div class="flex items-center justify-center w-14 h-14 rounded-full bg-emerald-600 shadow-lg mr-4">
  <i class="fas fa-chart-pie text-2xl text-white"></i>
</div>
      <div>
        <h2 class="text-xl font-extrabold text-emerald-700 tracking-tight">Dashboard Summary</h2>
        <p class="text-sm text-emerald-800">Your quick status at a glance</p>
      </div>
    </div>
    <div class="mb-6">
      <p class="mb-2 text-lg">
        <span id="greetingText"></span>
        <b id="userNameBold" class="text-emerald-700"></b><b class="text-emerald-700">!</b>
      </p>
      <p class="mb-2 flex items-center gap-2">
        <i class="fas fa-clock text-gray-500"></i>
        <b>Last Login:</b>
        <span class="text-gray-700"><?php echo isset($_SESSION['LAST_ACTIVITY']) ? date('d M Y, h:i A', $_SESSION['LAST_ACTIVITY']) : 'N/A'; ?></span>
      </p>
      <div class="mb-2">
        <div class="flex items-center gap-2 mb-1">
          <i class="fas fa-user-check <?php echo $profileCompleted ? 'text-green-600' : 'text-red-600'; ?>"></i>
          <b>Profile Completion:</b>
          <span class="<?php echo $profileCompleted ? 'text-green-600' : 'text-red-600'; ?> font-bold">
            <?php echo $profilePercent; ?>%
          </span>
        </div>
        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 rounded-full h-3">
          <div class="<?php echo $profileCompleted ? 'bg-green-500' : 'bg-red-500'; ?> h-3 rounded-full transition-all duration-700"
               style="width: <?php echo $profilePercent; ?>%"></div>
        </div>
        <?php if(!$profileCompleted): ?>
          <p class="text-xs text-red-500 mt-1 flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> Please complete your profile to access all features.</p>
        <?php else: ?>
          <p class="text-xs text-green-600 mt-1 flex items-center gap-2"><i class="fas fa-check-circle"></i> Your profile is complete!</p>
        <?php endif; ?>
      </div>
    </div>
    <div class="flex-1"></div>
    <div class="flex items-center justify-end gap-2 mt-4">
      <i class="fas fa-info-circle text-emerald-500"></i>
      <span class="text-xs text-gray-500">All your information is kept secure.</span>
    </div>
  </div>
</div>
  </div>
</main>
    <!-- My Applications Box -->
<div id="myApplicationsBox" class="md:col-span-2 col-span-1 mt-2 mx-auto w-[96vw] max-w-7xl px-2">
  <div class="bg-white rounded-lg shadow-xl bg-gradient-to-br from-indigo-100 via-white to-indigo-50 border-2 border-indigo-300 p-6">
    <div class="flex items-center gap-3 mb-4"> 
      <h2 class="text-xl font-bold text-indigo-700 tracking-tight">
        <i class="fas fa-file-alt text-xl"></i> My Applications</h2>
    </div>
    <?php if (empty($userApplications)): ?>
      <div class="text-gray-500 text-center py-8">
        <i class="fas fa-folder-open text-4xl mb-2"></i>
        <div>No applications submitted yet.</div>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto rounded-lg">
        <table class="min-w-full border text-sm bg-white rounded-lg shadow">
          <thead>
            <tr class="bg-indigo-200 text-indigo-900">
              <th class="px-4 py-2 border text-center">Application ID</th>
              <th class="px-4 py-2 border text-center">Degree Level</th>
              <th class="px-4 py-2 border text-center">Status</th>
              <th class="px-4 py-2 border text-center">Applied On</th>
              <th class="px-4 py-2 border text-center">View</th>
              <th class="px-4 py-2 border text-center">Award Letter</th>
            </tr>
          </thead>
<tbody>
<?php foreach ($userApplications as $app): 
    // Fetch personal info for this applicant (if needed)
    $stmtPersonal = $conn->prepare("SELECT * FROM personal_info WHERE Applicant_Id = ?");
    $stmtPersonal->execute([$applicantId]);
    $personal = $stmtPersonal->fetch(PDO::FETCH_ASSOC);

    // Fetch scholarship details for this application (including Program 1 and 2)
    $stmtScholar = $conn->prepare("SELECT * FROM scholarship_applications WHERE Application_Id = ?");
    $stmtScholar->execute([$app['Application_Id']]);
    $scholar = $stmtScholar->fetch(PDO::FETCH_ASSOC);
?>
   <tr class="hover:bg-indigo-50 transition group">
    <td class="px-4 py-2 border text-center font-semibold text-indigo-700"><?php echo htmlspecialchars($app['Application_Id']); ?></td>
    <td class="px-4 py-2 border text-center"><?php echo htmlspecialchars($app['Degree_Level']); ?></td>
    <td class="px-4 py-2 border text-center">
      <?php
        $status = $app['Status'] ?? 'Pending';
        $badge = 'bg-gray-200 text-gray-700';
        if ($status === 'Approved') $badge = 'bg-green-100 text-green-700 font-bold';
        elseif ($status === 'Rejected') $badge = 'bg-red-100 text-red-700 font-bold';
        elseif ($status === 'Under Review') $badge = 'bg-yellow-100 text-yellow-700 font-bold';
      ?>
      <span class="inline-block px-3 py-1 rounded-full text-xs <?php echo $badge; ?>">
        <?php echo htmlspecialchars($status); ?>
      </span>
    </td>
    <td class="px-4 py-2 border text-center"><?php echo date('d M Y', strtotime($app['Submitted_At'])); ?></td>
    <td class="px-4 py-2 border text-center">
      <button class="expand-row-btn text-indigo-600 hover:text-indigo-900 focus:outline-none" data-app-id="<?php echo htmlspecialchars($app['Application_Id']); ?>">
        <i class="fas fa-eye"></i>
      </button>
    </td>
    <td class="px-4 py-2 border text-center">NILL</td>
  </tr>
  <tr class="expandable-row hidden bg-indigo-50">
  <td colspan="6" class="p-0 border-t-0">
  <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-gradient-to-br from-indigo-50 via-white to-indigo-100 rounded-b-lg shadow-inner">
    <!-- Personal Info Card -->
    <div class="bg-white rounded-lg shadow p-5 border border-blue-200 flex flex-col gap-2">
      <div class="flex items-center gap-2 mb-2">
        <i class="fas fa-user-circle text-blue-600 text-2xl"></i>
        <h3 class="text-lg font-bold text-blue-700">Personal Information</h3>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-gray-700 text-sm">
        <div><span class="font-semibold">Name:</span> <?php echo htmlspecialchars($personal['Name'] ?? ''); ?></div>
        <div><span class="font-semibold">Applicant ID:</span> <?php echo htmlspecialchars($personal['Applicant_Id'] ?? ''); ?></div>
        <div><span class="font-semibold">Religion:</span> <?php echo htmlspecialchars($personal['Religion'] ?? ''); ?></div>
        <div><span class="font-semibold">Date of Birth:</span> <?php echo htmlspecialchars($personal['Date_of_Birth'] ?? ''); ?></div>
        <div><span class="font-semibold">Gender:</span> <?php echo htmlspecialchars($personal['Gender'] ?? ''); ?></div>
        <div><span class="font-semibold">Nationality:</span> <?php echo htmlspecialchars($personal['Nationality'] ?? ''); ?></div>
        <div><span class="font-semibold">Qualification:</span> <?php echo htmlspecialchars($personal['Qualification'] ?? ''); ?></div>
        <div><span class="font-semibold">Grade %:</span> <?php echo htmlspecialchars($personal['Grade_Percent'] ?? ''); ?></div>
        <div><span class="font-semibold">Contact:</span> <?php echo htmlspecialchars($personal['Contact_Number'] ?? ''); ?></div>
        <div class="sm:col-span-2"><span class="font-semibold">Address:</span> <?php echo htmlspecialchars($personal['Address'] ?? ''); ?></div>
      </div>
       <!-- Terms & Conditions -->
<div class="col-span-1 sm:col-span-2 mt-4">
  <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded flex items-start gap-3">
    <i class="fas fa-check-circle text-blue-600 text-xl mt-1"></i>
    <div>
      <div class="font-semibold text-blue-700 mb-1">Terms & Conditions Accepted</div>
      <div class="text-gray-700 text-sm">
        You will be ineligible if: <br>
        <ol class="list-decimal ml-6 text-gray-700">
          <li>You provide false or misleading information in your application.</li>
          <li>You are found with multiple accounts.</li>
        </ol>
        <span class="italic text-blue-800">Our policy for granting scholarship.</span>
      </div>
    </div>
  </div>
</div>
    </div>
    
        <!-- Scholarship Info -->
        <div class="bg-white rounded-lg shadow p-5 border border-emerald-200 flex flex-col gap-2">
      <div class="flex items-center gap-2 mb-2">
        <i class="fas fa-graduation-cap text-emerald-600 text-2xl"></i>
        <h3 class="text-lg font-bold text-emerald-700">Scholarship Application</h3>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-gray-700 text-sm">
        <div><span class="font-semibold">Application ID:</span> <?php echo htmlspecialchars($scholar['Application_Id'] ?? ''); ?></div>
        <div><span class="font-semibold">Degree Level:</span> <?php echo htmlspecialchars($scholar['Degree_Level'] ?? ''); ?></div>
        <div><span class="font-semibold">Program (Choice 1):</span> <?php echo htmlspecialchars($scholar['Program_1'] ?? ''); ?></div>
        <div><span class="font-semibold">Program (Choice 2):</span> <?php echo htmlspecialchars($scholar['Program_2'] ?? ''); ?></div>
        <div><span class="font-semibold">Status:</span> 
          <span class="inline-block px-2 py-1 rounded-full text-xs
            <?php
              $status = $scholar['Status'] ?? 'Pending';
              if ($status === 'Approved') echo 'bg-green-100 text-green-700 font-bold';
              elseif ($status === 'Rejected') echo 'bg-red-100 text-red-700 font-bold';
              elseif ($status === 'Under Review') echo 'bg-yellow-100 text-yellow-700 font-bold';
              else echo 'bg-gray-200 text-gray-700';
            ?>">
            <?php echo htmlspecialchars($status); ?>
          </span>
        </div>
        <div><span class="font-semibold">Applied On:</span> <?php echo date('d M Y', strtotime($scholar['Submitted_At'] ?? '')); ?></div>
      </div>
      <!-- Terms & Conditions Acknowledgement -->
<div class="col-span-1 sm:col-span-2 mt-4">
  <div class="bg-emerald-50 border-l-4 border-emerald-400 p-4 rounded flex items-start gap-3">
    <i class="fas fa-check-circle text-emerald-600 text-xl mt-1"></i>
    <div>
      <div class="font-semibold text-emerald-700 mb-1">Eligibility Terms Accepted</div>
      <div class="text-gray-700 text-sm">
        By submitting this application, you <b>admitted and agreed</b> to all eligibility criteria and terms &amp; conditions as stated in the scholarship announcement.<br>
        <span class="italic text-emerald-800">Your eligibility was confirmed at the time of application.</span>
      </div>
    </div>
  </div>
</div>
          <div class="mt-4 flex gap-2">
            <button class="collapse-row-btn px-3 py-1 text-xs rounded bg-gray-200 hover:bg-gray-300 text-gray-700" type="button">
              <i class="fas fa-chevron-up"></i> Collapse
            </button>
            <button class="px-3 py-1 text-xs rounded bg-blue-600 hover:bg-blue-700 text-white" type="button" onclick="window.print()">
              <i class="fas fa-print"></i> Print
            </button>
          </div>
        </div>
      </div>
    </td>
  </tr>
<?php endforeach; ?>
</tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- My Profile Box from dropdown (hidden by default) -->
<div id="myProfileBox" class="hidden container mx-auto my-6 p-6 bg-white rounded-lg shadow-md max-w-4xl bg-gradient-to-br from-blue-100 via-white to-blue-100">
  <h2 class="text-2xl font-bold mb-4 text-gray-700 flex items-center border-b pb-2">
    <i class="fas fa-user-circle text-2xl mr-3"></i>My Profile
  </h2>
  <div class="flex flex-col md:flex-row gap-4 md:gap-8 items-start">
    <!-- Profile Picture & Basic Info -->
    <div class="flex flex-col items-center md:w-1/3">
      <!-- Profile Picture of user upload -->
      <img id="profilePicPreview" src="binz-pic.jpg" alt="Profile Picture" class="w-28 h-28 sm:w-36 sm:h-36 rounded-full object-cover border-2 border-gray-600 mb-4 md:mb-1">
      <input type="file" id="profilePicInput" accept="image/png, image/jpg, image/jpeg" style="display: none;">
      <button type="button" id="uploadPicBtn" class="bg-gray-600 text-white px-3 py-1 rounded mb-2 hover:bg-gray-700">Upload Profile Pic</button>
    </div>
    <!-- Profile Details (Read-Only) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 md:gap-x-8 gap-y-4 md:gap-y-6 flex-1">
      <div><span class="font-semibold">Name:</span> <span id="profileName"></span></div>
      <div><span class="font-semibold">Applicant ID:</span> <span id="profileApplicantId"></span></div>
      <div><span class="font-semibold">Email:</span> <span id="profileEmail"></span></div>
      <div><span class="font-semibold">Religion:</span> <span id="profileReligion"></span></div>
      <div><span class="font-semibold">Date of Birth:</span> <span id="profileDob"></span></div>
      <div><span class="font-semibold">Gender:</span> <span id="profileGender"></span></div>
      <div><span class="font-semibold">Qualification:</span> <span id="profileEduLevel"></span></div>
      <div><span class="font-semibold">Previous Grade:</span> <span id="profileEduGrade"></span></div>
      <div><span class="font-semibold">Nationality:</span> <span id="profileNationality"></span></div>
      <div><span class="font-semibold">Contact Number:</span> <span id="profileContact"></span></div>
      <div class="md:col-span-2"><span class="font-semibold">Address:</span> <span id="profileAddress"></span></div>
    </div>
  </div>
  <div class="flex justify-between items-center mt-6">
    <span class="text-sm text-gray-500">
      <i class="fas fa-user-edit text-gray-500 mr-1"></i>
      If you want to edit your info then write message to us via <a href="#" id="openMessageBtnProfile" class="text-blue-600 hover:underline">Message</a> or email us! 
    </span>
    <button id="closeProfileBoxBtn" class="px-6 py-2 text-blue-700 border border-blue-300 rounded-lg hover:bg-red-50 hover:text-red-700 font-semibold transition">Close</button>
  </div>
</div>   

    <!-- Personal Info Form (hidden by default) -->
<div id="personalInfoSection" class="hidden container mx-auto my-6 p-6 bg-white rounded-lg shadow-md max-w-2xl">
  <h2 class="text-2xl font-bold mb-4 text-blue-700"><i class="fas fa-user-circle text-2xl mr-3"></i>Enter Personal Information</h2>
  <form id="personalInfoForm" class="space-y-4">
    <div>
      <label class="block text-gray-700 mb-1">Full Name</label>
      <input type="text" id="piFullName" name="fullName" class="w-full p-2 border rounded" placeholder="First Middle Last Name" minlength="3" pattern="^[A-Za-z\s]+$"
       title="Only letters and spaces are allowed" required>
    </div>
    <div>
        <label class="block text-gray-700 mb-2">Applicant ID</label>
        <input type="text" id="piApplicantId" name="applicantId" placeholder="Enter Passport Number" class="w-full p-2 border rounded" pattern="[A-Za-z0-9]+" 
       title="Only letters and numbers are allowed"  minlength="6" required>
      </div>
    <div>
      <label class="block text-gray-700 mb-1">Religion</label>
      <input type="text" id="piReligion" name="religion" class="w-full p-2 border rounded" placeholder="Enter your religion" pattern="^[A-Za-z\s]+$" title="Only letters and spaces are allowed" required>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Date of Birth</label>
      <input type="date" id="piDob" name="dob" class="w-full p-2 border rounded" required>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Gender</label>
      <select id="piGender" name="gender" class="w-full p-2 border rounded" required>
        <option value="">Select</option>
        <option>Male</option>
        <option>Female</option>
        <option>Other</option>
      </select>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Address</label>
      <input type="text" id="piAddress" name="address" class="w-full p-2 border rounded" placeholder="Enter your address" minlength="12" pattern="^[a-zA-Z0-9\s]+$" title="Special Characters are not allowed" required>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Nationality</label>
      <select id="piNationality" name="nationality" class="w-full p-2 border rounded" required>
        <option value="">Select</option>
        <option>Pakistan</option>
        <option>Palestine</option>
        <option>China</option>
        <option>Iran</option>
        <option>Turkey</option>
        <option>Afganistan</option>
        <option>Bangladesh</option>
        <option>Sirilanka</option>
        <option>Oman</option>
        <option>Spain</option>
        <option>Egypt</option>
        <option>Panama</option>
      </select>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Your Qualification</label>
      <select id="piEducation" name="eduLevel" class="w-full p-2 border rounded" required>
        <option value="">Select</option>
        <option >Intermediate</option>
        <option >Bachelor</option>
        <option >Master</option>
      </select>
    </div>
    <div>
    <label class="block text-gray-700 mb-1">Grade Percentage</label>
    <div class="relative">
        <input 
            type="text" name="percentage"
            id="percentageInput"
            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="70.00 - 100.00"
            inputmode="decimal"
            oninput="
                // Show/hide percentage symbol
                this.nextElementSibling.style.display = this.value ? 'block' : 'none';
                
                // Allow only numbers and single decimal point
                this.value = this.value.replace(/[^0-9.]/g, '');
                
                // Prevent multiple decimal points
                if ((this.value.match(/\./g) || []).length > 1) {
                    this.value = this.value.substring(0, this.value.lastIndexOf('.'));
                }
                
                // Limit to 6 characters (3 before and 2 after decimal)
                if (this.value.length > 6) {
                    this.value = this.value.slice(0, 6);
                }
            "
            onblur="
                if (this.value) {
                    let num = parseFloat(this.value);
                    
                    // Handle cases where user entered just '.' or incomplete number
                    if (isNaN(num)) {
                        this.value = '70.00';
                        return;
                    }
                    
                    // Enforce minimum and maximum
                    if (num < 70) this.value = '70.00';
                    if (num > 100) this.value = '100.00';
                    
                    // Format to 2 decimal places if needed
                    if (this.value.indexOf('.') === -1) {
                        this.value = this.value + '.00';
                    } else if (this.value.split('.')[1].length === 1) {
                        this.value = this.value + '0';
                    }
                }
            "
            onkeydown="
                if (event.key === 'Backspace' && this.value.length === 1) {
                    setTimeout(() => { 
                        this.nextElementSibling.style.display = this.value ? 'block' : 'none'; 
                    }, 0);
                }
            "
        >
        <span id="percentageSymbol" class="absolute right-3 top-2 text-gray-500" style="display: none">%</span>
    </div>
    <p class="text-sm text-gray-500 mt-2"><b>Note:</b> Minimum 70% is required</p>
</div>
    <div>
      <label class="block text-gray-700 mb-1">Contact Number</label>
      <input type="text" id="piContact" name="contact" class="w-full p-2 border rounded" placeholder="92-**-*******-*" minlength="10" inputmode="numeric" pattern="^\d+$" title="Only numbers are allowed" required>
    </div>
    <div class=" flex items-centered">
      <input type="checkbox" id="info-correct" name="info-correct" class="mb-4" required>
      <label for="info-correct" class="text-sm text-gray-700 ">&nbsp; I hereby declare that the information provided is correct to the best of my knowledge and I understand that I will be disqualified if any information is found to be incorrect.</label>
    </div>
    <div class="flex justify-end gap-2 mt-4">
      <button type="button" onclick="closePersonalInfoSection()" class="px-4 py-2 text-gray-600">Cancel</button>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Submit</button>
    </div>
  </form>
</div>

<!-- Success Message -->
<div id="personalInfoSuccess" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-md text-center">
    <div class="text-green-500 text-5xl mb-4">✓</div>
    <h2 class="text-xl font-bold mb-2">Personal info saved Successfully!</h2>
  </div>
</div>


<!-- apply for scholarship card -Eligibility & Aim Box (hidden by default) -->
<div id="eligibilitySection" class="hidden container mx-auto my-6 p-6 bg-white rounded-lg shadow-md max-w-2xl">
  <h2 class="text-2xl font-bold mb-4 text-emerald-800"><i class="fas fa-graduation-cap text-2xl mr-3"></i>Scholarship Aim & Eligibility</h2>
  <p class="mb-4 text-gray-700">
    <b>Aim:</b> To support talented students from selected countries in pursuing higher education internationally.
  </p>
  <div class="mb-4">
    <h3 class="text-lg font-semibold text-gray-700 mb-2">Eligible Countries:</h3>
    <ul class="list-disc list-inside text-gray-700 grid grid-cols-2 gap-x-6">
      <li>Pakistan</li>
      <li>Palestine</li>
      <li>China</li>
      <li>Iran</li>
      <li>Turkeye</li>
      <li>Afganistan</li>
      <li>Bangladesh</li>
      <li>Sirilanka</li>
      <li>Oman</li>
      <li>Spain</li>
      <li>Egypt</li>
      <li>Panama</li>
    </ul>
  </div>
  <div class="mb-4">
    <h3 class="text-lg font-semibold text-gray-700 mb-2">Program-wise Eligibility Criteria:</h3>
    <div class="mb-2">
      <span class="font-bold text-gray-600">Bachelor:</span>
      <ul class="list-disc list-inside ml-6 text-gray-700">
        <li>Must hold Intermediate or equivalent</li>
        <li>Maximum age: <b>23 years</b></li>
        <li>Minimum CGPA: <b>3.2</b></li>
      </ul>
    </div>
    <div class="mb-2">
      <span class="font-bold text-gray-600">Master:</span>
      <ul class="list-disc list-inside ml-6 text-gray-700">
        <li>Must hold Bachelor degree</li>
        <li>Maximum age: <b>26 years</b></li>
        <li>Minimum CGPA: <b>3.4</b></li>
      </ul>
    </div>
    <div>
      <span class="font-bold text-gray-600">PhD:</span>
      <ul class="list-disc list-inside ml-6 text-gray-700">
        <li>Must hold Bachelor degree</li>
        <li>Maximum age: <b>28 years</b></li>
        <li>Minimum CGPA: <b>3.5</b></li>
      </ul>
    </div>
  </div>
  <p class="mb-4 text-gray-700"><b>Note:</b> Only students from the above countries who meet the respective program criteria are eligible to apply.</p>
  <div class="flex items-center mb-4">
    <input type="checkbox" id="eligibilityCheck" class="mr-2" required>
    <label for="eligibilityCheck" class="text-sm text-gray-700">I have read and meet the eligibility criteria as mentioned above.</label>
  </div>
  <div class="flex justify-between mt-2">
      <button type="button" id="eligibilityBackBtn" class="px-4 py-2 text-gray-600 bg-gray-200 rounded hover:bg-gray-300">Back</button>
    <button id="admitBtn" class="bg-emerald-700 text-white px-4 py-2 rounded hover:bg-emerald-800 cursor-pointer" disabled>Admit & Apply</button>
  </div>
  <div id="scholarshipErrorMsg" class="hidden border border-red-600 text-red-700 bg-red-50 rounded px-3 py-2 mt-2 text-sm"></div>
</div>
<!-- apply for scholarship forum start here -->
<div id="scholarshipSection" class="hidden container mx-auto my-6 p-6 bg-white rounded-lg shadow-md max-w-2xl">
  <h2 class="text-2xl font-bold mb-4 text-emerald-800"><i class="fas fa-graduation-cap text-2xl mr-3"></i>Apply for Scholarship</h2>
  <form id="scholarshipForm" enctype="multipart/form-data" class="space-y-4">
    <div>
      <label class="block text-gray-700 mb-1">Application ID</label>
      <input type="text" id="applicationId" name="applicationId" class="w-full p-2 border rounded" required>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Select University</label>
      <select id="scholarshipUniversity" name="scholarshipUniversity" class="w-full p-2 border rounded" required>
        <option value="">Select</option>
        <option>The University of Narowal (UON), Narowal</option>
        <option>Quaid-i-Azam University (QAU), Islamabad</option>
        <option>National University of Sciences & Technology (NUST), Islamabad</option>
        <option>Govt. College University (GCU), Lahore</option>
        <option>University of Karachi (UOK), Karachi</option>
        <option>University of the Punjab (PU), Lahore</option>
        <option>University of Engineering & Technology (UET), Lahore</option>
        <option>Ghulam Ishaq Khan Institute of Engineering Sciences & Technology (GIKI), Topi</option>
      </select>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Degree Level</label>
      <select id="scholarshipEligibility" name="scholarshipEligibility" class="w-full p-2 border rounded" required>
        <option value="">Select</option>
        <option>Bachelor</option>
        <option>Master</option>
        <option>PhD</option>
      </select>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Program (Choice 1)</label>
      <select id="scholarshipName" name="scholarshipName" class="w-full p-2 border rounded" required>
        <option value="">Select</option>
        <option>Computer Science</option>
        <option>Software Engineering</option>
        <option>Data Science</option>
        <option>AI & Machine Learning</option>
        <option>Cyber Security</option>
        <option>Information Technology</option>
        <option>Robotics Science</option>
      </select>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Program (Choice 2)</label>
      <select id="scholarshipName2" name="scholarshipName2" class="w-full p-2 border rounded" required>
        <option value="">Select</option>
        <option>Computer Science</option>
        <option>Software Engineering</option>
        <option>Data Science</option>
        <option>AI & Machine Learning</option>
        <option>Cyber Security</option>
        <option>Information Technology</option>
        <option>Robotics Science</option>
      </select>
      <p id="choiceError" class="text-red-600 text-xs mt-1 hidden">Both choices must be different.</p>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Upload Profile Picture</label>
      <input type="file" id="scholarshipProfilePic" name="profilePic" accept="image/png,image/jpg,image/jpeg" class="w-full p-2 border rounded" required>
      <p class="text-xs text-gray-500 mt-1">Please upload a recent passport-size photo. Only JPG/JPEG/PNG, max 1MB.</p>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Upload File (.pdf only)</label>
      <input type="file" accept=".pdf" name="doxfile" class="w-full p-2 border rounded" required>
    </div>
      <div>
        <p class="block text-gray-700 mb-1"><i class="fa fa-exclamation-triangle"></i> Please note that:</p>
        <ul class="list-disc list-inside text-sm text-gray-700">
          <li>Merge passport, educational docs & English cert into one file</li>
          <li>only max size 5MB PDF file is allowed</li>
          <li>Docs must clear & not password-protected</li>
        </ul>
      </div>
    <div class="flex justify-end gap-2 mt-4">
      <button type="button" onclick="closeScholarshipSection()" class="px-4 py-2 text-gray-600">Cancel</button>
      <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded">Submit</button>
    </div>
  </form>
</div>

<!-- Scholarship Success Message -->
<div id="scholarshipSuccess" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-md text-center">
    <div class="text-green-500 text-5xl mb-4">✓</div>
    <h2 class="text-xl font-bold mb-2">Scholarship application submitted!</h2>
        <p>Please Check you email Regularly for updates!</p>
  </div>
</div>

<!-- Payment Form (hidden by default) -->
<div id="paymentSection" class="hidden container mx-auto my-6 p-6 bg-white rounded-lg shadow-md max-w-2xl">
  <h2 class="text-2xl font-bold mb-4 text-red-700"><i class="fas fa-credit-card text-2xl mr-3"></i>Pay Application Fee</h2>
  <form id="paymentForm" class="space-y-4">
    <div>
      <label class="block text-gray-700 mb-1">Application ID</label>
      <input type="text" id="payapplicationId" placeholder="e.g, BNZ-101" class="w-full p-2 border rounded" pattern="[A-Za-z]{3}-[0-9]{3}"
       title="Format: 3 letters followed by 3 numbers (e.g. ABC-123)"
       maxlength="7"
       oninput="
           // Convert to uppercase and remove invalid chars
           let val = this.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
           
           // Auto-insert hyphen after 3 letters
           if (val.length === 3 && !val.includes('-')) {
               val = val + '-';
           }
           
           // Handle backspace properly
           if (val.length < 3 && val.includes('-')) {
               val = val.replace('-', '');
           }
           
           // Prevent exceeding max length
           if (val.length > 7) {
               val = val.slice(0,7);
           }
           
           this.value = val;
       "
       onkeydown="
           // Allow backspace to work naturally
           if (event.key === 'Backspace') {
               let cursorPos = this.selectionStart;
               if (cursorPos === 4 && this.value.charAt(3) === '-') {
                   // If backspacing the hyphen, remove both hyphen and previous char
                   this.value = this.value.slice(0,2) + this.value.slice(4);
                   this.setSelectionRange(2,2);
                   event.preventDefault();
               }
           }
       " required>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Applicant ID</label>
      <input type="text" id="payApplicantId" placeholder="Enter Passport Number" class="w-full p-2 border rounded" pattern="[A-Za-z0-9]+" 
       title="Only letters and numbers are allowed" minlength="6" required>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Challan Date</label>
      <input type="date" id="payChallanDate" class="w-full p-2 border rounded" required readonly>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Contact Number</label>
      <input type="text" id="payContact" placeholder="92-**-*******-*" class="w-full p-2 border rounded" minlength="10" required>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">PSID (Auto Generated)</label>
      <input type="text" id="payPSID" class="w-full p-2 border rounded" required readonly>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Application Fee</label>
      <input type="text" id="payFee" class="w-full p-2 border rounded" value="$10" readonly>
    </div>
    <div> 
    <p  class="block text-gray-700 mb-1"><i class="fa fa-exclamation-triangle"></i> Please note that:</p>
    <ul class="list-disc list-inside text-sm text-gray-700">
       <li>Please double check your inforomation</li>
        <li>Make your payments duely</li>
    </ul>
  </div>
    <div class="flex justify-end gap-2 mt-4">
      <button type="button" onclick="closePaymentSection()" class="px-4 py-2 text-gray-600">Cancel</button>
      <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Generate Challan</button>
    </div>
  </form>
</div>

<!-- Challan Details Modal -->
<div id="challanModal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
  <div id="challanContent" class="bg-white rounded-lg p-8 max-w-lg w-full text-gray-800 shadow-lg border-2 border-red-600 relative">
    <div class="flex items-center justify-between mb-6">
      <img src="logo.png" alt="Binz Logo" class="h-12">
      <span class="text-2xl font-bold text-red-700"> Binz International Scholarship (BIS)</span>
    </div>
     <p class="text-xl font-bold text-red-700 text-center">Scholarship Application Fee Challan!</p>
    <hr class="mb-4 border-red-600">
    <div id="challanDetails" class="mb-6 text-base leading-relaxed"></div>
    <hr class="mb-4 border-red-600">
    <div class="flex justify-between items-center">
      <span class="font-semibold"> Due Date: 20/06/2025 (Mon)</span>
      <span class="text-xl font-bold text-green-700" id="challanFee"></span>
    </div>
    <div class="flex justify-end gap-2 mt-6">
      <button id="cancelChallanBtn" class="px-4 py-2 text-gray-600 hover:bg-gray-200">Back</button>
      <button id="saveChallanBtn" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-green-700">Save Challan</button>
    </div>
  </div>
</div>

<!-- Interviewer Details Modal -->
<div id="interviewerModal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-8 max-w-2xl w-full text-gray-800 shadow-lg border-2 border-yellow-600 relative">
    <h2 class="text-2xl font-bold mb-4 text-yellow-700"><i class="fas fa-users text-2xl mr-3"></i>Interviewer Details</h2>
    <div class="mb-4">
      <label class="block text-gray-700 mb-1">Select Program</label>
      <select id="programSelect" class="w-full p-2 border rounded">
        <option value="">Select</option>
        <option value="bachelor">Bachelor</option>
        <option value="master">Master</option>
        <option value="phd">PhD</option>
      </select>
    </div>
    <div id="interviewerTable" class="hidden mt-4">
      <table class="min-w-full border text-left text-sm">
        <thead>
          <tr class="bg-yellow-100">
            <th class="border px-4 py-2">Interviewer Name</th>
            <th class="border px-4 py-2">Email</th>
            <th class="border px-4 py-2">Interviewer ID</th>
            <th class="border px-4 py-2">Interview Date</th>
          </tr>
        </thead>
        <tbody id="interviewerTableBody"></tbody>
      </table>
    </div>
    <div class="flex justify-end gap-2 mt-6">
      <button id="closeInterviewerModal" class="px-4 py-2 text-gray-600 hover:bg-gray-200">Close</button>
    </div>
  </div>
</div>

<!-- Floating Message Icon--feedback -->
<button id="openMessageBtn" class="fixed bottom-6 right-6 z-50 bg-blue-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg hover:bg-blue-700 focus:outline-none transition-transform duration-200 hover:scale-110">
  <i class="fas fa-comment-dots text-2xl"></i>
</button>

<!-- Message Modal -->
<div id="messageModal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-8 w-full max-w-xl text-gray-800 shadow-lg relative border border-blue-600">
    <button id="closeMessageModal" class="absolute top-2 right-4 text-gray-400 hover:text-red-700 hover:font-bold text-3xl">&times;</button>
    <h2 class="text-xl font-bold mb-4 text-blue-800 flex items-center"><i class="fas fa-comment-dots mr-2"></i>Send us a Message</h2>
    <form id="messageForm" class="space-y-4">
      <div>
        <label class="block text-gray-700 mb-1">Type</label>
        <select id="messageType" name="messageType" class="w-full p-2 border rounded bg-blue-50 required" required>
          <option value="">Select</option>
          <option value="Feedback">Feedback</option>
          <option value="Request">Request</option>
          <option value="Complaint">Complaint</option>
          <option value="Suggestion">Suggestion</option>
          <option value="Other">Technical issue</option>
        </select>
      </div>
      <div>
        <label class="block text-gray-700 mb-1">Your Message</label>
        <textarea id="messageText" name="messageText" class="w-full p-2 border rounded bg-blue-50" rows="4" required placeholder="Type your message..." minlength="20" maxlength="500"></textarea>
      </div>
      <div> 
    <p class="block text-gray-700 mb-1 text-sm"><i class="far fa-smile"></i> <i>We value your message & we will respond you shortly</p></i> 
  </div>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelMessageBtn" class="px-4 py-2 text-gray-600">Cancel</button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
      </div>
    </form>
  </div>
</div>

<!-- Message Success Modal -->
<div id="messageSuccess" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-sm text-center">
    <div class="text-green-500 text-5xl mb-4">✓</div>
    <h2 class="text-xl font-bold mb-2">Your message was delivered successfully!</h2>
  </div>
</div>

    <!-- Curved background layers for dashboard section -->
<div class="pointer-events-none fixed top-0 left-0 w-full h-[800px] -z-10">
  <div class="absolute inset-0 bg-gray-100" style="clip-path: ellipse(90% 60% at 50% 40%);"></div>
  <div class="absolute inset-0 bg-gray-200" style="clip-path: ellipse(80% 40% at 50% 20%);"></div>
  <div class="absolute inset-0 bg-gray-300" style="clip-path: ellipse(70% 30% at 50% 10%);"></div>

</div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white p-4 mt-8 text-sm">
        <div class="container mx-auto text-center">
          <b><i class="fas fa-envelope text-white-600"></i> Email:</b> <a href="mailto:binzeria@gmail.com" class="hover:text-blue-600">binzeria@gmail.com</a>
          <b>| <i class="fas fa-phone text-white-600 "></i> Call:</b> <a href="tel:+923446072989"
           class="hover:text-blue-600"> +92-344-6072989</a>
          <b>| <i class="fas fa-phone-alt"></i> Landline:</b> <a href="tel:9238-476937" class="hover:text-blue-600"> 9238-476937</a>
          <b>| <i class="fas fa-question-circle"></i></b> <a href="faqs.html" class="hover:text-blue-600">FAQs</a><br><br>
            <p><b>©</b> 2023 Binz International Scholarship Portal. All rights reserved.</p>
        </div>
    </footer>
</body>
<!-- suitable messages prompted here for all cards/modules-->
<!-- Personal Info Already Completed Modal -->
<div id="personalInfoCompletedModal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-sm w-full text-center">
    <div class="text-green-600 text-4xl mb-2">✓</div>
    <h2 class="text-xl font-bold mb-2">Profile Already Completed</h2>
    <p class="mb-4">You have already filled your personal information. You cannot enter details again.</p>
    <button id="closePersonalInfoCompletedModal" class="bg-emerald-700 text-white px-4 py-2 rounded hover:bg-emerald-800">OK</button>
  </div>
</div>
<!-- Personal Info Required Modal -->
<div id="personalInfoRequiredModal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-sm w-full text-center">
    <div class="text-red-600 text-4xl mb-2"><i class="fas fa-exclamation-triangle"></i></div>
    <h2 class="text-xl font-bold mb-2">Personal Info Required</h2>
    <p class="mb-4">Please fill your personal information before applying for a scholarship.</p>
    <button id="closePersonalInfoRequiredModal" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">OK</button>
  </div>
</div>

        <!-- style css for dashboards-->
<!-- prevent user partially from inspect or suspiciousness -->
<script> 
document.addEventListener('DOMContentLoaded', function() {
    const userName = <?php echo json_encode($name); ?>;
    const greetingText = document.getElementById('greetingText');
    const userNameBold = document.getElementById('userNameBold');
    const hour = new Date().getHours();
    let greeting = "Hello";
    if (hour >= 5 && hour < 12) {
      greeting = "Good morning";
    } else if (hour >= 12 && hour < 17) {
      greeting = "Good afternoon";
    } else if (hour >= 17 && hour < 22) {
      greeting = "Good evening";
    } else {
      greeting = "Good night";
    }
if (greetingText && userNameBold) {
      greetingText.textContent = greeting;
      userNameBold.textContent = userName;
    }  });
//Disable right-click
document.addEventListener('contextmenu', function(e) {
  e.preventDefault();
});
// Disable F12, Ctrl+Shift+I, Ctrl+U
document.addEventListener('keydown', function(e) {
    // Disable Ctrl+U
  if (e.ctrlKey && e.key.toLowerCase() === 'u') {
    e.preventDefault();
    return false;
  }
  // Disable F12
  if (e.key === "F12") {
    e.preventDefault();
    return false;
  }
  // Disable Ctrl+Shift+I
  if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'i') {
    e.preventDefault();
    return false;
  }
});
</script>
<!-- link to js file -->
<script src="dashboard.js"></script>
<script src="extras.js"></script>

</html>