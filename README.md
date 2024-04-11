# SourceStack

A Statamic utility to only include css/js sources once. This can be useful when you want to include dedicated files for specific partials/sets that would otherwise be included multiple times within a template.

Assume a basic Statamic blueprint with a replicator field `pagebuilder` that stacks sets in a template:

```antlers
<!-- page-template.antlers.html -->

{{ pagebuilder }}
    {{ partial:if_exists src="sets/{type}" }}
{{ /pagebuilder }}
```

Further assume that there is a "slider" set, which requires a dedicated javascript source file:

```antlers
<!-- sets/slider.antlers.html -->

<div class="slider">
    …
</div>

{{ push:scripts }}
    {{ vite src="resources/js/slider.js" }}
{{ /push:scripts }}
```

The scripts [stack](https://statamic.dev/antlers#stacks) is rendered in the layout.

Problem is: if you add multiple sliders in a page, the script file will also be added multiple times. The stack has no way to determine if a file was already added or not.

This is where SourceStack comes in.

## How to Install

Run the following command from your project root:

```bash
composer require visuellverstehen/statamic-sourcestack
```

## How to Use

Let's modify the Statamic setup described above. Instead of using the `scripts` stack for our js source, we use the `sourcestack` tag included in this package:

```antlers
<!-- sets/slider.antlers.html -->

<div class="slider">
    …
</div>

{{ sourcestack src="resources/js/slider.js" }}
```

The tag collects all the sources throughout the template, making sure to add every source only once. Eventually, the `sourcestack:render` tag renders all collected sources as a vite source tag.

```antlers
<!-- layout.antlers.html -->
<!-- … -->

<body>
    <main>
        {{ template_content }}
    </main>
    
    {{ sourcestack:render }}
</body>
```

You can also use the tag alias `srcstk`. Sources can be added with the `src`, `source` or `file` parameter. Output can be generated with `render` or `vite`.

```antlers
{{ srcstk src="slider.js" }}
{{ srcstk:vite }}
```

### Dedicated stacks

If you need multiple stacks for different sources, you can define dedicated stacks in the config file:

```php
'stacks' => [
    'css' => [
        'base_dir' => 'resources/css/',
        'extension' => 'css',
    ]
],
```
For each stack you can optionally define a base directory and a file extension. Assuming the example above, the tag would be used like this:

```antlers
{{ sourcestack:css src="slider" }}
{{ sourcestack:render stack="css" }}
```

Please note that stacks can't be called `render` or any other strings that resolve to public function names of the `Sourcestack` tag class.

### Caveats

The way SourceStack is currently built requires the output to be called _after_ all sources have been collected. You can however use Statamics [section and yield](https://statamic.dev/antlers#section-amp-yield) logic to move e. g. a stack with css files to the `<head>`  element:

```antlers
<head>
    <!-- … -->
    
    {{ yield:css-sources }}
</head>
<body>
    <!-- … -->
    
    {{ section:css-sources }}
        {{ sourcestack:render stack="css" }}
    {{ /section:css-sources }}
</body>
```

## Configuration

In addition to dedicated stacks (see above), you can configure the default base directory to be used for all regular source file paths in `config/sourcestack.php`.

## More about us

- [www.visuellverstehen.de](https://visuellverstehen.de)

## License
The MIT license (MIT). Please take a look at the [license file](LICENSE.md) for more information.

