<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\ArticlePrice;
use App\Models\Barcode;
use App\Models\BuyArticleTransaction;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\UndoTransaction;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Throwable;

#[Signature('import:legacy-database {path? : Path to the legacy SQLite database file}')]
#[Description('Imports users, articles and transactions from a legacy strichliste SQLite database.')]
class ImportLegacyDatabase extends Command
{
    private const LEGACY_CONNECTION = 'legacy_import';

    /**
     * Legacy system user that receives article purchases (mapped to a Vendor user).
     */
    private const SNACKBAR_USER_ID = 0;

    /**
     * Legacy system user that funds deposits / receives withdrawals (mapped to a World user).
     */
    private const AUFLADUNG_USER_ID = 1;

    /**
     * Maps a legacy user id to the imported User model.
     *
     * @var array<int, User>
     */
    private array $users = [];

    /**
     * Maps a legacy group id to its sorted list of legacy user ids.
     *
     * @var array<int, list<int>>
     */
    private array $groupMembers = [];

    /**
     * Maps a legacy article id to the imported Article model.
     *
     * @var array<int, Article>
     */
    private array $articles = [];

    /**
     * Maps a legacy user id to its stored balance in cents, used to reconcile
     * against the balance recomputed from the imported transactions.
     *
     * @var array<int, int>
     */
    private array $legacyBalances = [];

    /**
     * The category every imported article is filed under.
     */
    private ?Category $category = null;

    private int $articlePriceCount = 0;

    private int $articleBarcodeCount = 0;

    private int $userBarcodeCount = 0;

    private int $transactionCount = 0;

    /**
     * Non-fatal issues collected during the import, rendered together at the end.
     *
     * @var list<string>
     */
    private array $warnings = [];

