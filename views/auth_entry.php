<?php
declare(strict_types=1);

/** Decorative entry illustration; no private account data or form behavior. */
function auth_entry_scene(bool $registration = false): string
{
    if ($registration) {
        return <<<'HTML'
<aside class="auth-scene enter" aria-label="About library access">
<img src="/assets/library-study.jpg" alt="" width="1200" height="800">
<span class="scene-caption">Illustrative library setting</span>
<div class="scene-label">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<path d="M12 5v15m0-15C8 2 5 3 2 4v15c3-1 6-1 10 1m0-15c4-3 7-2 10-1v15c-3-1-6-1-10 1"/>
</svg>
<span>Your campus reading room</span>
</div>
<p class="scene-title">Your school ID.<br>Your library access.</p>
<p class="scene-description">Create your account online. A school ID check with your librarian unlocks borrowing.</p>
<div class="scene-foot">
<img src="/assets/universidad-de-dagupan-seal.png" alt="" width="25" height="25">Universidad de Dagupan</div>
</aside>
HTML;
    }
    return <<<'HTML'
<aside class="auth-scene enter" aria-label="About library access">
<img src="/assets/library-reading.jpg" alt="" width="1200" height="800">
<span class="scene-caption">Illustrative library setting</span>
<div class="scene-label">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<path d="M12 5v15m0-15C8 2 5 3 2 4v15c3-1 6-1 10 1m0-15c4-3 7-2 10-1v15c-3-1-6-1-10 1"/>
</svg>
<span>Your campus reading room</span>
</div>
<p class="scene-title">Pick up where<br>you left off.</p>
<p class="scene-description">Course reading, a new interest, or one more chapter. Your library is ready when you are.</p>
<div class="scene-foot">
<img src="/assets/universidad-de-dagupan-seal.png" alt="" width="25" height="25">Universidad de Dagupan</div>
</aside>
HTML;
}
