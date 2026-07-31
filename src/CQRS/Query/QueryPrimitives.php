<?php

namespace Schorts\SharedKernel\CQRS\Query;

final readonly class QueryPrimitives
{
  public function __construct(
    public string $id,
    public string $type,
    public string $created_at,
    public string $correlation_id,
    public ?string $causation_id,
    public ?string $request_id,
    public int $version,
    public ?string $user_id,
    public ?string $tenant_id,
    public array $payload,
    public ?array $headers,
    public ?array $context,
  ) {}
}
