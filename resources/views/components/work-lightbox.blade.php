@props(['label' => 'Media'])

{{-- The single media viewer shared by every grid/collage on a page. Extracted so
     more than one media component can appear together without emitting two
     dialogs — work-lightbox.js binds to the first [data-work-lightbox] it finds. --}}
@once
<div class="wlb" data-work-lightbox hidden role="dialog" aria-modal="true" aria-label="{{ $label }}">
    <button class="wlb__close" data-wlb-close aria-label="Close">&times;</button>
    <button class="wlb__nav wlb__nav--prev" data-wlb-prev aria-label="Previous">&#8249;</button>
    <div class="wlb__stage" data-wlb-stage></div>
    <button class="wlb__nav wlb__nav--next" data-wlb-next aria-label="Next">&#8250;</button>
    <p class="wlb__caption" data-wlb-caption></p>
</div>
@endonce
