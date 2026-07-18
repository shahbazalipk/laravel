<?php

namespace Tests\Feature\Finance;

use App\Finance\Enums\FinanceRecordStatus;
use App\Finance\Enums\FinanceTransactionDirection;
use App\Finance\Enums\FinanceTransactionStatus;
use App\Finance\Enums\FinanceTransactionType;
use App\Finance\Integrations\RegistrationPaymentProjector;
use App\Finance\Models\FinanceBudgetAlert;
use App\Finance\Models\FinanceCategory;
use App\Finance\Models\FinanceReconciliationMatch;
use App\Finance\Models\FinanceStatementEntry;
use App\Finance\Models\FinanceTransaction;
use App\Finance\Models\FinanceVendor;
use App\Finance\Services\AccountBalanceService;
use App\Finance\Services\ExchangeRateService;
use App\Finance\Services\FinanceAccountService;
use App\Finance\Services\FinanceApprovalService;
use App\Finance\Services\FinanceBudgetService;
use App\Finance\Services\FinanceDashboardService;
use App\Finance\Services\FinancePaymentService;
use App\Finance\Services\FinanceReconciliationService;
use App\Finance\Services\FinanceRecordService;
use App\Finance\Services\FinancialDocumentService;
use App\Finance\Services\MoneyService;
use App\Finance\Services\RecordFinanceTransaction;
use App\Finance\Services\StatementImportService;
use App\Models\Event;
use App\Payments\Enums\PaymentEntryStatus;
use App\Payments\Enums\PaymentEntryType;
use App\Payments\Models\RegistrationPaymentEntry;
use App\Shared\Authorization\Models\ModuleAccessGrant;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FinanceFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        (require database_path('migrations/2026_07_18_050000_create_shared_module_foundation_tables.php'))->up();
        (require database_path('migrations/2026_07_18_051000_create_finance_foundation_tables.php'))->up();
        (require database_path('migrations/2026_07_18_053000_create_finance_control_tables.php'))->up();
        (require database_path('migrations/2026_07_18_054000_create_finance_reconciliation_tables.php'))->up();

        Schema::create('registration_payment_entries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('registration_id');
            $table->string('type', 32);
            $table->string('status', 32);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->timestamp('occurred_at');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('reverses_entry_id')->nullable();
            $table->string('recorded_by_type')->nullable();
            $table->unsignedBigInteger('recorded_by_id')->nullable();
            $table->string('recorded_by_name')->nullable();
            $table->string('recorded_by_email')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');
        });

        config([
            'event.event_id' => 10,
            'event.org_id' => 20,
            'modules.finance.enabled' => false,
        ]);
        session([
            'admin_logged_in' => true,
            'admin_id' => 55,
            'admin_name' => 'Finance Admin',
            'admin_email' => 'finance@example.com',
            'admin_type' => 'organization_admin',
            'admin_is_primary' => true,
        ]);

        $event = new Event;
        $event->forceFill([
            'id' => 10,
            'organization_id' => 20,
            'title' => 'Finance Summit',
            'currency' => 'USD',
        ]);
        app()->instance('current.event', $event);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registration_payment_entries');
        (require database_path('migrations/2026_07_18_054000_create_finance_reconciliation_tables.php'))->down();
        (require database_path('migrations/2026_07_18_053000_create_finance_control_tables.php'))->down();
        (require database_path('migrations/2026_07_18_051000_create_finance_foundation_tables.php'))->down();
        (require database_path('migrations/2026_07_18_050000_create_shared_module_foundation_tables.php'))->down();

        parent::tearDown();
    }

    #[Test]
    public function money_is_calculated_without_binary_floating_point_drift(): void
    {
        $money = app(MoneyService::class);

        $this->assertSame('0.3000', $money->normalize('0.3'));
        $this->assertSame('12.3456', $money->multiply('10.2880', '1.2000'));

        $this->expectException(InvalidArgumentException::class);
        $money->positive('0');
    }

    #[Test]
    public function accounts_encrypt_sensitive_fields_and_transactions_are_idempotent_and_immutable(): void
    {
        $account = app(FinanceAccountService::class)->create([
            'name' => 'Operating Bank',
            'type' => 'bank',
            'currency' => 'usd',
            'opening_balance' => '10.00',
            'account_number' => '123456789',
            'iban' => 'PK00SECRET',
            'is_default' => true,
        ]);

        $raw = DB::table('finance_accounts')->where('id', $account->id)->first();
        $this->assertNotSame('123456789', $raw->account_number);
        $this->assertSame('123456789', $account->account_number);

        $service = app(RecordFinanceTransaction::class);
        $incoming = $service->record([
            'account_id' => $account->id,
            'direction' => FinanceTransactionDirection::Incoming->value,
            'type' => FinanceTransactionType::Payment->value,
            'amount' => '100.00',
            'currency' => 'USD',
            'source_type' => 'manual_income',
            'source_key' => 'manual-income:1',
        ]);
        $duplicate = $service->record([
            'account_id' => $account->id,
            'direction' => FinanceTransactionDirection::Incoming->value,
            'type' => FinanceTransactionType::Payment->value,
            'amount' => '100.00',
            'currency' => 'USD',
            'source_type' => 'manual_income',
            'source_key' => 'manual-income:1',
        ]);
        $service->record([
            'account_id' => $account->id,
            'direction' => FinanceTransactionDirection::Outgoing->value,
            'type' => FinanceTransactionType::Payment->value,
            'amount' => '25.00',
            'currency' => 'USD',
            'source_type' => 'manual_expense',
            'source_key' => 'manual-expense:1',
        ]);

        $this->assertTrue($incoming->is($duplicate));
        $this->assertSame(2, FinanceTransaction::query()->count());
        $this->assertSame('85.0000', app(AccountBalanceService::class)->calculate($account));

        $this->expectException(LogicException::class);
        $incoming->update(['reference' => 'tampered']);
    }

    #[Test]
    public function income_expense_and_dashboard_totals_follow_operational_ledger_rules(): void
    {
        $account = app(FinanceAccountService::class)->create([
            'name' => 'Cash',
            'type' => 'cash',
            'currency' => 'USD',
            'opening_balance' => '10',
            'is_default' => true,
        ]);
        $incomeCategory = FinanceCategory::query()->create([
            'kind' => 'income',
            'name' => 'Registration',
            'code' => 'registration',
        ]);
        $expenseCategory = FinanceCategory::query()->create([
            'kind' => 'expense',
            'name' => 'Venue',
            'code' => 'venue',
        ]);

        $records = app(FinanceRecordService::class);
        $records->createIncome([
            'title' => 'Registration Income',
            'category_id' => $incomeCategory->id,
            'expected_amount' => '150',
            'currency' => 'USD',
            'status' => FinanceRecordStatus::Approved->value,
        ]);
        $records->createExpense([
            'title' => 'Venue Deposit',
            'category_id' => $expenseCategory->id,
            'expense_date' => now()->toDateString(),
            'expected_amount' => '40',
            'approved_amount' => '40',
            'currency' => 'USD',
            'status' => FinanceRecordStatus::Approved->value,
        ]);

        $transactions = app(RecordFinanceTransaction::class);
        $transactions->record([
            'account_id' => $account->id,
            'direction' => 'incoming',
            'type' => 'payment',
            'amount' => '100',
            'currency' => 'USD',
            'source_type' => 'manual_income',
            'source_key' => 'income:1',
        ]);
        $transactions->record([
            'account_id' => $account->id,
            'direction' => 'outgoing',
            'type' => 'payment',
            'amount' => '25',
            'currency' => 'USD',
            'source_type' => 'manual_expense',
            'source_key' => 'expense:1',
        ]);

        $summary = app(FinanceDashboardService::class)->summary();

        $this->assertSame('150.0000', $summary['total_expected_income']);
        $this->assertSame('100.0000', $summary['total_received_income']);
        $this->assertSame('40.0000', $summary['total_approved_expenses']);
        $this->assertSame('25.0000', $summary['total_paid_expenses']);
        $this->assertSame('85.0000', $summary['current_balance']);
        $this->assertSame('70.0000', $summary['available_balance']);
    }

    #[Test]
    public function invalid_financial_record_totals_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(FinanceRecordService::class)->createExpense([
            'title' => 'Invalid Expense',
            'expense_date' => now()->toDateString(),
            'expected_amount' => '100',
            'approved_amount' => '80',
            'paid_amount' => '90',
            'currency' => 'USD',
        ]);
    }

    #[Test]
    public function registration_payments_project_once_into_the_finance_ledger(): void
    {
        app(FinanceAccountService::class)->create([
            'name' => 'Registration Gateway',
            'type' => 'payment_gateway',
            'currency' => 'USD',
            'is_default' => true,
        ]);
        $entry = RegistrationPaymentEntry::query()->create([
            'registration_id' => 500,
            'type' => PaymentEntryType::Payment,
            'status' => PaymentEntryStatus::Succeeded,
            'amount' => '75.00',
            'currency' => 'USD',
            'method' => 'card',
            'reference' => 'gateway-123',
            'occurred_at' => now(),
        ]);

        $projector = app(RegistrationPaymentProjector::class);
        $first = $projector->project($entry);
        $second = $projector->project($entry);

        $this->assertTrue($first->is($second));
        $this->assertSame(FinanceTransactionDirection::Incoming, $first->direction);
        $this->assertSame(FinanceTransactionStatus::Completed, $first->status);
        $this->assertSame('registration_payment_entry', $first->source_type);
        $this->assertSame(1, FinanceTransaction::query()->count());
    }

    #[Test]
    public function invoices_bills_approvals_payments_refunds_and_budget_alerts_are_controlled(): void
    {
        $account = app(FinanceAccountService::class)->create([
            'name' => 'Control Account',
            'type' => 'bank',
            'currency' => 'USD',
            'is_default' => true,
        ]);
        $category = FinanceCategory::query()->create([
            'kind' => 'expense',
            'name' => 'Venue',
            'code' => 'venue',
        ]);
        $vendor = FinanceVendor::query()->create([
            'name' => 'Venue Company',
            'default_currency' => 'USD',
            'is_active' => true,
        ]);
        $documents = app(FinancialDocumentService::class);
        $invoice = $documents->createInvoice([
            'customer_name' => 'Sponsor',
            'invoice_date' => today()->toDateString(),
            'currency' => 'USD',
            'status' => 'issued',
            'items' => [[
                'description' => 'Gold sponsorship',
                'quantity' => '1',
                'unit_price' => '100',
                'tax_rate' => '10',
                'discount_amount' => '5',
            ]],
        ]);
        $bill = $documents->createBill([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'bill_date' => today()->toDateString(),
            'currency' => 'USD',
            'items' => [[
                'description' => 'Venue deposit',
                'quantity' => '1',
                'unit_price' => '100',
            ]],
        ]);

        $approval = app(FinanceApprovalService::class)->submit($bill);
        app(FinanceApprovalService::class)->decide($approval, 'approved', 'Budget confirmed.');
        app(FinancePaymentService::class)->recordFor($bill->fresh(), [
            'account_id' => $account->id,
            'amount' => '100',
            'method' => 'bank_transfer',
            'payment_date' => today()->toDateString(),
        ]);
        app(FinancePaymentService::class)->recordFor($invoice, [
            'account_id' => $account->id,
            'amount' => '40',
            'method' => 'bank_transfer',
            'payment_date' => today()->toDateString(),
        ]);
        app(FinancePaymentService::class)->refund($invoice->fresh(), [
            'account_id' => $account->id,
            'amount' => '10',
            'reason' => 'Sponsorship adjustment',
        ]);
        $budget = app(FinanceBudgetService::class)->create([
            'name' => 'Venue Budget',
            'type' => 'category',
            'category_id' => $category->id,
            'period_start' => today()->subDay()->toDateString(),
            'period_end' => today()->addDay()->toDateString(),
            'currency' => 'USD',
            'planned_expense' => '50',
            'warning_threshold' => 75,
            'critical_threshold' => 90,
        ]);
        $alert = app(FinanceBudgetService::class)->evaluateAlerts($budget);

        $this->assertSame('105.0000', $invoice->total_amount);
        $this->assertSame('approved', $bill->fresh()->approval_status);
        $this->assertSame('paid', $bill->fresh()->payment_status);
        $this->assertSame('30.0000', $invoice->fresh()->paid_amount);
        $this->assertSame('exceeded', $alert?->type);
        $this->assertSame(1, FinanceBudgetAlert::query()->count());
        $this->assertSame(3, FinanceTransaction::query()->count());
    }

    #[Test]
    public function statements_are_imported_matched_and_reconciled_with_explicit_exchange_rates(): void
    {
        Storage::fake('local');
        $account = app(FinanceAccountService::class)->create([
            'name' => 'Reconciliation Bank',
            'type' => 'bank',
            'currency' => 'USD',
            'is_default' => true,
        ]);
        $transaction = app(RecordFinanceTransaction::class)->record([
            'account_id' => $account->id,
            'direction' => FinanceTransactionDirection::Incoming->value,
            'type' => FinanceTransactionType::Payment->value,
            'amount' => '100',
            'currency' => 'USD',
            'source_type' => 'manual',
            'source_key' => 'reconciliation-test-payment',
            'reference' => 'BANK-100',
            'occurred_at' => now(),
        ]);
        $csv = implode("\n", [
            'date,description,amount,direction,reference,currency,balance',
            today()->toDateString().',Customer payment,100,incoming,BANK-100,USD,100',
        ]);
        $import = app(StatementImportService::class)->import(
            $account,
            UploadedFile::fake()->createWithContent('statement.csv', $csv),
        );
        $entry = $import->entries->first();
        $reconciliation = app(FinanceReconciliationService::class)->create([
            'account_id' => $account->id,
            'period_start' => today()->toDateString(),
            'period_end' => today()->toDateString(),
            'statement_opening_balance' => '0',
            'statement_closing_balance' => '100',
        ]);
        $matches = app(FinanceReconciliationService::class)->suggest($entry);
        app(FinanceReconciliationService::class)->confirm($reconciliation, $matches[0]);
        app(FinanceReconciliationService::class)->complete($reconciliation);
        app(ExchangeRateService::class)->set([
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
            'rate' => '0.9',
            'effective_date' => today()->toDateString(),
            'source' => 'manual',
            'override_reason' => 'Test treasury rate',
        ]);

        $this->assertSame('completed', $import->status);
        $this->assertSame('matched', $entry->fresh()->status);
        $this->assertSame('reconciled', $transaction->fresh()->reconciliation_status);
        $this->assertSame('completed', $reconciliation->fresh()->status);
        $this->assertSame('90.0000', app(ExchangeRateService::class)->convert('100', 'USD', 'EUR', today()));
        $this->assertSame(1, FinanceReconciliationMatch::query()->where('status', 'confirmed')->count());
        $this->assertSame(1, FinanceStatementEntry::query()->count());
    }

    #[Test]
    public function finance_admin_pages_are_responsive_ready_and_forms_create_tenant_records(): void
    {
        config(['modules.finance.enabled' => true]);

        $this->get(route('admin.finance.dashboard'))
            ->assertOk()
            ->assertSee('data-testid="finance-dashboard-metrics"', false)
            ->assertDontSee('aria-label="Finance sections"', false);
        $this->get(route('admin.finance.accounts.index'))
            ->assertOk()
            ->assertSee('data-testid="finance-account-form"', false);
        $this->get(route('admin.finance.income.index'))->assertOk();
        $this->get(route('admin.finance.expenses.index'))->assertOk();
        $this->get(route('admin.finance.transactions.index'))->assertOk();
        $this->get(route('admin.finance.vendors.index'))->assertOk();
        $this->get(route('admin.finance.categories.index'))->assertOk();
        $this->get(route('admin.finance.invoices.index'))->assertOk();
        $this->get(route('admin.finance.invoices.create'))->assertOk();
        $this->get(route('admin.finance.bills.index'))->assertOk();
        $this->get(route('admin.finance.bills.create'))->assertOk();
        $this->get(route('admin.finance.payments.index'))->assertOk();
        $this->get(route('admin.finance.refunds.index'))->assertOk();
        $this->get(route('admin.finance.budgets.index'))->assertOk();
        $this->get(route('admin.finance.approvals.index'))->assertOk();
        $this->get(route('admin.finance.reconciliation.index'))->assertOk();
        $this->get(route('admin.finance.reports.index'))->assertOk();

        $this->post(route('admin.finance.accounts.store'), [
            'name' => 'Event Cash',
            'type' => 'cash',
            'currency' => 'usd',
            'opening_balance' => '25.00',
            'is_default' => '1',
            'is_active' => '1',
        ])->assertRedirect(route('admin.finance.accounts.index'));

        $this->assertDatabaseHas('finance_accounts', [
            'event_id' => 10,
            'org_id' => 20,
            'name' => 'Event Cash',
            'currency' => 'USD',
        ]);
    }

    #[Test]
    public function finance_routes_deny_non_primary_users_without_a_persisted_grant(): void
    {
        config(['modules.finance.enabled' => true]);
        session(['admin_is_primary' => false]);

        $this->get(route('admin.finance.dashboard'))->assertForbidden();
    }

    #[Test]
    public function view_only_finance_users_cannot_see_or_open_mutating_forms(): void
    {
        config(['modules.finance.enabled' => true]);
        session(['admin_is_primary' => false]);
        ModuleAccessGrant::query()->create([
            'organization_admin_user_id' => 55,
            'module' => 'finance',
            'role' => 'viewer',
            'abilities' => ['finance.view'],
        ]);

        $this->get(route('admin.finance.accounts.index'))
            ->assertOk()
            ->assertDontSee('data-testid="finance-account-form"', false);
        $this->get(route('admin.finance.income.create'))->assertForbidden();
        $this->get(route('admin.finance.expenses.create'))->assertForbidden();
    }
}
