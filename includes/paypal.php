<?php

require_once __DIR__ . '/../config/api.php';

function rwfToUsd(float $rwf): float
{
    return round($rwf * RWF_TO_USD_RATE, 2);
}

function getPayPalAccessToken(): ?string
{
    if (empty(PAYPAL_CLIENT_ID) || empty(PAYPAL_CLIENT_SECRET)) {
        return null;
    }

    $ch = curl_init(PAYPAL_API_BASE . '/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_USERPWD => PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return null;
    }

    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}

function createPayPalOrder(float $amountUsd, string $description = 'Bambe Order'): ?array
{
    $token = getPayPalAccessToken();
    if (!$token) {
        return null;
    }

    $payload = [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'amount' => [
                'currency_code' => 'USD',
                'value' => number_format($amountUsd, 2, '.', ''),
            ],
            'description' => $description,
        ]],
        'application_context' => [
            'brand_name' => 'Bambe Fashion Store',
            'shipping_preference' => 'NO_SHIPPING',
            'user_action' => 'PAY_NOW',
        ],
    ];

    $ch = curl_init(PAYPAL_API_BASE . '/v2/checkout/orders');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300 || !$response) {
        return null;
    }

    return json_decode($response, true);
}

function capturePayPalOrder(string $paypalOrderId): ?array
{
    $token = getPayPalAccessToken();
    if (!$token) {
        return null;
    }

    $ch = curl_init(PAYPAL_API_BASE . '/v2/checkout/orders/' . $paypalOrderId . '/capture');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300 || !$response) {
        return null;
    }

    return json_decode($response, true);
}

function isPayPalConfigured(): bool
{
    return !empty(PAYPAL_CLIENT_ID) && !empty(PAYPAL_CLIENT_SECRET);
}
