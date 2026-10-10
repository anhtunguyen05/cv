# Epic 6 Architecture Decisions

## ADR-006-01 — Module-level Ports-and-Adapters

**Status:** accepted

**Decision:** Keep Laravel's application structure but make module boundaries
explicit through application contracts and infrastructure adapters. Use cohesive
workflow ports instead of one repository per table.

**Rationale:** The API already has feature workflows and a working HTTP
contract. A narrow port at each real external boundary protects those contracts
without introducing speculative domain ceremony.

**Consequences:** Eloquent models remain useful persistence representations but
are not application contracts. Transactions and driver-specific locks move
behind shared infrastructure ports. Feature tests remain the behavioral oracle.

## ADR-006-02 — Persisted status strings remain stable

**Status:** accepted

**Decision:** Domain-backed enums use the exact existing database values. The
schema and stored data are unchanged.

**Rationale:** State transitions need one vocabulary while replay, migration,
and historical records remain compatible.

**Consequences:** New code uses enum constructors/policies at the domain edge;
adapters serialize the enum value when persisting or presenting data.

## ADR-006-03 — Provider output remains proposal-only

**Status:** accepted

**Decision:** External providers implement an application provider port and
return validated proposal data. Only the application workflow persists trusted
Patch state.

**Rationale:** Provider availability and output must not bypass ownership,
validation, reservation, or approval rules.
