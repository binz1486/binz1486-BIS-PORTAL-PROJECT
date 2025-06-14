<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Enhanced security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

$timeout_duration = 6 * 60; // 6 minutes
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: index.html?timeout=1');
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Enhanced admin authentication
if (!isset($_SESSION['applicant_id'])) {
    header('Location: index.html');
    exit;
}

// Secure admin validation with constant-time comparison
function secureCompare($a, $b) {
    if (!is_string($a) || !is_string($b)) {
        return false;
    }
    
    if (strlen($a) !== strlen($b)) {
        return false;
    }
    
    $result = 0;
    for ($i = 0; $i < strlen($a); $i++) {
        $result |= ord($a[$i]) ^ ord($b[$i]);
    }
    
    return $result === 0;
}

if (!secureCompare($_SESSION['applicant_id'], 'admin')) {
    header('Location: index.html');
    exit;
}

// CSRF token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['last_admin_login'])) {
    $_SESSION['last_admin_login'] = date('Y-m-d H:i:s');
}

// Enhanced logging of admin activities
function logAdminActivity($action) {
    $logFile = __DIR__ . '/admin_activity.log';
    $logEntry = sprintf(
        "[%s] Admin %s: %s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        $action
    );
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

$lastLogin = $_SESSION['last_admin_login'];
$_SESSION['last_admin_login'] = date('Y-m-d H:i:s'); // update for next login

include 'db_connect.php'; // Make sure this sets up $conn (PDO)

$applications = [];
try {
    $stmt = $conn->prepare("SELECT Application_Id, Applicant_Id, University, Degree_Level, Program_1, Program_2, Docs_File, Submitted_At, Status FROM scholarship_applications ORDER BY Submitted_At DESC");
    $stmt->execute();
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $applications = [];
}

// Fetch latest messages, grouped by user (limit to 10 users)
$messages = [];
try {
    $stmt = $conn->prepare("
        SELECT Applicant_Id, Name, GROUP_CONCAT(CONCAT(Message_Id, '||', Message, '||', Created_at, '||', Message_type) ORDER BY Created_at DESC SEPARATOR '##') as UserMessages
        FROM messages
        GROUP BY Applicant_Id, Name
        ORDER BY MAX(Created_at) DESC
        LIMIT 10
    ");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $messages = [];
}

// Application stats (example: Approved, Pending, Rejected)
$appStats = ['Approved' => 0, 'Pending' => 0, 'Rejected' => 0];
$stmt = $conn->query("SELECT Status, COUNT(*) as count FROM scholarship_applications GROUP BY Status");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $appStats[$row['Status']] = (int)$row['count'];
}

// Message stats (example: by Message_type)
$msgStats = [];
$stmt = $conn->query("SELECT Message_type, COUNT(*) as count FROM messages GROUP BY Message_type");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $msgStats[$row['Message_type']] = (int)$row['count'];
}

