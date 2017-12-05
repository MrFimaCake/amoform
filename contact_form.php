<!doctype html>
<html lang="en">
<head>
    <title>Form</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-4 offset-md-4 col-lg-4 offset-lg-4">
            <h1>Create contact</h1>
            <?php
            if (isset($errors)) {
                var_dump($errors);
                foreach ($errors as $errorList) {
                    foreach ($errorList as $error) {
                        echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
                    }
                }
            }
            if (isset($success)) {
                var_dump($success);
                foreach ((array)$success as $successItem) {
                    echo '<div class="alert alert-success" role="alert">' . $successItem . '</div>';
                }
            }
            ?>
            <form method="post">
                <div class="form-group">
                    <label class="col-form-label" for="inputPhone">Name:</label>
                    <input type="text"
                           class="form-control"
                           name="name"
                           value="<?php echo $name ?? '' ?>"
                           id="inputName">
                </div>
                <div class="form-group">
                    <label for="inputEmail">Email:</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           id="inputEmail"
                           required
                           value="<?php echo $email ?? '' ?>"
                           aria-describedby="emailHelp">
                    <small id="emailHelp" class="form-text text-muted">Field is required.</small>
                </div>
                <div class="form-group">
                    <label class="col-form-label" for="inputPhone">Phone:</label>
                    <input type="text"
                           name="phone"
                           class="form-control"
                           id="inputPhone"
                           required
                           value="<?php echo $phone ?? '' ?>"
                           area-describedby="phoneHelp">
                    <small id="phoneHelp" class="form-text text-muted">Field is required.</small>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
</body>
</html>