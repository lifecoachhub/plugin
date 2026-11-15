# CLAUDE.md

Life Coach Hub WordPress plugin (WordPress.org slug `lifecoachhub`). It connects a WordPress site to a Life Coach Hub account and provides blocks that show the coach's Life Coach Hub data.

## Structure

- `lifecoachhub.php` — bootstrap, constants (`LIFECOACHHUB_VERSION`, `LIFECOACHHUB_APP_URL`), block and pattern registration.
- `includes/` — PSR-4 `LifeCoachHub\` classes (Composer autoloader; `vendor/` is gitignored and generated).
  - `Admin/` — admin pages (embedded Life Coach Hub dashboard, settings).
  - `Api/LifeCoachHub_Store.php` — fetches the coach's offers from the app (`/api/v1/wordpress/store`, `X-LCH-Key` header), 10-minute transient plus last-good fallback.
  - `Api/LifeCoachHub_Rest.php` — `lifecoachhub/v1/store` REST route for the block editor.
  - `Patterns/LifeCoachHub_Patterns.php` — shop page patterns and the layout presets shared with the Shop block's Layout picker.
- `src/` — block sources (`lifecoachhub-status`, `shop-grid`); `build/` is the compiled output and **is committed**.
- `assets/` — admin CSS/JS/images shipped in the plugin.
- `.wordpress-org/` — banner, icon and screenshots for the WordPress.org listing (deployed to SVN `/assets`, not shipped in the zip).
- `uninstall.php` — removes plugin options and caches.

## Blocks

- Block API v3 with `supports.interactivity`: React for `edit.js`, Interactivity API (`viewScriptModule`) for the frontend, server rendering in `render.php`.
- Do not use `__experimental*` components or APIs (lint fails).
- Sanitize everything rendered from attributes; CSS values go through the existing sanitizers in `render.php`.

## Commands

```bash
npm run build                          # wp-scripts build --blocks-manifest --experimental-modules
npm run start                          # watch mode
npx wp-scripts lint-js src/
npx wp-scripts lint-style 'src/**/*.scss'
npm run plugin-zip                     # lifecoachhub.zip from the package.json "files" list
composer dump-autoload -o
```

Plugin Check (`wp plugin check lifecoachhub`) should only report `hidden_files` / `compressed_files`, which come from dev files that are not in the release zip.

## Conventions

- WordPress coding standards, text domain `lifecoachhub`, escape output, sanitize input, nonces and capability checks on every admin action.
- Supported: WordPress 6.7+, PHP 7.4+. Keep `Requires at least`, `Requires PHP` and `Tested up to` in sync between `lifecoachhub.php` and `readme.txt`.
- Rebuild and commit `build/` with any `src/` change.
- Commits: author `lch-ci`, plain imperative subject with a short bullet body, no ticket IDs, no Co-Authored-By lines. Push to `main`; this repo does not use pull requests.

## Releasing a new version

Releases are automated by `.github/workflows/release.yml`: publishing a GitHub Release tagged `X.Y.Z` (no `v` prefix) checks versions, builds the plugin, deploys to WordPress.org SVN (trunk, `tags/X.Y.Z`, listing assets) and attaches `lifecoachhub.zip` to the release. Running the workflow manually does a build and an SVN dry run.

1. Bump the version in **all** of: `lifecoachhub.php` header `Version:` and `LIFECOACHHUB_VERSION`, `readme.txt` `Stable tag:`, `package.json` `version`, and each `src/*/block.json` `version`. The workflow stops if they don't match the tag.
2. Add a `= X.Y.Z - Mon DD, YYYY =` entry at the top of the `readme.txt` changelog with user-facing changes since the last tag. Update `Tested up to` when tested against a newer WordPress.
3. Run lint, `npm run build` and Plugin Check; commit any `build/` changes.
4. Commit and push to `main`.
5. Create a draft release for review:
   `gh release create X.Y.Z -R lifecoachhub/plugin --draft --target main --title X.Y.Z --notes-file notes.md`
6. Publishing the draft starts the deploy. Check the run under Actions and that the zip is attached.

SVN credentials are the `SVN_USERNAME` / `SVN_PASSWORD` repository secrets. The password is the separate WordPress.org **SVN password** (Profile → Account & Security), not the account password. An `E215004` authentication error means that secret is wrong: update it and use "Re-run failed jobs" rather than creating a new release.

Plugin features that depend on new Life Coach Hub app endpoints must only be released after the app change is deployed.
