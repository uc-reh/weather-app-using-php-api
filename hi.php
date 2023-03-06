<?php

$file = __DIR__.'/functions.php';
print_r($file);
$functions = get_defined_functions();

print_r($functions);

echo '<table>';
echo '<tr><th>Function name</th><th>Description</th><th>Parameters</th><th>Return value</th></tr>';

foreach ($functions['user'] as $function) {
    $reflection = new ReflectionFunction($function);

    if ($reflection->getFileName() === $file) {
        echo '<tr>';
        echo '<td>' . $function . '</td>';
        echo '<td>' . $reflection->getDocComment() . '</td>';

        echo '<td>';
        foreach ($reflection->getParameters() as $param) {
            echo '$' . $param->getName() . ' ';
        }
        echo '</td>';

        echo '<td>' . $reflection->getReturnType() . '</td>';
        echo '</tr>';
    }
}

echo '</table>';
