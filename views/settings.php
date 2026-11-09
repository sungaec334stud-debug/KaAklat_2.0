<?php

declare(strict_types=1);

function appearance_settings_html(): string
{
    return <<<'HTML'
<div class="settings-page">
    <h1>Settings</h1>
    <p>Make KaAklat comfortable to read.</p>
    <section class="panel appearance-panel" aria-labelledby="appearance-heading">
        <h2 id="appearance-heading">Appearance</h2>
        <noscript><p class="flash flash-info">Your device’s color theme is used automatically. Enable JavaScript to choose a different appearance and save it in this browser.</p></noscript>
        <fieldset id="appearance-options" class="appearance-options" disabled aria-describedby="appearance-help">
            <legend>Choose your color theme</legend>
            <label class="appearance-option">
                <span class="theme-sample theme-sample-system" aria-hidden="true"><span></span></span>
                <span class="appearance-choice"><input type="radio" name="appearance" value="system" checked><span>System default</span></span>
                <span class="appearance-description">Follow your device</span>
            </label>
            <label class="appearance-option">
                <span class="theme-sample theme-sample-light" aria-hidden="true"><span></span></span>
                <span class="appearance-choice"><input type="radio" name="appearance" value="light"><span>Light</span></span>
                <span class="appearance-description">Bright reading surfaces</span>
            </label>
            <label class="appearance-option">
                <span class="theme-sample theme-sample-dark" aria-hidden="true"><span></span></span>
                <span class="appearance-choice"><input type="radio" name="appearance" value="dark"><span>Dark</span></span>
                <span class="appearance-description">A softer screen in low light</span>
            </label>
        </fieldset>
        <p id="appearance-help">Changes apply immediately across KaAklat. Your choice is saved in this browser, including on shared devices. It does not change anyone’s account settings.</p>
        <p id="appearance-status" class="appearance-status" role="status" aria-live="polite"></p>
    </section>
</div>
HTML;
}
