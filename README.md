
# XML/CSV vers JSON Générateur
Ce projet est un générateur de JSON à partir de fichier xml ou CSV upload à partir d'un formulaire
#### Il existe deux version de ce projet. 
- La premiere est une version simple avec seulement le générateur de JSON à partir de fichier CSV présent dans (/EVal/index.php)
- La second est la version complete présente dans (/EVal/version-2/index.php)



## Instalation du générateur dans votre projet (Version 2)

#### 1. Ajouter la classe 'class convert' au début de votre projet

```bash
class convert {...}
```

#### 2. Ajouter le formulaire où vous voulez que les utilisateur puisse générer leurs JSON

```bash
<form name="frmUpload" method="post" enctype="multipart/form-data">
    <input type="file" name="xml_csv_file_input" accept=".csv, .xml" />
    <input type="submit" name="convert" value="Convert">
</form>
```

#### 3. Lancer la conversion 
```bash
$session_convert = new convert();         //Appel de l'objet convert();
$session_convert->get_file_extension();   //Appel de la méthode get_file_extension
$session_convert->convert_file();         //Appel de la méthode convert_file
```

## Fonctionnement de l'object 'convert()'

#### 1. L'object possède deux variables : L'extension du fichier et le nom du fichier sans l'extension
```bash
public $file_extension;
public $filename_without_extension;
```

#### 2. On execute la function get_file_extension() qui permet de récupérer l'extension du fichier et de la stocker dans la variable $file_extension
```bash
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
```

#### 3. On executre la fonction convert_file() qui vérifie l'extension du fichier, et appel la function qui correspond (csv = convert_csv_to_json() ; xml = convert_xml_to_json())
```bash
function convert_file()
    {
        if ($this->file_extension == 'csv') {
            $this->convert_csv_to_json();
        } elseif ($this->file_extension == 'xml') {
            $this->convert_xml_to_json();
        }
    }
```

#### 4. Les fonctions convert_csv_to_json() et convert_xml_to_json() transforme les données en JSON et créer un fichier JSON avec le contenu, puis execute la fonction display_download_link()
```bash
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
```

```bash
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
```

#### 5. La fonction display_download_link() permet d'affichier le bounton de téléchargement du fichier en JSON avec le nom du fichier qui à été rentré dans le formulaire
```bash
 function display_download_link()
    {
        if ($this->filename_without_extension) {
            echo '<a href="' . $this->filename_without_extension . '.json" download="' . $this->filename_without_extension . '">Download ' . $this->filename_without_extension . '.json</a>';
        }
    }
```

## Instalation du générateur dans votre projet (Version simple, csv seulement)

#### 1. Ajouter le formulaire où vous voulez que les utilisateur puisse générer leurs JSON à partir de fichier CSV
```bash
<form name="frmUpload" method="post" enctype="multipart/form-data">
    <input type="file" name="csv_file_input" accept=".csv" />
    <input type="submit" name="convert" value="Convert">
</form>
```

#### 2. Ajouter le php à votre projet 
```bash
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
```
