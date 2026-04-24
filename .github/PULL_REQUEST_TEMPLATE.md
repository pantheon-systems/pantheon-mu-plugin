## Description

<!-- Describe the change and why it's needed. -->

## Related Issues / Tickets

<!-- Link to any related GitHub issues or internal tickets. -->

## Testing

<!-- Describe how you tested this change. -->

---

## Release Reminder

Releases are gated by the version in `pantheon.php`. Before merging:

| Intent | What to do |
|---|---|
| Normal PR (no release) | Leave version as `X.Y.Z-dev` — no action needed |
| **Ship a release with this PR** | Remove `-dev` from the version (e.g. `1.5.7-dev` → `1.5.7`) |
| **Ship a minor version** | Update version to the target minor (e.g. `1.5.7-dev` → `1.6.0`) |

See [CONTRIBUTING.md](CONTRIBUTING.md) for details.
