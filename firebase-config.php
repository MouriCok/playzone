<?php
header("Content-Type: application/json");

// Load the .env file
$dotenv = parse_ini_file(__DIR__ . '/.env');

// Output Firebase configuration as JSON
echo json_encode([
    "apiKey" => $dotenv['FIREBASE_API_KEY'],
    "authDomain" => $dotenv['FIREBASE_AUTH_DOMAIN'],
    "projectId" => $dotenv['FIREBASE_PROJECT_ID'],
    "storageBucket" => $dotenv['FIREBASE_STORAGE_BUCKET'],
    "messagingSenderId" => $dotenv['FIREBASE_MESSAGING_SENDER_ID'],
    "appId" => $dotenv['FIREBASE_APP_ID'],
    "measurementId" => $dotenv['FIREBASE_MEASUREMENT_ID'],
]);
