<?php
// डेटाबेस कनेक्शन और अन्य सेटिंग्स मान लें कि पहले से मौजूद हैं
// $conn = mysqli_connect("localhost", "username", "password", "database");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $utr_number = mysqli_real_escape_string($conn, $_POST['utr_number']);
    $user_id = $_SESSION['user_id']; // यूजर आईडी

    // 1. जाँच करें कि क्या यह UTR पहले ही इस्तेमाल हो चुका है
    $check_query = "SELECT * FROM payments WHERE utr_number = '$utr_number'";
    $result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(["status" => "error", "message" => "यह UTR पहले ही इस्तेमाल हो चुका है!"]);
        exit();
    }

    // 2. पेमेंट गेटवे या बैंक API से भुगतान का समय (Timestamp) प्राप्त करें
    // मान लेते हैं कि आपके पास $payment_time है जो डेटाबेस या API से आया है (YYYY-MM-DD HH:MM:SS फॉर्मेट में)
    // या आप वर्तमान समय के मुकाबले चेक कर रहे हैं:
    
    $payment_timestamp = strtotime($fetched_payment_time); // भुगतान का समय
    $current_time = time(); // वर्तमान समय
    
    $time_difference = $current_time - $payment_timestamp; // सेकंड्स में अंतर
    
    // 2 मिनट = 120 सेकंड
    if ($time_difference > 120) {
        echo json_encode(["status" => "error", "message" => "यह UTR एक्सपायर हो चुका है! भुगतान केवल 2 मिनट के भीतर का होना चाहिए।"]);
        exit();
    } else {
        // यदि समय 2 मिनट के अंदर है, तो भुगतान स्वीकार करें और बैलेंस जोड़ें
        $insert_query = "INSERT INTO payments (user_id, utr_number, status, created_at) VALUES ('$user_id', '$utr_number', 'Success', NOW())";
        if (mysqli_query($conn, $insert_query)) {
            // यूजर के वॉलेट में पैसे जोड़ने का कोड यहाँ आएगा
            echo json_encode(["status" => "success", "message" => "भुगतान सफलतापूर्वक स्वीकार कर लिया गया है!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "डेटाबेस एरर!"]);
        }
    }
}
?>
