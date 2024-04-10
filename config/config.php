<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Base directory
    |--------------------------------------------------------------------------
    |
    | Which directory to use as a base for all supplied files, relative to the 
    | project root. Make sure to have a trailing slash.
    | 
    | Default: 'resources/'
    |
    */
    
    'base_dir' => 'resources/',
    
    /*
    |--------------------------------------------------------------------------
    | Folder presets
    |--------------------------------------------------------------------------
    |
    | Define additional folders for specific files as shortcuts.
    | Each key in the array defines a path relative to the project root.
    | 
    | Example: 
    |   'js' => 'resources/js/'
    |   {{ sourcestack:js src="slider.js" }}
    |
    | You can optionally define a file extension to use. The definition
    | extends to an array in this case:
    | 
    |   'js' => [
    |       'path' => 'resources/js/',
    |       'extension' => 'js',
    |   ]
    |   {{ sourcestack:js src="slider" }}
    |
    */
    
    'presets' => [
        //
    ],
];