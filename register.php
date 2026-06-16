<?php include('server.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmerLink — Register</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="styles.css">
  <style>
    body {
      background: linear-gradient(rgba(255,255,255,0.7), rgba(255,255,255,0.6)), url('photos/bg2.jpeg') center/cover fixed !important;
      color: #333;
      min-height: 100vh;
    }

    .navbar {
      background: white;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .auth-container {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      min-height: calc(100vh - 70px);
    }

    .auth-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      max-width: 500px;
      width: 100%;
      padding: 40px;
    }

    .auth-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .auth-header h1 {
      font-size: 1.8rem;
      color: #333;
      margin: 0 0 10px 0;
      font-weight: 700;
    }

    .auth-header p {
      color: #666;
      font-size: 0.95rem;
      margin: 0;
    }

    .role-selector {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 25px;
    }

    .role-option {
      display: flex;
      align-items: center;
      position: relative;
    }

    .role-option input[type="radio"] {
      display: none;
    }

    .role-label {
      flex: 1;
      padding: 15px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      cursor: pointer;
      text-align: center;
      transition: all 0.3s;
      font-weight: 600;
      color: #666;
    }

    .role-option input[type="radio"]:checked + .role-label {
      border-color: #4CAF50;
      background: #f0f7f0;
      color: #4CAF50;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    .input-group {
      margin-bottom: 20px;
      display: flex;
      flex-direction: column;
    }

    .input-group label {
      font-weight: 600;
      color: #333;
      margin-bottom: 8px;
      font-size: 0.95rem;
    }

    .input-group input,
    .input-group select {
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-size: 0.95rem;
      transition: border-color 0.3s, box-shadow 0.3s;
    }

    .input-group input:focus,
    .input-group select:focus {
      outline: none;
      border-color: #4CAF50;
      box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
    }

    .input-group button {
      padding: 12px 15px;
      background: #4CAF50;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s, transform 0.2s;
      font-family: 'Poppins', sans-serif;
    }

    .input-group button:hover {
      background: #45a049;
      transform: translateY(-2px);
    }

    .input-group button:active {
      transform: translateY(0);
    }

    .auth-footer {
      text-align: center;
      margin-top: 20px;
    }

    .auth-footer p {
      color: #666;
      font-size: 0.95rem;
      margin: 0;
    }

    .auth-footer a {
      color: #4CAF50;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s;
    }

    .auth-footer a:hover {
      color: #45a049;
      text-decoration: underline;
    }

    .errors {
      background: #ffebee;
      border-left: 4px solid #f44336;
      color: #c62828;
      padding: 12px 15px;
      border-radius: 4px;
      margin-bottom: 20px;
      font-size: 0.9rem;
    }

    .errors li {
      margin: 5px 0;
    }

    @media (max-width: 600px) {
      .auth-card {
        padding: 30px 20px;
      }

      .auth-header h1 {
        font-size: 1.5rem;
      }

      .role-selector {
        grid-template-columns: 1fr;
      }
    }

    .location-info {
      font-size: 0.85rem;
      color: #666;
      font-style: italic;
      margin-top: 5px;
    }

    #location-type {
      width: 100%;
    }
  </style>
  <script>
    const savedCounty = <?php echo json_encode($county ?? ''); ?>;
    const savedRole = <?php echo json_encode($role ?? ''); ?>;

    function buildOptions(items) {
      return items.map(item => `<option value="${item.value}">${item.label}</option>`).join('');
    }

    function setLocationOptions(options, placeholder) {
      const locationSelect = document.getElementById('location');
      locationSelect.innerHTML = `<option value="">${placeholder}</option>` + buildOptions(options);
      if (savedCounty) {
        locationSelect.value = savedCounty;
      }
    }

    function updateLocations() {
      const role = document.querySelector('input[name="role"]:checked')?.value;
      const locationTypeGroup = document.getElementById('location-type-group');
      const locationTypeSelect = document.getElementById('location-type');
      const locationLabel = document.getElementById('location-label');
      const locationInfo = document.getElementById('location-info');
      const locationSelect = document.getElementById('location');

      const kiambuAreas = [
        { value: 'Gatundu North', label: 'Gatundu North' },
        { value: 'Gatundu South', label: 'Gatundu South' },
        { value: 'Juja', label: 'Juja' },
        { value: 'Kabete', label: 'Kabete' },
        { value: 'Kiambaa', label: 'Kiambaa' },
        { value: 'Kiambu', label: 'Kiambu' },
        { value: 'Kikuyu', label: 'Kikuyu' },
        { value: 'Limuru', label: 'Limuru' },
        { value: 'Lari', label: 'Lari' },
        { value: 'Ruiru', label: 'Ruiru' },
        { value: 'Thika East', label: 'Thika East' },
        { value: 'Thika West', label: 'Thika West' },
        { value: 'Other (Kiambu)', label: 'Other (Kiambu)' }
      ];

      const otherCounties = [
        { value: 'Nairobi', label: 'Nairobi' },
        { value: 'Nakuru', label: 'Nakuru' },
        { value: 'Meru', label: 'Meru' },
        { value: 'Kisumu', label: 'Kisumu' },
        { value: 'Nyandarua', label: 'Nyandarua' },
        { value: "Murang'a", label: "Murang'a" },
        { value: 'Nyeri', label: 'Nyeri' },
        { value: 'Other', label: 'Other' }
      ];

      if (role === 'farmer') {
        locationTypeGroup.style.display = 'none';
        locationLabel.textContent = 'Subcounty (Kiambu)';
        locationInfo.textContent = 'As a farmer, select your Kiambu subcounty from the dropdown.';
        setLocationOptions(kiambuAreas, '-- Select Your Kiambu Subcounty --');
        locationSelect.disabled = false;
      } else if (role === 'buyer') {
        locationTypeGroup.style.display = 'block';
        locationLabel.textContent = 'County (Location)';
        locationInfo.textContent = 'Choose Kiambu if you are in Kiambu, or select Other counties.';

        const locationType = locationTypeSelect.value;
        if (locationType === 'Kiambu') {
          locationLabel.textContent = 'Subcounty (Kiambu)';
          locationInfo.textContent = 'As a buyer in Kiambu, choose your subcounty.';
          setLocationOptions(kiambuAreas, '-- Select Your Kiambu Subcounty --');
          locationSelect.disabled = false;
        } else if (locationType === 'Other') {
          locationLabel.textContent = 'County (Location)';
          locationInfo.textContent = 'Choose your county outside Kiambu.';
          setLocationOptions(otherCounties, '-- Select County --');
          locationSelect.disabled = false;
        } else {
          locationLabel.textContent = 'County (Location)';
          locationInfo.textContent = 'Choose Kiambu or Other counties first.';
          locationSelect.innerHTML = '<option value="">-- Select region first --</option>';
          locationSelect.disabled = true;
        }
      } else {
        locationTypeGroup.style.display = 'none';
        locationLabel.textContent = 'County (Location)';
        locationInfo.textContent = 'Please select a role first.';
        locationSelect.innerHTML = '<option value="">-- Select role first --</option>';
        locationSelect.disabled = true;
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('input[name="role"]').forEach(radio => {
        radio.addEventListener('change', () => {
          const locationTypeSelect = document.getElementById('location-type');
          locationTypeSelect.value = '';
          updateLocations();
        });
      });

      const locationTypeSelect = document.getElementById('location-type');
      locationTypeSelect.addEventListener('change', updateLocations);

      if (savedRole === 'buyer') {
        const buyerKiambuAreas = [
          'Gatundu North', 'Gatundu South', 'Juja', 'Kabete', 'Kiambaa', 'Kiambu', 'Kikuyu',
          'Limuru', 'Lari', 'Ruiru', 'Thika East', 'Thika West', 'Other (Kiambu)'
        ];

        if (savedCounty && buyerKiambuAreas.includes(savedCounty)) {
          locationTypeSelect.value = 'Kiambu';
        } else if (savedCounty) {
          locationTypeSelect.value = 'Other';
        }
      }

      updateLocations();
    });
  </script>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <h1>Join FarmerLink</h1>
      <p>Create your account to get started</p>
    </div>

    <form method="post" action="register.php">
      <?php include('errors.php'); ?>

      <div class="input-group">
        <label>I am a</label>
        <div class="role-selector">
          <div class="role-option">
            <input type="radio" id="farmer" name="role" value="farmer" <?php echo ($role == 'farmer' ? 'checked' : ''); ?>>
            <label for="farmer" class="role-label">🌾 Farmer</label>
          </div>
          <div class="role-option">
            <input type="radio" id="buyer" name="role" value="buyer" <?php echo ($role == 'buyer' ? 'checked' : ''); ?>>
            <label for="buyer" class="role-label">🛒 Buyer</label>
          </div>
        </div>
      </div>

      <div class="input-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?php echo marketplace_h($username ?? ''); ?>" required>
      </div>

      <div class="input-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo marketplace_h($email ?? ''); ?>" required>
      </div>

      <div class="input-group">
        <label for="phone">Phone Number</label>
        <input type="text" id="phone" name="phone" value="<?php echo marketplace_h($phone ?? ''); ?>" required>
      </div>

      <div class="input-group" id="location-type-group" style="display:none;">
        <label for="location-type">Buyer location type</label>
        <select id="location-type" name="location_type">
          <option value="">-- Select region --</option>
          <option value="Kiambu">Kiambu</option>
          <option value="Other">Other counties</option>
        </select>
        <div class="location-info">Choose whether you are based in Kiambu or another county.</div>
      </div>

      <div class="input-group">
        <label for="location" id="location-label">County (Location)</label>
        <select id="location" name="county" required>
          <option value="">-- Select County --</option>
        </select>
        <div class="location-info" id="location-info"></div>
      </div>

      <div class="input-group">
        <label for="password_1">Password</label>
        <input type="password" id="password_1" name="password_1" required>
      </div>

      <div class="input-group">
        <label for="password_2">Confirm Password</label>
        <input type="password" id="password_2" name="password_2" required>
      </div>

      <div class="input-group">
        <button type="submit" name="reg_user">Create Account</button>
      </div>
    </form>

    <div class="auth-footer">
      <p>Already have an account? <a href="login.php">Sign in here</a></p>
    </div>
  </div>
</div>

</body>
</html>
