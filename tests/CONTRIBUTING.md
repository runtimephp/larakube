# Testing Guidelines

## General Testing Principles

- **Full test coverage required** - Test happy paths, failure paths, edge cases
- **All tests must pass before committing**
- Use `composer test:lint` to check code style
- Use `composer test` to run all the test suite
- **ALWAYS use `test('description', function ():void { ... });` syntax** - NEVER use `it()`.
- **New tests must be added at the TOP of the file** - Don't append to the bottom

## Test Structure

### Flat Test Organization
**ALWAYS use flat structure** - NO nested subdirectories:
- Correct: `tests/Unit/Actions/CreateLocationTest.php`
- Wrong: `tests/Unit/Actions/Location/CreateLocationTest.php`
- Exception: Organizing by type is allowed (Models/, Actions/, Enums/)
- Exception: For multi-tenant applications, use Landlord/ and Tenant/ subdirectories under Feature/ and Browser/ to scope tests appropriately (e.g., tests/Feature/Tenant/UserProfileControllerTest.php).

### Test File Naming
- Test files end with `Test.php`
- Match the class being tested: `CreateLocation` -> `CreateLocationTest`
- Place in appropriate directory: Unit/, Feature/, or Browser/

### Test Naming Convention

**ALWAYS use imperative mood for test names** - describe what the code does, not what "it" does:

```php
// CORRECT - Imperative mood (commands)
test('creates user with valid data', function (): void { ... });
test('validates required email field', function (): void { ... });
test('transforms arrays to JSON strings', function (): void { ... });
test('handles null values correctly', function (): void { ... });
test('preserves user id when updating', function (): void { ... });

// WRONG - "It" statements
test('it creates user with valid data', function (): void { ... });
test('it validates required email field', function (): void { ... });
test('it transforms arrays to JSON strings', function (): void { ... });
test('it handles null values correctly', function (): void { ... });
```

**Why imperative mood?**
- More concise and readable
- Matches Pest/PHPUnit conventions
- Focuses on behavior, not the subject
- Cleaner test output

## Dependency Injection in Tests

### Actions - Use Container Resolution with beforeEach

**ALWAYS resolve Actions from container** - NEVER use `new`:

```php
<?php

use App\Actions\UpdateHousehold;
use App\Data\UpdateHouseholdData;
use App\Models\Household;

beforeEach(function (): void {
    $this->updateHousehold = app(UpdateHousehold::class);
});

test('updates organization with all fields', function (): void {
    /** @var Household $household */
    $household = Household::factory()
        ->create();

    $data = UpdateHouseholdData::from([
        'name' => 'New Name',
    ]);

    $result = $this->updateHousehold->handle($organization, $data);

    expect($result->name)->toBe('New Name');
});
```

**WRONG - Don't use new:**
```php
test('updates organization', function (): void {
    $action = new UpdateHousehold(); // Missing dependencies!
    // ...
});
```

**CORRECT - Use app() in beforeEach:**
```php
beforeEach(function (): void {
    $this->updateHousehold = app(UpdateHousehold::class); // Container resolves dependencies
});
```

### Why Container Resolution?

1. **Automatic Dependency Injection** - Container resolves constructor dependencies
2. **Consistency** - Same resolution as production code
3. **Testing Real Behavior** - Tests actual dependency wiring
4. **Refactoring Safety** - Adding dependencies doesn't break tests

### Processors, Validators & Other Classes

**Same pattern applies to ALL container-resolved classes** (Processors, Validators, Services, etc.).

### Reusable Test Data in beforeEach

**If models, actions, or other resources are reused across multiple tests, ALWAYS define them in `beforeEach`:**

```php
beforeEach(
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Country $country */
        $this->country = Country::factory()->createQuietly();

        /** @var CreateOffice $action */
        $this->action = app(CreateOffice::class);
    });

test('creates office', function (): void {
    $data = CreateOfficeData::from([
        'country_id' => $this->country->id, // Reusing from beforeEach
        'name' => 'HQ',
    ]);

    $office = $this->action->handle($data); // Reusing from beforeEach

    expect($office->name)->toBe('HQ');
});
```

**When NOT to use beforeEach:**
- Data specific to a single test
- Variations that differ per test
- One-off test scenarios

## Test Conventions

### Type Hints & Exception Annotations
**ALWAYS type hint all variables and add @throws annotations before closures:**

```php
test('example',
    /**
     * @throws Throwable
     */
    function (): void {  // @throws annotation BEFORE closure
        /** @var User $user */  // Type hint
        $user = User::factory()->createQuietly();

        expect($user->id)->toBeInt();
    });
```

**For `beforeEach` hooks:**
```php
beforeEach(
    /**
     * @throws Throwable
     */
    function (): void {  // @throws annotation BEFORE closure
        /** @var CreateOffice $action */
        $this->action = app(CreateOffice::class);
    });
```

