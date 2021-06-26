<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/app')
    ->exclude(['bootstrap', 'storage', 'vendor', 'tests'])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();

// Rules Docs : https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.18/doc/rules/index.rst
// https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.18/doc/ruleSets/PSR2.rst

return $config->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'ordered_imports' => ['sort_algorithm' => 'alpha'],

    'binary_operator_spaces' => [
        'default' => 'single_space',
    ],
    'cast_spaces' => true,
    'class_attributes_separation' => true,
    'concat_space' => true,
    'fully_qualified_strict_types' => true,
    'function_typehint_space' => true,
    'heredoc_to_nowdoc' => true,
    'linebreak_after_opening_tag' => true,
    'lowercase_keywords' => true,
    'magic_method_casing' => true,
    'magic_constant_casing' => true,
    'native_function_casing'  => true,
    'no_blank_lines_after_phpdoc' => true,
    'no_empty_phpdoc' => true,
    'no_empty_statement' => true,
    'no_leading_namespace_whitespace' => true,
    'no_mixed_echo_print' => [
        'use' => 'echo'
    ],
    'no_multiline_whitespace_around_double_arrow' => true,
    'multiline_whitespace_before_semicolons' => [
        'strategy' => 'no_multi_line'
    ],
    'no_short_bool_cast' => true,
    'no_singleline_whitespace_before_semicolons' => true,
    'no_spaces_around_offset' => true,
    'no_trailing_comma_in_list_call' => true,
    'no_trailing_comma_in_singleline_array' => true,
    'no_unneeded_control_parentheses' => true,
    'no_useless_return' => true,
    'no_whitespace_before_comma_in_array' => true,
    'normalize_index_brace'  => true,
    'object_operator_without_whitespace' => true,
    'phpdoc_indent' => true,
    'phpdoc_no_access' => true,
    'phpdoc_no_package' => true,
    'phpdoc_no_useless_inheritdoc' => true,
    'phpdoc_scalar' => true,
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_summary' => true,
    'phpdoc_to_comment' => true,
    'phpdoc_trim' => true,
    'phpdoc_types' => true,
    'phpdoc_var_without_name' => true,
    'simplified_null_return' => true,
    'single_line_comment_style' => true,
    'single_quote' => true,
    'space_after_semicolon' => true,
    'standardize_not_equals' => true,
    'trailing_comma_in_multiline' => true,
    'trim_array_spaces' => true,
    'unary_operator_spaces' => true,
    'whitespace_after_comma_in_array' => true,
])->setFinder($finder);