    public function handle(): int
    {
        $path = $this->argument('path') ?? storage_path('app/private/legacy-db/database.db');

        if (! is_file($path)) {
            $this->components->error("Legacy database not found at: {$path}");

            return self::FAILURE;
        }

        $this->configureLegacyConnection($path);

        $this->newLine();
        $this->components->info('Importing legacy strichliste database');
        $this->components->twoColumnDetail('<fg=gray>Source database</>', "<fg=cyan>{$path}</>");
        $this->newLine();

        try {
            DB::transaction(function (): void {
                $this->components->task('Ensuring user roles exist', fn () => $this->ensureRolesExist());
                $this->components->task('Creating import category', fn () => $this->category = $this->createImportCategory());
                $this->components->task('Importing users', fn () => $this->importUsers());
                $this->components->task('Loading user groups', fn () => $this->loadGroupMembers());
                $this->components->task('Importing articles', fn () => $this->importArticles($this->category));
                $this->components->task('Importing article prices', fn () => $this->importArticlePrices());
                $this->components->task('Importing article barcodes', fn () => $this->importBarcodes());
                $this->components->task('Importing user barcodes', fn () => $this->importUserBarcodes());
                $this->importTransactions();
                $this->components->task('Reconciling user balances', fn () => $this->reconcileBalances());
            });
        } catch (Throwable $e) {
            $this->newLine();
            $this->components->error('Import failed and was rolled back: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->renderSummary();
        $this->renderWarnings();

        $this->newLine();
        $this->components->info('Legacy database imported successfully. 🎉');

        return self::SUCCESS;
    }

    private function renderSummary(): void
    {
        $this->newLine();
        $this->components->info('Import summary');

        $this->components->twoColumnDetail('<fg=green>Users</>', '<fg=white;options=bold>'.count($this->users).'</>');
        $this->components->twoColumnDetail('<fg=green>Articles</>', '<fg=white;options=bold>'.count($this->articles).'</>');
        $this->components->twoColumnDetail('<fg=green>Article prices</>', '<fg=white;options=bold>'.$this->articlePriceCount.'</>');
        $this->components->twoColumnDetail('<fg=green>Article barcodes</>', '<fg=white;options=bold>'.$this->articleBarcodeCount.'</>');
        $this->components->twoColumnDetail('<fg=green>User barcodes</>', '<fg=white;options=bold>'.$this->userBarcodeCount.'</>');
        $this->components->twoColumnDetail('<fg=green>Transactions</>', '<fg=white;options=bold>'.$this->transactionCount.'</>');
    }

    private function renderWarnings(): void
    {
        if ($this->warnings === []) {
            return;
        }

        $this->newLine();
        $this->components->warn(count($this->warnings).' issue(s) were skipped during the import:');
        $this->components->bulletList($this->warnings);
    }

    /**
     * Records a non-fatal issue to be reported once the import has finished,
     * keeping the per-step task output clean.
     */
    private function recordWarning(string $message): void
    {
        $this->warnings[] = $message;
    }

    private function legacy(): Connection
    {
        return DB::connection(self::LEGACY_CONNECTION);
    }

    private function configureLegacyConnection(string $path): void
    {
        Config::set('database.connections.'.self::LEGACY_CONNECTION, [
            'driver' => 'sqlite',
            'database' => $path,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        DB::purge(self::LEGACY_CONNECTION);
    }

    private function ensureRolesExist(): void
    {
        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value]);
        }
    }

    private function createImportCategory(): Category
    {
        $category = new Category;
        $category->name = 'Import '.now()->format('Y-m-d H:i:s');
        $category->icon = 'lucide-archive';
        $category->save();

        return $category;
    }

    private function importUsers(): void
    {
        $legacyUsers = $this->legacy()->table('Users')->get();

        foreach ($legacyUsers as $legacyUser) {
            $createdAt = $this->parseTimestamp($legacyUser->created_at);

            $user = new User;
            $user->name = $legacyUser->nickname;
            $user->created_at = $createdAt;
            $user->updated_at = $createdAt;

            if ($legacyUser->disabled) {
                $user->deleted_at = $createdAt;
            }

            $user->save();

            $user->assignRole($this->roleForLegacyUser((int) $legacyUser->id)->value);

            $this->users[(int) $legacyUser->id] = $user;
            $this->legacyBalances[(int) $legacyUser->id] = (int) $legacyUser->money;
        }
    }

    private function roleForLegacyUser(int $legacyUserId): UserRole
    {
        return match ($legacyUserId) {
            self::SNACKBAR_USER_ID => UserRole::Vendor,
            self::AUFLADUNG_USER_ID => UserRole::World,
            default => UserRole::Customer,
        };
    }

    private function loadGroupMembers(): void
    {
        $rows = $this->legacy()->table('UserGroupMap')->orderBy('uid')->get();

        foreach ($rows as $row) {
            $this->groupMembers[(int) $row->gid][] = (int) $row->uid;
        }
    }

    private function importArticles(Category $category): void
    {
        $legacyArticles = $this->legacy()->table('Articles')->get();

        foreach ($legacyArticles as $legacyArticle) {
            $article = new Article;
            $article->name = $legacyArticle->name;
            $article->category_id = $category->id;

            if ($legacyArticle->is_disabled) {
                $article->deleted_at = now();
            }

            $article->save();

            $this->articles[(int) $legacyArticle->id] = $article;
        }
    }

    private function importArticlePrices(): void
    {
        $rows = $this->legacy()->table('ArticleCostMap')->get();

        foreach ($rows as $row) {
            $article = $this->articles[(int) $row->article_id] ?? null;

            if (! $article) {
                $this->recordWarning("Price for unknown legacy article #{$row->article_id} skipped.");

                continue;
            }

            $price = new ArticlePrice;
            $price->article_id = $article->id;
            $price->price = $this->centsToAmount((int) $row->cost);
            $price->effective_since = $this->parseTimestamp($row->effective_since);
            $price->save();

            $this->articlePriceCount++;
        }
    }

    private function importBarcodes(): void
    {
        $rows = $this->legacy()->table('ArticleBarcodes')->get();

        foreach ($rows as $row) {
            $article = $this->articles[(int) $row->article_id] ?? null;

            if (! $article) {
                $this->recordWarning("Barcode '{$row->barcode_content}' for unknown legacy article #{$row->article_id} skipped.");

                continue;
            }

            $barcode = new Barcode;
            $barcode->barcode = $row->barcode_content;
            $barcode->article_id = $article->id;
            $barcode->save();

            $this->articleBarcodeCount++;
        }
    }

    private function importUserBarcodes(): void
    {
        $rows = $this->legacy()->table('UserCardNumberMap')->get();

        foreach ($rows as $row) {
            $user = $this->users[(int) $row->user_id] ?? null;

            if (! $user) {
                $this->recordWarning("Card number '{$row->card_number}' for unknown legacy user #{$row->user_id} skipped.");

                continue;
            }

            // A barcode can only belong to one entity and the article always wins,
            // so skip any card number already claimed by an imported article barcode.
            if (Barcode::where('barcode', $row->card_number)->exists()) {
                $this->recordWarning("Card number '{$row->card_number}' skipped: already linked to an article.");

                continue;
            }

            $barcode = new Barcode;
            $barcode->barcode = $row->card_number;
            $barcode->user_id = $user->id;
            $barcode->save();

            $this->userBarcodeCount++;
        }
    }

    private function importTransactions(): void
    {
        $total = $this->legacy()->table('Transactions')->count();

        $this->newLine();
        $this->components->twoColumnDetail('Importing transactions', "<fg=yellow>{$total}</> legacy rows");

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%');
        $bar->start();

        foreach ($this->legacy()->table('Transactions')->orderBy('id')->cursor() as $legacy) {
            $bar->advance();

            $fromUsers = $this->resolveGroupUsers((int) $legacy->sender);
            $toUser = $this->resolveSingleGroupUser((int) $legacy->receiver, (int) $legacy->id);

            if ($fromUsers === [] || $toUser === null) {
                $this->recordWarning("Transaction #{$legacy->id} skipped: unresolvable sender or receiver.");

                continue;
            }

            $article = $legacy->t_type_data !== null
                ? ($this->articles[(int) $legacy->t_type_data] ?? null)
                : null;

            $createdAt = $this->parseTimestamp($legacy->timestamp);

            // Multi-user sender groups split the amount evenly between members,
            // flooring to whole cents and dropping any remainder.
            $amountPerSender = $this->centsToAmount(intdiv((int) $legacy->money, count($fromUsers)));

            foreach ($fromUsers as $fromUser) {
                $this->createTransaction(
                    fromUser: $fromUser,
                    toUser: $toUser,
                    amount: $amountPerSender,
                    createdAt: $createdAt,
                    article: $article,
                    isUndone: (bool) $legacy->is_undone,
                );

                $this->transactionCount++;
            }
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Verifies that each imported customer's stored legacy balance matches the
     * balance recomputed from the imported transactions, recording a warning
     * for any discrepancy. System users (snackbar / aufladung) carry no
     * meaningful stored balance in the legacy data and are skipped.
     */
    private function reconcileBalances(): void
    {
        foreach ($this->users as $legacyUserId => $user) {
            if ($this->roleForLegacyUser($legacyUserId) !== UserRole::Customer) {
                continue;
            }

            User::forgetCachedBalance($user->id);

            $expectedCents = $this->legacyBalances[$legacyUserId] ?? 0;
            $actualCents = (int) round((float) $user->balance * 100);

            if ($expectedCents !== $actualCents) {
                $this->recordWarning(sprintf(
                    "User '%s' balance mismatch: legacy %.2f vs computed %.2f.",
                    $user->name,
                    $this->centsToAmount($expectedCents),
                    $this->centsToAmount($actualCents),
                ));
            }
        }
    }

    private function createTransaction(
        User $fromUser,
        User $toUser,
        float $amount,
        Carbon $createdAt,
        ?Article $article,
        bool $isUndone,
    ): void {
        $transaction = new Transaction;
        $transaction->from_user_id = $fromUser->id;
        $transaction->to_user_id = $toUser->id;
        $transaction->amount = $amount;
        $transaction->created_at = $createdAt;
        $transaction->updated_at = $createdAt;
        $transaction->save();

        if ($article) {
            $buyArticleTransaction = new BuyArticleTransaction;
            $buyArticleTransaction->transaction_id = $transaction->id;
            $buyArticleTransaction->article_id = $article->id;
            $buyArticleTransaction->save();
        }

        if (! $isUndone) {
            return;
        }

        // Undoing is modelled as a counter transaction. It is placed one second
        // after the original so date sorting keeps it directly afterwards.
        $undoneAt = $createdAt->copy()->addSecond();

        $undo = new Transaction;
        $undo->from_user_id = $fromUser->id;
        $undo->to_user_id = $toUser->id;
        $undo->amount = -$amount;
        $undo->created_at = $undoneAt;
        $undo->updated_at = $undoneAt;
        $undo->save();

        $undoTransaction = new UndoTransaction;
        $undoTransaction->transaction_id = $undo->id;
        $undoTransaction->undone_transaction_id = $transaction->id;
        $undoTransaction->save();
    }

    /**
     * Resolves a legacy group id to the imported users of its members.
     *
     * @return list<User>
     */
    private function resolveGroupUsers(int $groupId): array
    {
        $users = [];

        foreach ($this->groupMembers[$groupId] ?? [] as $legacyUserId) {
            if (isset($this->users[$legacyUserId])) {
                $users[] = $this->users[$legacyUserId];
            }
        }

        return $users;
    }

    /**
     * Resolves a legacy group id to a single imported user.
     */
    private function resolveSingleGroupUser(int $groupId, int $transactionId): ?User
    {
        $users = $this->resolveGroupUsers($groupId);

        if (count($users) > 1) {
            $this->recordWarning("Transaction #{$transactionId}: receiver group #{$groupId} has multiple users, using the first.");
        }

        return $users[0] ?? null;
    }

    private function centsToAmount(int $cents): float
    {
        return $cents / 100;
    }

    private function parseTimestamp(string $value): Carbon
    {
        return Carbon::parse($value);
    }
}