**Why add @throws Throwable?**
- Tests can throw exceptions (database errors, validation failures, etc.)
- Makes exception handling explicit
- Helps static analysis tools understand test behavior
- Required for consistency across all test files
- **MUST be placed before the closure**, not before the test() or beforeEach() call

### Factory Methods
- **ALWAYS use `createQuietly()`** - Prevents events from firing
- **Use `fresh()`** for records from migrations/seeders
- **Pass data explicitly** - Don't rely on factory defaults in tests

```php
// Create models
/** @var User $user */
$user = User::factory()->createQuietly(['name' => 'Test']);

// Retrieve seeded records
/** @var Role $role */
$role = Role::query()
    ->where('name', 'employee')
    ->first()
    ?->fresh();
```

### Assertions
- **Chain expect() methods** for cleaner tests
- Use specific assertions: `assertOk()`, `assertForbidden()` not `assertStatus()`
- **Import all classes** - Never use fully qualified names inline

```php
expect($result->name)
    ->toBe('Test Name')
    ->and($result->email)
    ->toBe('test@example.com')
    ->and($result->active)
    ->toBeTrue();
```

## Exception Testing

**Use Pest's `->throws()` method:**

```php
test('validates required field', function () {
    $data = [];

    CreateUserData::validateAndCreate($data);
})->throws(ValidationException::class, 'email');
```

**WRONG - Don't wrap in expect():**
```php
expect(fn() => CreateUserData::validateAndCreate([]))
    ->toThrow(ValidationException::class); // Wrong
```

## InMemory Services (No Mocking)

**NEVER use Mockery** - Use InMemory implementations of services instead.

### Why InMemory Services?

1. **Real implementations** - Test with actual code, not mocks
2. **Controllable behavior** - Set up specific scenarios without complex mock expectations
3. **Stateful testing** - InMemory services maintain state like real services
4. **No mock syntax** - Cleaner, more readable tests
5. **Refactoring safe** - Changes to service interfaces break tests immediately

### Available InMemory Services

Located in `App\Services\InMemory\`:

| Service | Contract | Purpose |
|---------|----------|---------|
| `InMemoryHetznerService` | `CloudProviderService` | Token validation |
| `InMemoryHetznerServerService` | `ServerService` | Server CRUD operations |
| `InMemoryDigitalOceanService` | `CloudProviderService` | Token validation |
| `InMemoryDigitalOceanServerService` | `ServerService` | Server CRUD operations |

### Helper Functions

Use these helper functions from `tests/Pest.php`:

```php
// Create InMemory services
$hetznerService = useInMemoryHetznerService(isValid: true);  // or false
$serverService = useInMemoryHetznerServerService();

