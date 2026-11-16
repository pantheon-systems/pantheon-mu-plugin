# E2E tests (SITE-5884)

Playwright + [playwright-bdd](https://github.com/vitalets/playwright-bdd) browser E2E for the Pantheon WordPress update notice. Self-contained: no dependency on the shared `qa-e2e-automation` framework (steps are copied locally). Runs against an ephemeral Pantheon multidev provisioned from a base WordPress site.

> Added to PR #119 to validate the BDD approach for this plugin. Intended to move to the shared/public CMS test framework once that lands (pending Ander).

## Layout

```
tests/e2e/
├── features/          # Gherkin .feature files
├── steps/             # local step definitions (TypeScript)
├── global-setup.ts    # provisions the multidev + installs the branch plugin
├── global-teardown.ts # deletes the multidev
├── playwright.config.ts
└── .env.example       # copy to .env and fill in
```

## Prerequisites

- Node.js 20+
- [Terminus](https://docs.pantheon.io/terminus), authenticated (`terminus auth:login`)
- A Pantheon WordPress base site with multidev capacity (`TERMINUS_SITE`, default `pantheon-mu-plugin`)

## Run locally

```bash
cd tests/e2e
cp .env.example .env   # fill in WP_USER / WP_PASSWORD
npm install
npx playwright install --with-deps chromium
npm test               # provisions a multidev, runs scenarios, tears down
npm run test:headed    # watch the browser
```