// Fetch distinct degree levels for filter
$degreeLevels = [];
try {
    $stmt = $conn->query("SELECT DISTINCT Degree_Level FROM scholarship_applications ORDER BY Degree_Level");
    $degreeLevels = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $degreeLevels = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Terminal | Binz Scholarship Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #0a101a;
            color: #e0e0e0;
        }
        .neon {
            text-shadow: 0 0 2px #00ffae, 0 0 4px #00bfff;
        }
        .glass {
            background: rgba(20, 30, 40, 0.85);
            backdrop-filter: blur(8px);
        }
        .cyber-border {
            border: 2px solid #059669;
            box-shadow: 0 0 10px #05966955, 0 0 20px #2563eb33;
        }
        .terminal {
            font-family: 'Fira Mono', 'Consolas', 'Menlo', monospace;
            background: #10141a;
            color: #00ffae;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 0 16px #05966955;
            min-height: 160px;
            margin-bottom: 0;
        }
        .terminal-cursor, #terminalInput {
            background: transparent;
            border: none;
            outline: none;
            color: #a7ffeb;
            font-family: inherit;
            font-size: 1rem;
            width: 180px;
            display: inline-block;
            vertical-align: middle;
        }
        .terminal-cursor {
            width: 10px;
            animation: blink 1s steps(1) infinite;
            height: 1.2em;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
.light-dashboard {
    background:rgba(128, 183, 207, 0.8) !important; /* light gray */
    color: #222 !important;
}
.light-dashboard .bg-gray-800,
.light-dashboard .bg-gray-900 {
    background-color: #f3f4f6 !important; /* soft gray for cards */
    color: #222 !important;
}
.light-dashboard .border-gray-700 {
    border-color: #cbd5e1 !important; /* medium gray border */
}
.light-dashboard .text-gray-100,
.light-dashboard .text-gray-400 {
    color:rgb(90, 135, 207) !important; /* slate gray text */
}
.light-dashboard .shadow,
.light-dashboard .shadow-lg {
    box-shadow: 0 2px 8px #cbd5e1 !important;
}
.light-dashboard .bg-emerald-900 {
    background-color: #bbf7d0 !important;
    color: #065f46 !important;
}
.light-dashboard .bg-emerald-100 {
    background-color: #d1fae5 !important;
    color: #065f46 !important;
}
.light-dashboard .hover\:bg-emerald-700:hover {
    background-color: #6ee7b7 !important;
    color: #065f46 !important;
}
.light-dashboard .bg-blue-700 {
    background-color: #e0e7ff !important;
    color: #1e3a8a !important;
}
.light-dashboard .bg-amber-600 {
    background-color: #fef3c7 !important;
    color: #92400e !important;
}
.light-dashboard .bg-red-700 {
    background-color: #fee2e2 !important;
    color: #991b1b !important;
}
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="flex items-center justify-between px-8 py-4 bg-gradient-to-r from-emerald-700 to-blue-900 shadow">
        <div class="flex items-center gap-3">
            <i class="fas fa-user-secret text-2xl text-emerald-300 neon"></i>
            <span class="text-xl font-bold neon tracking-wider">Binz International Scholarship (BIS)</span>
        </div>
        <div>
            <a href="#" id="killDashNav" class="text-sm bg-red-600 hover:bg-red-800 text-white px-4 py-2 rounded-full shadow transition-all duration-200">
                <i class="fas fa-skull-crossbones mr-1"></i>Kill Dash
            </a>
        </div>
    </nav>

    <main class="flex-1 flex flex-col items-center justify-center p-8">
        <!-- Terminal Panel -->
        <div id="terminalPanel" class="glass rounded-xl p-6 shadow-lg cyber-border w-full max-w-2xl mt-12">
            <h3 class="text-lg font-bold mb-4 neon"><i class="fas fa-terminal mr-2"></i>Admin Terminal</h3>
            <div class="terminal" id="adminTerminal">
                <div id="terminalTextLine">
                    <span class="text-emerald-400">admin:</span><span class="text-blue-400">~</span>$ 
                    <span id="terminalText"></span>
                </div>
                <div id="terminalInputLine" class="mt-4 hidden">
                    <span class="text-emerald-400">admin&gt;</span>
                    <input id="terminalInput" type="text" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" />
                    <span class="terminal-cursor"></span>
                </div>
                <div id="terminalResponse" class="mt-2"></div>
            </div>
        </div>

        <!-- Real Dashboard (hidden by default) -->
        <div id="realDashboard" class="hidden w-full max-w-6xl mt-08">
            <nav class="flex justify-between items-center mb-6">
    <span class="text-2xl font-bold neon flex items-center gap-2">
        <i class="fas fa-gauge-high"></i> Admin Dashboard
    </span>
    <div class="flex items-center gap-4">
        <button class="px-3 py-1 text-xs rounded bg-blue-600 hover:bg-blue-700 text-white" type="button" onclick="window.print()">
              <i class="fas fa-print"></i> Print
            </button>
        <a href="#" id="refreshDash" class="text-blue-400 hover:text-blue-600 text-2xl" title="Refresh Dashboard">
            <i class="fas fa-rotate-right"></i>
        </a>
        <div class="relative">
    <button id="settingsBtn" class="text-emerald-400 hover:text-emerald-600 text-2xl focus:outline-none" title="Settings">
        <i class="fas fa-cog"></i>
    </button>
    <!-- Dropdown menu (hidden by default) -->
    <div id="settingsDropdown" class="hidden absolute right-0 mt-2 w-44 bg-gray-900 border border-gray-700 rounded shadow-lg z-50">
        <button class="block w-full text-left px-4 py-2 text-gray-100 hover:bg-emerald-700" id="editAppBtn"><i class="fas fa-pen-to-square text-red-400"></i> Edit Application</button>
        <button class="block w-full text-left px-4 py-2 text-gray-100 hover:bg-emerald-700" id="editMsgBtn"><i class="fas fa-message text-blue-400"></i> Edit Messages</button>
        <button class="block w-full text-left px-4 py-2 text-gray-100 hover:bg-emerald-700" id="editThemeBtn"><i class="fas fa-paintbrush text-amber-400"></i> Edit Theme</button>
    </div>
</div>
    </div>
</nav>
 <div class="flex flex-col md:flex-row gap-4 w-full">
                <!-- Statistics Section (Bar & Pie Chart, left/right) -->
<section class="bg-gray-800 rounded-lg p-4 md:ml-2 col-span-2 min-h-[220px] max-h-[400px] overflow-x-auto">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-bold flex items-center gap-2">
    <i class="fas fa-chart-bar"></i>
    <span id="statsTitle">Statistics</span>
</h2>
        <div class="flex gap-2">
            <button id="appStatsBtn" class="text-emerald-400 hover:text-emerald-600 text-xl focus:outline-none" title="Application Stats">
                <i class="fas fa-users"></i>
            </button>
            <button id="msgStatsBtn" class="text-blue-400 hover:text-blue-600 text-xl focus:outline-none" title="Message Stats">
                <i class="fas fa-envelope"></i>
            </button>
        </div>
    </div>
    <div class="flex flex-col md:flex-row items-center md:items-start md:justify-between gap-6 min-w-[340px]">
        <div class="flex-1 flex justify-center md:justify-start">
            <canvas id="barChart" width="320" height="170" class="!w-80 !h-44"></canvas>
        </div>
        <div class="flex-1 flex justify-center md:justify-end">
            <canvas id="pieChart" width="220" height="170" class="!w-56 !h-44"></canvas>
        </div>
    </div>
</section>
                <!-- Messages Section (Expandable) -->
    <section class="bg-gray-800 rounded-lg p-4 w-full md:w-6/12 min-h-[220px] max-h-[400px] overflow-y-auto">
    <div class="flex justify-between items-center mb-2">
        <h2 class="text-lg font-bold"><i class="fas fa-envelope"></i> Messages</h2>
        <button id="toggleAllMsgs" class="text-emerald-300 hover:text-emerald-500 font-semibold flex items-center gap-1 text-sm">
            <i class="fas fa-expand"></i> <span>Expand All</span>
        </button>
    </div>
    <!-- Add this button just above your messagesAccordion -->
<button id="deleteSelectedMsgs" class="hidden mb-2 bg-red-600 hover:bg-red-800 text-white px-3 py-1 rounded text-xs">
    <i class="fas fa-trash"></i> Delete Selected
</button>

<div id="messagesAccordion" class="space-y-2">
    <?php foreach ($messages as $msgGroup): 
        $userMsgs = explode('##', $msgGroup['UserMessages']);
    ?>
    <div class="bg-gray-900 rounded-lg shadow border border-gray-700">
        <button class="msg-question w-full px-4 py-3 text-left flex justify-between items-center focus:outline-none">
            <div>
                <span class="font-semibold text-emerald-200"><?= htmlspecialchars($msgGroup['Name']) ?></span>
                <span class="text-xs text-gray-400 ml-2">[<?= htmlspecialchars($msgGroup['Applicant_Id']) ?>]</span>
            </div>
            <i class="fas fa-chevron-down text-emerald-400 transition-transform duration-300"></i>
        </button>
        <div class="msg-answer px-4 pb-3 hidden">
            <?php foreach ($userMsgs as $umsg):
                list($mid, $message, $created, $type) = explode('||', $umsg);
            ?>
            <div class="relative mb-2 border-b border-gray-700 pb-2 last:border-0 last:pb-0 flex items-start gap-2">
                <!-- Checkbox for edit mode -->
                <input type="checkbox" class="msg-select-checkbox hidden mt-1" data-message-id="<?= htmlspecialchars($mid) ?>">
                <div class="flex-1">
                    <div class="flex justify-between items-center text-xs text-gray-400 mb-1">
                        <span><?= htmlspecialchars($type) ?></span>
                        <span><?= date('M d, H:i', strtotime($created)) ?></span>
                    </div>
                    <div class="text-gray-100 text-sm"><?= nl2br(htmlspecialchars($message)) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
            </div>
<!-- Applications Section (Excel-like Table) -->
<section class="bg-gray-800 rounded-lg p-4 col-span-3 mt-6">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-bold flex items-center gap-2">
            <i class="fas fa-users"></i> Applications
        </h2>
        <div class="flex gap-2">
            <!-- Small Search Bar -->
            <input type="text" id="appSearch" placeholder="Search..." 
                class="px-2 py-1 rounded bg-gray-900 text-gray-100 border border-gray-700 focus:outline-none focus:ring-1 focus:ring-emerald-400 text-xs w-36 md:w-48" />
            <!-- Small Degree Dropdown -->
            <select id="degreeFilter" 
                class="px-2 py-1 rounded bg-gray-900 text-gray-100 border border-gray-700 focus:outline-none focus:ring-1 focus:ring-emerald-400 text-xs w-24 md:w-32">
                <option value="">Degree</option>
                <option value="Bachelor">Bachelor</option>
                 <option value="Master">Master</option>
                  <option value="Phd">Phd</option>
            </select>
            <!-- Small Status Dropdown -->
            <select id="statusFilter" 
                class="px-2 py-1 rounded bg-gray-900 text-gray-100 border border-gray-700 focus:outline-none focus:ring-1 focus:ring-emerald-400 text-xs w-24 md:w-32">
                <option value="">Status</option>
                <option value="Submitted">Submitted</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
        </div>
    </div>
    <div class="overflow-x-auto">
        <?php if (count($applications) > 0): ?>
<table class="min-w-full text-sm border border-gray-600 rounded shadow bg-gray-900 text-gray-100" id="applicationsTable">            <thead>
                <tr class="bg-emerald-900 text-emerald-200">
                    <th class="border border-gray-300 px-2 py-1">App ID</th>
                    <th class="border border-gray-300 px-2 py-1">Applicant ID</th>
                    <th class="border border-gray-300 px-2 py-1">University</th>
                    <th class="border border-gray-300 px-2 py-1">Degree</th>
                    <th class="border border-gray-300 px-2 py-1">Program 1</th>
                    <th class="border border-gray-300 px-2 py-1">Program 2</th>
                    <th class="border border-gray-300 px-2 py-1">Docs</th>
                    <th class="border border-gray-300 px-2 py-1">Submitted</th>
                    <th class="border border-gray-300 px-2 py-1">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                <tr class="even:bg-gray-800 odd:bg-gray-900 hover:bg-emerald-950">
                    <td class="border border-gray-300 px-2 py-1"><?= htmlspecialchars($app['Application_Id']) ?></td>
                    <td class="border border-gray-300 px-2 py-1 cursor-pointer text-blue-500 hover:text-blue-700 font-semibold text-center applicant-id-cell"
        data-applicant-id="<?= htmlspecialchars($app['Applicant_Id']) ?>">
        <?= htmlspecialchars($app['Applicant_Id']) ?>
    </td>
                    <td class="border border-gray-300 px-2 py-1"><?= htmlspecialchars($app['University']) ?></td>
                    <td class="border border-gray-300 px-2 py-1"><?= htmlspecialchars($app['Degree_Level']) ?></td>
                    <td class="border border-gray-300 px-2 py-1"><?= htmlspecialchars($app['Program_1']) ?></td>
                    <td class="border border-gray-300 px-2 py-1"><?= htmlspecialchars($app['Program_2']) ?></td>
                    <td class="border border-gray-300 px-2 py-1">
                        <?php if ($app['Docs_File']): ?>
                            <a href="uploads/<?= urlencode($app['Docs_File']) ?>" target="_blank" class="text-blue-600 underline">View</a>
                        <?php else: ?>
                            <span class="text-gray-400">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td class="border border-gray-300 px-2 py-1"><?= htmlspecialchars($app['Submitted_At']) ?></td>
                    <td class="border border-gray-300 px-2 py-1">
                        <?php
                        $status = $app['Status'];
                        $color = $status === 'Approved' ? 'text-green-600' : ($status === 'Rejected' ? 'text-red-600' : 'text-yellow-600');
                        ?>
                        <span class="app-status <?= $color ?>" 
            data-app-id="<?= htmlspecialchars($app['Application_Id']) ?>" 
            data-current-status="<?= htmlspecialchars($status) ?>">
            <?= htmlspecialchars($status) ?>
        </span>
                    </td>
                </tr>
                <!-- Expandable row for personal info (hidden by default) -->
<tr class="personal-info-row hidden bg-gray-900">
    <td colspan="9" class="p-0 border-t-0">
        <div class="personal-info-content p-4"></div>
    </td>
</tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="text-gray-400">No applications submitted yet.</div>
        <?php endif; ?>
    </div>
</section>
            </div>
        </div>
    </main>

    <footer class="text-center text-xs text-emerald-300 py-4 neon">
        &copy; <?php echo date('Y'); ?> Binz International Scholarship Portal. All rights reserved.
    </footer>

    <script>
    // Pass PHP variables to JS
    const adminEmail = "binzeria@gmail.com";
    const lastLogin = "<?php echo htmlspecialchars($lastLogin, ENT_QUOTES, 'UTF-8'); ?>";
    const csrfToken = "<?php echo isset($_SESSION['csrf_token']) ? htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') : ''; ?>";
    const appStats = <?= json_encode($appStats) ?>;
const msgStats = <?= json_encode($msgStats) ?>;

    function getGreeting() {
        const hour = new Date().getHours();
        if (hour < 12) return "Good morning";
        if (hour < 18) return "Good afternoon";
        return "Good evening";
    }
    
    function getCurrentDateTime() {
        const now = new Date();
        return now.toLocaleString();
    }

    // Secure command validation
    function validateCommand(command) {
        // Only allow specific commands
        const allowedCommands = ['admin>all', 'admin>help', 'admin>status'];
        return allowedCommands.includes(command);
    }

    // Secure command execution
    function executeCommand(command) {
        switch(command) {
            case 'admin>all':
                return { success: true, message: 'access granted' };
            case 'admin>help':
                return { success: true, message: 'Available commands: admin>all, admin>help, admin>status' };
            case 'admin>status':
                return { success: true, message: 'System status: operational' };
            default:
                return { success: false, message: 'access denied' };
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Terminal typing effect
        const terminalLines = [
            `${getGreeting()},`,
            `Admin email: <span class='text-blue-400'>${adminEmail}</span>`,
            `Last login: ${lastLogin}`,
            `Current time: <span class='text-amber-300'>${getCurrentDateTime()}</span>`,
            "To get systems operational."
        ];
        let line = 0, char = 0, output = "";
        const terminalText = document.getElementById('terminalText');
        const inputLine = document.getElementById('terminalInputLine');
        const input = document.getElementById('terminalInput');
        const response = document.getElementById('terminalResponse');
        const cursor = inputLine.querySelector('.terminal-cursor');

        function typeTerminal() {
            if (line < terminalLines.length) {
                if (char < terminalLines[line].length) {
                    output += terminalLines[line][char];
                    terminalText.innerHTML = output;
                    char++;
                    setTimeout(typeTerminal, 30);
                } else {
                    output += "<br>";
                    terminalText.innerHTML = output;
                    line++; char = 0;
                    setTimeout(typeTerminal, 600);
                }
            } else {
                // Show input line after typing is done
                inputLine.classList.remove('hidden');
                input.focus();
            }
        }
        typeTerminal();

        // Terminal command input logic with enhanced security
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const val = input.value.trim();
                
                // Validate the command
               if (!validateCommand(val)) {
                response.innerHTML = '<span class="text-red-400">access denied</span>';
                  input.value = '';
                    setTimeout(() => { response.innerHTML = ''; }, 2000);
                        return;
            }
                
                // Execute the command
                const result = executeCommand(val);
                
                if (result.success) {
                    response.innerHTML = `<span class="text-green-400">${result.message}</span>`;
                    if (val === "admin>all") {
                        response.innerHTML = '<span class="text-green-400">access granted</span>';
    setTimeout(() => {
        response.innerHTML = '';
        document.getElementById('terminalPanel').classList.add('hidden');
        document.getElementById('realDashboard').classList.remove('hidden');
        localStorage.setItem('adminDashboardVisible', '1'); // Set flag
        drawCharts();
    }, 2000);
                    }
                } else {
                    response.innerHTML = `<span class="text-red-400">${result.message}</span>`;
                    setTimeout(() => { response.innerHTML = ''; }, 3000);
                }
                input.value = '';
            }
        });

        // Kill Dash (logout) button in nav and dashboard
        document.getElementById('killDashNav').addEventListener('click', function(e) {
            e.preventDefault();
            // Log the logout action
            fetch('log_activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ action: 'logout' })
            }).then(() => {
                window.location.href = 'logout.php';
            }).catch(() => {
                window.location.href = 'logout.php';
            });
        });

        // When logout is clicked
