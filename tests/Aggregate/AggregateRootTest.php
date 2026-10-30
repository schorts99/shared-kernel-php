<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Tests\Aggregate;

use PHPUnit\Framework\TestCase;
use Schorts\SharedKernel\Aggregate\AggregateRoot;
use Schorts\SharedKernel\DomainEvent\DomainEvent;
use Schorts\SharedKernel\Entity\EntityRegistry;
use Schorts\SharedKernel\ValueObjects\StringValue;

class TestId extends StringValue {
  public function getAttributeName(): string { return 'id'; }
}

class UserCreatedEvent extends DomainEvent {
  public function getEventName(): string { return 'user.created'; }
}

class SampleUser extends AggregateRoot {
  private string $name = '';

  public function getName(): string { return $this->name; }

  public function setName(string $name): void {
    $this->name = $name;
    $this->recordDomainEvent(new UserCreatedEvent((string) $this->id, ['name' => $name]));
  }

  public function toPrimitives(): array {
    return [
      'id' => (string) $this->id,
      'name' => $this->name,
      'version' => $this->getVersion(),
    ];
  }

  protected function restoreFromPrimitives(array $primitives): void {
    $this->name = $primitives['name'] ?? '';
  }
}

final class AggregateRootTest extends TestCase
{
  public function testRecordAndPullDomainEvents(): void
  {
    $id = new TestId('user-123');
    $user = new SampleUser($id);

    $this->assertSame(0, $user->getVersion());
    $this->assertFalse($user->hasUncommittedChanges());

    $user->setName('Alice');

    $this->assertSame(1, $user->getVersion());
    $this->assertTrue($user->hasUncommittedChanges());

    $events = $user->pullDomainEvents();
    $this->assertCount(1, $events);
    $this->assertSame('user.created', $events[0]->getEventName());
    $this->assertSame(1, $events[0]->sequenceNumber);

    $this->assertCount(0, $user->pullDomainEvents());
  }

  public function testClearDomainEvents(): void
  {
    $id = new TestId('user-456');
    $user = new SampleUser($id);
    $user->setName('Bob');

    $this->assertSame(1, $user->getVersion());
    $user->clearDomainEvents();
    $this->assertSame(0, $user->getVersion());
    $this->assertCount(0, $user->pullDomainEvents());
  }

  public function testSnapshotRestoration(): void
  {
    $id = new TestId('user-789');
    $user = new SampleUser($id);
    $user->setName('Charlie');

    $snapshot = $user->toSnapshot();
    $this->assertSame($id, $snapshot['id']);
    $this->assertSame(1, $snapshot['version']);

    $restored = SampleUser::fromSnapshot($snapshot);
    $this->assertSame('Charlie', $restored->getName());
    $this->assertSame(1, $restored->getVersion());
    $this->assertFalse($restored->hasUncommittedChanges());
  }

  public function testEntityRegistry(): void
  {
    EntityRegistry::clear();
    $this->assertFalse(EntityRegistry::has('sample_user'));

    EntityRegistry::register('sample_user', static function (array $data): SampleUser {
      $user = new SampleUser(new TestId($data['id']));
      $user->setName($data['name']);
      return $user;
    });

    $this->assertTrue(EntityRegistry::has('sample_user'));

    $user = EntityRegistry::fromPrimitives('sample_user', [
      'id' => 'user-999',
      'name' => 'Dave',
    ]);

    $this->assertInstanceOf(SampleUser::class, $user);
    $this->assertSame('Dave', $user->getName());

    EntityRegistry::unregister('sample_user');
    $this->assertFalse(EntityRegistry::has('sample_user'));
  }
}
