<html>
<head>
    <meta http-equiv="Content-Type" content="text/html"; charset="utf-8" />
    <link href='http://fonts.googleapis.com/css?family=Irish+Grover' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=La+Belle+Aurore' rel='stylesheet' type='text/css'>
    <link href="/css/screen.css" type="text/css" rel="stylesheet" />
    <link href="/css/sidebar.css" type="text/css" rel="stylesheet" />
    <link href="/css/blog.css" type="text/css" rel="stylesheet" />
    <link rel="shortcut icon" href="/img/favicon.ico" />
</head>
<body>
    <section id="wrapper">
        <header id="header">
            <div class="top">
                <nav>
                    <ul class="navigation">
                        <li><a href="index_sb.php">Home</a></li>
                        <li><a href="about_sb.php">About</a></li>
                        <li><a href="contact_sb.php">Contact</a></li>
                    </ul>
                </nav>
            </div>
            <hgroup>
                <h2><a href="index_sb.php/">symblog</a></h2>
                <h3><a href="index_sb.php/">creating a blog in Symfony2</a></h3>
            </hgroup>
        </header>
        <section class="main-col">
            <?php if (!empty($blogs)): ?>
                <?php foreach ($blogs as $blog): ?>
                    <article class="blog">
                        <div class="date">
                            <time datetime="<?= htmlspecialchars($blog->created_at) ?>"> <?= htmlspecialchars($blog->created_at) ?> </time>
                        </div>
                        <header>
                            <h2><a href="show_sb.php?id=<?= htmlspecialchars($blog->id) ?>"><?= htmlspecialchars($blog->title) ?></a></h2>
                        </header>
                        <img src="<?= htmlspecialchars($blog->image) ?>" alt="<?= htmlspecialchars($blog->title) ?>" />
                        <div class="snippet">
                            <p><?= htmlspecialchars($blog->blog) ?></p>
                            <p class="continue"><a href="#">Continue reading...</a></p>
                        </div>
                        <footer class="meta">
                            <p>Comments: <a href="#"> <?= htmlspecialchars($blog->comments_count ?? 0) ?> </a></p>
                            <p>Posted by <span class="highlight"><?= htmlspecialchars($blog->author) ?></span> at <?= htmlspecialchars($blog->created_at) ?></p>
                            <p>Tags: <span class="highlight"><?= htmlspecialchars($blog->tags) ?></span></p>
                        </footer>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No blogs found.</p>
            <?php endif; ?>
        </section>
    </section>
</body>
</html>