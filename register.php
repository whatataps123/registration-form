<?php
require 'config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $numAttendees = intval($_POST['num_attendees'] ?? 1);
    $street = trim($_POST['street'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $zipCode = trim($_POST['zip_code'] ?? '');

    // Validation
    if (empty($firstName) || empty($lastName)) {
        $errors[] = 'First name and last name are required.';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors[] = 'Phone number should contain only numbers (10-15 digits).';
    }
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    
    if ($numAttendees < 1 || $numAttendees > 10) {
        $errors[] = 'Number of attendees must be between 1 and 10.';
    }
    
    if (empty($street) || empty($barangay) || empty($city) || empty($district) || empty($province) || empty($region) || empty($zipCode)) {
        $errors[] = 'All address fields are required.';
    }

    if (empty($errors)) {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $errors[] = 'Email already registered.';
            } else {
                // Handle file upload
                $filePath = null;
                if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true); // create uploads directory if it doesn't exist
                    }

                    $fileTmpPath = $_FILES['document']['tmp_name'];
                    $originalName = basename($_FILES['document']['name']);
                    $safeName = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $originalName);
                    $targetFilePath = $uploadDir . $safeName;

                    if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
                        $filePath = 'uploads/' . $safeName;
                    } else {
                        $errors[] = 'Failed to move uploaded file.';
                    }
                }

                if (empty($errors)) {
                    // Hash password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // Insert user
                    $stmt = $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$email, $hashedPassword, $firstName, $lastName, $phone]);
                    $userId = $pdo->lastInsertId();

                    // Insert registration
                    $stmt = $pdo->prepare("INSERT INTO registrations (user_id, num_attendees, street, barangay, city, district, province, region, zip_code, document_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$userId, $numAttendees, $street, $barangay, $city, $district, $province, $region, $zipCode, $filePath]);

                    $success = true;
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conference Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .registration-container {
            max-width: 800px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .registration-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .registration-body {
            background-color: white;
            padding: 30px;
        }
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .form-section h3 {
            color: #6a11cb;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .btn-register {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .btn-register:hover {
            opacity: 0.9;
        }
        .address-group .form-group {
            margin-bottom: 15px;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="registration-container mx-auto">
            <div class="registration-header">
                <h1><i class="fas fa-calendar-check me-2"></i> Conference Registration</h1>
                <p class="mb-0">Join us for an amazing conference experience</p>
            </div>
            
            <div class="registration-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-1"><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success text-center">
                        <h4 class="alert-heading">Registration Successful!</h4>
                        <p>You can now <a href="login.php" class="alert-link">login</a> to access your account.</p>
                    </div>
                <?php else: ?>
                    <form action="register.php" method="post" enctype="multipart/form-data">
                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <h3><i class="fas fa-user-circle me-2"></i> Personal Information</h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required 
                                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required 
                                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                           pattern="[0-9]{10,15}">
                                    <div class="form-text">Digits only (10-15 numbers)</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                    <div class="form-text">At least 8 characters</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Conference Details Section -->
                        <div class="form-section">
                            <h3><i class="fas fa-users me-2"></i> Conference Details</h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="num_attendees" class="form-label">Number of People Attending *</label>
                                    <input type="number" class="form-control" id="num_attendees" name="num_attendees" required 
                                           min="1" max="10" value="<?php echo htmlspecialchars($_POST['num_attendees'] ?? '1'); ?>">
                                    <div class="form-text">Between 1 and 10</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address Information Section -->
                        <div class="form-section">
                            <h3><i class="fas fa-map-marker-alt me-2"></i> Address Information</h3>
                            <div class="mb-3">
                                <label for="street" class="form-label">Street *</label>
                                <input type="text" class="form-control" id="street" name="street" required 
                                       value="<?php echo htmlspecialchars($_POST['street'] ?? ''); ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="region" class="form-label">Region *</label>
                                    <select class="form-select" id="region" name="region" required>
                                        <option value="">Select Region</option>
                                        <?php foreach ($regions as $regionName => $regionData): ?>
                                            <option value="<?php echo htmlspecialchars($regionName); ?>"
                                                <?php if (($_POST['region'] ?? '') === $regionName) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($regionName); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="province" class="form-label">Province *</label>
                                    <select class="form-select" id="province" name="province" required>
                                        <option value="">Select Province</option>
                                        <?php if (!empty($_POST['region'])): ?>
                                            <?php foreach ($regions[$_POST['region']]['provinces'] as $province): ?>
                                                <option value="<?php echo htmlspecialchars($province); ?>"
                                                    <?php if (($_POST['province'] ?? '') === $province) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($province); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City *</label>
                                    <select class="form-select" id="city" name="city" required>
                                        <option value="">Select City</option>
                                        <?php if (!empty($_POST['region'])): ?>
                                            <?php foreach ($regions[$_POST['region']]['cities'] as $city): ?>
                                                <option value="<?php echo htmlspecialchars($city); ?>"
                                                    <?php if (($_POST['city'] ?? '') === $city) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($city); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                <label for="district" class="form-label">District *</label>
                                    <select class="form-select" id="district" name="district" required>
                                        <option value="">Select District</option>
                                        <?php if (!empty($_POST['region'])): ?>
                                            <?php foreach ($regions[$_POST['region']]['districts'] as $district): ?>
                                                <option value="<?php echo htmlspecialchars($district); ?>"
                                                    <?php if (($_POST['district'] ?? '') === $district) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($district); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="barangay" class="form-label">Barangay *</label>
                                    <select class="form-select" id="barangay" name="barangay" required>
                                        <option value="">Select Barangay</option>
                                        <?php if (!empty($_POST['region'])): ?>
                                            <?php foreach ($regions[$_POST['region']]['barangays'] as $barangay): ?>
                                                <option value="<?php echo htmlspecialchars($barangay); ?>"
                                                    <?php if (($_POST['barangay'] ?? '') === $barangay) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($barangay); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="zip_code" class="form-label">Zip Code *</label>
                                    <input type="text" class="form-control" id="zip_code" name="zip_code" required 
                                           value="<?php echo htmlspecialchars($_POST['zip_code'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        <!-- Agreement Checkbox -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="agree" name="agree" value="1" required 
                                <?php if (isset($_POST['agree'])) echo 'checked'; ?>>
                            <label class="form-check-label" for="agree">
                                I agree to the <a href="#" target="_blank">terms and conditions</a>
                            </label>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="document" class="form-label">Upload 1x1 Formal Picture (JPG, PNG) *</label>
                            <input type="file" class="form-control" id="document" name="document" accept=".jpg,.jpeg,.png" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-register btn-lg">
                                <i class="fas fa-user-plus me-2"></i> Register Now
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Dynamic dropdown population based on region selection
        document.getElementById('region').addEventListener('change', function() {
            const region = this.value;
            const provinceSelect = document.getElementById('province');
            const districtSelect = document.getElementById('district');
            const citySelect = document.getElementById('city');
            const barangaySelect = document.getElementById('barangay');
            
            // Reset all dependent selects
            provinceSelect.innerHTML = '<option value="">Select Province</option>';
            districtSelect.innerHTML = '<option value="">Select District</option>';
            citySelect.innerHTML = '<option value="">Select City</option>';
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            
            if (region) {
                const regionsData = <?php echo json_encode($regions); ?>;
                const regionData = regionsData[region];
                
                // Populate provinces
                regionData.provinces.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province;
                    option.textContent = province;
                    provinceSelect.appendChild(option);
                });
                
                // Populate districts
                regionData.districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district;
                    option.textContent = district;
                    districtSelect.appendChild(option);
                });
                
                // Populate cities
                regionData.cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
                
                // Populate barangays
                regionData.barangays.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay;
                    option.textContent = barangay;
                    barangaySelect.appendChild(option);
                });
            }
        });
    </script>
</body>
</html>