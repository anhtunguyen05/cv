# Adversarial Architecture Review — CareerFitCV

## Verdict

The spine prevents the main independent-builder divergences: alternate CV
representations, multiple trusted-state owners, mutable historical Versions,
opaque MVP matching, and direct provider writes. No additional conflicting
owner or mutation path was found after the AD-4 refinement.

## Pair checks

- Web renderer and Laravel renderer can both exist, but AD-8/AD-10 require the
  renderer to consume a saved CV Version and do not give either renderer write
  ownership.
- Deterministic matcher and later AI provider can both produce analysis, but
  AD-5 makes deterministic MVP output authoritative and AD-6 limits AI to
  validated proposals.
- CV Profile editor and Version service can both handle save actions, but AD-3
  separates mutable draft state from immutable snapshot creation.
- Worker and API can both process jobs, but AD-2/AD-8 keep trusted state and
  ownership in Laravel.

## Deferred risk

The future authentication mechanism, storage deployment topology, and async
job contract need explicit decisions before independent Phase 0/5 units are
implemented. The spine names each as Deferred rather than silently selecting
one.
