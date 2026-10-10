<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$flash = $_SESSION['registration_flash'] ?? [];
unset($_SESSION['registration_flash']);

$formValues = is_array($flash['values'] ?? null) ? $flash['values'] : [];
$formErrors = is_array($flash['errors'] ?? null) ? $flash['errors'] : [];
$formStatus = is_string($flash['status'] ?? null) ? $flash['status'] : '';
$successMessage = is_string($flash['success'] ?? null) ? $flash['success'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f7f4">
    <meta name="description" content="Create your customer profile with Luma. Share your contact details to get started.">
    <title>Customer Registration </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js" defer></script>
</head>
<body>
    <a class="skip-link" href="#contact-form">Skip to the form</a>

    <header class="site-header">
        <a class="brand" href="index.php" aria-label="Luma home">
            <span class="brand-mark" aria-hidden="true">
                
            </span>
            
        </a>
        <a class="header-link" href="#contact-form">Get started <span aria-hidden="true">↗</span></a>
    </header>

    <main class="page-shell">
        <section class="intro" aria-labelledby="page-title">
            <p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span> Sign Up</p>
            <h1 id="page-title">Good things<br>start <span>here.</span></h1>
            <p class="intro-copy">Create your customer profile to get started. Share your contact details and a little about what you’re looking for.</p>

            <div class="response-note">
                <span class="note-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="m5 12.5 4.25 4.25L19 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span><strong>Just the essentials.</strong><br>A few details are all it takes.</span>
            </div>

            <div class="decorative-orbit" aria-hidden="true">
                <span class="orbit orbit-one"></span>
                <span class="orbit orbit-two"></span>
                <span class="orbit orbit-three"></span>
                <span class="orbit-center"></span>
            </div>
        </section>

        <section class="form-card" aria-labelledby="form-title">
            <div class="form-heading">
                <div>
                    <p class="form-kicker">GET STARTED</p>
                    <h2 id="form-title">Create your profile</h2>
                </div>
                <span class="form-tag">NEW CUSTOMER</span>
            </div>

            <form id="contact-form" action="submit.php" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                <div class="field">
                    <label for="fullName">Full name <span aria-hidden="true">*</span></label>
                    <input
                        type="text"
                        id="fullName"
                        name="fullName"
                        placeholder="e.g. Alex Morgan"
                        autocomplete="name"
                        minlength="2"
                        maxlength="100"
                        aria-describedby="fullName-error"
                        <?= isset($formErrors['fullName']) ? 'aria-invalid="true"' : '' ?>
                        value="<?= escape_html(is_string($formValues['fullName'] ?? null) ? $formValues['fullName'] : '') ?>"
                        required
                    >
                    <span class="field-error" id="fullName-error"><?= escape_html(is_string($formErrors['fullName'] ?? null) ? $formErrors['fullName'] : '') ?></span>
                </div>

                <div class="field">
                    <label for="email">Email address <span aria-hidden="true">*</span></label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="alex@example.com"
                        autocomplete="email"
                        maxlength="254"
                        aria-describedby="email-error"
                        <?= isset($formErrors['email']) ? 'aria-invalid="true"' : '' ?>
                        value="<?= escape_html(is_string($formValues['email'] ?? null) ? $formValues['email'] : '') ?>"
                        required
                    >
                    <span class="field-error" id="email-error"><?= escape_html(is_string($formErrors['email'] ?? null) ? $formErrors['email'] : '') ?></span>
                </div>

                <div class="field">
                    <label for="phone">Phone number <span aria-hidden="true">*</span></label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        placeholder="+1 (555) 000-0000"
                        autocomplete="tel"
                        maxlength="30"
                        aria-describedby="phone-hint phone-error"
                        <?= isset($formErrors['phone']) ? 'aria-invalid="true"' : '' ?>
                        value="<?= escape_html(is_string($formValues['phone'] ?? null) ? $formValues['phone'] : '') ?>"
                        required
                    >
                    <span class="field-hint" id="phone-hint">Include your country code for international numbers.</span>
                    <span class="field-error" id="phone-error"><?= escape_html(is_string($formErrors['phone'] ?? null) ? $formErrors['phone'] : '') ?></span>
                </div>

                <div class="field">
                    <div class="label-row">
                        <label for="message">Anything else we should know? <span aria-hidden="true">*</span></label>
                        <span class="character-count" id="message-count">0 / 500</span>
                    </div>
                    <textarea
                        id="message"
                        name="message"
                        rows="4"
                        placeholder="Tell us a little about what you’re looking for..."
                        minlength="10"
                        maxlength="500"
                        aria-describedby="message-hint message-error"
                        <?= isset($formErrors['message']) ? 'aria-invalid="true"' : '' ?>
                        required
                    ><?= escape_html(is_string($formValues['message'] ?? null) ? $formValues['message'] : '') ?></textarea>
                    <span class="field-hint" id="message-hint">A few details help us make your experience a good one.</span>
                    <span class="field-error" id="message-error"><?= escape_html(is_string($formErrors['message'] ?? null) ? $formErrors['message'] : '') ?></span>
                </div>

                <button class="submit-button" type="submit">
                    <span>Create profile</span>
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <p class="form-footnote">By registering, you agree that we may contact you about your account.</p>
                <p class="form-status<?= $successMessage !== '' ? ' form-status-success' : '' ?>" id="form-status" role="status" aria-live="polite"><?= escape_html($successMessage !== '' ? $successMessage : $formStatus) ?></p>
            </form>
        </section>
    </main>

    <footer class="site-footer">
        <span>© <span id="current-year"><?= date('Y') ?></span> Here for you.</span>
        <span>We’re glad you’re here.</span>
    </footer>
</body>
</html>
