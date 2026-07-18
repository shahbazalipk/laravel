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

#[Name('checkin_summary')]
#[Description('Returns event check-in totals, rate, today count, and counts grouped by registration category.')]
#[IsReadOnly]
#[IsIdempotent]
class CheckInSummaryTool extends ReadOnlyEventTool
{
    public function __construct(private readonly RegistrationReadService $registrations) {}

    protected function execute(Request $request): Response|ResponseFactory
    {
        return Response::structured($this->registrations->checkInSummary());
    }

    protected function toolName(): string
    {
        return 'checkin_summary';
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
