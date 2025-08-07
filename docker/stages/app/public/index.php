<?php

echo 'Laravel with SQL Server Extensions Test Page';
echo '<br>';
echo 'PHP Version: '.phpversion();
echo '<br>';

// Check if SQL Server extensions are loaded
if (extension_loaded('sqlsrv')) {
    echo 'sqlsrv extension is loaded';
} else {
    echo 'sqlsrv extension is NOT loaded';
}
echo '<br>';

if (extension_loaded('pdo_sqlsrv')) {
    echo 'pdo_sqlsrv extension is loaded';
} else {
    echo 'pdo_sqlsrv extension is NOT loaded';
}
echo '<br>';

// Display loaded extensions
echo '<h2>Loaded Extensions:</h2>';
echo '<pre>';
print_r(get_loaded_extensions());
echo '</pre>';

// Display PHP info
phpinfo();
