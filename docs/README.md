# superpms/basic Developer Docs

This directory is the developer documentation for the `superpms/basic` composer package. It explains the package mechanism itself: boot, hooks, facades, drivers, services, helpers, and runtime error flow.

It is not business documentation. Server-side applications may use this package as their runtime foundation, but business domains such as cashier, shop, workflow, filesystem, user, or auth should only appear here as brief integration examples when needed.

## Read First

1. [Installation and package shape](getting-started/installation.md)
2. [boot.json contract](getting-started/boot-json.md)
3. [Boot lifecycle](architecture/boot-lifecycle.md)
4. [API map](reference/api-map.md)

## Read By Question

- "How does Composer load the package?": [Composer autoload](architecture/composer-autoload.md)
- "What happens when `new Boot(...)` runs?": [Boot lifecycle](architecture/boot-lifecycle.md)
- "How do I read paths, config, context, or services?": [Path, Config, Ctx, and Service](core/path-config-ctx-service.md)
- "How do facades work?": [Facades and drivers](core/facades-and-drivers.md)
- "How does `#[Inject]` work?": [Container and Inject](core/container-and-inject.md)
- "Which helpers and constants are globally available?": [Helpers and constants](core/helpers-and-constants.md)
- "How do I mount a lifecycle hook or interpreter?": [Hooks](extension-points/hooks.md)
- "How do service classes activate behavior?": [ServiceApp](extension-points/service-app.md)
- "How do interpreters and adapters plug in?": [Interpreters and adapters](extension-points/interpreters-and-adapters.md)
- "How are startup and runtime errors handled?": [Error handling](runtime/error-handling.md)
- "How does the server project currently enter this package?": [Server integration](runtime/server-integration.md)
- "What is intentionally not covered by this package?": [Known limits](reference/known-limits.md)

## Boundaries

- Use repository-relative links in these docs.
- Keep examples package-level and mechanism-level.
- Do not copy server business docs into this package.
- Recheck real code before changing behavior described here.
