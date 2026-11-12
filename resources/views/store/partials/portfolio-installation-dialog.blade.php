<dialog class="portfolio-request-dialog" data-installation-dialog aria-labelledby="portfolio-request-title">
    <div class="portfolio-request-card">
        <div class="portfolio-request-dialog-head"><div class="portfolio-request-heading"><p class="eyebrow eyebrow-accent">PLAN YOUR UPGRADE</p><h2 id="portfolio-request-title">Request an installation</h2><p>Tell us about your car and the upgrade you have in mind. Our team will check fitment and contact you to agree on a time and quote.</p></div><button class="portfolio-request-close" type="button" data-close-installation-modal aria-label="Close installation request">×</button></div>
        <div class="portfolio-request-progress" data-installation-progress aria-label="Request steps">
            <span data-progress-step="1" aria-current="step"><b>1</b><span>Your details</span></span><i></i><span data-progress-step="2"><b>2</b><span>Your vehicle</span></span><i></i><span data-progress-step="3"><b>3</b><span>Installation</span></span>
        </div>
        <form class="portfolio-request-form" id="portfolio-installation-form" action="{{ route('portfolio.installation-requests.store') }}" method="post" data-installation-request-form novalidate>
            @csrf
            <div class="portfolio-request-trap" aria-hidden="true"><label for="portfolio-company-website">Leave this field empty</label><input id="portfolio-company-website" name="company_website" type="text" tabindex="-1" autocomplete="off"></div>
            <section class="portfolio-request-step" data-installation-step="1" aria-labelledby="installation-step-one-title">
                <h3 id="installation-step-one-title">How can we reach you?</h3><p>We’ll contact you to discuss fitment and arrange the next step.</p>
                <div class="portfolio-request-grid">
                    <label>Your name <span aria-hidden="true">*</span><input name="customer_name" type="text" autocomplete="name" maxlength="190" required></label>
                    <label>Email address <span aria-hidden="true">*</span><input name="email" type="email" autocomplete="email" maxlength="190" required></label>
                    <label>Phone number <span aria-hidden="true">*</span><input name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="e.g. 0712 345 678" maxlength="40" required></label>
                </div>
            </section>
            <section class="portfolio-request-step" data-installation-step="2" aria-labelledby="installation-step-two-title" hidden>
                <h3 id="installation-step-two-title">Which vehicle is this for?</h3><p>Vehicle details help us check product and installation fitment.</p>
                <div class="portfolio-request-grid">
                    <label>Vehicle make <span aria-hidden="true">*</span><input name="vehicle_make" type="text" autocomplete="off" placeholder="e.g. Toyota" maxlength="100" required></label>
                    <label>Vehicle model <span aria-hidden="true">*</span><input name="vehicle_model" type="text" autocomplete="off" placeholder="e.g. Fielder" maxlength="120" required></label>
                    <label>Vehicle year <span class="portfolio-request-optional">Optional</span><input name="vehicle_year" type="number" min="1950" max="{{ now()->year + 1 }}" inputmode="numeric" placeholder="e.g. 2018"></label>
                </div>
            </section>
            <section class="portfolio-request-step" data-installation-step="3" aria-labelledby="installation-step-three-title" hidden>
                <h3 id="installation-step-three-title">What would you like installed?</h3><p>Share your preferred timing and any details that will help us prepare.</p>
                <div class="portfolio-request-grid">
                    <label>Upgrade <span aria-hidden="true">*</span><select name="service" required><option value="">Choose an upgrade</option><option>Android radio upgrade</option><option>Car speaker or subwoofer upgrade</option><option>Amplifier installation</option><option>Reverse camera or dashcam installation</option><option>Alarm or parking sensor installation</option><option>Other</option></select></label>
                    <label class="portfolio-request-service-detail" data-service-detail-wrapper hidden>Tell us about the upgrade <span aria-hidden="true">*</span><input name="service_detail" type="text" maxlength="140" disabled placeholder="What would you like us to install?"></label>
                    <label>Preferred date and time <span class="portfolio-request-optional">Optional</span><input name="preferred_at" type="datetime-local" min="{{ now()->format('Y-m-d\\TH:i') }}"></label>
                    <label>Preferred location <span class="portfolio-request-optional">Optional</span><input name="location" type="text" maxlength="190" placeholder="e.g. Nairobi or nearby area"></label>
                    <label class="portfolio-request-wide">Anything else we should know? <span class="portfolio-request-optional">Optional</span><textarea name="notes" rows="3" maxlength="3000" placeholder="Share your goals, questions or fitment details."></textarea></label>
                </div>
            </section>
            <div class="portfolio-request-feedback" data-installation-feedback role="status" aria-live="polite" tabindex="-1" hidden></div>
            <div class="portfolio-request-wizard-actions"><button class="portfolio-request-back" type="button" data-installation-back hidden>Back</button><p>We’ll use these details only to respond to your installation request.</p><button class="button button-dark" type="button" data-installation-next>Continue <span aria-hidden="true">→</span></button><button class="button button-dark" type="submit" data-installation-submit hidden>Send request <span aria-hidden="true">→</span></button></div>
        </form>
    </div>
</dialog>
