@props(['label' => 'Media'])

{{-- The single media viewer shared by every grid/collage on a page. Extracted so
     more than one media component can appear together without emitting two
     dialogs — work-lightbox.js binds to the first [data-work-lightbox] it finds. --}}
@once
<div class="wlb" data-work-lightbox hidden role="dialog" aria-modal="true" aria-label="{{ $label }}">
    {{-- The auditorium around the screen. Purely decorative and aria-hidden: the
         dialog already carries its label, and a screen reader has nothing to
         gain from being told there are drapes. Real elements rather than more
         pseudo-elements because the room needs more layers than .wlb has, and
         the seats have to sit in FRONT of the picture while the drapes sit
         behind it. --}}
    <div class="wlb__house" aria-hidden="true">
        <span class="wlb__valance"></span>
        <span class="wlb__drape wlb__drape--l"></span>
        <span class="wlb__drape wlb__drape--r"></span>
        <span class="wlb__seats"></span>
    </div>
    <button class="wlb__close" data-wlb-close aria-label="Close">&times;</button>
    <button class="wlb__nav wlb__nav--prev" data-wlb-prev aria-label="Previous">&#8249;</button>
    <div class="wlb__stage" data-wlb-stage></div>
    <button class="wlb__nav wlb__nav--next" data-wlb-next aria-label="Next">&#8250;</button>
    <p class="wlb__caption" data-wlb-caption></p>
</div>
@endonce
