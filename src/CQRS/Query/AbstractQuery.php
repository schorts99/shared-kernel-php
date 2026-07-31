<?php

namespace Schorts\SharedKernel\CQRS\Query;

use Schorts\SharedKernel\CQRS\Query\Query;
use Schorts\SharedKernel\CQRS\Query\QueryMetadata;
use Schorts\SharedKernel\CQRS\Query\QueryPrimitives;

abstract class AbstractQuery implements Query
{
  protected QueryMetadata $metadata;

  public function __construct(string $correlationId, ?array $customMetadata = null)
  {
    $generateId = static fn (): string => sprintf(
      '%d-%s',
      (int) (microtime(true) * 1000),
      substr(bin2hex(random_bytes(5)), 0, 9),
    );
    $this->metadata = new QueryMetadata(
      id: $customMetadata['id'] ?? $generateId(),
      createdAt: $customMetadata['createdAt'] ?? new \DateTimeImmutable(),
      correlationId: $customMetadata['correlationId'] ?? $correlationId,
      causationId: $customMetadata['causationId'] ?? null,
      requestId: $customMetadata['requestId'] ?? null,
      version: $customMetadata['version'] ?? 1,
      userId: $customMetadata['userId'] ?? null,
      tenantId: $customMetadata['tenantId'] ?? null,
      headers: $customMetadata['headers'] ?? null,
      context: $customMetadata['context'] ?? null,
    );
  }

  abstract public function getType(): string;

  public function getMetadata(): QueryMetadata
  {
    return $this->metadata;
  }

  public function toPrimitives(): QueryPrimitives
  {
    return new QueryPrimitives(
      id: $this->metadata->id,
        type: $this->getType(),
        created_at: $this->metadata->createdAt->format(\DateTimeInterface::ATOM),
        correlation_id: $this->metadata->correlationId,
        causation_id: $this->metadata->causationId,
        request_id: $this->metadata->requestId,
        version: $this->metadata->version,
        user_id: $this->metadata->userId,
        tenant_id: $this->metadata->tenantId,
        payload: [],
        headers: $this->metadata->headers,
        context: $this->metadata->context,
    );
  }

  public function setCorrelationId(string $correlationId): void
  {
    $this->metadata->correlationId = $correlationId;
  }

  public function setCausationId(string $causationId): void
  {
    $this->metadata->causationId = $causationId;
  }

  public function setUserId(string $userId): void
  {
    $this->metadata->userId = $userId;
  }

  public function setTenantId(string $tenantId): void
  {
    $this->metadata->tenantId = $tenantId;
  }

  public function addHeaders(array $headers): void
  {
    $this->metadata->headers = array_merge(
      $this->metadata->headers ?? [],
      $headers,
    );
  }

  public function addContext(string $key, mixed $value): void
  {
    $context = $this->metadata->context ?? [];
    $context[$key] = $value;
    $this->metadata->context = $context;
  }
}
