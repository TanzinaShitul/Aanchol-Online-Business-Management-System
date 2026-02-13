<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    $division_id = $_POST['division'];
    $district_id = $_POST['district'];
    $upazila_id = $_POST['upazila'];
    $detailed_address = $_POST['detailed_address'];

    // Password length validation (server-side)
    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error = "Email already registered!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $sql = "INSERT INTO users (name, email, password, phone, division_id, district_id, upazila_id, detailed_address, role) 
                    VALUES (:name, :email, :password, :phone, :division_id, :district_id, :upazila_id, :detailed_address, 'customer')";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':division_id', $division_id);
            $stmt->bindParam(':district_id', $district_id);
            $stmt->bindParam(':upazila_id', $upazila_id);
            $stmt->bindParam(':detailed_address', $detailed_address);

            if ($stmt->execute()) {
                // Auto login after registration
                $user_id = $conn->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'customer';

                redirect('index.php');
            } else {
                $error = "Registration failed! Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>Register - Aanchol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Create New Account</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <!-- Password field (client-side minlength validation) -->
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        minlength="6" required placeholder="At least 6 characters">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="division" class="form-label">Division (Area) *</label>
                                    <select class="form-control" id="division" name="division" required>
                                        <option value="">Select Division</option>
                                        <?php
                                        $divisions = getDivisions();
                                        foreach ($divisions as $division) {
                                            echo "<option value='{$division['id']}'>{$division['name']} ({$division['name_en']})</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="district" class="form-label">District (Jela) *</label>
                                    <select class="form-control" id="district" name="district" required disabled>
                                        <option value="">Select District</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="upazila" class="form-label">Upazila (Thana) *</label>
                                    <select class="form-control" id="upazila" name="upazila" required disabled>
                                        <option value="">Select Upazila</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="detailed_address" class="form-label">Detailed Address *</label>
                                    <textarea class="form-control" id="detailed_address" name="detailed_address"
                                        rows="2" required placeholder="House/Road/Area details"></textarea>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#">Terms & Conditions</a>
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Create Account</button>
                        </form>

                        <hr>
                        <p class="text-center">Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('division').addEventListener('change', function () {
            const divisionId = this.value;
            const districtSelect = document.getElementById('district');
            const upazilaSelect = document.getElementById('upazila');

            // Reset districts and upazilas
            districtSelect.innerHTML = '<option value="">Select District</option>';
            upazilaSelect.innerHTML = '<option value="">Select Upazila</option>';
            districtSelect.disabled = true;
            upazilaSelect.disabled = true;

            if (divisionId) {
                // Fetch districts via AJAX
                fetch(`../includes/get_locations.php?type=districts&division_id=${divisionId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(district => {
                            districtSelect.innerHTML += `<option value="${district.id}">${district.name} (${district.name_en})</option>`;
                        });
                        districtSelect.disabled = false;
                    });
            }
        });

        document.getElementById('district').addEventListener('change', function () {
            const districtId = this.value;
            const upazilaSelect = document.getElementById('upazila');

            // Reset upazilas
            upazilaSelect.innerHTML = '<option value="">Select Upazila</option>';
            upazilaSelect.disabled = true;

            if (districtId) {
                // Fetch upazilas via AJAX
                fetch(`../includes/get_locations.php?type=upazilas&district_id=${districtId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(upazila => {
                            upazilaSelect.innerHTML += `<option value="${upazila.id}">${upazila.name} (${upazila.name_en})</option>`;
                        });
                        upazilaSelect.disabled = false;
                    });
            }
        });
    </script>
</body>

</html>