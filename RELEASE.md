# Release guide — v1.0.0

Repository: `ITMOSOFT/laravel-campaign-tracking`

Composer package: `itmosoft/laravel-campaign-tracking`

## Repository description

UTM attribution, campaign touchpoints, conversion recording, and attribution summaries for Laravel applications.

## User-run Git commands

Run locally from the extracted package directory. Review staged files before committing.

```bash
git init
git branch -M main
git add .
git status
git commit -m "chore: initial Laravel campaign tracking package"
git remote add origin https://github.com/ITMOSOFT/laravel-campaign-tracking.git
git push -u origin main
```

After local tests and GitHub Actions pass:

```bash
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

## GitHub Release

Title: `v1.0.0 - Initial Release`

Description:

Initial release of ITMOSOFT Laravel Campaign Tracking.

- Capture campaign touchpoints and UTM source/medium/campaign/term/content fields.
- Store visitor/session identifiers, landing URLs, timestamps, and JSON metadata.
- Record conversion events with optional value, currency, external ID, and touchpoint relation.
- Support idempotency keys to prevent duplicate conversion records on retries.
- Provide campaign-scoped first-touch and last-touch summaries.
- Configurable database tables and model classes.
- Laravel 12/13 and PHP 8.3+ support.

This release does not include JavaScript tracking, cookies, dashboards, ad platform integrations, or cross-campaign multi-touch attribution.

## Packagist

1. Open https://packagist.org/packages/submit
2. Submit `https://github.com/ITMOSOFT/laravel-campaign-tracking`
3. Configure the Packagist GitHub webhook if prompted.
4. Confirm Packagist detects tag `v1.0.0`.
5. Verify installation with `composer require itmosoft/laravel-campaign-tracking`.

Do not publish until local tests and the GitHub Actions matrix pass.
