<?php

declare(strict_types=1);

function landing_html(): string
{
    return <<<'HTML'
<section class="landing-hero" aria-labelledby="welcome-title">
    <div class="landing-intro">
        <h1 id="welcome-title">A good book is <span>just the beginning.</span></h1>
        <p>Explore the collection, request the books you need, and keep track of your borrowing in one place.</p>
        <div class="landing-actions"><a class="button" href="/login.php">Log in to the library</a><a class="button button-secondary" href="/register.php">Create student account</a></div>
        <a class="landing-jump" href="#how-borrowing-works">See how borrowing works <span aria-hidden="true">↓</span></a>
    </div>
    <figure class="landing-photo"><img src="/assets/library-reading.jpg" width="1200" height="800" alt="An open book and blue and gray books on a sunlit library desk" fetchpriority="high" decoding="async"><figcaption>Illustrative library setting.</figcaption></figure>
</section>
<section class="landing-process" id="how-borrowing-works" aria-labelledby="borrowing-title">
    <figure class="landing-study"><img src="/assets/library-study.jpg" width="900" height="600" alt="An open book, notebook and blue pen arranged for a reading session" loading="lazy" decoding="async"></figure>
    <div>
        <h2 id="borrowing-title">Find it. Request it.<br>Make time to read.</h2>
        <p>Your next read starts here. Register as a student and ask your librarian to activate borrowing access.</p>
        <ol class="landing-steps">
            <li><h3>Explore the catalog</h3><p>Sign in to search by title, author or category and check which books are available.</p></li>
            <li><h3>Send a request</h3><p>Choose your requested days and explain why you need the book. A librarian reviews the final terms.</p></li>
            <li><h3>Collect your book</h3><p>Once approved, collect it from the librarian within 48 hours. Borrowing starts at physical pickup.</p></li>
            <li><h3>Return and keep exploring</h3><p>Bring the book back to the librarian by its due date. Follow your requests and loan history in your account.</p></li>
        </ol>
    </div>
</section>
<section class="landing-close" aria-labelledby="start-title"><div><h2 id="start-title">Your next chapter starts here.</h2><p>Create your student account, then explore the library.</p></div><a class="button" href="/register.php">Create an account</a></section>
HTML;
}
