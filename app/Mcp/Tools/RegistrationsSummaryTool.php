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

#[Name('registrations_summary')]
#[Description('Returns event-scoped registration totals grouped by workflow status, category, payment status, and check-in state.')]
#[IsReadOnly]
#[IsIdempotent]
class RegistrationsSummaryTool extends ReadOnlyEventTool
{
    public function __construct(private readonly RegistrationReadService $registrations) {}

    protected function execute(Request $request): Response|ResponseFactory
    {
        $input = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        return Response::structured($this->registrations->summary($input['from'] ?? null, $input['to'] ?? null));
    }

    protected function toolName(): string
    {
        return 'registrations_summary';
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
