# AI Workflow (Compatibility Entry Point)

AI-assisted revision is post-MVP. Its durable implementation blueprint is
[architecture/ai-service-implementation.md](architecture/ai-service-implementation.md).
It defines the planned Laravel-to-AI-service boundary, versioned candidate
contract, phased delivery, verification gates, and explicit production
approvals.

The permanent repository guardrails remain in
[architecture/overview.md](architecture/overview.md),
[standards/security.md](standards/security.md),
[standards/observability.md](standards/observability.md), and
[standards/reliability.md](standards/reliability.md).

AI returns evidence-bound, untrusted proposals through an application contract;
it never writes trusted state and every Patch requires explicit user approval.
