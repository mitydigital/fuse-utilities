<?php

use MityDigital\FuseUtilities\Listeners\PreventDeletingMountsListener;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Entries\Entry;
use Statamic\Events\EntryDeleting;
use Statamic\Facades\Collection;

function mountedEntry(): Entry
{
    $entry = Mockery::mock(Entry::class);
    $entry->shouldReceive('offsetGet')->with('title')->andReturn('Blog');

    Collection::shouldReceive('findByMount')->with($entry)->andReturn(Mockery::mock(CollectionContract::class));

    return $entry;
}

it('prevents deleting a mount in production with a translated message', function () {
    app()->detectEnvironment(fn () => 'production');

    (new PreventDeletingMountsListener)->handle(new EntryDeleting(mountedEntry()));
})->throws(Exception::class, 'Your entry Blog has a collection mounted and can\'t be deleted.');

it('allows deleting a mount outside production', function () {
    app()->detectEnvironment(fn () => 'local');

    (new PreventDeletingMountsListener)->handle(new EntryDeleting(mountedEntry()));

    expect(true)->toBeTrue();
});
