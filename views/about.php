<?php

declare(strict_types=1);

function about_html(): string
{
    return <<<'HTML'
<article class="about-page">
    <header class="about-intro">
        <h1>About KaAklat</h1>
        <p class="about-lead">A place for books, learning, and discovery.</p>
        <p>KaAklat helps students browse books and follow their borrowing requests. Librarians manage requests, record physical pickups, and receive returned books.</p>
        <p class="muted">This is a student-developed school demonstration. Its borrowing rules are demonstration settings, not a statement of official university library policy.</p>
    </header>

    <section class="panel about-school" aria-labelledby="about-school-heading">
        <h2 id="about-school-heading">Universidad de Dagupan</h2>
        <p>Universidad de Dagupan (UdD), formerly Colegio de Dagupan, is located on Arellano Street, Pantal, Dagupan City, Pangasinan. Its educational philosophy emphasizes developing individual abilities for personal fulfillment and community uplift.</p>
        <h3>Mission and vision — summary</h3>
        <p>The university aims to prepare individuals for society’s needs and build a community that responds to a changing world. Its objectives include critical thinking, research, community service, discipline, justice, and equality.</p>
        <h3>Core values</h3>
        <p>Competence, Unity, Dynamism, Dedication, Integrity, and Teamwork.</p>
        <p class="muted">The school introduction and mission and vision above summarize the university’s official information.</p>
        <a href="https://udd.edu.ph/">Visit the official Universidad de Dagupan website</a>
    </section>

    <section class="about-borrowing" aria-labelledby="about-borrowing-heading">
        <h2 id="about-borrowing-heading">How borrowing works</h2>
        <p>From finding a book to bringing it back, each step stays visible in My requests &amp; loans.</p>
        <ol class="about-steps">
            <li><h3>Create your account</h3><p>Register with your student details and school ID.</p></li>
            <li><h3>Wait for account approval</h3><p>A librarian checks your details before activating borrowing access. You can sign in and browse while your account is pending.</p></li>
            <li><h3>Find and request a book</h3><p>Sign in to browse the catalog. Once your account is active, request an available book and choose your borrowing duration.</p></li>
            <li><h3>Check the librarian’s decision</h3><p>Follow your request in My requests &amp; loans. An approved request holds a copy for pickup; it does not mean the book has been collected.</p></li>
            <li><h3>Collect your book</h3><p>Visit the library before the displayed pickup deadline. The librarian records the handover, and your loan begins with its final due date.</p></li>
            <li><h3>Return it on time</h3><p>Bring the book back by its due date. The librarian records the return, and your borrowing history remains available.</p></li>
        </ol>
    </section>
    <section class="panel about-developer" aria-labelledby="about-developer-heading">
        <img class="developer-photo" src="/assets/emmanuel-sunga.jpg" width="180" height="180" loading="lazy" alt="Emmanuel C. Sunga, developer of KaAklat">
        <div>
            <h2 id="about-developer-heading">Meet the developer</h2>
            <h3>Emmanuel C. Sunga</h3>
            <p>Third-year Computer Science student at Universidad de Dagupan.</p>
            <p>Developer of KaAklat.</p>
            <a href="mailto:emmanuelsunga200527@gmail.com">emmanuelsunga200527@gmail.com</a>
        </div>
    </section>
</article>
HTML;
}
