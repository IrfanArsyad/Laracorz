# Static Analysis (PHPStan) — Toolchain Notes

Reference for running the PHPStan quality gate reliably, especially inside AI-agent
environments. Written while resolving STU-97.

## TL;DR — how to run

```bash
# Agent-optimized output (compact JSON via laravel/pao). Fully clean, no xsl noise.
LD_LIBRARY_PATH= composer analyse

# Raw human-readable PHPStan output (tables). Use this to DEBUG the toolchain,
# because pao hides output it cannot parse (see "Gotcha #2" below).
PAO_DISABLE=1 LD_LIBRARY_PATH= ./vendor/bin/phpstan analyse --memory-limit=2G

# Scope to a single module / file
PAO_DISABLE=1 LD_LIBRARY_PATH= ./vendor/bin/phpstan analyse modules/AccessControl
```

A green run either prints `{"tool":"phpstan","result":"passed","errors":0}` (pao mode)
or `[OK] No errors` (raw mode). Real type errors are the gate working as intended.

## What was broken (STU-97) and why

Symptom reported: `phpstan analyse` produced **zero output for any file** while
`phpstan --version` worked. It was initially attributed to `LD_LIBRARY_PATH` / the
`xsl` extension. The real chain was three separate layers:

### Root cause #1 (the actual blocker): invalid `phpstan.neon` keys
`phpstan.neon` carried two options that were **valid in PHPStan 1.x but removed in
2.x**:

```
checkMissingIterableValueType: false
checkGenericClassInNonGenericObjectType: false
```

PHPStan 2.x rejects unknown keys with `Invalid configuration: Unexpected item ...`,
prints it to **stderr**, and does **no analysis**. Fixed by replacing them with the
2.x-equivalent error-identifier ignores (preserving the original intent):

```neon
ignoreErrors:
    - identifier: missingType.iterableValue
    - identifier: missingType.generics
```

### Gotcha #2 (why nobody saw the error): `laravel/pao` agent mode
`laravel/pao` (dev dependency, "agent-optimized output") auto-loads on every process
that loads the project autoloader — including `vendor/bin/phpstan`. When
`laravel/agent-detector` detects an agent (`CLAUDECODE=1` → `name=claude`), pao:

1. silences the tool's real stdout/stderr and forces `--error-format=json`;
2. on shutdown, parses the captured JSON and re-emits a compact summary.

If the captured output is **not** parseable JSON with a `totals` key (e.g. because
PHPStan errored out on the invalid config), pao's parser returns `null` and the
`if ($result !== [])` guard means **nothing is printed at all**. That is what turned a
loud "Invalid configuration" error into a silent no-op for agents.

> When phpstan (or any pao-wrapped tool: pest, phpunit, rector) mysteriously prints
> nothing for an agent, re-run with `PAO_DISABLE=1` to see the raw error. This is the
> single most useful debugging trick for this toolchain.

### Gotcha #3 (cosmetic only): `LD_LIBRARY_PATH` / `xsl.so`
Running tests via `@embedded-postgres` exports
`LD_LIBRARY_PATH=…/@embedded-postgres/linux-x64/native/lib` into the shell. That dir
ships an older `libxslt.so.1` which the dynamic loader picks **before** the system
one (LD_LIBRARY_PATH outranks the ldconfig cache), so PHP fails to load `xsl.so`:

```
Unable to load dynamic library 'xsl.so' ... libxslt.so.1: version `LIBXML2_1.1.30' not found
```

This is a **stderr warning only** — analysis still runs correctly with the warning
present (verified). Clearing `LD_LIBRARY_PATH` for the phpstan process removes the
noise and does **not** affect Postgres (embedded-postgres only needs the var when
running its own server binary during tests). The `composer analyse` script now clears
it for the phpstan subprocess; prefix the whole command (`LD_LIBRARY_PATH= composer
analyse`) to also silence composer's own boot warning.

## Verification (STU-97 acceptance)

| Check | Result |
|-------|--------|
| `phpstan analyse` produces analysis output | yes (61 errors across `app` + `modules`) |
| `modules/AccessControl` | analyses (7 real type errors surfaced) |
| stubs generator `app/Console/Commands/ModuleMakeCrudCommand.php` | analyses (`[OK] No errors`) |
| `stubs/laracorz/*.stub` | correctly outside `paths`, not analysed (templates contain `{{placeholders}}`) |
| pao agent JSON output | `{"tool":"phpstan","result":"failed","errors":61,...}` |
| config validity | no `Invalid configuration` |

The 61/7 errors are genuine type issues in application code — they are for the
Full-stack Engineer to address, not part of this toolchain fix.
