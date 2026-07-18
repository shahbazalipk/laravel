<?php

namespace App\Mcp\Tools;

use App\Mcp\Services\RegistrationReadService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('payment_summary')]
#[Description('Returns paid, unpaid, partial, failed, and refunded registration counts plus financial totals grouped by currency.')]
#[IsReadOnly]
#[IsIdempotent]
class PaymentSummaryTool extends ReadOnlyEventTool
{
    public function __construct(private readonly RegistrationReadService $registrations) {}

    protected function execute(Request $request): Response|ResponseFactory
    {
        $input = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        return Response::structured($this->registrations->paymentSummary($input['from'] ?? null, $input['to'] ?? null));
    }

    protected function toolName(): string
    {
        return 'payment_summary';
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'from' => $schema->string()->description('Optional start date (YYYY-MM-DD), inclusive.'),
            'to' => $schema->string()->description('Optional end date (YYYY-MM-DD), inclusive.'),
        ];
    }
}
