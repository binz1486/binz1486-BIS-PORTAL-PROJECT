<?php
session_start();
if (!isset($_SESSION['applicant_id'])) {
    die("Unauthorized access. Please login first.");
}

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    try {
        // Validate required fields
        $requiredFields = [
            'fullName', 'religion', 'dob', 'gender',
            'address', 'nationality', 'eduLevel', 'percentage', 'contact'
        ];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All fields are required. Missing: " . $field);
            }
        }
        if (empty($_POST['info-correct'])) {
            throw new Exception("Please confirm the information is correct");
        }

        // Prepare and sanitize data
        $data = [
            'applicant_id' => $_SESSION['applicant_id'],
            'name' => htmlspecialchars(trim($_POST['fullName'])),
            'religion' => htmlspecialchars(trim($_POST['religion'])),
            'dob' => $_POST['dob'],
            'gender' => $_POST['gender'],
            'address' => htmlspecialchars(trim($_POST['address'])),
            'nationality' => $_POST['nationality'],
            'qualification' => $_POST['eduLevel'],
            'grade_percent' => floatval($_POST['percentage']),
            'contact_number' => preg_replace('/[^0-9]/', '', $_POST['contact'])
        ];
if (!empty($_POST['applicantId'])) {
    $data['applicant_id'] = htmlspecialchars(trim($_POST['applicantId']));
}
        // Field validations
        if (!preg_match('/^[A-Za-z\s]+$/', $data['name'])) {
            throw new Exception("Full name can only contain letters and spaces");
        }
        if (!preg_match('/^[A-Za-z\s]+$/', $data['religion'])) {
            throw new Exception("Religion can only contain letters and spaces");
        }
        if (!DateTime::createFromFormat('Y-m-d', $data['dob'])) {
            throw new Exception("Invalid date format for date of birth");
        }
        if (!in_array($data['gender'], ['Male', 'Female', 'Other'])) {
            throw new Exception("Invalid gender selection");
        }
        if (strlen($data['address']) < 12) {
            throw new Exception("Address must be at least 12 characters");
        }
        $validNationalities = [
            'Pakistan', 'Palestine', 'China', 'Iran', 'Turkey',
            'Afganistan', 'Bangladesh', 'Sirilanka', 'Oman',
            'Spain', 'Egypt', 'Panama'
        ];
        if (!in_array($data['nationality'], $validNationalities)) {
            throw new Exception("Invalid nationality selection");
        }
        if (!in_array($data['qualification'], ['Intermediate', 'Bachelor', 'Master'])) {
            throw new Exception("Invalid qualification selection");
        }
        if ($data['grade_percent'] < 70 || $data['grade_percent'] > 100) {
            throw new Exception("Percentage must be between 70.00 and 100.00");
        }
        if (strlen($data['contact_number']) < 10) {
            throw new Exception("Contact number must be at least 10 digits");
        }

        // Check if record exists by Contact_Number
        $checkStmt = $conn->prepare("SELECT 1 FROM personal_info WHERE Contact_Number = ?");
        $checkStmt->execute([$data['contact_number']]);
        $recordExists = $checkStmt->fetch();

        if ($recordExists) {
            // Update by Contact_Number
            $sql = "UPDATE personal_info SET 
                Applicant_Id = ?, Name = ?, Religion = ?, Date_of_Birth = ?, Gender = ?,
                Address = ?, Nationality = ?, Qualification = ?, Grade_Percent = ?,
                Updated_at = CURRENT_TIMESTAMP
                WHERE Contact_Number = ?";
            $params = [
                $data['applicant_id'], $data['name'], $data['religion'], $data['dob'], $data['gender'],
                $data['address'], $data['nationality'], $data['qualification'],
                $data['grade_percent'], $data['contact_number']
            ];
        } else {
            // Insert with Contact_Number as PK and Applicant_Id as FK
            $sql = "INSERT INTO personal_info (
                Contact_Number, Applicant_Id, Name, Religion, Date_of_Birth, Gender, Address,
                Nationality, Qualification, Grade_Percent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $data['contact_number'], $data['applicant_id'], $data['name'], $data['religion'], $data['dob'],
                $data['gender'], $data['address'], $data['nationality'],
                $data['qualification'], $data['grade_percent']
            ];
        }

        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($params);

        if ($success) {
            // Update Applicant table with new name and applicant_id
            $updateApplicantSql = "UPDATE Applicant SET Name = ?, Applicant_Id = ? WHERE Applicant_Id = ?";
            $updateApplicantStmt = $conn->prepare($updateApplicantSql);
            // $data['applicant_id'] is the new applicant id, $_SESSION['applicant_id'] is the old one
            $updateApplicantStmt->execute([$data['name'], $data['applicant_id'], $_SESSION['applicant_id']]);

            // Update session if applicant_id changed
            if ($_SESSION['applicant_id'] !== $data['applicant_id']) {
                $_SESSION['applicant_id'] = $data['applicant_id'];
            }

            $response['success'] = true;
            $response['message'] = 'Personal information saved successfully';
        } else {
            throw new Exception("Failed to save information to database");
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

// --- AJAX GET for admin to fetch personal info ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['applicant_id'])) {
    header('Content-Type: application/json');
    require_once 'db_connect.php';
    $stmt = $conn->prepare("SELECT * FROM personal_info WHERE Applicant_Id = ?");
    $stmt->execute([$_GET['applicant_id']]);
    $personal = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($personal) {
        echo json_encode(['success' => true, 'personal' => $personal]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Personal Information Form</title>
    <style>
        .hidden { display: none; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 15px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
        #percentageSymbol { position: absolute; right: 10px; top: 10px; }
        .percentage-container { position: relative; }
        .success-message { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; }
        .success-content { background: white; padding: 20px; border-radius: 5px; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <h2>Enter Personal Information</h2>
    <form id="personalInfoForm">
        <div class="form-group">
            <label for="piFullName">Full Name</label>
            <input type="text" id="piFullName" name="fullName" placeholder="First Middle Last Name" required>
        </div>
        <div class="form-group">
            <label for="piReligion">Religion</label>
            <input type="text" id="piReligion" name="religion" placeholder="Enter your religion" required>
        </div>
        <div class="form-group">
            <label for="piDob">Date of Birth</label>
            <input type="date" id="piDob" name="dob" required>
        </div>
        <div class="form-group">
            <label for="piGender">Gender</label>
            <select id="piGender" name="gender" required>
                <option value="">Select</option>
                <option>Male</option>
                <option>Female</option>
                <option>Other</option>
            </select>
        </div>
        <div class="form-group">
            <label for="piAddress">Address</label>
            <input type="text" id="piAddress" name="address" placeholder="Enter your address" required>
        </div>
        <div class="form-group">
            <label for="piNationality">Nationality</label>
            <select id="piNationality" name="nationality" required>
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
        <div class="form-group">
            <label for="piEducation">Your Qualification</label>
            <select id="piEducation" name="eduLevel" required>
                <option value="">Select</option>
                <option>Intermediate</option>
                <option>Bachelor</option>
                <option>Master</option>
            </select>
        </div>
        <div class="form-group">
            <label for="percentageInput">Grade Percentage</label>
            <div class="percentage-container">
                <input type="text" id="percentageInput" name="percentage" placeholder="70.00 - 100.00" required>
                <span id="percentageSymbol" style="display: none">%</span>
            </div>
            <small><b>Note:</b> Minimum 70% is required</small>
        </div>
        <div class="form-group">
            <label for="piContact">Contact Number</label>
            <input type="text" id="piContact" name="contact" placeholder="92-**-*******-*" required>
        </div>
        <div class="form-group">
            <input type="checkbox" id="info-correct" name="info-correct" required>
            <label for="info-correct">I hereby declare that the information provided is correct</label>
        </div>
        <div class="form-group">
            <button type="submit">Submit</button>
        </div>
    </form>
</div>

<!-- Success Message -->
<div id="personalInfoSuccess" class="hidden success-message">
    <div class="success-content">
        <div style="color: green; font-size: 50px;">✓</div>
        <h2>Personal info saved Successfully!</h2>
    </div>
</div>

<script>
document.getElementById('personalInfoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!document.getElementById('info-correct').checked) {
        alert('Please confirm that the information is correct');
        return;
    }
    const formData = {
        fullName: document.getElementById('piFullName').value,
         applicantId: document.getElementById('piApplicantId').value,
        religion: document.getElementById('piReligion').value,
        dob: document.getElementById('piDob').value,
        gender: document.getElementById('piGender').value,
        address: document.getElementById('piAddress').value,
        nationality: document.getElementById('piNationality').value,
        eduLevel: document.getElementById('piEducation').value,
        percentage: document.getElementById('percentageInput').value,
        contact: document.getElementById('piContact').value,
        'info-correct': document.getElementById('info-correct').checked ? 1 : 0
    };
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('personalInfoSuccess').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('personalInfoSuccess').classList.add('hidden');
            }, 3000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving your information');
    });
});

// Percentage input formatting
document.getElementById('percentageInput').addEventListener('input', function(e) {
    this.nextElementSibling.style.display = this.value ? 'block' : 'none';
    this.value = this.value.replace(/[^0-9.]/g, '');
    if ((this.value.match(/\./g) || []).length > 1) {
        this.value = this.value.substring(0, this.value.lastIndexOf('.'));
    }
    if (this.value.length > 6) {
        this.value = this.value.slice(0, 6);
    }
});
document.getElementById('percentageInput').addEventListener('blur', function(e) {
    if (this.value) {
        let num = parseFloat(this.value);
        if (isNaN(num)) {
            this.value = '70.00';
            return;
        }
        if (num < 70) this.value = '70.00';
        if (num > 100) this.value = '100.00';
        if (this.value.indexOf('.') === -1) {
            this.value = this.value + '.00';
        } else if (this.value.split('.')[1].length === 1) {
            this.value = this.value + '0';
        }
    }
});
</script>
</body>
</html>