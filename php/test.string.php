<?php
function testIsDate(string $d) {
    $do = DateTime::createFromFormat("Y-m-d", $d);
    return $do && $do->format("Y-m-d") === $d;
}

//Eu precisava de um comportamento em que 0 não fosse reconhecido como vazio.
//I needed a behavior where 0 would not be recognized as empty.
function testIsEmpty(string $s) {
    return in_array($s, [null, ""], true);
};

function testMultipleIsEmpty(array $v) {
    return (bool) array_filter($v, 'testIsEmpty');
};

function testMultipleIsNumeric(array $v) {
    return (bool) array_filter($v, 'is_numeric');
};

function removeAccents(string $text): string {
    return transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
};

function escapeForRegex(string $text): string {
    return preg_replace('/([\\\\.^$|()[\]{}*+?])/', '\\\$1', $text);
};
?>