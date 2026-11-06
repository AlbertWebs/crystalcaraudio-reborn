@extends('store.layout')
@section('content')
@if($slug === 'find-us')
<section class="contact-hero">
    <div>
        <p class="eyebrow">CONTACT CRYSTAL</p>
        <h1>Let’s get your sound right.</h1>
        <p>Talk with our team about product advice, installation, and finding the right setup for your car.</p>
    </div>
    <a class="button button-dark" href="https://wa.me/254790721397">Chat on WhatsApp <span aria-hidden="true">↗</span></a>
</section>
<section class="contact-directory" aria-label="Crystal Car Audio contact information">
    <article class="contact-card contact-card-primary">
        <span class="contact-card-icon" aria-hidden="true">◉</span>
        <p class="eyebrow">FASTEST WAY TO REACH US</p>
        <h2>WhatsApp</h2>
        <p>Message our team for product guidance, fitment questions, or installation support.</p>
        <a href="https://wa.me/254790721397">+254 790 721 397 <span aria-hidden="true">↗</span></a>
    </article>
    <article class="contact-card">
        <span class="contact-card-icon" aria-hidden="true">⌕</span>
        <p class="eyebrow">CALL OUR TEAM</p>
        <h2>Phone</h2>
        <p>Prefer to speak with someone? Give Crystal a call.</p>
        <a href="tel:+254790721397">+254 790 721 397 <span aria-hidden="true">↗</span></a>
    </article>
    <article class="contact-card">
        <span class="contact-card-icon" aria-hidden="true">✉</span>
        <p class="eyebrow">SEND US AN EMAIL</p>
        <h2>Email</h2>
        <p>For product questions, orders, and other enquiries.</p>
        <a href="mailto:info@crystalcaraudio.com">info@crystalcaraudio.com <span aria-hidden="true">↗</span></a>
    </article>
    <article class="contact-card">
        <span class="contact-card-icon" aria-hidden="true">⌖</span>
        <p class="eyebrow">FIND CRYSTAL</p>
        <h2>Nairobi, Kenya</h2>
        <p>Visit our location in Nairobi. We serve drivers across Kenya.</p>
        <a href="https://www.google.com/maps/search/?api=1&amp;query=Crystal+Car+Audio+Nairobi+Kenya" target="_blank" rel="noopener noreferrer">Open in Google Maps <span aria-hidden="true">↗</span></a>
    </article>
</section>
@else
<section class="page-hero compact info-hero">
    <p class="eyebrow">CRYSTAL CAR AUDIO</p>
    <h1>{{ ucwords(str_replace('-', ' ', $slug)) }}</h1>
    @if($records->isEmpty())
        <p>Our team is ready to help with any questions.</p>
    @endif
    <a class="button button-dark" href="https://wa.me/254790721397">Contact our team <span aria-hidden="true">↗</span></a>
</section>

