<?php

require_once __DIR__ . '/../config/api.php';

function getProductCatalogForAI(PDO $db): string
{
    $stmt = $db->query(
        'SELECT p.name, p.price, p.description, p.stock, c.name AS category
         FROM products p
         JOIN categories c ON p.category_id = c.id
         WHERE p.stock > 0
         ORDER BY p.featured DESC, p.name ASC
         LIMIT 30'
    );
    $products = $stmt->fetchAll();

    if (empty($products)) {
        return 'No products currently in stock.';
    }

    $lines = [];
    foreach ($products as $p) {
        $lines[] = sprintf(
            '- %s (%s) — RWF %s — %s',
            $p['name'],
            $p['category'],
            number_format((float) $p['price'], 0),
            mb_substr($p['description'], 0, 120)
        );
    }

    return implode("\n", $lines);
}

function buildChatSystemPrompt(PDO $db): string
{
    $catalog = getProductCatalogForAI($db);

    return <<<PROMPT
You are Bambe Assistant, the friendly AI shopping helper for Bambe Fashion Store — an online clothes and shoes shop based in Kigali, Rwanda.

Your role:
- Help customers find products, answer questions about sizing, delivery, and returns
- Recommend products from the catalog below based on customer needs
- Be warm, concise, and professional
- Prices are in Rwandan Francs (RWF)
- Free delivery in Kigali on orders over RWF 50,000; otherwise RWF 3,000 delivery fee
- 7-day return policy on unworn items with tags
- Payment options: Cash on Delivery or PayPal

Current product catalog:
{$catalog}

When recommending products, mention the product name and price. If asked about something not in the catalog, suggest browsing products.php or similar categories. Keep responses under 150 words unless listing multiple products.
PROMPT;
}

function callOpenAIChat(array $messages): ?string
{
    if (empty(OPENAI_API_KEY)) {
        return null;
    }

    $payload = [
        'model' => OPENAI_MODEL,
        'messages' => $messages,
        'max_tokens' => 400,
        'temperature' => 0.7,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY,
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return null;
    }

    $data = json_decode($response, true);
    return $data['choices'][0]['message']['content'] ?? null;
}

function getFallbackChatResponse(PDO $db, string $userMessage): string
{
    $message = strtolower($userMessage);

    if (preg_match('/hello|hi|hey|muraho|amakuru/', $message)) {
        return "Muraho! Welcome to Bambe Fashion Store 👋 I'm your shopping assistant. I can help you find clothes, shoes, check delivery info, or recommend products. What are you looking for today?";
    }

    if (preg_match('/deliver|shipping|ship|kigali/', $message)) {
        return "We offer fast delivery across Rwanda! 🚚 Free delivery in Kigali on orders over RWF 50,000. For smaller orders, delivery is RWF 3,000. Nationwide shipping is also available.";
    }

    if (preg_match('/return|refund|exchange/', $message)) {
        return "Bambe offers a 7-day return policy on unworn items with original tags attached. Contact us at info@bambe.rw or +250 788 000 000 for return assistance.";
    }

    if (preg_match('/pay|payment|paypal|money/', $message)) {
        return "We accept Cash on Delivery (pay when your order arrives) and PayPal for secure online payments. You can choose your payment method at checkout.";
    }

    if (preg_match('/price|cheap|budget|afford/', $message)) {
        $stmt = $db->query('SELECT name, price FROM products WHERE stock > 0 ORDER BY price ASC LIMIT 3');
        $products = $stmt->fetchAll();
        $list = array_map(fn($p) => $p['name'] . ' (RWF ' . number_format((float) $p['price'], 0) . ')', $products);
        return "Here are our most affordable picks:\n• " . implode("\n• ", $list) . "\n\nBrowse all products at our Shop page!";
    }

    $category = null;
    if (preg_match('/shoe|sneaker|boot|sandal|footwear/', $message)) {
        $category = 'shoes';
    } elseif (preg_match('/cloth|dress|shirt|jacket|pant|hoodie|fashion|wear/', $message)) {
        $category = 'clothes';
    }

    if ($category) {
        $stmt = $db->prepare(
            'SELECT p.name, p.price, p.description FROM products p
             JOIN categories c ON p.category_id = c.id
             WHERE c.slug = ? AND p.stock > 0
             ORDER BY p.featured DESC LIMIT 4'
        );
        $stmt->execute([$category]);
        $products = $stmt->fetchAll();

        if (!empty($products)) {
            $lines = ["Great choice! Here are popular {$category} items:"];
            foreach ($products as $p) {
                $lines[] = "• {$p['name']} — RWF " . number_format((float) $p['price'], 0);
            }
            $lines[] = "Visit our Shop to add them to your cart!";
            return implode("\n", $lines);
        }
    }

    if (preg_match('/recommend|suggest|popular|best|featured/', $message)) {
        $stmt = $db->query('SELECT name, price FROM products WHERE featured = 1 AND stock > 0 LIMIT 4');
        $products = $stmt->fetchAll();
        $lines = ["Our featured picks this season:"];
        foreach ($products as $p) {
            $lines[] = "• {$p['name']} — RWF " . number_format((float) $p['price'], 0);
        }
        return implode("\n", $lines);
    }

    $searchTerms = array_filter(preg_split('/\s+/', $message), fn($w) => strlen($w) > 3);
    if (!empty($searchTerms)) {
        $term = '%' . $searchTerms[0] . '%';
        $stmt = $db->prepare(
            'SELECT name, price FROM products WHERE (name LIKE ? OR description LIKE ?) AND stock > 0 LIMIT 3'
        );
        $stmt->execute([$term, $term]);
        $products = $stmt->fetchAll();

        if (!empty($products)) {
            $lines = ["I found these products matching your query:"];
            foreach ($products as $p) {
                $lines[] = "• {$p['name']} — RWF " . number_format((float) $p['price'], 0);
            }
            return implode("\n", $lines);
        }
    }

    return "Thanks for your message! I can help with product recommendations, delivery info, returns, and payment options. Try asking about 'shoes', 'dresses', 'delivery', or 'payment'. For full AI responses, configure an OpenAI API key.";
}

function getChatbotResponse(PDO $db, string $userMessage, array $history = []): array
{
    $userMessage = trim($userMessage);
    if ($userMessage === '') {
        return ['reply' => 'Please type a message so I can help you.', 'source' => 'system'];
    }

    if (strlen($userMessage) > 500) {
        return ['reply' => 'Your message is too long. Please keep it under 500 characters.', 'source' => 'system'];
    }

    if (!empty(OPENAI_API_KEY)) {
        $messages = [['role' => 'system', 'content' => buildChatSystemPrompt($db)]];
        foreach (array_slice($history, -6) as $entry) {
            if (!empty($entry['role']) && !empty($entry['content'])) {
                $messages[] = ['role' => $entry['role'], 'content' => $entry['content']];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $aiReply = callOpenAIChat($messages);
        if ($aiReply) {
            return ['reply' => trim($aiReply), 'source' => 'openai'];
        }
    }

    return ['reply' => getFallbackChatResponse($db, $userMessage), 'source' => 'fallback'];
}

function isAIConfigured(): bool
{
    return !empty(OPENAI_API_KEY);
}
