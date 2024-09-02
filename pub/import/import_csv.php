<?php
// Connect to database
include("db_connect.php");

if (isset($_POST["import"])) {

    $fileName = $_FILES["file"]["tmp_name"];

    if ($_FILES["file"]["size"] > 0) {

        $file = fopen($fileName, "r");

        while (($column = fgetcsv($file, null, ",")) !== FALSE) {


            $sql = "SELECT * FROM catalog_product_super_link

            WHERE parent_id ='" . $column[0] . "'";

            $result = mysqli_query($conn, $sql);

            while ($row = $result->fetch_assoc()) {
                //echo $row['product_id']."<br />\n";

                $sql = "DELETE FROM catalog_product_entity_decimal

                WHERE entity_id = '" . $row['product_id'] . "' AND attribute_id = '87'";

                $result2 = mysqli_query($conn, $sql);


                $sql = "INSERT into catalog_product_entity_decimal (attribute_id,store_id,entity_id,value)
                    values (87,0,'" . $row['product_id'] . "','" . $column[1] . "')";
                $result3 = mysqli_query($conn, $sql);


            }
            //echo "Returned rows are: " . mysqli_num_rows($result);


            //$sql = "INSERT into produit (id,name,description,price)
            //   values ('" . $column[0] . "','" . $column[1] . "','" . $column[2] . "','" . $column[3] . "')";
            //$result = mysqli_query($conn, $sql);


            if (!empty($result2)) {
                $type = "success";
                echo $message = "Les Données sont importées dans la base de données";
            } else {
                $type = "error";
                $message = "Probléme lors de l'importation de donn�es CSV";
            }
        }
    }
}

?>