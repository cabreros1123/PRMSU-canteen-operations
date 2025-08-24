<?php
session_start();
if (!isset($_SESSION["admin_name"]) || !isset($_SESSION["user"])) {
    header("Location: 404.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support & User Guide</title>
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        .support-container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px #1976d220;
            padding: 32px 24px;
        }
        .support-container h1 {
            color: #1976d2;
            margin-bottom: 12px;
        }
        .support-container h2 {
            color: #388e3c;
            margin-top: 28px;
        }
        .support-container ul {
            margin-left: 18px;
        }
        .support-link {
            color: #1976d2;
            text-decoration: underline;
            cursor: pointer;
        }
        .support-section {
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
        <div id="sidebar-container">
            <?php require_once "sidebar.php"; ?>
        </div>
        <div id="header-container">
            <?php require_once "header.php"; ?>
        </div>
    <div class="support-container">
        <h1><span class="material-symbols-rounded" style="vertical-align:middle;">help</span> System Support & User Guide</h1>
        <p>Welcome to the Food Safety and Payment Delivery System! This guide will help you understand and use all the features of the system.</p>
        
        <div class="support-section">
            <h2>1. Dashboard & Navigation</h2>
            <ul>
                <li><b>Home:</b> Overview of your system and quick access to main features.</li>
                <li><b>Canteen:</b> Manage canteen lists and food safety records. <a href="cantine.php" class="support-link">Go to Canteen List</a></li>
                <li><b>Food Safety:</b> View, inspect, and rate canteens for food safety compliance. <a href="food_safety.php" class="support-link">Go to Food Safety</a></li>
                <li><b>Ledger:</b> Monitor payment deliveries and billing. <a href="cantine_bills.php" class="support-link">Go to OR Delivery</a> | <a href="bills_payments.php" class="support-link">Go to Payments Monitoring</a></li>
            </ul>
        </div>
        
        <div class="support-section">
            <h2>2. Food Safety Inspections</h2>
            <ul>
                <li>Click <b>Food Safety</b> in the sidebar to see all canteens and their compliance status.</li>
                <li>Click a canteen box to start or continue an inspection.</li>
                <li>For each obligation, select the status: <span style="color:#43a047;">Complied</span>, <span style="color:#fbc02d;">Pending</span>, or <span style="color:#d32f2f;">Not Complied</span>.</li>
                <li>Click <b>Finish Inspection</b> to complete and rate the canteen.</li>
                <li>Rate using emoji faces for Food Quality, Food Safety, Hygiene, and Service Quality.</li>
                <li>Past inspections can be viewed and filtered by number using the selector beside "Past Inspections".</li>
            </ul>
        </div>
        
        <div class="support-section">
            <h2>3. Billing & Payments</h2>
            <ul>
                <li>Go to <b>OR Delivery</b> to view and manage canteen billing records.</li>
                <li>Go to <b>Payments Monitoring</b> to track payment status and history.</li>
            </ul>
        </div>
        
        <div class="support-section">
            <h2>4. Announcements & Messenger</h2>
            <ul>
                <li>Send announcements to all canteens using the <span class="material-symbols-rounded" style="vertical-align:middle;">campaign</span> button.</li>
                <li>Use the messenger at the bottom of the canteen page to send messages and attach files.</li>
            </ul>
        </div>
        
        <div class="support-section">
            <h2>5. Profile & Sign Out</h2>
            <ul>
                <li>Click your name or profile icon in the sidebar to view your admin profile.</li>
                <li>Click <b>Sign Out</b> to securely log out of the system.</li>
            </ul>
        </div>
        
        <div class="support-section">
            <h2>Need More Help?</h2>
            <ul>
                <li>Contact your system administrator for further assistance.</li>
            </ul>
        </div>
    </div>
    <!-- Chatbase AI Support Widget -->
<script>
(function(){
    if(!window.chatbase||window.chatbase("getState")!=="initialized"){
        window.chatbase=(...arguments)=>{
            if(!window.chatbase.q){window.chatbase.q=[]}
            window.chatbase.q.push(arguments)
        };
        window.chatbase=new Proxy(window.chatbase,{
            get(target,prop){
                if(prop==="q"){return target.q}
                return(...args)=>target(prop,...args)
            }
        })
    }
    const onLoad=function(){
        const script=document.createElement("script");
        script.src="https://www.chatbase.co/embed.min.js";
        script.id="qHKSU-YoRsQYaMQ1dWVwc";
        script.domain="www.chatbase.co";
        document.body.appendChild(script)
    };
    if(document.readyState==="complete"){onLoad()}
    else{window.addEventListener("load",onLoad)}
})();
</script>
</body>
</html>