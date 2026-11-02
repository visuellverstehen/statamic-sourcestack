<?php

use Illuminate\Foundation\Vite;
use Statamic\Facades\Parse;

beforeEach(function () {
    $this->swap(Vite::class, new class extends Vite
    {
        public function toHtml()
        {
            return collect($this->entryPoints)
                ->map(fn ($src) => '<vite src="'.$src.'"></vite>')
                ->implode("\n");
        }
    });
});

function parseAntlers(string $template): string
{
    // Site templates are parsed as trusted; the untrusted default would guard
    // non-core tags behind an allow-list.
    return (string) Parse::template($template, [], [], true);
}

test('renders each stored source only once', function () {
    $output = parseAntlers('{{ sourcestack file="js/lightbox.js" }}{{ sourcestack src="js/lightbox.js" }}{{ sourcestack:render }}');

    expect(substr_count($output, 'resources/js/lightbox.js'))->toBe(1);
});

test('supports multiple sources separated by pipe or comma', function () {
    $output = parseAntlers('{{ sourcestack src="js/a.js|js/b.js" }}{{ sourcestack src="js/b.js,js/c.js" }}{{ sourcestack:render }}');

    foreach (['a.js', 'b.js', 'c.js'] as $file) {
        expect(substr_count($output, "resources/js/{$file}"))->toBe(1);
    }
});

test('prepends configured base dir and ensures a trailing slash', function () {
    config(['sourcestack.base_dir' => 'assets']);

    $output = parseAntlers('{{ sourcestack file="css/app.css" }}{{ sourcestack:render }}');

    expect($output)->toContain('<vite src="assets/css/app.css"></vite>');
});

test('stores dedicated stacks separately with base dir and extension', function () {
    config(['sourcestack.stacks' => [
        'js' => ['base_dir' => 'resources/js/', 'extension' => 'js'],
    ]]);

    $output = parseAntlers('{{ sourcestack:js src="slider" }}{{ sourcestack:js src="slider" }}{{ sourcestack:render stack="js" }}');

    expect($output)->toContain('<vite src="resources/js/slider.js"></vite>');
    expect(substr_count($output, 'slider.js'))->toBe(1);
});

test('a stack configured as string only overrides the base dir', function () {
    config(['sourcestack.stacks' => ['css' => 'resources/css/']]);

    $output = parseAntlers('{{ sourcestack:css src="main" }}{{ sourcestack:render stack="css" }}');

    expect($output)->toContain('<vite src="resources/css/main"></vite>');
});

test('stack sources are not rendered by the default stack', function () {
    config(['sourcestack.stacks' => [
        'js' => ['base_dir' => 'resources/js/', 'extension' => 'js'],
    ]]);

    parseAntlers('{{ sourcestack:js src="slider" }}');

    expect(parseAntlers('{{ sourcestack:render }}'))->toBe('');
});

test('ignores unknown stacks', function () {
    config(['sourcestack.stacks' => ['js' => 'resources/js/']]);

    $output = parseAntlers('{{ sourcestack:typo src="x" }}{{ sourcestack:render }}');

    expect($output)->toBe('');
});

test('renders nothing when no sources were stored', function () {
    expect(parseAntlers('{{ sourcestack:render }}'))->toBe('');
});

test('the vite tag method works as render alias', function () {
    $output = parseAntlers('{{ sourcestack file="js/a.js" }}{{ sourcestack:vite }}');

    expect($output)->toContain('<vite src="resources/js/a.js"></vite>');
});

test('the srcstk alias works', function () {
    $output = parseAntlers('{{ srcstk file="js/a.js" }}{{ srcstk:render }}');

    expect($output)->toContain('<vite src="resources/js/a.js"></vite>');
});
