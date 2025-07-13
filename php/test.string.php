<?php
function testIsDate(string $d): bool {
    $do = DateTime::createFromFormat("Y-m-d", $d);
    return $do && $do->format("Y-m-d") === $d;
}

//Eu precisava de um comportamento em que 0 não fosse reconhecido como vazio.
//I needed a behavior where 0 would not be recognized as empty.
function testIsEmpty($s) {
    return in_array($s, [null, ""], true);
};

function testMultipleIsEmpty($v) {
    return array_filter($v, 'testIsEmpty');
};

function testMultipleIsNumeric(array $v) {
    return array_filter($v, 'is_numeric');
};

function removeAccents(string $text): string {
    return transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
};

function escapeForRegex(string $text): string {
    return preg_replace('/([\\\\.^$|()[\]{}*+?])/', '\\\$1', $text);
};

function isValidMonetary(string $value): bool {
    return preg_match('/^\d+(\.\d{1,2})?$/', $value);
};

function adjustValueMonetary(string $value): string {
    $value = trim($value);

    if ($value[0] === '.') {
        $value = '0' . $value;
    };

    if (strpos($value, '.') === false) {
        $value = $value . '.00';
    };

    list($integer, $decimal) = explode('.', $value);

    $integer = ltrim($integer, '0');
    if ($integer === '') {
        $integer = '0';
    };

    $decimal = substr($decimal, 0, 2);
    $decimal = str_pad($decimal, 2, '0');

    return $integer . '.' . $decimal;
}

function hasPermission($permissions, $module): int {
    $permissions_map = [
        'cashbook'  => 0,
        'clients'   => 2,
        'orders'    => 4,
        'inventory' => 6,
        'reminders' => 8,
        'users'     => 10,
    ];

    if (!isset($permissions_map[$module])) return false;

    $shift = $permissions_map[$module];
    $module_permission = ($permissions >> $shift) & 0b11;

    return $module_permission;
};

?>