document.getElementById('killDashNav')?.addEventListener('click', function() {
    localStorage.removeItem('adminDashboardVisible');
});
document.getElementById('killDash')?.addEventListener('click', function() {
    localStorage.removeItem('adminDashboardVisible');
});

        // Refresh Dashboard (keeps dashboard visible, hides terminal)
        document.getElementById('refreshDash').onclick = function(e) {
            e.preventDefault();
            // If using localStorage to keep dashboard visible:
            localStorage.setItem('adminDashboardVisible', '1');
            location.reload();
        };


        // Check local storage flag on load
        if (localStorage.getItem('adminDashboardVisible') === '1') {
            document.getElementById('terminalPanel').classList.add('hidden');
            document.getElementById('realDashboard').classList.remove('hidden');
            drawAppStats(); // Show application stats by default
        }

        // Chart icon switching logic
        document.getElementById('appStatsBtn').onclick = drawAppStats;
    document.getElementById('msgStatsBtn').onclick = drawMsgStats;

        // Messages accordion logic
        const msgQuestions = document.querySelectorAll('.msg-question');
        msgQuestions.forEach(btn => {
            btn.addEventListener('click', () => {
                const answer = btn.nextElementSibling;
                const icon = btn.querySelector('i');
                answer.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            });
        });

        // Expand/Collapse All
        const toggleAllMsgs = document.getElementById('toggleAllMsgs');
        let allMsgsExpanded = false;
        toggleAllMsgs.addEventListener('click', () => {
            const answers = document.querySelectorAll('.msg-answer');
            const icons = document.querySelectorAll('.msg-question i');
            if (allMsgsExpanded) {
                answers.forEach(ans => ans.classList.add('hidden'));
                icons.forEach(icon => icon.classList.remove('rotate-180'));
                toggleAllMsgs.innerHTML = '<i class="fas fa-expand"></i> <span>Expand All</span>';
            } else {
                answers.forEach(ans => ans.classList.remove('hidden'));
                icons.forEach(icon => icon.classList.add('rotate-180'));
                toggleAllMsgs.innerHTML = '<i class="fas fa-compress"></i> <span>Collapse All</span>';
            }
            allMsgsExpanded = !allMsgsExpanded;
        });

        const searchInput = document.getElementById('appSearch');
    const degreeFilter = document.getElementById('degreeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const table = document.querySelector('#applicationsTable');
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const search = searchInput.value.toLowerCase();
        const degree = degreeFilter.value;
        const status = statusFilter.value;

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const rowText = row.textContent.toLowerCase();
            const rowDegree = cells[3]?.textContent.trim();
            const rowStatus = cells[8]?.textContent.trim();

            const matchesSearch = rowText.includes(search);
            const matchesDegree = !degree || rowDegree === degree;
            const matchesStatus = !status || rowStatus === status;

            row.style.display = (matchesSearch && matchesDegree && matchesStatus) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterTable);
    degreeFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);

    const settingsBtn = document.getElementById('settingsBtn');
    const dropdown = document.getElementById('settingsDropdown');

    settingsBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function() {
        dropdown.classList.add('hidden');
    });

    // Prevent dropdown from closing when clicking inside
    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Example: Add your logic for each option here
    document.getElementById('editAppBtn').onclick = function() {
        // Open edit application modal or page
    };
    document.getElementById('editMsgBtn').onclick = function() {
        // Open edit messages modal or page
    };
    document.getElementById('editThemeBtn').onclick = function() {
        // Toggle a class on <body> for light theme
    document.body.classList.toggle('light-dashboard');
    // Optionally, store preference in localStorage
    if(document.body.classList.contains('light-dashboard')) {
        localStorage.setItem('dashboardTheme', 'light');
    } else {
        localStorage.removeItem('dashboardTheme');
    }
    };

    // On page load, apply theme if set
    if(localStorage.getItem('dashboardTheme') === 'light') {
        document.body.classList.add('light-dashboard');
    }
    });

    // --- Chart Switching Logic for Statistics Box ---

