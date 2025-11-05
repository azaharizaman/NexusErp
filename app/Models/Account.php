<?php

namespace App\Models;

use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model implements Sortable
{
    use HasFactory;
    use SoftDeletes;
    use SortableTrait;

    protected $fillable = [
        'account_code',
        'account_name',
        'description',
        'account_type',
        'sub_type',
        'account_group_id',
        'parent_account_id',
        'company_id',
        'is_group',
        'is_control_account',
        'allow_manual_entries',
        'is_active',
        'level',
        'sort_order',
        'opening_balance',
        'current_balance',
        'balance_type',
        'currency_id',
        'created_by',
        'updated_by',
    ];

    // Note: The database columns for opening_balance and current_balance use DECIMAL(20,4).
    // The cast 'decimal:4' ensures that 4 decimal places are used for display and calculations.
    // If you want to standardize on 2 decimal places for display, change to 'decimal:2'.
    protected $casts = [
        'is_group' => 'boolean',
        'is_control_account' => 'boolean',
        'allow_manual_entries' => 'boolean',
        'is_active' => 'boolean',
        'level' => 'integer',
        'sort_order' => 'integer',
        'opening_balance' => 'decimal:4', // Matches DB precision (DECIMAL(20,4))
        'current_balance' => 'decimal:4', // Matches DB precision (DECIMAL(20,4))
    ];

    /**
     * Sortable configuration
     */
    public array $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    /**
     * Get the account group
     */
    public function accountGroup(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class, 'account_group_id');
    }

    /**
     * Get the parent account
     */
    public function parentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    /**
     * Get child accounts
     */
    public function childAccounts(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_account_id')->orderBy('sort_order');
    }

    /**
     * Get the company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the currency
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the user who created this record
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to get only active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get accounts by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('account_type', $type);
    }

    /**
     * Scope to get accounts by sub type
     */
    public function scopeOfSubType($query, string $subType)
    {
        return $query->where('sub_type', $subType);
    }

    /**
     * Scope to get only group accounts
     */
    public function scopeGroups($query)
    {
        return $query->where('is_group', true);
    }

    /**
     * Scope to get only ledger accounts (non-groups)
     */
    public function scopeLedgers($query)
    {
        return $query->where('is_group', false);
    }

    /**
     * Scope to get root level accounts (no parent)
     */
    public function scopeRootLevel($query)
    {
        return $query->whereNull('parent_account_id');
    }

    /**
     * Scope to get accounts for a specific company
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Get the full account path (for hierarchical display)
     */
    public function getFullPathAttribute(): string
    {
        $path = collect([$this->account_name]);
        $parent = $this->parentAccount;

        while ($parent) {
            $path->prepend($parent->account_name);
            $parent = $parent->parentAccount;
        }

        $this->save();
    }
    /**
     * Update the current balance safely using row-level locking.
     */
    public function updateBalance(float $amount, string $type = 'debit'): void
    {
        \DB::transaction(function () use ($amount, $type) {
            $account = self::where('id', $this->id)->lockForUpdate()->first();

            if ($type === 'debit') {
                if ($account->balance_type === 'Debit') {
                    $account->current_balance += $amount;
                } else {
                    $account->current_balance -= $amount;
                }
            } else { // credit
                if ($account->balance_type === 'Credit') {
                    $account->current_balance += $amount;
                } else {
                    $account->current_balance -= $amount;
                }
            }

            $account->save();

            // Optionally, refresh the current instance
            $this->refresh();
        });
    }
}
