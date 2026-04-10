@extends('layouts.app')
@section('title', 'About Us')

@section('content')

<!-- About Section -->
<section class="about-section" style="padding:clamp(24px,5vw,40px) 20px;background:#ffffff;">
    <div class="container" style="max-width:1200px;margin:0 auto;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;align-items:start;" class="about-grid">
            <div style="text-align:center;">
                <img src="{{ asset('images/zz.png') }}" alt="Zaga Technologies"
                     style="max-width:80%;height:auto;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            </div>
            <div>
                <h2 style="margin-bottom:16px;color:var(--dark-text);font-size:28px;">About Zaga Technologies Ltd</h2>
                <p style="color:var(--dark-text);line-height:1.6;margin-bottom:16px;">
                    At Zaga Technologies Ltd, we believe that access to cutting-edge technology should be within
                    everyone's reach. Our credit solutions are designed to empower individuals and businesses to
                    acquire the latest tech products without the immediate financial burden.
                </p>
                <p style="color:var(--dark-text);line-height:1.6;margin-bottom:16px;">
                    With flexible payment plans and competitive interest rates, we make it easier than ever to stay
                    ahead in the digital age. We also bring you affordable offline courses that can be accessed
                    immediately after purchasing our products.
                </p>
                <p style="color:var(--dark-text);line-height:1.6;">
                    Join us on a journey to unlock the potential of technology through smart financing options
                    tailored to your needs.
                </p>

                <div style="margin-top:24px;display:flex;gap:12px;">
                    <a href="{{ route('shop.index') }}" class="btn-primary">Shop Now</a>
                    <a href="https://wa.me/256700706809" target="_blank" class="btn-secondary">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section style="padding:clamp(24px,5vw,40px) 20px;background:#f8fafc;" id="testimonials-heading">
    <div class="container">
        <h2 style="text-align:center;margin-bottom:32px;">Why Choose Zaga Technologies?</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;">
            @foreach([
                ['💳', 'Flexible Credit', 'Pay in 3 or 6 monthly instalments with rates starting from 0% APR.'],
                ['🚀', 'Fast Approval', 'Quick application process — get your devices delivered within 48 hours.'],
                ['🛡️', 'Trusted & Secure', 'All transactions are transparent with full amortization schedules provided.'],
                ['🎓', 'Digital Skills', 'Free and affordable courses to help you get the most from your technology.'],
            ] as [$icon, $title, $desc])
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px;text-align:center;">
                    <div style="font-size:32px;margin-bottom:12px;">{{ $icon }}</div>
                    <h3 style="margin-bottom:8px;color:#1e293b;">{{ $title }}</h3>
                    <p style="color:#64748b;font-size:14px;line-height:1.5;">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Contact Section -->
<section style="padding:clamp(24px,5vw,40px) 20px;background:#fff;">
    <div class="container" style="max-width:600px;text-align:center;">
        <h2 style="margin-bottom:16px;">Get In Touch</h2>
        <p style="color:#64748b;margin-bottom:24px;">Visit us at our Kampala office or reach out via phone or WhatsApp.</p>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:24px;text-align:left;">
            <p style="margin-bottom:8px;">📍 <strong>Address:</strong> Kabaka Kintu House Level 1 Shop no C-03, Kampala Road, Kampaka, Uganda</p>
            <p style="margin-bottom:8px;">📧 <strong>Email:</strong> sales2.zagatechnologiesltd@gmail.com</p>
            <p>📞 <strong>Phone:</strong> +256 700 706809</p>
        </div>
        <div style="margin-top:20px;">
            <a href="https://wa.me/256700706809" target="_blank" class="btn-primary">Chat on WhatsApp</a>
        </div>
    </div>
</section>

<style>
@media (max-width: 768px) {
    .about-grid { grid-template-columns: 1fr !important; }
}
</style>
@endsection