function drawAppStats() {
    const barCtx = document.getElementById('barChart').getContext('2d');
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    if (window.barChartObj) window.barChartObj.destroy();
    if (window.pieChartObj) window.pieChartObj.destroy();

    // Define your default colors for statuses
    const defaultColors = {
        'Submitted': '#22c55e', // green
        'Approved': '#22d3ee',  // cyan
        'Pending':  '#facc15',  // yellow
        'Rejected': '#f87171'   // red
        // Add more if you have more statuses
    };

    const labels = Object.keys(appStats);
    const colors = labels.map(label => defaultColors[label] || '#64748b'); // fallback to gray

    window.barChartObj = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Applications',
                data: Object.values(appStats),
                backgroundColor: colors
            }]
        },
        options: { plugins: { legend: { display: false } } }
    });
    window.pieChartObj = new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: Object.values(appStats),
                backgroundColor: colors
            }]
        }
    });
    document.querySelector('#statsTitle').textContent = 'Application Statistics';
}

function drawMsgStats() {
    const barCtx = document.getElementById('barChart').getContext('2d');
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    if (window.barChartObj) window.barChartObj.destroy();
    if (window.pieChartObj) window.pieChartObj.destroy();

    // Assign a unique color to each message type
    const typeColors = {
        'Suggestion': '#10b981', // emerald
        'Feedback':   '#3b82f6', // blue
        'Request':    '#f59e42', // amber
        'Complaint':  '#ef4444', // red
        // fallback for any other types
        'default':    '#64748b'  // gray
    };

    const labels = Object.keys(msgStats);
    const colors = labels.map(label => typeColors[label] || typeColors['default']);

    window.barChartObj = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Messages',
                data: Object.values(msgStats),
                backgroundColor: colors
            }]
        },
        options: { plugins: { legend: { display: false } } }
    });
    window.pieChartObj = new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: Object.values(msgStats),
                backgroundColor: colors
            }]
        }
    });
    document.querySelector('#statsTitle').textContent = 'Message Statistics';
}

    // Edit Messages logic
    const editMsgBtn = document.getElementById('editMsgBtn');
    const deleteBtn = document.getElementById('deleteSelectedMsgs');
    let editMode = false;

    editMsgBtn.addEventListener('click', function() {
        editMode = !editMode;
        // Show/hide checkboxes
        document.querySelectorAll('.msg-select-checkbox').forEach(cb => {
            cb.classList.toggle('hidden', !editMode);
            cb.checked = false;
        });
        // Show/hide delete button
        deleteBtn.classList.toggle('hidden', !editMode);
        // Optionally, change button style to indicate edit mode
        editMsgBtn.classList.toggle('bg-emerald-700', editMode);
    });

    // Bulk delete selected messages
    deleteBtn.addEventListener('click', function() {
        const selected = Array.from(document.querySelectorAll('.msg-select-checkbox:checked'));
        if (selected.length === 0) {
            alert('Select at least one message to delete.');
            return;
        }
        if (!confirm('Are you sure you want to delete the selected messages?')) return;

        const ids = selected.map(cb => cb.getAttribute('data-message-id'));
        // Send as FormData for compatibility with PHP
        const formData = new FormData();
        formData.append('action', 'delete_messages');
        ids.forEach(id => formData.append('message_ids[]', id));
        fetch('save_message.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Remove deleted messages from UI
                selected.forEach(cb => cb.closest('.relative').remove());
            } else {
                alert('Failed to delete messages.');
            }
        });
    });

