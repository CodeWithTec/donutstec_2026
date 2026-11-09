<?php
/**
 * URL helpers for clean URLs.
 * Works whether you use /public/index.php or /public/ directly.
 */

/**
 * Build a URL to a public page.
 *   url('services')       -> BASE_URL/services
 *   url('blog/post-slug') -> BASE_URL/blog/post-slug
 */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}

/**
 * Current path relative to BASE_URL — used by SEO canonical.
 */
function current_path(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $base = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
    if ($base && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base));
    }
    return '/' . ltrim(parse_url($uri, PHP_URL_PATH) ?: '', '/');
}

/**
 * Canonical URL of the current page.
 */
function canonical_url(): string
{
    return rtrim(BASE_URL, '/') . current_path();
}