<form class="form reveal" method="POST" action="{{ url('/contact') }}">
    @csrf
    <input type="text" name="website" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px" aria-hidden="true">
    <input type="hidden" name="source_page" value="{{ request()->path() }}">

    <div class="field-row">
        <div class="field">
            <label>Name</label>
            <input name="name" required placeholder="Your full name" value="{{ old('name') }}">
            @error('name') <small class="err">{{ $message }}</small> @enderror
        </div>
        <div class="field">
            <label>Company</label>
            <input name="company" placeholder="Optional" value="{{ old('company') }}">
            @error('company') <small class="err">{{ $message }}</small> @enderror
        </div>
    </div>
    <div class="field-row">
        <div class="field">
            <label>Email</label>
            <input name="email" type="email" required placeholder="you@studio.com" value="{{ old('email') }}">
            @error('email') <small class="err">{{ $message }}</small> @enderror
        </div>
        <div class="field">
            <label>Phone</label>
            <input name="phone" type="tel" placeholder="+91" value="{{ old('phone') }}">
            @error('phone') <small class="err">{{ $message }}</small> @enderror
        </div>
    </div>
    <div class="field">
        <label>Project type</label>
        <select name="project_type">
            <option {{ old('project_type') == 'Brand film / commercial' ? 'selected' : '' }}>Brand film / commercial</option>
            <option {{ old('project_type') == 'Corporate event' ? 'selected' : '' }}>Corporate event</option>
            <option {{ old('project_type') == 'Product launch' ? 'selected' : '' }}>Product launch</option>
            <option {{ old('project_type') == 'Wedding' ? 'selected' : '' }}>Wedding</option>
            <option {{ old('project_type') == 'Editorial / photography' ? 'selected' : '' }}>Editorial / photography</option>
            <option {{ old('project_type') == 'Post-production only' ? 'selected' : '' }}>Post-production only</option>
            <option {{ old('project_type') == 'Other' ? 'selected' : '' }}>Other</option>
        </select>
        @error('project_type') <small class="err">{{ $message }}</small> @enderror
    </div>
    <div class="field-row">
        <div class="field">
            <label>Budget</label>
            <select name="budget">
                <option {{ old('budget') == '—' ? 'selected' : '' }}>—</option>
                <option {{ old('budget') == 'Under ₹5L' ? 'selected' : '' }}>Under ₹5L</option>
                <option {{ old('budget') == '₹5L – ₹15L' ? 'selected' : '' }}>₹5L – ₹15L</option>
                <option {{ old('budget') == '₹15L – ₹50L' ? 'selected' : '' }}>₹15L – ₹50L</option>
                <option {{ old('budget') == '₹50L+' ? 'selected' : '' }}>₹50L+</option>
            </select>
            @error('budget') <small class="err">{{ $message }}</small> @enderror
        </div>
        <div class="field">
            <label>Timeline</label>
            <select name="timeline">
                <option {{ old('timeline') == 'Flexible' ? 'selected' : '' }}>Flexible</option>
                <option {{ old('timeline') == 'Within 2 weeks' ? 'selected' : '' }}>Within 2 weeks</option>
                <option {{ old('timeline') == '1–2 months' ? 'selected' : '' }}>1–2 months</option>
                <option {{ old('timeline') == '3+ months' ? 'selected' : '' }}>3+ months</option>
            </select>
            @error('timeline') <small class="err">{{ $message }}</small> @enderror
        </div>
    </div>
    <div class="field">
        <label>Tell us about it</label>
        <textarea name="message" rows="5" placeholder="A few sentences about goals, audience, and references.">{{ old('message') }}</textarea>
        @error('message') <small class="err">{{ $message }}</small> @enderror
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-top:8px">
        <span style="font-family:var(--f-mono);font-size:11px;letter-spacing:.18em;color:var(--muted)">PROTECTED · NO SPAM</span>
        <button type="submit" class="btn" data-magnetic data-cursor="SEND">Send brief <span class="arr"></span></button>
    </div>
</form>
