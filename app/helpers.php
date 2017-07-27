<?php

if (!function_exists('http_parse_headers')) {
    function http_parse_headers(string $raw_headers): array {
        $headers = [];
        $header_separator = ': ';

        $raw_headers = explode("\r\n", $raw_headers);
        foreach($raw_headers as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                if (!strpos($line, $header_separator)) {
                    break;
                }

                list($key, $value) = explode($header_separator, $line);
                $headers[$key] = $value;
            }
        }

        return $headers;
    }
}