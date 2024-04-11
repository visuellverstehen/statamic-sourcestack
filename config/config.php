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
    | Dedicated stacks
    |--------------------------------------------------------------------------
    |
    | Define isolated stacks for dedicated files. Each stack can have
    | its own base_dir and a default extension. A dedicated stack
    | can be rendered independently from the default stack.
    | 
    |   'js' => [
    |       'base_dir' => 'resources/js/',
    |       'extension' => 'js',
    |   ]
    |   {{ sourcestack:js src="slider" }}
    |   {{ sourcestack:render stack="js" }}
    |
    */
    
    'stacks' => [
        //
    ],
];