# API fixtures

One JSON file per public endpoint, named after its `api_endpoints.endpoint` key —
`heroes_stats` becomes `heroes_stats.json`.

These are served instead of live data whenever an account has not activated live
data or has test mode switched on. `ServeApiFixtures` returns the file verbatim
before the controller runs, so no database work happens and no quota is consumed.

Rules:

- **Real shape, obviously fake values.** Fixed battletags, round numbers, a stable
  patch. Nobody should mistake one for production data.
- **Deterministic.** The same file every call, so a consumer can assert against it.
- **One per routed endpoint.** `php artisan api:check-fixtures` fails when a routed
  endpoint has no file, because an endpoint that falls through to live data
  silently defeats the gate.

An object at the top level also gets a `_test_data` note added to the response. A
top-level array is served untouched so the shape still matches the real endpoint.
