
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">



<style>

/* RESET */


/* MAIN WRAPPER */
.loanmaster-wrapper{
    width:100%;
   
    display:flex;
    justify-content:center;
    align-items:center;
    padding: 40px 20px;
    background:linear-gradient(135deg,#0b1f57,#04152d);
    font-family:Arial,sans-serif;
}

/* CONTAINER */
.loanmaster-container{
    width:100%;
    max-width:1200px;
}

/* HEADING */
.loanmaster-heading{
    text-align:center;
    margin-bottom:28px;
}

.loanmaster-title{
    color:#fff;
    font-size: 26px;
    font-weight: 300;
    margin-bottom:8px;
}

.loanmaster-subtitle{
    color:#fff;
    font-size:28px;
    font-weight:300;
}

/* CARD */
.loanmaster-card{
    background:#fff;
    border-radius:7px;
    padding:35px 30px;
}

/* FORM */
.loanmaster-form{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:18px;
    flex-wrap:wrap;
}

/* INPUT GROUP */
.loanmaster-field{
    position:relative;
    flex:1;
    min-width:220px;
}

.loanmaster-field i{
    position:absolute;
    left:14px;
    top:50%;
    transform:translateY(-50%);
    color:#666;
    font-size:14px;
}

/* INPUT */
.loanmaster-input{
    width:100%;
    height:52px;
    border:1px solid #d7d7d7;
    border-radius:4px;
    padding:0 15px 0 42px;
    font-size:14px;
    background:#fff;
    outline:none;
}

.loanmaster-input::placeholder{
    color:#777;
}

/* BUTTON */
.loanmaster-button{
    height:52px;
    border:none;
    padding:0 34px;
    background:#ff1111;
    color:#fff;
    border-radius:4px;
    font-size:16px;
    font-weight:500;
    cursor:pointer;
    transition:0.3s;
    white-space:nowrap;
}

.loanmaster-button:hover{
    background:#df0000;
}

/* RESULT */
.loanmaster-result{
    margin-top:28px;
    text-align:center;
    display:none;
}

.loanmaster-result h3{
    font-size:32px;
    color:#0b1f57;
    margin-bottom:10px;
}

.loanmaster-result p{
    font-size:18px;
    color:#444;
}

/* MOBILE */
@media(max-width:768px){

    .loanmaster-title{
        font-size:30px;
    }

    .loanmaster-subtitle{
        font-size:22px;
    }

    .loanmaster-card{
        padding:28px 20px;
    }

    .loanmaster-form{
        flex-direction:column;
    }

    .loanmaster-field{
        width:100%;
    }

    .loanmaster-button{
        width:100%;
    }
}
@media only screen and (max-width:600px){
    .loanmaster-title{
        font-size: 20px;
    }
    .loanmaster-subtitle {
        font-size: 18px;
    }
}
</style>


<div class="loanmaster-wrapper">

    <div class="loanmaster-container">

        <!-- HEADING -->
        <div class="loanmaster-heading">

            <h2 class="loanmaster-title">
                Loan Calculator
            </h2>

            <h2 class="loanmaster-subtitle">
                How Much Do You Need?
            </h2>

        </div>

        <!-- CARD -->
        <div class="loanmaster-card">

            <form class="loanmaster-form">

                <!-- LOAN AMOUNT -->
                <div class="loanmaster-field">

                    <i class="fa-solid fa-indian-rupee-sign"></i>

                    <input 
                    type="number"
                    id="loanAmount"
                    class="loanmaster-input"
                    placeholder="Loan Amount">

                </div>

                <!-- INTEREST RATE -->
                <div class="loanmaster-field">

                    <i class="fa-solid fa-percent"></i>

                    <input 
                    type="number"
                    id="interestRate"
                    class="loanmaster-input"
                    placeholder="Interest Rate (%)">

                </div>

                <!-- TENURE -->
                <div class="loanmaster-field">

                    <i class="fa-regular fa-calendar"></i>

                    <input 
                    type="number"
                    id="loanTenure"
                    class="loanmaster-input"
                    placeholder="Loan Tenure (Years)">

                </div>

                <!-- BUTTON -->
                <button 
                type="button" 
                class="loanmaster-button"
                onclick="calculateEMI()">

                    Calculate EMI

                </button>

            </form>

            <!-- RESULT -->
            <div class="loanmaster-result" id="loanmasterResult">

                <h3 id="monthlyEmi">₹0</h3>

                <p>Monthly EMI</p>

            </div>

        </div>

    </div>

</div>

<script>

function calculateEMI(){

    let amount = document.getElementById("loanAmount").value;
    let rate = document.getElementById("interestRate").value;
    let years = document.getElementById("loanTenure").value;

    if(amount === "" || rate === "" || years === ""){
        alert("Please fill all fields");
        return;
    }

    let monthlyRate = (rate / 12) / 100;
    let months = years * 12;

    let emi = (
        amount * monthlyRate * Math.pow(1 + monthlyRate, months)
    ) / (
        Math.pow(1 + monthlyRate, months) - 1
    );

    emi = emi.toFixed(0);

    document.getElementById("monthlyEmi").innerHTML = "₹" + emi;

    document.getElementById("loanmasterResult").style.display = "block";
}

</script>

