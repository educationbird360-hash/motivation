<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>

</head>
<body>
    <header>
        <h1>Header.</h1>
    </header>
    <nav>
        <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
    </nav>
