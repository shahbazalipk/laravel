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

#[Name('get_registration_status')]
#[Description('Looks up one registration by registration number and returns workflow, payment, and check-in status without contact details.')]
#[IsReadOnly]
#[IsIdempotent]
class RegistrationStatusTool extends ReadOnlyEventTool
{
    public function __construct(private readonly RegistrationReadService $registrations) {}

    protected function execute(Request $request): Response|ResponseFactory
    {
        $input = $request->validate([
            'registration_number' => ['required', 'string', 'max:50'],
        ]);
        $registration = $this->registrations->findStatus($input['registration_number']);

        return $registration
            ? Response::structured($registration)
            : Response::error('Registration not found in the token-scoped event.');
    }

    protected function toolName(): string
    {
        return 'get_registration_status';
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'registration_number' => $schema->string()
                ->description('Exact registration number to look up.')
                ->required(),
        ];
    }
}
