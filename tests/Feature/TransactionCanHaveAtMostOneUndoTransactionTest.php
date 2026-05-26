<?php

use App\Models\UndoTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('any transaction can have at most one undo transaction that references it', function () {
    $this->seed();

    $hasDuplicates = UndoTransaction::select('undone_transaction_id')
        ->groupBy('undone_transaction_id')
        ->havingRaw('COUNT(*) > 1')
        ->exists();

    $this->assertFalse($hasDuplicates, 'Undo transactions referencinng the same transaction found.');
});
