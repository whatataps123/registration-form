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
    $agree = isset($_POST['agree']) ? true : false;

    // Validation
    if (empty($firstName)) {
        $errors['first_name'] = 'First name is required.';
    }

    if (empty($lastName)) {
        $errors['last_name'] = 'Last name is required.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors['phone'] = 'Phone number should contain only numbers (10-15 digits).';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    }

    if (empty($confirmPassword)) {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if ($numAttendees < 1 || $numAttendees > 10) {
        $errors['num_attendees'] = 'Number of attendees must be between 1 and 10.';
    }

    // Address validation
    $addressFields = [
        'street' => $street,
        'barangay' => $barangay,
        'city' => $city,
        'district' => $district,
        'province' => $province,
        'region' => $region,
        'zip_code' => $zipCode
    ];

    foreach ($addressFields as $field => $value) {
        if (empty($value)) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }

    if (!$agree) {
        $errors['agree'] = 'You must agree to the terms and conditions.';
    }

    // File upload validation
    if (!isset($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['document'] = 'Please upload your 1x1 formal picture.';
    } elseif ($_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $errors['document'] = 'File upload error. Please try again.';
    } else {
        $allowedTypes = ['image/jpeg', 'image/png'];
        $fileType = $_FILES['document']['type'];
        if (!in_array($fileType, $allowedTypes)) {
            $errors['document'] = 'Only JPG and PNG files are allowed.';
        }
    }

    if (empty($errors)) {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $errors['email'] = 'Email already registered.';
            } else {
                // Handle file upload
                $filePath = null;
                if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $fileTmpPath = $_FILES['document']['tmp_name'];
                    $originalName = basename($_FILES['document']['name']);
                    $safeName = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $originalName);
                    $targetFilePath = $uploadDir . $safeName;

                    if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
                        $filePath = 'uploads/' . $safeName;
                    } else {
                        $errors['document'] = 'Failed to move uploaded file.';
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

// Get selected region data if form was submitted
$selectedRegionData = [];
if (isset($_POST['region'])) {
    $selectedRegion = $_POST['region'];
    if (isset($regions[$selectedRegion])) {
        $selectedRegionData = $regions[$selectedRegion];
    }
}

// Get selected city data if form was submitted
$selectedCityData = [];
if (isset($_POST['city']) && isset($selectedRegionData['cities'][$_POST['city']])) {
    $selectedCityData = $selectedRegionData['cities'][$_POST['city']];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conference Registration</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style-files/register.css">
    <!-- SweetAlert2 for popup alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container py-5">
        <div class="registration-container mx-auto">
            <div class="registration-header">
                <h1><i class="fas fa-calendar-check me-2"></i> Conference Registration</h1>
                <p class="mb-0">Join us for an amazing conference experience</p>
            </div>

            <div class="registration-body">
                <?php if ($success): ?>
                    <div class="alert alert-success text-center">
                        <h4 class="alert-heading">Registration Successful!</h4>
                        <p>You can now <a href="login.php" class="alert-link">login</a> to access your account.</p>
                    </div>
                <?php else: ?>
                    <form id="registrationForm" action="register.php" method="post" enctype="multipart/form-data" novalidate>
                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <h3><i class="fas fa-user-circle me-2"></i> Personal Information</h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                                           id="first_name" name="first_name" required
                                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                                    <div class="invalid-feedback"><?php echo $errors['first_name'] ?? ''; ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                                           id="last_name" name="last_name" required
                                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                                    <div class="invalid-feedback"><?php echo $errors['last_name'] ?? ''; ?></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                           id="email" name="email" required
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                    <div class="invalid-feedback"><?php echo $errors['email'] ?? ''; ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                           id="phone" name="phone" required
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                           pattern="[0-9]{10,15}">
                                    <div class="invalid-feedback"><?php echo $errors['phone'] ?? ''; ?></div>
                                    <div class="form-text">Digits only (10-15 numbers)</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                           id="password" name="password" required minlength="8">
                                    <div class="invalid-feedback"><?php echo $errors['password'] ?? ''; ?></div>
                                    <div class="form-text">At least 8 characters</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                           id="confirm_password" name="confirm_password" required minlength="8">
                                    <div class="invalid-feedback"><?php echo $errors['confirm_password'] ?? ''; ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Conference Details Section -->
                        <div class="form-section">
                            <h3><i class="fas fa-users me-2"></i> Conference Details</h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="num_attendees" class="form-label">Number of People Attending *</label>
                                    <input type="number" class="form-control <?php echo isset($errors['num_attendees']) ? 'is-invalid' : ''; ?>" 
                                           id="num_attendees" name="num_attendees" required
                                           min="1" max="10" value="<?php echo htmlspecialchars($_POST['num_attendees'] ?? '1'); ?>">
                                    <div class="invalid-feedback"><?php echo $errors['num_attendees'] ?? ''; ?></div>
                                    <div class="form-text">Between 1 and 10</div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information Section -->
                        <div class="form-section">
                            <h3><i class="fas fa-map-marker-alt me-2"></i> Address Information</h3>
                            <div class="mb-3">
                                <label for="street" class="form-label">Street *</label>
                                <input type="text" class="form-control <?php echo isset($errors['street']) ? 'is-invalid' : ''; ?>" 
                                       id="street" name="street" required
                                       value="<?php echo htmlspecialchars($_POST['street'] ?? ''); ?>">
                                <div class="invalid-feedback"><?php echo $errors['street'] ?? ''; ?></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="region" class="form-label">Region *</label>
                                    <select class="form-select <?php echo isset($errors['region']) ? 'is-invalid' : ''; ?>" 
                                            id="region" name="region" required>
                                        <option value="">Select Region</option>
                                        <?php foreach ($regions as $regionName => $regionData): ?>
                                            <option value="<?php echo htmlspecialchars($regionName); ?>"
                                                <?php if (($_POST['region'] ?? '') === $regionName) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($regionName); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"><?php echo $errors['region'] ?? ''; ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="province" class="form-label">Province *</label>
                                    <select class="form-select <?php echo isset($errors['province']) ? 'is-invalid' : ''; ?>" 
                                            id="province" name="province" required>
                                        <option value="">Select Province</option>
                                        <?php if (!empty($selectedRegionData['provinces'])): ?>
                                            <?php foreach ($selectedRegionData['provinces'] as $province): ?>
                                                <option value="<?php echo htmlspecialchars($province); ?>"
                                                    <?php if (($_POST['province'] ?? '') === $province) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($province); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="invalid-feedback"><?php echo $errors['province'] ?? ''; ?></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="district" class="form-label">District *</label>
                                    <select class="form-select <?php echo isset($errors['district']) ? 'is-invalid' : ''; ?>" 
                                            id="district" name="district" required>
                                        <option value="">Select District</option>
                                        <?php if (!empty($selectedRegionData['districts'])): ?>
                                            <?php foreach ($selectedRegionData['districts'] as $district): ?>
                                                <option value="<?php echo htmlspecialchars($district); ?>"
                                                    <?php if (($_POST['district'] ?? '') === $district) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($district); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="invalid-feedback"><?php echo $errors['district'] ?? ''; ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City *</label>
                                    <select class="form-select <?php echo isset($errors['city']) ? 'is-invalid' : ''; ?>" 
                                            id="city" name="city" required>
                                        <option value="">Select City</option>
                                        <?php if (!empty($selectedRegionData['cities'])): ?>
                                            <?php foreach ($selectedRegionData['cities'] as $cityName => $cityData): ?>
                                                <option value="<?php echo htmlspecialchars($cityName); ?>"
                                                    <?php if (($_POST['city'] ?? '') === $cityName) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($cityName); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="invalid-feedback"><?php echo $errors['city'] ?? ''; ?></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="barangay" class="form-label">Barangay *</label>
                                    <select class="form-select <?php echo isset($errors['barangay']) ? 'is-invalid' : ''; ?>" 
                                            id="barangay" name="barangay" required>
                                        <option value="">Select Barangay</option>
                                        <?php if (!empty($selectedCityData['barangays'])): ?>
                                            <?php foreach ($selectedCityData['barangays'] as $barangay): ?>
                                                <option value="<?php echo htmlspecialchars($barangay); ?>"
                                                    <?php if (($_POST['barangay'] ?? '') === $barangay) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($barangay); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php elseif (!empty($selectedRegionData['barangays'])): ?>
                                            <?php foreach ($selectedRegionData['barangays'] as $barangay): ?>
                                                <option value="<?php echo htmlspecialchars($barangay); ?>"
                                                    <?php if (($_POST['barangay'] ?? '') === $barangay) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($barangay); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="invalid-feedback"><?php echo $errors['barangay'] ?? ''; ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="zip_code" class="form-label">Zip Code *</label>
                                    <input type="text" class="form-control <?php echo isset($errors['zip_code']) ? 'is-invalid' : ''; ?>" 
                                           id="zip_code" name="zip_code" required
                                           value="<?php echo htmlspecialchars($_POST['zip_code'] ?? ''); ?>">
                                    <div class="invalid-feedback"><?php echo $errors['zip_code'] ?? ''; ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Agreement Checkbox -->
                        <div class="form-check mb-4">
                            <input class="form-check-input <?php echo isset($errors['agree']) ? 'is-invalid' : ''; ?>" 
                                   type="checkbox" id="agree" name="agree" value="1" required
                                   <?php if (isset($_POST['agree'])) echo 'checked'; ?>>
                            <label class="form-check-label" for="agree">
                                I agree to the <a href="#" target="_blank">terms and conditions</a>
                            </label>
                            <div class="invalid-feedback"><?php echo $errors['agree'] ?? ''; ?></div>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="document" class="form-label">Upload 1x1 Formal Picture (JPG, PNG) *</label>
                            <input type="file" class="form-control <?php echo isset($errors['document']) ? 'is-invalid' : ''; ?>" 
                                   id="document" name="document" accept=".jpg,.jpeg,.png" required>
                            <div class="invalid-feedback"><?php echo $errors['document'] ?? ''; ?></div>
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
        // Show popup alerts for errors
        <?php if (!empty($errors)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                // Convert errors object to array of messages
                const errorMessages = [];
                <?php foreach ($errors as $field => $message): ?>
                    errorMessages.push('<?php echo addslashes($message); ?>');
                <?php endforeach; ?>
                
                // Show all error messages in a popup
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Error',
                    html: errorMessages.join('<br>'),
                    confirmButtonColor: '#6a11cb'
                });
                
                // Add was-validated class to form to show validation messages
                document.getElementById('registrationForm').classList.add('was-validated');
                
                // Scroll to first error field
                const firstErrorField = document.querySelector('.is-invalid');
                if (firstErrorField) {
                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorField.focus();
                }
            });
        <?php endif; ?>

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
                if (regionData.provinces) {
                    regionData.provinces.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province;
                        option.textContent = province;
                        provinceSelect.appendChild(option);
                    });
                }

                // Populate districts
                if (regionData.districts) {
                    regionData.districts.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district;
                        option.textContent = district;
                        districtSelect.appendChild(option);
                    });
                }

                // Populate cities
                if (regionData.cities) {
                    Object.keys(regionData.cities).forEach(cityName => {
                        const option = document.createElement('option');
                        option.value = cityName;
                        option.textContent = cityName;
                        citySelect.appendChild(option);
                    });
                }

                // Populate barangays (from region if no city selected)
                if (regionData.barangays) {
                    regionData.barangays.forEach(barangay => {
                        const option = document.createElement('option');
                        option.value = barangay;
                        option.textContent = barangay;
                        barangaySelect.appendChild(option);
                    });
                }
            }
        });

        // Dynamic barangay population based on city selection
        document.getElementById('city').addEventListener('change', function() {
            const city = this.value;
            const region = document.getElementById('region').value;
            const barangaySelect = document.getElementById('barangay');

            // Reset barangay select
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

            if (city && region) {
                const regionsData = <?php echo json_encode($regions); ?>;
                const regionData = regionsData[region];

                if (regionData.cities && regionData.cities[city] && regionData.cities[city].barangays) {
                    // Populate barangays from selected city
                    regionData.cities[city].barangays.forEach(barangay => {
                        const option = document.createElement('option');
                        option.value = barangay;
                        option.textContent = barangay;
                        barangaySelect.appendChild(option);
                    });
                } else if (regionData.barangays) {
                    // Fallback to region barangays
                    regionData.barangays.forEach(barangay => {
                        const option = document.createElement('option');
                        option.value = barangay;
                        option.textContent = barangay;
                        barangaySelect.appendChild(option);
                    });
                }
            }
        });
        
        // Client-side form validation
        document.getElementById('registrationForm').addEventListener('submit', function(event) {
            const form = this;
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated');
                
                // Find first invalid field and scroll to it
                const invalidFields = form.querySelectorAll(':invalid');
                if (invalidFields.length > 0) {
                    invalidFields[0].focus();
                    invalidFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    </script>
</body>

</html>