<?php
header("Content-Type: application/json");

// XPay API Credentials (Replace with your actual credentials)
$api_key = "";
$account_id = "";
$hmac_secret = "";


// Read the raw POST input
$data = file_get_contents("php://input");


// Define the payload
$payload = [
    "amount" => 10000, // Amount in cents
    "currency" => "PKR",
    "payment_method_types" => "card",
    "customer" => [
        "name" => "John Doe",
        "email" => "johndoe@example.com",
        "phone" => "1234567890"
    ],
    "shipping" => [
        "address1" => "123 Street",
        "city" => "Lahore",
        "country" => "Pakistan",
        "province" => "Punjab",
        "zip" => "54000"
    ],
    "metadata" => [
        "order_reference" => "ORD131212310",
        "rule_attribute" => "some_rule",
        "continue_payment" => true
    ],
    "gateway_instance_id" => "YOUR_GATEWAY_INSTANCE_ID",
    "capture_method" => "manual"
];


$json_payload = json_encode($payload);

// Generate the HMAC Signature
$signature = hash_hmac('sha256', $json_payload, $hmac_secret);

// API Request to XPay
$url = "https://xstak-pay-stg.xstak.com/public/v1/payment/intent";

$headers = [
    "x-api-key: $api_key",
    "x-account-id: $account_id",
    "x-signature: $signature",
    "Content-Type: application/json"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode(["error" => "cURL Error: $error"]);
    exit;
}

// Convert response to JSON
$response_data = json_decode($response, true);

// Check if client_secret exists in response
if (isset($response_data['data']['pi_client_secret']) && isset($response_data['data']["encryptionKey"])) {
    echo json_encode([
        "clientSecret" => $response_data['data']['pi_client_secret'],
        "encryptionKey" => $response_data['data']["encryptionKey"]
    ]);
} else {
    echo json_encode(["error" => "Invalid API response", "details" => $response_data]);
}
