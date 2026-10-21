<?php
declare(strict_types=1);

if (!function_exists('waqt_seo_base_url')) {
function waqt_seo_base_url(): string
{
    return 'https://waqtfinance.com/';
}
}

if (!function_exists('waqt_seo_url')) {
function waqt_seo_url(string $path = ''): string
{
    $path = trim($path);

    if ($path === '' || $path === '/') {
        return waqt_seo_base_url();
    }

    if (preg_match('#^https?://#i', $path)) {
        $path = preg_replace('#^https?://www\.waqtfinance\.com/?#i', waqt_seo_base_url(), $path) ?? $path;
        $path = preg_replace('#^http://waqtfinance\.com/?#i', waqt_seo_base_url(), $path) ?? $path;

        return $path;
    }

    return waqt_seo_base_url() . ltrim($path, '/');
}
}

if (!function_exists('waqt_seo_area_served')) {
function waqt_seo_area_served(): array
{
    return [
        ['@type' => 'Country', 'name' => 'India']
    ];
}
}

if (!function_exists('waqt_seo_organization_schema')) {
function waqt_seo_organization_schema(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => ['Organization', 'FinancialService'],
        '@id' => waqt_seo_url('#organization'),
        'name' => 'Waqt Finance',
        'legalName' => 'WAQT FINANCE PVT. LTD',
        'url' => waqt_seo_url(),
        'logo' => waqt_seo_url('assets/images/logo1.webp'),
        'image' => waqt_seo_url('assets/images/logo1.webp'),
        'description' => 'Waqt Finance is a registered NBFC in India offering instant personal loans, business loans, payday loans, EDI loans, vehicle loans and loan against property services.',
        'telephone' => '+91-9217086610',
        'email' => 'customercare@waqtfinance.com',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'BSI Business Park, H-15, Sector 63',
            'addressLocality' => 'Noida',
            'addressRegion' => 'Uttar Pradesh',
            'postalCode' => '201301',
            'addressCountry' => 'IN',
        ],
        'areaServed' => waqt_seo_area_served(),
        'contactPoint' => [
            [
                '@type' => 'ContactPoint',
                'telephone' => '+91-9217086610',
                'contactType' => 'customer support',
                'areaServed' => 'IN',
                'availableLanguage' => ['English', 'Hindi'],
            ],
        ],
        'identifier' => [
            [
                '@type' => 'PropertyValue',
                'propertyID' => 'CIN',
                'value' => 'U65923DL2022PTC396001',
            ],
            [
                '@type' => 'PropertyValue',
                'propertyID' => 'NBFC Registration Number',
                'value' => 'B.10.00143',
            ],
        ],
    ];
}
}

if (!function_exists('waqt_seo_website_schema')) {
function waqt_seo_website_schema(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => waqt_seo_url('#website'),
        'url' => waqt_seo_url(),
        'name' => 'Waqt Finance',
        'publisher' => ['@id' => waqt_seo_url('#organization')],
        'inLanguage' => 'en-IN',
    ];
}
}

if (!function_exists('waqt_seo_breadcrumb_schema')) {
function waqt_seo_breadcrumb_schema(array $items): array
{
    $list = [];
    $position = 1;

    foreach ($items as $item) {
        $name = is_array($item) ? (string) ($item['name'] ?? '') : '';
        $url = is_array($item) ? (string) ($item['url'] ?? '') : '';

        if ($name === '') {
            continue;
        }

        $list[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => $name,
            'item' => waqt_seo_url($url),
        ];
    }

    return [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $list,
    ];
}
}

if (!function_exists('waqt_seo_financial_product_schema')) {
function waqt_seo_financial_product_schema(string $name, string $description, string $url, array $keywords = []): array
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'FinancialProduct',
        'name' => $name,
        'url' => waqt_seo_url($url),
        'description' => $description,
        'provider' => ['@id' => waqt_seo_url('#organization')],
        'areaServed' => waqt_seo_area_served(),
    ];

    if ($keywords !== []) {
        $schema['keywords'] = implode(', ', $keywords);
    }

    return $schema;
}
}

if (!function_exists('waqt_seo_is_list')) {
function waqt_seo_is_list(array $array): bool
{
    return $array === [] || array_keys($array) === range(0, count($array) - 1);
}
}

if (!function_exists('waqt_seo_schema_graph')) {
function waqt_seo_schema_graph(array $items): array
{
    $graph = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        if (isset($item['@graph']) && is_array($item['@graph'])) {
            foreach ($item['@graph'] as $graphItem) {
                if (is_array($graphItem)) {
                    unset($graphItem['@context']);
                    $graph[] = $graphItem;
                }
            }
            continue;
        }

        unset($item['@context']);
        $graph[] = $item;
    }

    return [
        '@context' => 'https://schema.org',
        '@graph' => $graph,
    ];
}
}

if (!function_exists('waqt_seo_json_ld_script')) {
function waqt_seo_json_ld_script(array $schema): string
{
    $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    if ($json === false || $json === '') {
        return '';
    }

    return "<script type=\"application/ld+json\">\n" . $json . "\n</script>\n";
}
}
