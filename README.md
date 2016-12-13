# Peacock

[![Build Status](https://travis-ci.org/TomWright/Peacock.svg?branch=master)](https://travis-ci.org/TomWright/Peacock)
[![Total Downloads](https://poser.pugx.org/peacock/view/d/total.svg)](https://packagist.org/packages/peacock/view)
[![Latest Stable Version](https://poser.pugx.org/peacock/view/v/stable.svg)](https://packagist.org/packages/peacock/view)
[![Latest Unstable Version](https://poser.pugx.org/peacock/view/v/unstable.svg)](https://packagist.org/packages/peacock/view)
[![License](https://poser.pugx.org/peacock/view/license.svg)](https://packagist.org/packages/peacock/view)

Peacock is a View/Layout engine designed to make it easy to use and manipulate views without having to dive into the HTML.

# Installation

    composer install peacock/view
    
# Usage

Create a view file: `say_hello.php`.

    <p>Hello there, <?= $username ?>.</p>

Get an instance of the `ViewFactory`.

    $viewFactory = ViewFactory::getInstance();
    $viewFactory->setViewsDirectory(VIEWS_PATH);

Get a `View` out of the `ViewFactory`.

    $viewData = ['username' => 'Tom'];
    $view = $viewFactory->view('say_hello', $viewData);

Render the `View`.

    $view->render();
    // <p>Hello there, Tom.</p>

## Using Layouts

Create a layout file: `layout.php`.

    <html>
        <head>
        <title>Example</title>
        </head>
        <body>
            <h1>Example</h1>
            {RENDER_SECTION: content}
        </body>
    </html>

Create a view file: `say_hello.php`.

    <p>Hello there, <?= $username ?>.</p>
    
Get a `Layout` out of the `ViewFactory`.

    $viewData = ['username' => 'Tom'];
    $layout = $viewFactory->layout('layout', $viewData);

Add a child view to the layout specifying that the view content should be added to the `content` section, and then render the layout.
   
    $childView = $layout->childView('say_hello', 'content');
    $layout->render();

That will give the following output.

    <html>
        <head>
        <title>Example</title>
        </head>
        <body>
            <h1>Example</h1>
            <p>Hello there, Tom.</p>
        </body>
    </html>

## Multiple Child Views & Layouts

You can add the same View/Layout multiple times.

    $childView = $layout->childView('say_hello', 'content');
    $childView = $layout->childView('say_hello', 'content');
    $childView = $layout->childView('say_hello', 'content');
    $layout->render();

That will give the following output.

    <html>
        <head>
        <title>Example</title>
        </head>
        <body>
            <h1>Example</h1>
            <p>Hello there, Tom.</p>
            <p>Hello there, Tom.</p>
            <p>Hello there, Tom.</p>
        </body>
    </html>
    
## View Data

You can also pass data directly to a child view/layout.

    $childView = $layout->childView('say_hello', 'content', ['username' => 'Frank']);
    $childView = $layout->childView('say_hello', 'content', ['username' => 'Amelia']);
    $childView = $layout->childView('say_hello', 'content', ['username' => 'Steve']);
    $layout->render();

That will give the following output.

    <html>
        <head>
        <title>Example</title>
        </head>
        <body>
            <h1>Example</h1>
            <p>Hello there, Frank.</p>
            <p>Hello there, Amelia.</p>
            <p>Hello there, Steve.</p>
        </body>
    </html>

## Child Layouts

If you want a view to add content to multiple sections then a `Layout` is the way to go.

Let's say we have a blog post that will show content in the main body, but also add the author's name to the footer.

`layout.php`

    <html>
    <head>
        <title>{RENDER_SECTION:title} - My Blog</title>
    </head>
    <body>
        <div>
            {RENDER_SECTION:main_content}
        </div>
        <footer>
            {RENDER_SECTION:footer}
        </footer>
    </body>
    </html>

`blog_post`

    {SECTION:title}<?= $post->title; ?>{END_SECTION}
    
    {SECTION:main_content}
    <h1><?= $post->title; ?></h1>
    <?= $post->content; ?>
    {END_SECTION}
    
    {SECTION:footer}Written by <?= $post->author; ?>{END_SECTION}

Implementation:

    $post = new stdClass();
    $post->title = 'How to use Peacock';
    $post->author = 'Tom Wright';
    $post->content = 'You should check out the GitHub README!';
    $postData = ['post' => $post];
    
    $layout = $viewFactory->layout('layout');
    $layout->childLayout('blog_post', $postData);
    $layout->render();

Output:

    <html>
    <head>
        <title>How to use Peacock - My Blog</title>
    </head>
    <body>
    <div>
        
    <h1>How to use Peacock</h1>
    You should check out the GitHub README!
    </div>
    <footer>
        Written by Tom Wright
    </footer>
    </body>
    </html>