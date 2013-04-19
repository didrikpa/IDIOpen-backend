<?

    function _p($what)
    {
        echo "<pre>" . print_r($what, true) . "</pre>";
    }

    _p($_GET);
    _p($_POST);

?>

<html>
    <body>
        <form action="test.php" method="post" enctype="multipart/form-data">
            <input type="text" name="formtext" />
            <br />
            <input type="file" name="formfile" />
            <br />
            <input type="submit" /> 
        </form>
    </body>
</html>
