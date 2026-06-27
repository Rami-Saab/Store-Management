<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$name = $argv[1] ?? '';
if ($name === '') {
    echo "Usage: php scripts/check-user.php \"User Name\"\n";
    exit(1);
}

$matches = App\Models\User::where('name', $name)->orderBy('id')->get();
echo 'count='.$matches->count()."\n";
foreach ($matches as $user) {
    $managedStoreId = $user->managedStore()->value('id');
    $storesCount = $user->stores()->count();
    echo 'id='.$user->id
        .' role='.$user->role
        .' status='.$user->status
        .' job_title='.$user->job_title
        .' dept='.$user->department
        .' dept_id='.$user->department_id
        .' store_id='.$user->store_id
        .' managed_store_id='.$managedStoreId
        .' stores_count='.$storesCount
        ."\n";
    echo 'password_hash='.$user->password."\n";
    foreach (['12345', '123', '777', '999'] as $candidate) {
        $ok = password_verify($candidate, (string) $user->password) ? 'yes' : 'no';
        echo 'password_'.$candidate.'='.$ok."\n";
    }
}
