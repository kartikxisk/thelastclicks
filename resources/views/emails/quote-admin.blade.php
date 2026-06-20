<h2>New quote from {{ $quote->name }}</h2>
<ul>
    <li>Email: {{ $quote->email }}</li>
    <li>Phone: {{ $quote->phone ?: '—' }}</li>
    <li>Company: {{ $quote->company ?: '—' }}</li>
    <li>Project: {{ $quote->project_type ?: '—' }}</li>
    <li>Budget: {{ $quote->budget ?: '—' }}</li>
    <li>Timeline: {{ $quote->timeline ?: '—' }}</li>
    <li>Source page: {{ $quote->source_page }}</li>
</ul>
<p>{{ $quote->message }}</p>
