<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediBridge - Your Trusted Healthcare Partner</title>
  <meta name="description" content="Bridging the gap between pharmacies and communities with accessible, affordable healthcare solutions">
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  
  <!-- Custom CSS -->
  <style>

/* Modal Custom Styles */
#loginModal .modal-content {
  border-radius: 12px;
  overflow: hidden;
  border: none;
  box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

#loginModal .modal-header {
  padding: 0;
  border-bottom: none;
}

#loginModal .nav-tabs {
  border-bottom: none;
}

#loginModal .nav-tabs .nav-link {
  padding: 12px;
  border: none;
  border-radius: 0;
  color: #6c757d;
  background-color: #f8f9fa;
  transition: all 0.3s ease;
}

#loginModal .nav-tabs .nav-link.active {
  color: #2a7fba;
  background-color: white;
  border-bottom: 3px solid #2a7fba;
}

#loginModal .nav-tabs .nav-link:hover:not(.active) {
  background-color: rgba(42, 127, 186, 0.1);
}

#loginModal .btn-close {
  position: absolute;
  right: 15px;
  top: 15px;
  z-index: 10;
}

#loginModal .form-control, 
#loginModal .form-select {
  border-radius: 8px;
  padding: 10px 15px;
  border: 1px solid #dee2e6;
  transition: all 0.3s ease;
}

#loginModal .form-control:focus, 
#loginModal .form-select:focus {
  border-color: #2a7fba;
  box-shadow: 0 0 0 0.25rem rgba(42, 127, 186, 0.25);
}

#loginModal .input-group-text {
  background-color: #f8f9fa;
  border-color: #dee2e6;
}

#loginModal .btn {
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
}

#loginModal .btn-primary {
  background-color: #2a7fba;
  border-color: #2a7fba;
}

#loginModal .btn-primary:hover {
  background-color: #1f6a9a;
  border-color: #1f6a9a;
}

#loginModal .btn-success {
  background-color: #3bb77e;
  border-color: #3bb77e;
}

#loginModal .btn-success:hover {
  background-color: #2fa36b;
  border-color: #2fa36b;
}

#loginModal .toggle-password {
  border-top-right-radius: 8px;
  border-bottom-right-radius: 8px;
}

#loginModal .invalid-feedback {
  font-size: 0.85rem;
}

#loginModal .text-muted {
  font-size: 0.85rem;
}

#loginModal .illustration-img {
  max-height: 200px;
  margin-bottom: 20px;
}

@media (max-width: 991.98px) {
  #loginModal .modal-dialog {
    max-width: 500px;
  }
  
  #loginModal .illustration-col {
    display: none;
  }
}
  </style>
