<?php

header('Content-Type: application/json');

$request = json_decode(file_get_contents('php://input'), true) ?? [];
$selected = $request['selected'] ?? [];

$products = [
    [
        'name' => 'Industrial Widget 100',
        'part_number' => 'IW-100',
        'size' => 'small',
        'material' => 'steel',
        'price' => 249,
    ],
    [
        'name' => 'Industrial Widget 200',
        'part_number' => 'IW-200',
        'size' => 'medium',
        'material' => 'steel',
        'price' => 329,
    ],
    [
        'name' => 'Industrial Widget 300',
        'part_number' => 'IW-300',
        'size' => 'large',
        'material' => 'steel',
        'price' => 449,
    ],
    [
        'name' => 'Industrial Widget 150',
        'part_number' => 'IW-150',
        'size' => 'small',
        'material' => 'aluminum',
        'price' => 279,
    ],
    [
        'name' => 'Industrial Widget 250',
        'part_number' => 'IW-250',
        'size' => 'medium',
        'material' => 'aluminum',
        'price' => 359,
    ],
];

$options = [
    [
        'key' => 'size',
        'label' => 'Size',
        'values' => ['small', 'medium', 'large'],
    ],
    [
        'key' => 'material',
        'label' => 'Material',
        'values' => ['steel', 'aluminum'],
    ],
];

$matchingProducts = array_filter($products, function ($product) use ($selected) {
    foreach ($selected as $key => $value) {
        if ($value === '') {
            continue;
        }

        if (!isset($product[$key]) || $product[$key] !== $value) {
            return false;
        }
    }

    return true;
});

$availableOptions = [];
foreach ($options as $option) {
    $availableValues = [];

    foreach ($matchingProducts as $product) {
        if (isset($product[$option['key']])) {
            $availableValues[] = $product[$option['key']];
        }
    }

    $availableOptions[] = [
        'key' => $option['key'],
        'label' => $option['label'],
        'values' => array_values(array_unique($availableValues)),
    ];
}

$product = count($matchingProducts) === 1
    ? reset($matchingProducts)
    : null;

echo json_encode([
    'selected' => $selected,
    'options' => $availableOptions,
    'product_count' => count($matchingProducts),
    'product' => $product,
]);