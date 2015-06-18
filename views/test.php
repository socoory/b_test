<!DOCTYPE html>
<html>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <div>
        <input type="file"/>
        <button class="btn btn-default">select</button>
    </div>

</body>
<script>
    $('button').click(function() {
        $('input').click();

        $('input').change(function() {
            var fileElement = $('input')[0];
            var reader = new FileReader();

            reader.onload = function(e) {
                $.ajax({
                    url: '/api_server/files',
                    method: 'POST',
                    data: {
                        file: e.target.result
                    },
                    success: function() {
                        console.log(arguments);
                    },
                    error: function() {
                        console.log(arguments);
                    }
                })
            }

            reader.readAsDataURL(fileElement.files[0]);
        });
    });
</script>
</html>