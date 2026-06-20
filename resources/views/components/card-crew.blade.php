@props(['member'])
<article class="card card--crew">
    @if ($img = $member->getFirstMediaUrl('headshot'))
        <img src="{{ $img }}" alt="">
    @endif
    <a href="{{ url('/crew/'.$member->slug) }}">
        <h3>{{ $member->name }}</h3>
        <p>{{ $member->role }}</p>
    </a>
</article>