</head>
<body>
<!-- Login/Signup Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content overflow-hidden">
      <!-- Modal Header with Tabs -->
      <div class="modal-header p-0 border-0">
        <ul class="nav nav-tabs w-100" id="authTabs" role="tablist">
          <li class="nav-item w-50" role="presentation">
            <button class="nav-link active w-100 py-3 fs-5 fw-bold" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-tab-pane" type="button" role="tab">
              <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>
          </li>
          <li class="nav-item w-50" role="presentation">
            <button class="nav-link w-100 py-3 fs-5 fw-bold" id="signup-tab" data-bs-toggle="tab" data-bs-target="#signup-tab-pane" type="button" role="tab">
              <i class="fas fa-user-plus me-2"></i> Sign Up
            </button>
          </li>
        </ul>
        <button type="button" class="btn-close position-absolute end-0 top-0 m-2" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body p-4 pt-3">
        <div class="tab-content" id="authTabsContent">
          <!-- Login Tab -->
          <div class="tab-pane fade show active" id="login-tab-pane" role="tabpanel" tabindex="0">
            <div class="row">
              <div class="col-lg-6">
                <form id="loginForm" novalidate>
                  <div class="mb-4">
                    <label for="loginEmail" class="form-label fw-medium">Email Address</label>
                    <div class="input-group">
                      <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                      <input type="email" class="form-control py-2" id="loginEmail" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="invalid-feedback">Please enter a valid email</div>
                  </div>
                  
                  <div class="mb-4">
                    <label for="loginPassword" class="form-label fw-medium">Password</label>
                    <div class="input-group">
                      <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                      <input type="password" class="form-control py-2" id="loginPassword" name="password" 
                            placeholder="Enter your password" required
                            maxlength="10"
                            onpaste="return false;"
                            oncopy="return false;"
                            oncut="return false;">
                      <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="fas fa-eye-slash"></i>
                      </button>
                    </div>
                    <div class="invalid-feedback">Please enter your password</div>
                    <small class="text-muted">Password must be 6-10 characters</small>
                  </div>
                  
                  <button type="submit" class="btn btn-primary w-100 py-2 mb-3 fw-medium">
                    Login to your account
                  </button>
                </form>
              </div>
              
              <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-light rounded">
                <div class="text-center p-4">
                  <img src="https://cdn-icons-png.flaticon.com/512/3209/3209260.png" alt="Login" class="img-fluid mb-3" style="max-height: 180px;">
                  <h5 class="fw-bold mb-2">Welcome Back!</h5>
                  <p class="text-muted">Login to access your personalized dashboard and continue your healthcare journey.</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Signup Tab -->
          <div class="tab-pane fade" id="signup-tab-pane" role="tabpanel" tabindex="0">
            <div class="row">
              <div class="col-lg-6">
                <form id="signupForm" novalidate>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label fw-medium">First Name</label>
                      <input type="text" name="fname" id="fname" class="form-control py-2" placeholder="Enter first name" required>
                      <div class="invalid-feedback">First name is required</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Middle Name</label>
                      <input type="text" name="mname" id="mname" class="form-control py-2" placeholder="Enter middle name">
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label fw-medium">Last Name</label>
                      <input type="text" name="lname" id="lname" class="form-control py-2" placeholder="Enter last name" required>
                      <div class="invalid-feedback">Last name is required</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label fw-medium">Phone Number</label>
                      <input type="text" name="phone" id="phone" class="form-control py-2" placeholder="10-digit number" required maxlength="10">
                      <div class="invalid-feedback">Phone number is required</div>
                    </div>
                    
                    <div class="col-12">
                      <label class="form-label fw-medium">Email</label>
                      <input type="email" name="email" id="signupEmail" class="form-control py-2" placeholder="Enter your email" required>
                      <div class="invalid-feedback">Email is required</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label fw-medium">Date of Birth</label>
                      <input type="date" name="dob" id="dob" class="form-control py-2" required>
                      <div class="invalid-feedback">Date of birth is required</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label fw-medium">Gender</label>
                      <select id="gender" name="gender" class="form-select py-2" required>
                        <option value="" disabled selected>Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                      </select>
                      <div class="invalid-feedback">Gender is required</div>
                    </div>
                    
                    <div class="col-12">
                      <label class="form-label fw-medium">Full Address</label>
                      <input type="text" name="address" id="address" class="form-control py-2" placeholder="Enter your full address" required>
                      <div class="invalid-feedback">Address is required</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label fw-medium">State</label>
                      <input type="text" name="state" id="state" class="form-control py-2" placeholder="Enter your state" required>
                      <div class="invalid-feedback">State is required</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label fw-medium">District</label>
                      <input type="text" name="district" id="district" class="form-control py-2" placeholder="Enter your district" required>
                      <div class="invalid-feedback">District is required</div>
                    </div>
                    
                    <div class="col-md-8">
                      <label class="form-label fw-medium">Pincode</label>
                      <input type="text" name="pincode" id="pincode" class="form-control py-2" placeholder="6-digit pincode" required maxlength="6">
                      <div class="invalid-feedback">Pincode is required</div>
                      <div id="pincodeResult" class="small mt-1"></div>
                    </div>
                    
                    <div class="col-md-4 d-flex align-items-end">
                      <!-- Smaller Check Availability button -->
                      <button class="btn btn-primary w-100 py-1 btn-sm" type="button" id="checkPincodeBtn">Check Availability</button>
                    </div>
                    
                    <div class="col-12">
                      <label class="form-label">Landmark</label>
                      <input type="text" name="landmark" id="landmark" class="form-control py-2" placeholder="Enter a landmark">
                    </div>
                    
                    <div class="col-12 position-relative">
                      <label class="form-label fw-medium">Password</label>
                      <div class="input-group">
                        <input type="password" name="password" id="signupPassword" class="form-control py-2" 
                              placeholder="Create password" required
                              maxlength="10"
                              onpaste="return false;"
                              oncopy="return false;"
                              oncut="return false;"
                              pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,10}$">
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                          <i class="fas fa-eye-slash"></i>
                        </button>
                      </div>
                      <div id="passwordHelp" class="form-text text-danger small ps-2 pt-1 d-none">
                        Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.
                      </div>
                    </div>
                    
                    <div class="col-12 position-relative">
                      <label class="form-label fw-medium">Confirm Password</label>
                      <div class="input-group">
                        <input type="password" name="confirm_password" id="confirmPassword" class="form-control py-2" 
                              placeholder="Confirm password" required
                              maxlength="10"
                              onpaste="return false;"
                              oncopy="return false;"
                              oncut="return false;">
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                          <i class="fas fa-eye-slash"></i>
                        </button>
                      </div>
                      <div class="invalid-feedback">Passwords do not match</div>
                    </div>
                    
                    <div class="col-12 mt-3">
                      <button type="submit" class="btn btn-success w-100 py-2 fw-medium">
                        Create your account
                      </button>
                    </div>
                  </div>
                </form>
              </div>
              
              <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-light rounded">
                <!-- Shifted logo upwards by reducing padding -->
                <div class="text-center p-3">
                  <img src="images/logo.png" alt="Signup" class="img-fluid mb-2" style="max-height: 150px;">
                  <h5 class="fw-bold mb-2">Join MediBridge Today</h5>
                  <p class="text-muted">Create your account to access personalized healthcare services and exclusive benefits.</p>
                  <ul class="list-unstyled text-start mt-3">
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Fast medicine delivery</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Online doctor consultations</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Prescription management</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>