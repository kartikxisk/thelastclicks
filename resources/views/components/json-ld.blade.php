@props(['data' => []])
<script type="application/ld+json">{!! json_encode(array_merge(['@context'=>'https://schema.org'], $data), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