// Bind to container
bindInMemoryHetznerFactory(
    validationService: $hetznerService,
    serverService: $serverService
);
```

### Configuring InMemory Services

**Set validation result:**
```php
$service = useInMemoryHetznerService(true);   // Token is valid
$service = useInMemoryHetznerService(false);  // Token is invalid
```

**Set up server data:**
```php
$serverService = useInMemoryHetznerServerService();
$serverService->addServer(new ServerData(
    externalId: 123,
    name: 'web-1',
    status: ServerStatus::Running,
    type: 'cx11',
    region: 'fsn1',
    ipv4: '1.2.3.4',
));
```

**Simulate failures:**
```php
$serverService->shouldFailCreate(true);  // Create will throw exception
$serverService->shouldFailDelete(true);  // Delete will return false
```

### Example: Testing Command with InMemory Service

```php
test('list servers syncs and displays table', function (): void {
    // Arrange: Create and configure InMemory service
    $serverService = useInMemoryHetznerServerService();
    $serverService->addServer(new ServerData(
        externalId: 123,
        name: 'web-1',
        status: ServerStatus::Running,
        type: 'cx11',
        region: 'fsn1',
        ipv4: '1.2.3.4',
    ));

    // Bind to container
    bindInMemoryHetznerFactory(serverService: $serverService);

    // Act & Assert
    $this->artisan('server:list')
        ->expectsOutputToContain('web-1')
        ->assertSuccessful();
});
```

### WRONG - Don't Use Mockery

```php
// WRONG - Never do this:
$mockServerService = Mockery::mock(ServerService::class);
$mockServerService->shouldReceive('getAll')->andReturn(collect([...']));

$mockFactory = Mockery::mock(CloudProviderFactory::class);
$mockFactory->shouldReceive('makeServerService')->andReturn($mockServerService);
```

### CORRECT - Use InMemory Services

```php
// CORRECT - Use InMemory implementations:
$serverService = useInMemoryHetznerServerService();
$serverService->addServer(new ServerData(...));

bindInMemoryHetznerFactory(serverService: $serverService);
```

## Authentication in Tests

**Use `$this->actingAs()`** for authentication:

```php
test('authorized user can access', function (): void {
    /** @var User $user */
    $user = User::factory()->createQuietly();
    $user->assignRole('owner');

    $this->actingAs($user);

    $response = $this->get(route('admin.settings'));

    $response->assertOk();
});
```

## Browser Tests (Pest v4)

### Using visit()
**Use `visit()` function** (no `$this->`):

```php
test('page renders correctly', function (): void {
    /** @var User $user */
    $user = User::factory()->createQuietly();

    $this->actingAs($user);

    $page = visit('/dashboard');  // No $this->

    $page->assertSee('Welcome');
});
```

### Global Configuration
- `RefreshDatabase` applied globally in `tests/Pest.php`
- Don't add `uses(RefreshDatabase::class)` in individual tests

## Test Organization

### New Tests First
**Place newly written tests at the TOP of the file:**

```php
// New test here
test('new feature works', function (): void {
    // ...
});

// Existing tests below
test('existing feature works', function (): void {
    // ...
});
```

### Running Tests
- To run style checks: `composer test:lint`
- To run all tests: `composer test`
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file)
- At the end of each work session, run the full test suite to ensure everything passes

### Before Every Commit

**ALWAYS run:**
- `composer test:lint` to check code style
- `composer test` to run the full test suite

## Model Testing Requirements

### Mandatory Tests for Every Model

Every model test file MUST include these tests in this exact order:

#### 1. Basic Model Creation Test
```php
test('creates model name',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ModelName $model */
        $model = ModelName::factory()->createQuietly([
            'some_field' => 'Some Value',
        ]);

        expect($model->some_field)->toBe('Some Value')
            ->and($model->id)->toBeString()
            ->and($model->created_at)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
    });
```

#### 2. Relationship Tests (if applicable)
```php
test('belongs to parent model',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ParentModel $parent */
        $parent = ParentModel::factory()->createQuietly();

        /** @var ChildModel $child */
        $child = ChildModel::factory()->createQuietly([
            'parent_id' => $parent->id,
        ]);

        expect($child->parent)->toBeInstanceOf(ParentModel::class)
            ->and($child->parent->id)->toBe($parent->id);
    });
```

#### 3. Cast Attributes Test
```php
test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ModelName $model */
        $model = ModelName::factory()->createQuietly();

        expect($model->id)->toBeString()
            ->and($model->created_at)->toBeInstanceOf(\Carbon\CarbonImmutable::class)
            ->and($model->updated_at)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
    });
```

#### 4. UUID Test
```php
test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ModelName $model */
        $model = ModelName::factory()->createQuietly();

        expect($model->id)
            ->toBeString()
            ->toBeUuid();
    });
```

#### 5. toArray() Field Order Test - MANDATORY FOR EVERY MODEL

**THIS TEST IS REQUIRED FOR EVERY MODEL - NO EXCEPTIONS**

```php
test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ModelName $model */
        $model = ModelName::factory()->createQuietly()->refresh();

        expect(array_keys($model->toArray()))
            ->toBe([
                'id',
                'created_at',
                'updated_at',
                'foreign_key_id',  // Foreign keys after timestamps
                'regular_field_1',
                'regular_field_2',
                // ... all fields in database column order
            ]);
    });
```

**Why this test is CRITICAL:**
- Ensures API responses are consistent
- Catches missing or extra fields in serialization
- Validates `$hidden`, `$visible`, and `$appends` properties
- Detects accidental exposure of sensitive fields
- **PREVENTS BREAKING CHANGES** in API contracts

**Field order in toArray():**
1. `id` (primary key)
2. `created_at`
3. `updated_at`
4. Foreign key fields
5. Regular fields (in database column order)

### Common Model Testing Mistakes

**WRONG - Missing toArray() test:**
```php
test('uses uuid for primary key', function (): void { /* ... */ });
// END OF FILE - MISSING toArray() test!
```

**CORRECT - All mandatory tests present:**
```php
test('creates model', function (): void { /* ... */ });
test('belongs to parent', function (): void { /* ... */ });
test('casts attributes correctly', function (): void { /* ... */ });
test('uses uuid for primary key', function (): void { /* ... */ });
test('to array has all fields in correct order', function (): void { /* ... */ }); // MUST BE LAST
```

**WRONG - No refresh() before toArray():**
```php
$model = ModelName::factory()->createQuietly();
expect(array_keys($model->toArray()))->toBe([...]); // Missing refresh()
```

**CORRECT - Always refresh() before toArray():**
```php
$model = ModelName::factory()->createQuietly()->refresh(); // Correct
expect(array_keys($model->toArray()))->toBe([...]);
```
