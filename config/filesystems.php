<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            // NB: do NOT set a disk-level 'visibility' here. It forces Laravel to build a
            // PortableVisibilityConverter the moment the disk resolves — on every page that
            // asks for a media URL — which hard-crashes if the flysystem-aws-s3-v3 version
            // on the server predates that class. ACL handling lives on the upload fields
            // instead (->visibility('private')), so it only runs during an actual upload.
            // Must throw: with 'throw' => false a rejected PutObject returns false and
            // Filament still saves the path, leaving the site pointing at an object that
            // does not exist. Failing loudly is the only way that surfaces.
            'throw' => env('AWS_THROW', true),
            'report' => false,
        ],

        // Where a company's scanned signature and seal are kept. NOT the media
        // disk: that one is an s3 bucket with CloudFront in front of it, serving
        // the public marketing site its imagery anonymously, and medialibrary
        // writes to a guessable {media_id}/{file_name}. See
        // Company::registerMediaCollections() for the whole failure.
        //
        // Local by default because CloudFront has no origin for storage/, which
        // is the entire requirement. ->visibility('private') is not a substitute:
        // it sets an object ACL, and the CDN is what serves the object —
        // SiteSettingsPage records the same finding on the branding uploads.
        // Driver and root are env-driven so this can move to a genuinely private
        // bucket without a code change; whatever it points at must not be a
        // bucket the CDN fronts.
        'billing_private' => [
            'driver' => env('BILLING_MEDIA_DRIVER', 'local'),
            'root' => env('BILLING_MEDIA_ROOT', storage_path('app/private/billing')),
            // No 'serve' and no 'url': nothing may hand these bytes to a browser
            // by URL. Phase 3 reads them server-side into the dompdf data URI,
            // which is also what lets dompdf's enable_remote stay off.
            'throw' => true,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Private billing media disk
    |--------------------------------------------------------------------------
    |
    | Which disk Company's `signature` and `stamp` collections are stored on.
    | It must never be the media disk, or any other bucket CloudFront fronts —
    | a scanned signature there is anonymously fetchable at a guessable path.
    |
    */

    'billing_disk' => env('BILLING_MEDIA_DISK', 'billing_private'),

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
