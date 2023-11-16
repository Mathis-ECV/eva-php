<?php
class convert
{

    public $file_extension;
    public $filename_without_extension;

    function get_file_extension()
    {
        if (isset($_POST["convert"])) {
            if ($_FILES['xml_csv_file_input']['name']) {
                if ($_FILES['xml_csv_file_input']["type"] == 'text/csv') {

                    $this->file_extension = 'csv';

                } elseif ($_FILES['xml_csv_file_input']["type"] == 'text/xml') {

                    $this->file_extension = 'xml';
                } else {
                    echo 'Ce type de fichier n\'est pas pris en charge';
                }
            }
        }
    }


    function convert_file()
    {
        if ($this->file_extension == 'csv') {
            $this->convert_csv_to_json();
        } elseif ($this->file_extension == 'xml') {
            $this->convert_xml_to_json();
        }
    }

    function convert_csv_to_json()
    {
        $this->filename_without_extension = basename($_FILES['xml_csv_file_input']['name'], '.csv');

        if (($handle = fopen($_FILES['xml_csv_file_input']['tmp_name'], "r")) !== FALSE) {

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
            file_put_contents($this->filename_without_extension . ".json", $json);

            $this->display_download_link();

        }

        exit();
    }


    function convert_xml_to_json()
    {
        $this->filename_without_extension = basename($_FILES['xml_csv_file_input']['name'], '.xml');

        $fileContents = file_get_contents($_FILES['xml_csv_file_input']['tmp_name']);
        $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
        $fileContents = trim(str_replace('"', "'", $fileContents));
        $simpleXml = simplexml_load_string($fileContents);
        $json = json_encode($simpleXml);

        file_put_contents($this->filename_without_extension . ".json", $json);

        $this->display_download_link();

    }

    function display_download_link()
    {
        if ($this->filename_without_extension) {
            echo '<a href="' . $this->filename_without_extension . '.json" download="' . $this->filename_without_extension . '">Download ' . $this->filename_without_extension . '.json</a>';
        }
    }

}

?>


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
            <input type="file" name="xml_csv_file_input" accept=".csv, .xml" />
            <input type="submit" name="convert" value="Convert">
        </form>
    </body>
</body>

</html>

<?php
$session_convert = new convert();
$session_convert->get_file_extension();
$session_convert->convert_file();
?>