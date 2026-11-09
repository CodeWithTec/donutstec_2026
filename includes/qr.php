<?php
/**
 * Simple QR code generator via Google Chart API.
 * Returns an <img> tag pointing to the QR image.
 */
function qr_img(string $data, int $size = 220): string
{
    $url = 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size
         . '&chld=M|0&cht=qr&chl=' . urlencode($data);
    return '<img src="' . htmlspecialchars($url) . '" alt="QR Code" width="' . $size . '" height="' . $size . '">';
}