@if($records->isNotEmpty())
    <section class="section info-content" aria-label="{{ ucwords(str_replace('-', ' ', $slug)) }}">
        @foreach($records as $record)
            @php
                $title = trim(strip_tags((string) ($record['title'] ?? $record['name'] ?? '')));
                $bodyParts = [];
                foreach (['content', 'description', 'details'] as $field) {
                    if (empty($record[$field])) continue;
                    $body = html_entity_decode((string) $record[$field], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $body = preg_replace('/<\/?(?:p|div|li|h[1-6]|section|article)\b[^>]*>|<br\s*\/?>/i', ' ', $body);
                    $body = strip_tags($body);
                    $body = preg_replace('/@(?:endsection|section|endforelse|endforeach|endif|if|foreach)\b(?:\s*\([^)]*\))?/', '', $body);
                    $body = str_replace(['\\r\\n', '\\n', '\\r'], ' ', $body);
                    $body = preg_replace('/Last updated:\s*[^.]+\.?/i', '', $body);
                    $body = preg_replace('/\bAmani Vehicle Sounds\b/i', 'Crystal Car Audio', $body);
                    $body = preg_replace('/\bwww\.amanivehiclesounds\.co\.ke\b|\bamanivehiclesounds\.co\.ke\b/i', 'crystalcaraudio.com', $body);
                    $body = preg_replace('/\s+/', ' ', trim($body));
                    if ($body !== '') $bodyParts[] = $body;
                }
            @endphp
            @if($title !== '' || count($bodyParts))
                <article class="info-record">
                    @if($title !== '')<h2>{{ $title }}</h2>@endif
                    @foreach($bodyParts as $body)<p>{{ $body }}</p>@endforeach
                    @if(!empty($record['date']))<small>{{ $record['date'] }}</small>@endif
                </article>
            @endif
        @endforeach
    </section>
@endif
@endif

<style>
    .contact-hero,.contact-directory{width:min(1200px,calc(100% - 48px));margin-inline:auto;font-family:'Outfit','Cairo','Helvetica Neue',Helvetica,Arial,sans-serif}
    .contact-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:32px;padding:64px 0 38px;border-bottom:1px solid var(--line)}
    .contact-hero .eyebrow,.contact-card .eyebrow{font:600 12px/24px 'Outfit','Cairo','Helvetica Neue',Helvetica,Arial,sans-serif;letter-spacing:.14em;color:var(--brand);margin:0 0 12px}
    .contact-hero h1{max-width:680px;margin:0;font:600 clamp(40px,5vw,60px)/1.08 'Outfit','Cairo','Helvetica Neue',Helvetica,Arial,sans-serif;letter-spacing:-.045em;color:var(--ink)}
    .contact-hero>div>p:last-child{max-width:560px;margin:17px 0 0;font:400 16px/24px 'Outfit','Cairo','Helvetica Neue',Helvetica,Arial,sans-serif;color:#646a66}
    .contact-hero>.button{flex:0 0 auto;min-height:48px;font:600 14px/24px 'Outfit','Cairo','Helvetica Neue',Helvetica,Arial,sans-serif}
    .contact-directory{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;padding:30px 0 76px}
    .contact-card{position:relative;min-height:250px;padding:28px;background:#fff;border:1px solid #e7eae7;border-radius:8px;display:flex;flex-direction:column;align-items:flex-start;transition:transform .2s,border-color .2s,box-shadow .2s}
    .contact-card:hover{transform:translateY(-3px);border-color:#c8ddd6;box-shadow:0 12px 26px rgba(22,44,34,.07)}
    .contact-card-primary{background:linear-gradient(140deg,#edf6f2,#fff 75%);border-color:#dcebe4}
    .contact-card-icon{width:44px;height:44px;display:grid;place-items:center;margin-bottom:19px;background:#e4f1ed;border-radius:50%;color:var(--brand);font-size:22px}
    .contact-card .eyebrow{margin-bottom:6px;color:#79847d}
    .contact-card h2{margin:0;color:var(--ink);font:600 22px/28px 'Outfit','Cairo','Helvetica Neue',Helvetica,Arial,sans-serif}
    .contact-card>p:not(.eyebrow){max-width:450px;margin:9px 0 18px;color:#6a706c;font:400 16px/24px 'Outfit','Cairo','Helvetica Neue',Helvetica,Arial,sans-serif}
    .contact-card>a{display:inline-flex;align-items:center;gap:12px;margin-top:auto;color:var(--brand);font:600 16px/24px 'Outfit','Cairo','Helvetica Neue',Helvetica,Arial,sans-serif;overflow-wrap:anywhere}
    .contact-card>a span{font-size:14px;transition:transform .2s}
    .contact-card>a:hover span{transform:translate(2px,-2px)}
    .contact-card>a:focus-visible,.contact-hero>.button:focus-visible{outline:2px solid #72c9bd;outline-offset:4px}
    .info-hero{padding-top:52px;padding-bottom:52px}
    .info-hero h1{font-size:clamp(38px,5vw,58px)}
    .info-content{max-width:960px;margin:0 auto;padding-top:48px;padding-bottom:72px}
    .info-record{padding:0 0 28px;margin:0 0 28px;border-bottom:1px solid var(--line)}
    .info-record:last-child{margin-bottom:0}
    .info-record h2{font:600 24px/1.3 'Outfit','Cairo','Helvetica Neue',Helvetica,Arial,sans-serif;letter-spacing:-.025em;margin:0 0 12px;color:var(--ink)}
    .info-record p{font:400 16px/1.8 'Outfit','Cairo','Helvetica Neue',Helvetica,Arial,sans-serif;color:#555;margin:0 0 12px;overflow-wrap:anywhere}
    .info-record small{font:400 12px/1.8 'Outfit','Cairo','Helvetica Neue',Helvetica,Arial,sans-serif;color:#76777c}
    @media(max-width:760px){.contact-hero,.contact-directory{width:calc(100% - 36px)}.contact-hero{align-items:flex-start;flex-direction:column;padding:42px 0 26px;gap:21px}.contact-hero h1{font-size:40px}.contact-directory{grid-template-columns:1fr;gap:12px;padding:22px 0 52px}.contact-card{min-height:220px;padding:22px}.contact-card-icon{margin-bottom:14px}.contact-card>p:not(.eyebrow){font-size:16px;line-height:24px}.info-hero{padding-top:38px;padding-bottom:38px}.info-content{padding-top:32px;padding-bottom:48px}.info-record h2{font-size:21px}.info-record p{font-size:15px}}
</style>
@endsection
