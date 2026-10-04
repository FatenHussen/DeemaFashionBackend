<?php

/**
 * Generate a Postman v2.1 collection from `php artisan route:list --json`.
 * Bodies are inferred from FormRequest validation rules when available.
 *
 * Usage: php scripts/generate_postman_collection.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$routesJson = shell_exec('php artisan route:list --path=api --json');
if (!$routesJson) {
    fwrite(STDERR, "Failed to get routes from artisan.\n");
    exit(1);
}

$routes = json_decode($routesJson, true);
if (!is_array($routes)) {
    fwrite(STDERR, "Invalid route JSON.\n");
    exit(1);
}

function normalizeMethod(string $method): string
{
    if (str_contains($method, '|')) {
        $parts = explode('|', $method);

        return match (true) {
            in_array('POST', $parts, true) => 'POST',
            in_array('PUT', $parts, true) => 'PUT',
            in_array('PATCH', $parts, true) => 'PATCH',
            in_array('DELETE', $parts, true) => 'DELETE',
            default => 'GET',
        };
    }

    return $method;
}

function detectAuthGuard(array $middleware): ?string
{
    foreach ($middleware as $m) {
        if (preg_match('/^auth:(.+)$/', $m, $matches)) {
            return pickGuard(explode(',', $matches[1]));
        }

        if (preg_match('/Authenticate:(.+)$/', $m, $matches)) {
            return pickGuard(explode(',', $matches[1]));
        }
    }

    return null;
}

function pickGuard(array $guards): ?string
{
    if (in_array('admin', $guards, true)) {
        return 'admin';
    }
    if (in_array('user', $guards, true)) {
        return 'user';
    }
    if (in_array('driver', $guards, true)) {
        return 'driver';
    }
    if (in_array('vendor-user', $guards, true)) {
        return 'vendor';
    }
    if (in_array('sanctum', $guards, true)) {
        return 'user';
    }

    return null;
}

function humanizeRouteName(string $method, string $uri, ?string $routeName): string
{
    if ($routeName) {
        $parts = explode('.', $routeName);
        $last = end($parts);
        $map = [
            'index' => 'List',
            'store' => 'Create',
            'show' => 'Show',
            'update' => 'Update',
            'destroy' => 'Delete',
            'create' => 'Create Form',
            'edit' => 'Edit Form',
        ];
        if (isset($map[$last])) {
            $resource = count($parts) > 1 ? str_replace('-', ' ', $parts[count($parts) - 2]) : 'Resource';

            return ucwords($map[$last] . ' ' . $resource);
        }
    }

    $path = preg_replace('#^api/#', '', $uri);
    $path = preg_replace('#\{[^}]+\}#', '{id}', $path);
    $label = str_replace(['/', '-', '_'], ' ', $path);

    return $method . ' ' . ucwords(trim($label));
}

function getControllerRequestProperty(string $controller, string $property): ?string
{
    if (!class_exists($controller)) {
        return null;
    }

    try {
        $instance = app($controller);
        $reflection = new ReflectionClass($instance);

        while ($reflection instanceof ReflectionClass) {
            if ($reflection->hasProperty($property)) {
                $prop = $reflection->getProperty($property);
                $prop->setAccessible(true);
                $value = $prop->getValue($instance);

                if (is_string($value) && class_exists($value)) {
                    return $value;
                }
            }

            $reflection = $reflection->getParentClass();
        }
    } catch (Throwable) {
        return null;
    }

    return null;
}

function resolveFormRequestClass(?string $action, string $httpMethod = 'GET'): ?string
{
    if (!$action || !str_contains($action, '@')) {
        return null;
    }

    [$controller, $method] = explode('@', $action, 2);

    if (!class_exists($controller) || !method_exists($controller, $method)) {
        return null;
    }

    try {
        $reflection = new ReflectionMethod($controller, $method);
    } catch (ReflectionException) {
        return null;
    }

    foreach ($reflection->getParameters() as $parameter) {
        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            continue;
        }

        $className = $type->getName();
        if (
            class_exists($className)
            && is_subclass_of($className, Illuminate\Foundation\Http\FormRequest::class)
        ) {
            return $className;
        }
    }

    $propertyMap = [
        'store' => 'createRequest',
        'update' => 'updateRequest',
    ];

    if (isset($propertyMap[$method])) {
        $fromProperty = getControllerRequestProperty($controller, $propertyMap[$method]);
        if ($fromProperty !== null) {
            return $fromProperty;
        }
    }

    return null;
}

function getValidationRules(?string $formRequestClass): ?array
{
    if (!$formRequestClass || !class_exists($formRequestClass)) {
        return null;
    }

    try {
        $reflection = new ReflectionClass($formRequestClass);
        if (!$reflection->isSubclassOf(Illuminate\Foundation\Http\FormRequest::class)) {
            return null;
        }

        $instance = $reflection->newInstanceWithoutConstructor();

        return $instance->rules();
    } catch (Throwable) {
        return null;
    }
}

function normalizeRuleSet(mixed $ruleSet): array
{
    if (is_string($ruleSet)) {
        return array_values(array_filter(explode('|', $ruleSet)));
    }

    if (!is_array($ruleSet)) {
        return [];
    }

    $normalized = [];
    foreach ($ruleSet as $rule) {
        if (is_string($rule)) {
            foreach (explode('|', $rule) as $part) {
                $normalized[] = $part;
            }
        } else {
            $normalized[] = $rule;
        }
    }

    return $normalized;
}

function ruleRequiresField(array $rules): bool
{
    foreach ($rules as $rule) {
        if (!is_string($rule)) {
            continue;
        }

        if ($rule === 'required' || str_starts_with($rule, 'required_')) {
            return true;
        }
    }

    return false;
}

function ruleIsNullable(array $rules): bool
{
    foreach ($rules as $rule) {
        if (is_string($rule) && $rule === 'nullable') {
            return true;
        }
    }

    return false;
}

function sampleScalarValue(string $field, array $rules): mixed
{
    $ruleStrings = array_values(array_filter($rules, 'is_string'));

    foreach ($ruleStrings as $rule) {
        if (str_starts_with($rule, 'in:')) {
            $options = explode(',', substr($rule, 3));

            return is_numeric($options[0]) ? (int) $options[0] : $options[0];
        }

        if (str_starts_with($rule, 'regex:')) {
            if (str_contains($field, 'phone')) {
                return '0991234567';
            }
        }
    }

    if (in_array('boolean', $ruleStrings, true)) {
        return true;
    }

    if (in_array('integer', $ruleStrings, true) || in_array('numeric', $ruleStrings, true)) {
        return 1;
    }

    if (in_array('array', $ruleStrings, true)) {
        return [];
    }

    if (in_array('email', $ruleStrings, true) || str_contains($field, 'email')) {
        return 'test@example.com';
    }

    if (str_contains($field, 'phone')) {
        return '0991234567';
    }

    if (str_contains($field, 'password')) {
        return 'Password1!';
    }

    if (in_array($field, ['otp', 'code', 'verification_code'], true)) {
        return '123456';
    }

    if (str_contains($field, 'token')) {
        return 'sample_fcm_token';
    }

    if (str_ends_with($field, '_id') || $field === 'id') {
        return 1;
    }

    if (str_contains($field, 'name')) {
        return 'Sample Name';
    }

    if (str_contains($field, 'description') || str_contains($field, 'note') || str_contains($field, 'message')) {
        return 'Sample text';
    }

    if (str_contains($field, 'date')) {
        return '2026-01-01';
    }

    if (str_contains($field, 'url') || str_contains($field, 'link')) {
        return 'https://example.com';
    }

    if (in_array('json', $ruleStrings, true)) {
        return [];
    }

    return 'sample';
}

function buildSampleFromRules(array $rules): array
{
    $body = [];
    $arrayChildren = [];
    $nestedArrayChildren = [];

    foreach ($rules as $field => $ruleSet) {
        if (!str_contains($field, '*')) {
            continue;
        }

        if (preg_match('/^([^.]+)\.\*\.([^.]+)\.\*\.([^.]+)$/', $field, $matches)) {
            [$root, $nested, $leaf] = [$matches[1], $matches[2], $matches[3]];
            $nestedArrayChildren[$root][$nested][$leaf] = normalizeRuleSet($ruleSet);
            continue;
        }

        if (preg_match('/^([^.]+)\.\*\.([^.]+)$/', $field, $matches)) {
            $prefix = $matches[1];
            $childField = $matches[2];
            $arrayChildren[$prefix][$childField] = normalizeRuleSet($ruleSet);
        }
    }

    foreach ($rules as $field => $ruleSet) {
        if (str_contains($field, '*') || isset($arrayChildren[$field])) {
            continue;
        }

        $normalized = normalizeRuleSet($ruleSet);

        if (!ruleRequiresField($normalized) && ruleIsNullable($normalized)) {
            continue;
        }

        $body[$field] = sampleScalarValue($field, $normalized);
    }

    foreach ($arrayChildren as $prefix => $children) {
        $item = [];
        foreach ($children as $childField => $childRules) {
            if (isset($nestedArrayChildren[$prefix][$childField])) {
                $nestedItem = [];
                foreach ($nestedArrayChildren[$prefix][$childField] as $leafField => $leafRules) {
                    if (!ruleRequiresField($leafRules) && ruleIsNullable($leafRules)) {
                        continue;
                    }
                    $nestedItem[$leafField] = sampleScalarValue($leafField, $leafRules);
                }

                if ($nestedItem !== []) {
                    $item[$childField] = [$nestedItem];
                }

                continue;
            }

            if (!ruleRequiresField($childRules) && ruleIsNullable($childRules)) {
                continue;
            }

            $item[$childField] = sampleScalarValue($childField, $childRules);
        }

        if ($item !== []) {
            $body[$prefix] = [$item];
        }
    }

    return $body;
}

function sampleBody(string $uri, string $method, ?string $action = null): ?array
{
    if (!in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        return null;
    }

    $formRequestClass = resolveFormRequestClass($action, $method);
    $rules = getValidationRules($formRequestClass);
    if ($rules !== null) {
        $sample = buildSampleFromRules($rules);
        if ($sample !== []) {
            return $sample;
        }
    }

    if (preg_match('#/admin/auth/login$#', $uri)) {
        return ['email' => 'admin@admin.com', 'password' => 'password'];
    }
    if (preg_match('#/user/auth/login$#', $uri)) {
        return ['phone' => '0991234567', 'password' => 'Password1!'];
    }
    if (preg_match('#/driver/auth/login$#', $uri)) {
        return ['phone' => '0998887777', 'password' => 'Password1!'];
    }
    if (preg_match('#/user/auth/register$#', $uri)) {
        return [
            'name' => 'Sara Ahmad',
            'phone' => '0991234567',
            'email' => 'sara@example.com',
            'password' => 'Password1!',
            'city_id' => 1,
            'governorate_id' => 1,
        ];
    }
    if (preg_match('#/user/auth/send-otp$#', $uri)) {
        return ['phone' => '0991234567'];
    }
    if (preg_match('#/user/auth/verify-otp$#', $uri)) {
        return ['phone' => '0991234567', 'otp' => '123456'];
    }

    return [];
}

function buildHeaders(?string $guard, string $method, string $uri): array
{
    $headers = [
        ['key' => 'Accept', 'value' => 'application/json'],
    ];

    if (
        str_starts_with($uri, 'api/user/')
        || str_starts_with($uri, 'api/admin/')
        || str_starts_with($uri, 'api/driver/')
    ) {
        $headers[] = ['key' => 'Accept-Language', 'value' => 'ar'];
    }

    if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        $headers[] = ['key' => 'Content-Type', 'value' => 'application/json'];
    }

    if ($guard) {
        $tokenVar = match ($guard) {
            'admin' => '{{admin_token}}',
            'driver' => '{{driver_token}}',
            'vendor' => '{{vendor_token}}',
            default => '{{user_token}}',
        };
        $headers[] = ['key' => 'Authorization', 'value' => "Bearer {$tokenVar}"];
    }

    return $headers;
}

function buildUrl(string $uri): array
{
    $raw = '{{base_url}}/' . $uri;
    $parts = explode('/', $uri);
    $path = [];
    $query = [];

    foreach ($parts as $part) {
        if (preg_match('/^\{(.+)\}$/', $part, $m)) {
            $param = $m[1];
            $path[] = "{{{$param}}}";
        } else {
            $path[] = $part;
        }
    }

    if (str_ends_with($uri, 's') || str_contains($uri, '/orders') || str_contains($uri, '/products')) {
        if (!str_contains($uri, '{')) {
            $query[] = ['key' => 'page', 'value' => '1', 'disabled' => true];
            $query[] = ['key' => 'per_page', 'value' => '15', 'disabled' => true];
        }
    }

    $url = [
        'raw' => $raw,
        'host' => ['{{base_url}}'],
        'path' => $path,
    ];

    if ($query) {
        $url['query'] = $query;
    }

    return $url;
}

function buildRequestItem(array $route): array
{
    $method = normalizeMethod(strtoupper($route['method'] ?? 'GET'));
    $uri = $route['uri'] ?? '';
    $action = $route['action'] ?? null;
    $middleware = $route['middleware'] ?? [];
    $guard = detectAuthGuard($middleware);
    $name = humanizeRouteName($method, $uri, $route['name'] ?? null);

    $item = [
        'name' => $name,
        'request' => [
            'method' => $method,
            'header' => buildHeaders($guard, $method, $uri),
            'url' => buildUrl($uri),
        ],
    ];

    $body = sampleBody($uri, $method, $action);
    if ($body !== null) {
        $item['request']['body'] = [
            'mode' => 'raw',
            'raw' => $body === []
                ? '{}'
                : json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
    }

    if (preg_match('#/auth/login$#', $uri) && $method === 'POST') {
        $tokenVar = match (true) {
            str_contains($uri, '/admin/') => 'admin_token',
            str_contains($uri, '/driver/') => 'driver_token',
            default => 'user_token',
        };

        $item['event'] = [[
            'listen' => 'test',
            'script' => [
                'type' => 'text/javascript',
                'exec' => [
                    'const res = pm.response.json();',
                    'if (res.data && res.data.token) {',
                    "    pm.collectionVariables.set('{$tokenVar}', res.data.token);",
                    '}',
                ],
            ],
        ]];
    }

    return $item;
}

function folderSegments(string $uri): array
{
    $segments = explode('/', substr($uri, 4));
    array_shift($segments);

    return array_values(array_filter($segments, fn ($s) => !str_starts_with($s, '{')));
}

function insertIntoTree(array &$tree, array $segments, array $item): void
{
    if (empty($segments)) {
        $tree['_items'][] = $item;

        return;
    }

    $current = array_shift($segments);
    if (!isset($tree['_children'][$current])) {
        $tree['_children'][$current] = ['_items' => [], '_children' => []];
    }

    insertIntoTree($tree['_children'][$current], $segments, $item);
}

function treeToPostmanFolders(array $tree): array
{
    $folders = [];
    $items = $tree['_items'] ?? [];
    usort($items, fn ($a, $b) => strcmp($a['name'], $b['name']));

    foreach ($tree['_children'] ?? [] as $name => $node) {
        $folders[] = [
            'name' => ucwords(str_replace(['-', '_'], ' ', $name)),
            'item' => treeToPostmanFolders($node),
        ];
    }

    usort($folders, fn ($a, $b) => strcmp($a['name'], $b['name']));

    return array_merge($folders, $items);
}

function countRequests(array $items): int
{
    $count = 0;
    foreach ($items as $item) {
        if (isset($item['request'])) {
            $count++;
        } elseif (isset($item['item'])) {
            $count += countRequests($item['item']);
        }
    }

    return $count;
}

$groups = [
    'admin' => ['_items' => [], '_children' => []],
    'user' => ['_items' => [], '_children' => []],
    'driver' => ['_items' => [], '_children' => []],
    'vendor' => ['_items' => [], '_children' => []],
    'socket' => ['_items' => [], '_children' => []],
    'shared' => ['_items' => [], '_children' => []],
];

foreach ($routes as $route) {
    $uri = $route['uri'] ?? '';
    if (!str_starts_with($uri, 'api/')) {
        continue;
    }

    $top = explode('/', substr($uri, 4))[0] ?? 'shared';
    $groupKey = isset($groups[$top]) ? $top : 'shared';

    $item = buildRequestItem($route);

    if ($groupKey === 'shared') {
        $groups['shared']['_items'][] = $item;
        continue;
    }

    $segments = folderSegments($uri);
    insertIntoTree($groups[$groupKey], $segments, $item);
}

$topFolders = [];
foreach (['admin', 'user', 'driver', 'vendor', 'socket', 'shared'] as $key) {
    $folderItems = treeToPostmanFolders($groups[$key]);
    if (empty($folderItems)) {
        continue;
    }

    $topFolders[] = [
        'name' => ucfirst($key),
        'item' => $folderItems,
    ];
}

$collection = [
    'info' => [
        '_postman_id' => 'deema-fashion-api-full-collection',
        'name' => 'Deema Fashion API',
        'description' => "Complete Postman collection for all Deema Fashion backend API routes.\n\n"
            . "**Setup**\n"
            . "1. Import `Deema_Fashion_Local.postman_environment.json`\n"
            . "2. Set `base_url` (default: http://localhost:8000)\n"
            . "3. Run a login request to auto-save tokens\n\n"
            . "**Auth tokens (auto-saved on login)**\n"
            . "- `admin_token` ← POST /api/admin/auth/login\n"
            . "- `user_token` ← POST /api/user/auth/login\n"
            . "- `driver_token` ← POST /api/driver/auth/login\n\n"
            . "**Regenerate after route changes**\n"
            . "`php scripts/generate_postman_collection.php`",
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'variable' => [
        ['key' => 'base_url', 'value' => 'http://localhost:8000'],
        ['key' => 'admin_token', 'value' => ''],
        ['key' => 'user_token', 'value' => ''],
        ['key' => 'driver_token', 'value' => ''],
        ['key' => 'vendor_token', 'value' => ''],
        ['key' => 'id', 'value' => '1'],
        ['key' => 'orderId', 'value' => '1'],
        ['key' => 'product', 'value' => '1'],
        ['key' => 'popupCampaign', 'value' => '1'],
    ],
    'item' => $topFolders,
];

$outputDir = dirname(__DIR__) . '/postman';
$files = [
    'Deema_Fashion_API.postman_collection.json',
    'Deema_Fashion_API_Full.postman_collection.json',
];

$json = json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

foreach ($files as $file) {
    file_put_contents("{$outputDir}/{$file}", $json . "\n");
}

echo 'Generated ' . count($routes) . " API routes\n";
echo "Output: {$outputDir}/Deema_Fashion_API.postman_collection.json\n";
echo 'Top-level folders: ' . count($topFolders) . "\n";

foreach ($topFolders as $folder) {
    echo '  - ' . $folder['name'] . ': ' . countRequests($folder['item']) . " requests\n";
}
