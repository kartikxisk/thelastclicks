<form class="form reveal" method="POST" action="{{ url('/contact') }}" novalidate>
    @csrf
    <input type="text" name="website" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px" aria-hidden="true">
    <input type="hidden" name="source_page" value="{{ request()->path() }}">

    {{-- Error summary: focusable and announced, so a screen reader hears what
         failed on the reloaded page and can jump straight to the fields. --}}
    @if ($errors->any())
        <div class="form-errors" role="alert" tabindex="-1" data-error-summary>
            <strong>Please fix the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="field-row">
        <div class="field">
            <label for="f-name">Name</label>
            <input id="f-name" name="name" required autocomplete="name" placeholder="Your full name" value="{{ old('name') }}"
                @error('name') aria-invalid="true" aria-describedby="f-name-err" @enderror>
            @error('name') <small class="err" id="f-name-err">{{ $message }}</small> @enderror
        </div>
        <div class="field">
            <label for="f-company">Company</label>
            <input id="f-company" name="company" autocomplete="organization" placeholder="Optional" value="{{ old('company') }}"
                @error('company') aria-invalid="true" aria-describedby="f-company-err" @enderror>
            @error('company') <small class="err" id="f-company-err">{{ $message }}</small> @enderror
        </div>
    </div>
    <div class="field-row">
        <div class="field">
            <label for="f-email">Email</label>
            <input id="f-email" name="email" type="email" required autocomplete="email" placeholder="you@studio.com" value="{{ old('email') }}"
                @error('email') aria-invalid="true" aria-describedby="f-email-err" @enderror>
            @error('email') <small class="err" id="f-email-err">{{ $message }}</small> @enderror
        </div>
        <div class="field">
            <label for="f-phone">Phone</label>
            <input id="f-phone" name="phone" type="tel" autocomplete="tel" placeholder="+91" value="{{ old('phone') }}"
                @error('phone') aria-invalid="true" aria-describedby="f-phone-err" @enderror>
            @error('phone') <small class="err" id="f-phone-err">{{ $message }}</small> @enderror
        </div>
    </div>
    <div class="field">
        <label for="f-project-type">Project type</label>
        <select id="f-project-type" name="project_type" @error('project_type') aria-invalid="true" aria-describedby="f-project-type-err" @enderror>
            <option {{ old('project_type') == 'Brand film / commercial' ? 'selected' : '' }}>Brand film / commercial</option>
            <option {{ old('project_type') == 'Corporate event' ? 'selected' : '' }}>Corporate event</option>
            <option {{ old('project_type') == 'Product launch' ? 'selected' : '' }}>Product launch</option>
            <option {{ old('project_type') == 'Wedding' ? 'selected' : '' }}>Wedding</option>
            <option {{ old('project_type') == 'Editorial / photography' ? 'selected' : '' }}>Editorial / photography</option>
            <option {{ old('project_type') == 'Post-production only' ? 'selected' : '' }}>Post-production only</option>
            <option {{ old('project_type') == 'Other' ? 'selected' : '' }}>Other</option>
        </select>
        @error('project_type') <small class="err" id="f-project-type-err">{{ $message }}</small> @enderror
    </div>
    <div class="field-row">
        <div class="field">
            <label for="f-budget">Budget</label>
            <select id="f-budget" name="budget" @error('budget') aria-invalid="true" aria-describedby="f-budget-err" @enderror>
                <option value="" {{ old('budget') === null || old('budget') === '' ? 'selected' : '' }}>Select a range</option>
                <option {{ old('budget') == 'Under ₹5L' ? 'selected' : '' }}>Under ₹5L</option>
                <option {{ old('budget') == '₹5L – ₹15L' ? 'selected' : '' }}>₹5L – ₹15L</option>
                <option {{ old('budget') == '₹15L – ₹50L' ? 'selected' : '' }}>₹15L – ₹50L</option>
                <option {{ old('budget') == '₹50L+' ? 'selected' : '' }}>₹50L+</option>
            </select>
            @error('budget') <small class="err" id="f-budget-err">{{ $message }}</small> @enderror
        </div>
        <div class="field">
            <label for="f-timeline">Timeline</label>
            <select id="f-timeline" name="timeline" @error('timeline') aria-invalid="true" aria-describedby="f-timeline-err" @enderror>
                <option {{ old('timeline') == 'Flexible' ? 'selected' : '' }}>Flexible</option>
                <option {{ old('timeline') == 'Within 2 weeks' ? 'selected' : '' }}>Within 2 weeks</option>
                <option {{ old('timeline') == '1–2 months' ? 'selected' : '' }}>1–2 months</option>
                <option {{ old('timeline') == '3+ months' ? 'selected' : '' }}>3+ months</option>
            </select>
            @error('timeline') <small class="err" id="f-timeline-err">{{ $message }}</small> @enderror
        </div>
    </div>
    <div class="field">
        <label for="f-message">Tell us about it</label>
        <textarea id="f-message" name="message" rows="5" placeholder="A few sentences about goals, audience, and references."
            @error('message') aria-invalid="true" aria-describedby="f-message-err" @enderror>{{ old('message') }}</textarea>
        @error('message') <small class="err" id="f-message-err">{{ $message }}</small> @enderror
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-top:8px">
        <span style="font-family:var(--f-mono);font-size:11px;letter-spacing:.18em;color:var(--muted)">PROTECTED · NO SPAM</span>
        <button type="submit" class="btn" data-magnetic data-cursor="SEND">Send brief <span class="arr"></span></button>
    </div>
</form>
