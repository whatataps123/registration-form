<?php
require 'config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get registration information
$stmt = $pdo->prepare("SELECT * FROM registrations WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$registration = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conference Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style-files/dashboard.css">
</head>
<body>
    <div class="container py-5">
        <div class="dashboard-container mx-auto">
            <div class="dashboard-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h1><i class="fas fa-tachometer-alt me-2"></i> Dashboard</h1>
                    <a href="logout.php" class="btn btn-light">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </div>
            </div>
            
            <div class="dashboard-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-success">
                            <h4 class="alert-heading">Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h4>
                            <p class="mb-0">Thank you for registering for our conference.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="profile-card">
                            <h3 class="mb-4"><i class="fas fa-user me-2"></i> Personal Information</h3>

                            <?php if ($registration && !empty($registration['document_path']) && file_exists($registration['document_path'])): ?>
                                <img src="<?php echo htmlspecialchars($registration['document_path']); ?>" 
                                     alt="Formal Picture" 
                                     class="uploaded-doc">
                            <?php else: ?>
                                <div class="text-center text-muted mb-3">No formal picture uploaded.</div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <div class="info-label">Name:</div>
                                <div><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Email:</div>
                                <div><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Phone:</div>
                                <div><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Member Since:</div>
                                <div><?php echo date('F j, Y', strtotime($user['created_at'])); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($registration): ?>
                    <div class="col-md-6">
                        <div class="profile-card">
                            <h3 class="mb-4"><i class="fas fa-calendar-check me-2"></i> Registration Details</h3>
                            <div class="mb-3">
                                <div class="info-label">Number of Attendees:</div>
                                <div><?php echo htmlspecialchars($registration['num_attendees']); ?></div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Address:</div>
                                <div>
                                    <?php echo htmlspecialchars(
                                        $registration['street'] . ', ' .
                                        $registration['barangay'] . ', ' .
                                        $registration['city'] . ', ' .
                                        $registration['district'] . ', ' .
                                        $registration['province'] . ', ' .
                                        $registration['region'] . ' ' .
                                        $registration['zip_code']
                                    ); ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Registered On:</div>
                                <div><?php echo date('F j, Y, g:i a', strtotime($registration['registered_at'])); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="text-center mt-4">
                    <button class="btn btn-logout btn-lg" onclick="window.location.href='logout.php'">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
