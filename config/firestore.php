<?php
return [
    'project_id' => env('FIREBASE_PROJECT_ID', 'jippymart-27c08'),
    'credentials' => storage_path('app/firebase/serviceAccount.json'),
    'database_id' => env('FIRESTORE_DATABASE_ID', '(default)'),
    'collection' => env('FIRESTORE_COLLECTION', 'activity_logs'),
    'timeout' => env('FIRESTORE_TIMEOUT', 30),
    'retry' => [
        'initial_delay' => env('FIRESTORE_RETRY_INITIAL_DELAY', 1.0),
        'max_delay' => env('FIRESTORE_RETRY_MAX_DELAY', 60.0),
        'multiplier' => env('FIRESTORE_RETRY_MULTIPLIER', 2.0),
        'retry_codes' => ['UNAVAILABLE', 'RESOURCE_EXHAUSTED', 'INTERNAL'],
    ],
]; 