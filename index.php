<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSON Generator</title>
</head>

<body>

    <body>
        <form name="frmUpload" method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file_input" accept=".csv" />
            <input type="submit" name="convert" value="Convert">
        </form>
    </body>
</body>

</html>


<?php

if (isset($_POST["convert"])) {
    if ($_FILES['csv_file_input']['name']) {
        if ($_FILES['csv_file_input']["type"] == 'text/csv') {
            $filename_without_extension = basename($_FILES['csv_file_input']['name'], '.csv');

            if (($handle = fopen($_FILES['csv_file_input']['tmp_name'], "r")) !== FALSE) {

                $csvs = [];
                while (!feof($handle)) {
                    $csvs[] = fgetcsv($handle);
                }

                $datas = [];
                $column_names = [];
                foreach ($csvs[0] as $single_csv) {
                    $column_names[] = $single_csv;
                }
                foreach ($csvs as $key => $csv) {
                    if ($key === 0) {
                        continue;
                    }
                    foreach ($column_names as $column_key => $column_name) {
                        $datas[$key - 1][$column_name] = $csv[$column_key];
                    }
                }
                $json = json_encode($datas);
                fclose($handle);
                file_put_contents($filename_without_extension . ".json", $json);

                echo '<a href="' . $filename_without_extension . '.json" download="' . $filename_without_extension . '">Download ' . $filename_without_extension . '.json</a>';

            }

            exit();
        } else {
            $error = 'Invalid CSV uploaded';
        }
    } else {
        $error = 'Invalid CSV uploaded';
    }
}


?>