// --- Edit Applications Logic ---
const editAppBtn = document.getElementById('editAppBtn');
let appEditMode = false;

editAppBtn.addEventListener('click', function() {
    appEditMode = !appEditMode;
    document.querySelectorAll('.app-status').forEach(span => {
        const appId = span.getAttribute('data-app-id');
        const currentStatus = span.getAttribute('data-current-status');
        if (appEditMode) {
            // Replace span with dropdown
            const select = document.createElement('select');
            select.className = 'app-status-select px-2 py-1 rounded bg-gray-900 text-gray-100 border border-gray-700 text-xs';
            select.setAttribute('data-app-id', appId);
            ['Approved', 'Pending', 'Rejected'].forEach(status => {
                const option = document.createElement('option');
                option.value = status;
                option.textContent = status;
                if (status === currentStatus) option.selected = true;
                option.className =
                    status === 'Approved' ? 'text-green-600' :
                    status === 'Rejected' ? 'text-red-600' :
                    'text-yellow-600';
                select.appendChild(option);
            });
            span.replaceWith(select);
        } else {
            // Replace dropdown with span (restore)
            const value = span.value || span.textContent;
            const color =
                value === 'Approved' ? 'text-green-600' :
                value === 'Rejected' ? 'text-red-600' :
                'text-yellow-600';
            const newSpan = document.createElement('span');
            newSpan.className = 'app-status ' + color;
            newSpan.setAttribute('data-app-id', appId);
            newSpan.setAttribute('data-current-status', value);
            newSpan.textContent = value;
            span.replaceWith(newSpan);
        }
    });
});

