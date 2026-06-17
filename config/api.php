<?php

// API keys and third-party service configuration (set via environment variables)
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('OPENAI_MODEL', getenv('OPENAI_MODEL') ?: 'gpt-4o-mini');

define('PAYPAL_CLIENT_ID', getenv('PAYPAL_CLIENT_ID') ?: '');
define('PAYPAL_CLIENT_SECRET', getenv('PAYPAL_CLIENT_SECRET') ?: '');
define('PAYPAL_MODE', getenv('PAYPAL_MODE') ?: 'sandbox'); // sandbox | live

// RWF to USD conversion for PayPal (PayPal does not support RWF)
define('RWF_TO_USD_RATE', (float) (getenv('RWF_TO_USD_RATE') ?: 0.00075));

define('PAYPAL_API_BASE', PAYPAL_MODE === 'live'
    ? 'https://api-m.paypal.com'
    : 'https://api-m.sandbox.paypal.com');
