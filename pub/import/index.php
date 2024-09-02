<!DOCTYPE html>
<html>

<head>
	<title>Import LCA</title>
</head>

<body>

    <form enctype="multipart/form-data" action="import_csv.php" method="post">
        <div class="input-row">
            <label class="col-md-4 control-label">Choisir un fichier CSV</label>
            <input type="file" name="file" id="file" accept=".csv">
            <br />
            <br />
            <input type="radio" name="prix-achat"> Prix achat <input type="radio" name="prix-achat"> Prix achat<br><br>

            <button type="submit" id="submit" name="import" class="btn-submit">Import</button>
            <br />          <br />          <br />          <br />
        </div>
    </form>

    <?php
			// Connect to database
			include("db_connect.php");

            $sql = "SELECT * FROM catalog_product_entity_decimal";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
    ?>
        <table style="display:none">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Prix achat</th>

                </tr>
            </thead>
            <?php while ($row = mysqli_fetch_array($result)) { ?>
                <tbody>
                    <tr>
                        <td> <?php  echo $row['entity_id']; ?> </td>
                        <td> <?php  echo $row['value']; ?> </td>

                    </tr>
            <?php } ?>
                </tbody>
        </table>
        <?php } ?>
</body>
</html>