<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Tests\RBAC;

use PHPUnit\Framework\TestCase;
use Schorts\SharedKernel\RBAC\BaseResource;
use Schorts\SharedKernel\RBAC\Permission;
use Schorts\SharedKernel\RBAC\RBACPolicy;

class DocumentResource implements BaseResource
{
  public function __construct(
    public readonly string $id,
    public readonly string $authorId,
    public readonly bool $isPublic = false,
  ) {}

  public function getResourceName(): string
  {
    return 'document';
  }
}

class AppPolicy extends RBACPolicy
{
  public function getPermissions(string $role): array
  {
    return match ($role) {
      'admin' => [
        new Permission('*', '*'),
      ],
      'editor' => [
        new Permission('document', 'read'),
        new Permission('document', 'write'),
      ],
      'viewer' => [
        new Permission('document', 'read'),
      ],
      default => [],
    };
  }
}

final class RBACTest extends TestCase
{
  public function testRBACPermissions(): void
  {
    $policy = new AppPolicy();

    $this->assertTrue($policy->can('admin', 'delete', 'document'));
    $this->assertTrue($policy->can('editor', 'write', 'document'));
    $this->assertFalse($policy->can('viewer', 'write', 'document'));
    $this->assertTrue($policy->can('viewer', 'read', 'document'));
    $this->assertFalse($policy->can('guest', 'read', 'document'));
  }

  public function testABACAttributePredicates(): void
  {
    $policy = new AppPolicy();
    $doc = new DocumentResource('doc-1', 'author-42', isPublic: false);
    $user = ['id' => 'author-42'];
    $otherUser = ['id' => 'user-99'];

    $isAuthor = static function (array $u, DocumentResource $r): bool {
      return $u['id'] === $r->authorId;
    };

    $this->assertTrue(
      $policy->canWithAttributes($user, 'editor', 'write', $doc, [$isAuthor])
    );

    $this->assertFalse(
      $policy->canWithAttributes($otherUser, 'editor', 'write', $doc, [$isAuthor])
    );
  }
}
