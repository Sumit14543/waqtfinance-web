<?php
$page_title = "Customer Login | Waqt Finance Pvt Ltd";
$meta_description = "Access your Waqt Finance account. Secure login using mobile number and OTP for managing your loans.";
$meta_keywords = "customer login, waqt finance login, loan account access, OTP login";
$canonical_url = "https://www.waqtfinance.com/customer-login";

include 'includes/header.php';
include 'includes/main-header.php';
?>


<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial, Helvetica, sans-serif;
}



.wf-login-container{
width:100%;
max-width:420px;
background:#fff;
border-radius:6px;
box-shadow:0 4px 12px rgba(0,0,0,0.15);
overflow:hidden;
margin:0 auto;
}

.wf-top-bar{
background:#e31818;
padding:18px;
display:flex;
align-items:center;
gap:10px;
}

.wf-logo-circle{
width:42px;
height:42px;
border-radius:50%;
background:#444;
color:#fff;
display:flex;
align-items:center;
justify-content:center;
font-size:20px;
}

.wf-brand{
font-size:20px;
font-weight:bold;
color:#ffffff;
}

.wf-brand small{
display:block;
font-size:11px;
font-weight:normal;
}

.wf-login-body{
padding:30px;
text-align:center;
}

.wf-login-body h2{
margin-bottom:25px;
color:#444;
}

.wf-form-group{
text-align:left;
margin-bottom:15px;
}

.wf-form-group label{
font-size:14px;
font-weight:bold;
display:block;
margin-bottom:6px;
}

.wf-form-group input{
width:100%;
padding:10px;
border:1px solid #ccc;
border-radius:4px;
font-size:14px;
}

.wf-btn{
margin-top:10px;
background:#e31818 !important;
border:none;
padding:10px 25px;
border-radius:4px;
font-weight:bold;
cursor:pointer;
}

.wf-btn:hover{
background:#2367a4;
}

.wf-terms{
margin-top:15px;
font-size:12px;
color:#666;
}

.wf-terms a{
color:#0066cc;
text-decoration:none;
}

.wf-footer{
background:#000000;
color:#fff;
text-align:center;
padding:14px;
font-size:14px;
}

.wf-footer span{
color:#ffff;
font-weight:bold;
}

/* Responsive */

@media (max-width:480px){

.wf-login-body{
padding:20px;
}

.wf-brand{
font-size:18px;
}

}
.form-one{
    margin-top:40px;
    margin-bottom:40px;
}

</style>

<div class="form-one">
    <div class="wf-login-container" data-aos="zoom-in">
        <div class="wf-top-bar">
            <div class="wf-brand">
                Waqt Finance <small>Secure SaaS Portal</small>
            </div>
        </div>

        <div class="wf-login-body">
            <h2 class="fw-bold mb-2">Welcome Back</h2>
            <p class="text-muted mb-4">Please enter your registered mobile number to access your dashboard.</p>

            <form id="loginForm">
                <div class="wf-form-group">
                    <label class="wf-label">Mobile Number</label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-phone-alt wf-input-icon"></i>
                        <input type="text" id="loginMobile" class="wf-control" required placeholder="9876543210" maxlength="10">
                    </div>
                </div>

                <button type="submit" class="wf-btn wf-btn-primary w-100" id="loginBtn">
                    Verify Identity <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </form>

            <div class="mt-4">
                <p class="small text-muted">New to Waqt Finance? <a href="payday-apply.php" class="text-primary fw-bold">Apply Now</a></p>
            </div>
        </div>

        <div class="wf-footer">
            <i class="fas fa-shield-alt me-2"></i> Secured by <span>Bank-Grade Encryption</span>
        </div>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const mobile = document.getElementById('loginMobile').value;
    const btn = document.getElementById('loginBtn');

    if (!/^[6-9]\d{9}$/.test(mobile)) {
        alert("Please enter a valid 10-digit mobile number");
        return;
    }

    btn.disabled = true;
    btn.innerHTML = 'Sending OTP... <i class="fas fa-spinner fa-spin ms-2"></i>';

    const formData = new FormData();
    formData.append('mobile', mobile);
    formData.append('csrf_token', window.WAQT_CSRF_TOKEN || '');

    // We use a simplified step1 for login to set session
    fetch('api/apply/step1_identity.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.href = "mobile-otp.php";
        } else {
            throw new Error(data.message);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = 'Verify Identity <i class="fas fa-arrow-right ms-2"></i>';
        alert(err.message || "Something went wrong");
    });
});
</script>


 <?php include 'includes/footer.php' ?>
