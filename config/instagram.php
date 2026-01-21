<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Python Executable Path
    |--------------------------------------------------------------------------
    |
    | Specify the path to the Python executable. Set to 'auto' to let the
    | application automatically detect the correct Python path.
    |
    | Examples:
    | - 'auto' (automatic detection)
    | - 'python3' (Linux/macOS)
    | - 'python' (Windows)
    | - '/usr/bin/python3' (absolute path Linux)
    | - 'C:\\Python311\\python.exe' (absolute path Windows)
    |
    */
    'python_path' => env('PYTHON_PATH', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait for the Python worker to respond.
    |
    */
    'timeout'     => env('INSTAGRAM_TIMEOUT', 60),
];
