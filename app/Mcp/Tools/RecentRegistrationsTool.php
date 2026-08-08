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

#[Name('recent_registrations')]
#[Description('Lists recent event registrations with operational status plus website profile fields (name, photo, job title, company, LinkedIn from existing/custom answers). Does not expose email or phone.')]
#[IsReadOnly]
#[IsIdempotent]
class RecentRegistrationsTool extends ReadOnlyEventTool
{
    public function __construct(private readonly RegistrationReadService $registrations) {}

    protected function execute(Request $request): Response|ResponseFactory
    {
        $input = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status' => ['nullable', 'string', 'max:100'],
            'payment_status' => ['nullable', 'in:pending,paid,failed,refunded'],
        ]);

        return Response::structured([
            'registrations' => $this->registrations->recent(
                (int) ($input['limit'] ?? 20),
                $input['status'] ?? null,
                $input['payment_status'] ?? null,
            ),
        ]);
    }

    protected function toolName(): string
    {
        return 'recent_registrations';
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()->description('Number of registrations to return (1-100).')->default(20),
            'status' => $schema->string()->description('Optional registration workflow status slug.'),
            'payment_status' => $schema->string()
                ->enum(['pending', 'paid', 'failed', 'refunded'])
                ->description('Optional legacy payment status filter.'),
        ];
    }
}
