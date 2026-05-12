<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if (!defined('TB_PREF')) {
    define('TB_PREF', 'fa_');
}

function db_query(string $sql) {
    return true;
}

function db_fetch_assoc($result) {
    return null;
}

function db_escape(string $value): string {
    return "'" . addslashes($value) . "'";
}

function db_insert(string $table) {
    return 1;
}

function db_insert_id() {
    return 1;
}

function get_option(string $name, mixed $default = ''): mixed {
    return $default;
}