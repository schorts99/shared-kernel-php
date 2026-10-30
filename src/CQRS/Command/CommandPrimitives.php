<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\Command;

use JsonSerializable;

final class CommandPrimitives implements JsonSerializable
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
    public ?array $payload,
    public ?array $headers,
    public ?array $context,
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toArray(): array
  {
    return [
      'id'             => $this->id,
      'type'           => $this->type,
      'created_at'     => $this->created_at,
      'correlation_id' => $this->correlation_id,
      'causation_id'   => $this->causation_id,
      'request_id'     => $this->request_id,
      'version'        => $this->version,
      'user_id'        => $this->user_id,
      'tenant_id'      => $this->tenant_id,
      'payload'        => $this->payload,
      'headers'        => $this->headers,
      'context'        => $this->context,
    ];
  }

  public function jsonSerialize(): mixed
  {
    return $this->toArray();
  }
}