// Listen for status change and update in DB
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('app-status-select')) {
        const select = e.target;
        const appId = select.getAttribute('data-app-id');
        const newStatus = select.value;
        // AJAX to update status
        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('application_id', appId);
        formData.append('status', newStatus);
        fetch('scholarship.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update UI: change color and text
                const color =
                    newStatus === 'Approved' ? 'text-green-600' :
                    newStatus === 'Rejected' ? 'text-red-600' :
                    'text-yellow-600';
                const newSpan = document.createElement('span');
                newSpan.className = 'app-status ' + color;
                newSpan.setAttribute('data-app-id', appId);
                newSpan.setAttribute('data-current-status', newStatus);
                newSpan.textContent = newStatus;
                select.replaceWith(newSpan);
            } else {
                alert('Failed to update status.');
            }
        });
    }
});
document.addEventListener('DOMContentLoaded', function() {
    // ...existing code...

    document.querySelectorAll('.applicant-id-cell').forEach((cell) => {
        cell.addEventListener('click', function() {
            const applicantId = cell.getAttribute('data-applicant-id');
            const row = cell.parentElement;
            const infoRow = row.nextElementSibling;
            const infoContent = infoRow.querySelector('.personal-info-content');
            // Toggle visibility
            if (!infoRow.classList.contains('hidden')) {
                infoRow.classList.add('hidden');
                infoContent.innerHTML = '';
                return;
            }
            // Hide any other open info rows
            document.querySelectorAll('.personal-info-row').forEach(r => {
                r.classList.add('hidden');
                r.querySelector('.personal-info-content').innerHTML = '';
            });
            // Show this info row
            infoRow.classList.remove('hidden');
            infoContent.innerHTML = '<div class="text-blue-400">Loading...</div>';
            // Fetch personal info via AJAX
            fetch('personal_info.php?applicant_id=' + encodeURIComponent(applicantId))
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.personal) {
                        const p = data.personal;
                        infoContent.innerHTML = `
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-user-circle text-blue-600 text-2xl"></i>
                                <h3 class="text-lg font-bold text-blue-700">Personal Information</h3>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-gray-100 text-sm">
                                <div><span class="font-semibold">Name:</span> ${p.Name ?? ''}</div>
                                <div><span class="font-semibold">Applicant ID:</span> ${p.Applicant_Id ?? ''}</div>
                                <div><span class="font-semibold">Religion:</span> ${p.Religion ?? ''}</div>
                                <div><span class="font-semibold">Date of Birth:</span> ${p.Date_of_Birth ?? ''}</div>
                                <div><span class="font-semibold">Gender:</span> ${p.Gender ?? ''}</div>
                                <div><span class="font-semibold">Nationality:</span> ${p.Nationality ?? ''}</div>
                                <div><span class="font-semibold">Qualification:</span> ${p.Qualification ?? ''}</div>
                                <div><span class="font-semibold">Grade %:</span> ${p.Grade_Percent ?? ''}</div>
                                <div><span class="font-semibold">Contact:</span> ${p.Contact_Number ?? ''}</div>
                                <div class="email-field"></div>
                                <div class="sm:col-span-2"><span class="font-semibold">Address:</span> ${p.Address ?? ''}</div>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <button class="collapse-personal-info-btn px-3 py-1 text-xs rounded bg-gray-200 hover:bg-gray-300 text-gray-700" type="button">
                                    <i class="fas fa-chevron-up"></i> Collapse
                                </button>
                            </div>
                        `;
                        // Collapse button logic
                        infoContent.querySelector('.collapse-personal-info-btn').onclick = function() {
                            infoRow.classList.add('hidden');
                            infoContent.innerHTML = '';
                        };
                        // Fetch email using get_user_info.php
fetch('get_user_info.php?applicant_id=' + encodeURIComponent(p.Applicant_Id))
    .then(res => res.json())
    .then(userData => {
        if (userData.success && userData.email) {
            infoContent.querySelector('.email-field').innerHTML =
                `<span class="font-semibold">Email:</span> ${userData.email}`;
        } else {
            infoContent.querySelector('.email-field').innerHTML = '';
        }
    });
                    } else {
                        infoContent.innerHTML = '<div class="text-red-400">No personal information found for this applicant.</div>';
                    }
                })
                .catch(() => {
                    infoContent.innerHTML = '<div class="text-red-400">Failed to load personal info.</div>';
                });
        });
    });
});
    </script>
    <script>
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
</body>
</html>