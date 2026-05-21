# Container and Inject

`pms\Container` is a small reflection-based object factory. It supports constructor argument resolution, shared instances, explicit bindings, and attribute-based property injection.

## Object Creation

`Container::invokeClass($class, $args = [])`:

1. Builds a `ReflectionClass`.
2. Runs `AnnotationClassHook` for class-level attributes.
3. Resolves constructor arguments.
4. Creates the object.
5. Runs `AnnotationPropertyHook` for property attributes.

Constructor arguments can be supplied by numeric position or parameter name. If a missing constructor parameter has a non-builtin class type, the container recursively creates and caches that dependency by class name.

If a required builtin parameter has no value and no default, `SystemException` is thrown.

## Instances and Bindings

The container keeps:

- `$bind`: class mappings that create a new object when requested.
- `$instances`: shared instances or class strings that become shared instances on first use.

Useful methods:

```php
$container->invokeClass(Foo::class);
$container->has(Foo::class);
$container->get(Foo::class);
$container->make(Foo::class);
$container->put(Foo::class, $foo);
```

`get()` throws `ClassNotFoundException` when the name is not bound or stored.

## Inject Attribute

`pms\annotate\Inject` is a PHP attribute:

```php
#[\Attribute]
class Inject
{
    public function __construct(string $classname, ...$args) {}
}
```

During setup, `Setup::initInject()` mounts a property handler for `Inject::class`.

Example:

```php
use pms\annotate\Inject;

class Handler
{
    #[Inject(ServiceClient::class)]
    protected ServiceClient $client;
}
```

The first attribute argument is treated as a container name or class name. Extra attribute arguments are passed as creation arguments. If the container already has that name, it reuses the stored value; otherwise it creates the class and stores it.

## Attribute Hooks

`AnnotationClassHook` handles class attributes before construction. `AnnotationPropertyHook` handles property attributes after construction. Both expose `mount()` and `audit()`.
