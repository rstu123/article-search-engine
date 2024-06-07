<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="article.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article Search Engine Database - Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 10px;
            text-align: center;
        }
        nav {
            background-color: #444;
            padding: 10px;
        }
        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        nav ul li {
            display: inline;
            margin-right: 20px;
        }
        nav ul li a {
            color: #fff;
            text-decoration: none;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }
        footer {
            background-color: #333;
            color: #fff;
            padding: 10px;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .result-item {
            background-color: #fff;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <header>
        <h1>Article Database Search Engine</h1>
        <h2>Group 10 - Project PDM</h2>
    </header>
    <nav>
        <ul>
            <li><a href="article.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="credit.html">Credits</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2>Search Results</h2>
        <?php
        // Retrieve the search keyword from the form submission
        $keyword = $_POST['keyword'];

        // Establish a connection to your MySQL database
        $conn = mysqli_connect("localhost", "root", "", "articleschema_mysql");

        // Check if the connection was successful
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Query to find the matching keyword_id
        $stmt = $conn->prepare("SELECT keyword_id FROM keyword WHERE article_keyword = ?");
        $stmt->bind_param("s", $keyword);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch the keyword_id
            $row = $result->fetch_assoc();
            $keyword_id = $row['keyword_id'];

            // Query to find the matching article_id(s)
            $stmt = $conn->prepare("SELECT article_id FROM articlekeyword WHERE keyword_id = ?");
            $stmt->bind_param("s", $keyword_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Loop through each article_id
                while ($row = $result->fetch_assoc()) {
                    $article_id = $row['article_id'];

                    // Query to find the article details
                    $stmt_article = $conn->prepare("SELECT article_title, article_views, article_publicationdate, article_journalname FROM article WHERE article_id = ?");
                    $stmt_article->bind_param("s", $article_id);
                    $stmt_article->execute();
                    $result_article = $stmt_article->get_result();

                    if ($result_article->num_rows > 0) {
                        // Fetch the article details
                        $article = $result_article->fetch_assoc();

                        // Query to find the author_id(s)
                        $stmt_author_id = $conn->prepare("SELECT author_id FROM article_author WHERE article_id = ?");
                        $stmt_author_id->bind_param("s", $article_id);
                        $stmt_author_id->execute();
                        $result_author_id = $stmt_author_id->get_result();

                        if ($result_author_id->num_rows > 0) {
                            // Fetch the author details
                            $author_names = [];
                            while ($author_row = $result_author_id->fetch_assoc()) {
                                $author_id = $author_row['author_id'];

                                $stmt_author = $conn->prepare("SELECT author_fullname FROM author WHERE author_id = ?");
                                $stmt_author->bind_param("s", $author_id);
                                $stmt_author->execute();
                                $result_author = $stmt_author->get_result();

                                if ($result_author->num_rows > 0) {
                                    $author = $result_author->fetch_assoc();
                                    $author_names[] = $author['author_fullname'];
                                }
                            }
                            $author_fullname = implode(", ", $author_names);
                        } else {
                            $author_fullname = "Unknown";
                        }

                        // Display the result
                        echo "<div class='result-item'>";
                        echo "<h3>{$article['article_title']}</h3>";
                        echo "<p><strong>Views:</strong> {$article['article_views']}</p>";
                        echo "<p><strong>Publication Date:</strong> {$article['article_publicationdate']}</p>";
                        echo "<p><strong>Author:</strong> {$author_fullname}</p>";
                        echo "<p><strong>Journal:</strong> {$article['article_journalname']}</p>";
                        echo "</div>";
                    }
                }
            } else {
                echo "<p>No articles found for the given keyword.</p>";
            }
        } else {
            echo "<p>No results found for the keyword: $keyword</p>";
        }

        // Close the database connection
        mysqli_close($conn);
        ?>
    </div>
    <footer>
        &copy; 2024 Group 10 - Principles of Database Management [G02]. All rights reserved.
    </footer>
</body>
</html>
