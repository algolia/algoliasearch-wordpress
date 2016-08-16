<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
	->exclude( 'includes/libraries' )
	->in( __DIR__ );

return Symfony\CS\Config\Config::create()
	->level( Symfony\CS\FixerInterface::NONE_LEVEL )
	->fixers(
		array(
			'linefeed',
			'short_tag',
			'php_closing_tag',
			'visibility',
			'array_element_no_space_before_comma',
			'array_element_white_space_after_comma',
			'blankline_after_open_tag',
			'double_arrow_multiline_whitespaces',
			'duplicate_semicolon',
			'empty_return',
			'extra_empty_lines',
			'function_typehint_space',
			'multiline_array_trailing_comma',
			'phpdoc_params',
			'phpdoc_scalar',
			'phpdoc_short_description',
			'return',
			'single_quote',
			'align_double_arrow',
			'concat_with_spaces',
			'long_array_syntax',
			'encoding',
		)
	)
	->finder( $finder );
