<?php
declare(strict_types=1);

function landing_html(): string
{
    return <<<'HTML'
<section class="hero landing-hero">
<div class="hero-copy enter">
<p class="eyebrow">Your campus. Your library.</p>
<h1>Good reads.<br>Great ideas.<br>
<em>Start here.</em>
</h1>
<p class="hero-description">Find the books for your course, your next project, or your own curiosity. Keep every request and loan in one place.</p>
<div class="hero-actions">
<a class="btn" href="/login.php">Log in to the library<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<path d="M4 12h15m-6-6 6 6-6 6"/>
</svg>
</a>
<a class="btn secondary" href="/register.php">Create student account</a>
</div>
<p class="hero-note">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<rect x="2" y="5" width="20" height="15" rx="2"/>
<circle cx="8" cy="11" r="2"/>
<path d="M5 17c0-4 6-4 6 0m3-7h5m-5 4h4"/>
</svg>New here? Have your school ID ready.</p>
<a class="entry-process-link" href="#how-borrowing-works">See how borrowing works ↓</a>
</div>
<figure class="hero-media enter-later">
<div class="hero-photo">
<img src="/assets/library-reading.jpg" width="1200" height="800" alt="An open book on a sunlit library desk, with blue books and shelves behind it" fetchpriority="high">
</div>
<figcaption>Illustrative library setting</figcaption>
<div class="reading-slip">
<span class="slip-icon">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<path d="M12 5v15m0-15C8 2 5 3 2 4v15c3-1 6-1 10 1m0-15c4-3 7-2 10-1v15c-3-1-6-1-10 1"/>
</svg>
</span>
<div>
<strong>A little less searching.<br>A lot more reading.</strong>
<p>Find a book. Send a request. Pick it up.</p>
</div>
<span class="slip-rule">
</span>
</div>
</figure>
</section>
<div class="discovery-line">
<p>From coursework<br>to personal curiosity.</p>
<div class="subjects">
<span>Technology</span>
<span>Health sciences</span>
<span>Business</span>
<span>Literature & arts</span>
</div>
</div>
<section class="process" id="how-borrowing-works" aria-labelledby="process-heading">
<div class="section-heading">
<div>
<p class="eyebrow">Borrowing, made clear</p>
<h2 id="process-heading">From the shelf to your hands.</h2>
</div>
<p>Create an account, then show your school ID to a librarian to activate borrowing.</p>
</div>
<ol class="steps">
<li>
<div class="step-num">
<span>01</span>
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<circle cx="10" cy="10" r="6"/>
<path d="m15 15 5 5"/>
</svg>
</div>
<h3>Find a book</h3>
<p>Log in to explore titles, authors and course categories. Check which copies are available.</p>
</li>
<li>
<div class="step-num">
<span>02</span>
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<path d="M8 4H5v17h14V4h-3M9 2h6v5H9zm-1 9h8m-8 4h5"/>
</svg>
</div>
<h3>Request your read</h3>
<p>Choose your requested days and add a reason. Your librarian reviews the request.</p>
</li>
<li>
<div class="step-num">
<span>03</span>
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<path d="M12 5v15m0-15C8 2 5 3 2 4v15c3-1 6-1 10 1m0-15c4-3 7-2 10-1v15c-3-1-6-1-10 1"/>
</svg>
</div>
<h3>Collect in person</h3>
<p>Once approved, pick up your book within 48 hours. Your loan starts at handover.</p>
</li>
<li>
<div class="step-num">
<span>04</span>
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<path d="M4 9h10a6 6 0 0 1 0 12m-6-7L3 9l5-5"/>
</svg>
</div>
<h3>Return & repeat</h3>
<p>Bring the copy back by its due date. Your borrowing history stays in your account.</p>
</li>
</ol>
</section>
<section class="closing">
<div>
<h2>Make room for your next read.</h2>
<p>Your student account is the first step. Your librarian will help with the rest.</p>
</div>
<a href="/register.php" class="btn">Create student account<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<path d="M4 12h15m-6-6 6 6-6 6"/>
</svg>
</a>
</section>
HTML;
}
