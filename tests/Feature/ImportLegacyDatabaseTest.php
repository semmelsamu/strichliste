<?php

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Barcode;
use App\Models\BuyArticleTransaction;
use App\Models\Transaction;
use App\Models\UndoTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

/**
 * Builds a minimal legacy SQLite database covering every import case and
 * returns the path to the created file.
 */
function buildLegacyDatabase(): string
{
    $path = tempnam(sys_get_temp_dir(), 'legacy').'.db';

    $pdo = new PDO('sqlite:'.$path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec('create table Users (id integer primary key, nickname varchar(255) not null unique, money integer not null, is_system_user boolean not null default false, created_at date not null, disabled boolean not null default false)');
    $pdo->exec('create table Groups (id integer primary key)');
    $pdo->exec('create table UserGroupMap (gid integer not null, uid integer not null)');
    $pdo->exec('create table Articles (id integer primary key, name text not null unique, is_disabled boolean not null default false)');
    $pdo->exec('create table ArticleBarcodes (article_id integer not null, barcode_content text not null unique)');
    $pdo->exec('create table UserCardNumberMap (user_id integer not null primary key, card_number varchar(255) not null unique)');
    $pdo->exec('create table ArticleCostMap (article_id integer not null, cost integer not null, effective_since date not null)');
    $pdo->exec('create table Transactions (id integer primary key, sender not null, receiver not null, is_undone boolean not null default false, t_type_data integer, money integer not null, description varchar(255), timestamp date not null)');

    // Users: 0 = snackbar (vendor), 1 = aufladung (world), plus customers.
    $pdo->exec("insert into Users (id, nickname, money, is_system_user, created_at, disabled) values
        (0, 'snackbar', 0, 1, '1970-01-01 00:00:00', 0),
        (1, 'aufladung', 0, 1, '1970-01-01 00:00:00', 0),
        (3, 'alice', 250, 0, '2025-01-10 08:00:00', 0),
        (4, 'bob', 500, 0, '2025-01-11 08:00:00', 1),
        (5, 'carol', -50, 0, '2025-01-12 08:00:00', 0),
        (6, 'dave', -50, 0, '2025-01-13 08:00:00', 0)");

    $pdo->exec('insert into Groups (id) values (0),(1),(3),(4),(5),(6),(99)');

    // Single-user groups plus a multi-user group (99) containing carol and dave.
    $pdo->exec('insert into UserGroupMap (gid, uid) values (0,0),(1,1),(3,3),(4,4),(5,5),(6,6),(99,5),(99,6)');

    // Articles: 10 active (two prices, one barcode), 11 disabled.
    $pdo->exec("insert into Articles (id, name, is_disabled) values (10, 'Cola', 0), (11, 'OldBeer', 1)");
    $pdo->exec("insert into ArticleBarcodes (article_id, barcode_content) values (10, 'BC-COLA')");

    // User card numbers, including one that collides with an article barcode
    // (BC-COLA) where the article must win.
    $pdo->exec("insert into UserCardNumberMap (user_id, card_number) values
        (3, 'CARD-ALICE'),
        (5, 'BC-COLA')");
    $pdo->exec("insert into ArticleCostMap (article_id, cost, effective_since) values
        (10, 150, '2025-01-01 00:00:00'),
        (10, 170, '2025-02-01 00:00:00'),
        (11, 200, '2025-01-01 00:00:00')");

    $pdo->exec("insert into Transactions (id, sender, receiver, is_undone, t_type_data, money, description, timestamp) values
        (1, 1, 3, 0, null, 1000, null, '2025-02-01 10:00:00'),
        (2, 3, 0, 0, 10, 150, null, '2025-02-02 10:00:00'),
        (3, 3, 1, 0, null, 200, null, '2025-02-03 10:00:00'),
        (4, 3, 4, 0, null, 500, 'pizza', '2025-02-04 10:00:00'),
        (5, 3, 0, 1, 10, 150, null, '2025-02-05 09:00:00'),
        (6, 99, 3, 0, null, 101, 'group dinner', '2025-03-01T12:00:00.123456789+00:00')");

    $pdo = null;

    return $path;
}

beforeEach(function () {
    $this->legacyPath = buildLegacyDatabase();
});

afterEach(function () {
    @unlink($this->legacyPath);
});

it('imports users with the correct roles and soft deletes', function () {
    $this->artisan('import:legacy-database', ['path' => $this->legacyPath])->assertSuccessful();

    expect(User::withTrashed()->count())->toBe(6);

    expect(User::where('name', 'snackbar')->first()->hasRole(UserRole::Vendor->value))->toBeTrue();
    expect(User::where('name', 'aufladung')->first()->hasRole(UserRole::World->value))->toBeTrue();
    expect(User::where('name', 'alice')->first()->hasRole(UserRole::Customer->value))->toBeTrue();

    // Disabled legacy users become soft deleted.
    expect(User::where('name', 'bob')->exists())->toBeFalse();
    expect(User::withTrashed()->where('name', 'bob')->first()->trashed())->toBeTrue();

    // created_at is preserved from the legacy data.
    expect(User::where('name', 'alice')->first()->created_at->toDateTimeString())
        ->toBe('2025-01-10 08:00:00');
});

it('imports articles, prices and barcodes', function () {
    $this->artisan('import:legacy-database', ['path' => $this->legacyPath])->assertSuccessful();

    $cola = Article::where('name', 'Cola')->first();
    expect($cola)->not->toBeNull();
    expect($cola->prices()->count())->toBe(2);
    expect((float) $cola->currentPrice)->toBe(1.70);
    expect($cola->barcodes()->where('barcode', 'BC-COLA')->exists())->toBeTrue();
    expect($cola->category->name)->toStartWith('Import ');

    // Disabled legacy articles become soft deleted.
    expect(Article::where('name', 'OldBeer')->exists())->toBeFalse();
    expect(Article::withTrashed()->where('name', 'OldBeer')->first()->trashed())->toBeTrue();
});

it('imports user card numbers as barcodes, letting articles win collisions', function () {
    $this->artisan('import:legacy-database', ['path' => $this->legacyPath])->assertSuccessful();

    $alice = User::where('name', 'alice')->first();
    $carol = User::where('name', 'carol')->first();

    // Alice's unique card number is linked to her.
    $card = Barcode::where('barcode', 'CARD-ALICE')->first();
    expect($card)->not->toBeNull();
    expect($card->user_id)->toBe($alice->id);

    // BC-COLA collides with an article barcode, so the article keeps it and
    // carol does not receive a barcode.
    expect(Barcode::where('barcode', 'BC-COLA')->first()->user_id)->toBeNull();
    expect(Barcode::where('user_id', $carol->id)->exists())->toBeFalse();
});

it('imports a purchase as a buy article transaction', function () {
    $this->artisan('import:legacy-database', ['path' => $this->legacyPath])->assertSuccessful();

    $alice = User::where('name', 'alice')->first();
    $snackbar = User::where('name', 'snackbar')->first();
    $cola = Article::where('name', 'Cola')->first();

    // The non-undone purchase (legacy id 2).
    $buy = BuyArticleTransaction::where('article_id', $cola->id)
        ->whereHas('transaction', fn ($q) => $q->where('amount', '>', 0))
        ->first();

    expect($buy)->not->toBeNull();
    expect($buy->transaction->from_user_id)->toBe($alice->id);
    expect($buy->transaction->to_user_id)->toBe($snackbar->id);
    expect((float) $buy->transaction->amount)->toBe(1.50);
});

it('imports deposits and withdrawals in the right direction', function () {
    $this->artisan('import:legacy-database', ['path' => $this->legacyPath])->assertSuccessful();

    $alice = User::where('name', 'alice')->first();
    $world = User::where('name', 'aufladung')->first();

    // Deposit: world -> alice.
    expect(Transaction::where('from_user_id', $world->id)->where('to_user_id', $alice->id)->where('amount', 10.00)->exists())->toBeTrue();
    // Withdrawal: alice -> world.
    expect(Transaction::where('from_user_id', $alice->id)->where('to_user_id', $world->id)->where('amount', 2.00)->exists())->toBeTrue();
});

it('models an undone transaction as a linked counter transaction', function () {
    $this->artisan('import:legacy-database', ['path' => $this->legacyPath])->assertSuccessful();

    $undo = UndoTransaction::first();
    expect($undo)->not->toBeNull();

    $original = Transaction::find($undo->undone_transaction_id);
    $counter = Transaction::find($undo->transaction_id);

    expect((float) $original->amount)->toBe(1.50);
    expect((float) $counter->amount)->toBe(-1.50);

    // The counter transaction sits one second after the original.
    expect($counter->created_at->toDateTimeString())
        ->toBe($original->created_at->copy()->addSecond()->toDateTimeString());
});

it('splits multi-user group transactions evenly and floors the remainder', function () {
    $this->artisan('import:legacy-database', ['path' => $this->legacyPath])->assertSuccessful();

    $alice = User::where('name', 'alice')->first();
    $carol = User::where('name', 'carol')->first();
    $dave = User::where('name', 'dave')->first();

    // 101 cents split between two senders -> 50 cents each, 1 cent dropped.
    expect(Transaction::where('from_user_id', $carol->id)->where('to_user_id', $alice->id)->where('amount', 0.50)->exists())->toBeTrue();
    expect(Transaction::where('from_user_id', $dave->id)->where('to_user_id', $alice->id)->where('amount', 0.50)->exists())->toBeTrue();

    expect((float) $carol->balance)->toBe(-0.50);
    expect((float) $dave->balance)->toBe(-0.50);
});

it('parses mixed timestamp formats', function () {
    $this->artisan('import:legacy-database', ['path' => $this->legacyPath])->assertSuccessful();

    $alice = User::where('name', 'alice')->first();

    $groupTransaction = Transaction::where('to_user_id', $alice->id)
        ->whereDate('created_at', '2025-03-01')
        ->first();

    expect($groupTransaction)->not->toBeNull();
    expect($groupTransaction->created_at->toDateTimeString())->toBe('2025-03-01 12:00:00');
});

it('produces reconciling balances', function () {
    $this->artisan('import:legacy-database', ['path' => $this->legacyPath])->assertSuccessful();

    $alice = User::where('name', 'alice')->first();
    $bob = User::withTrashed()->where('name', 'bob')->first();

    // Deposit +10.00, purchase -1.50, withdrawal -2.00, transfer -5.00,
    // undone purchase nets 0, group dinner received +1.00.
    expect((float) $alice->balance)->toBe(2.50);
    expect((float) $bob->balance)->toBe(5.00);
});

it('reconciles legacy balances without warnings when they match', function () {
    $this->artisan('import:legacy-database', ['path' => $this->legacyPath])
        ->assertSuccessful()
        ->doesntExpectOutputToContain('balance mismatch');
});

it('warns but still succeeds when a legacy balance does not match', function () {
    // Corrupt alice's stored legacy balance so it no longer matches the
    // balance computed from her transactions (2.50).
    $pdo = new PDO('sqlite:'.$this->legacyPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('update Users set money = 999 where nickname = "alice"');
    $pdo = null;

    $this->artisan('import:legacy-database', ['path' => $this->legacyPath])
        ->assertSuccessful()
        ->expectsOutputToContain("User 'alice' balance mismatch: legacy 9.99 vs computed 2.50");

    expect((float) User::where('name', 'alice')->first()->balance)->toBe(2.50);
});
