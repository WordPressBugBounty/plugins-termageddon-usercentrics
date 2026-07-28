---
name: version-bump
description: Update the WordPress plugin version, stable tag, and public-facing README.txt changelog using SemVer release rules.
---

Update the version of this plugin in all necessary places, including the plugin header, version constant, stable tag, and README.

Follow SemVer when choosing the next version:

- Patch (`x.y.Z`): bug fixes only.
- Minor (`x.Y.0`): new user-facing features, new compliance/geolocation support, or meaningful improvements that are backward compatible.
- Major (`X.0.0`): breaking changes.

Update `README.txt` with a changelog entry for the release, maintaining the current changelog format. Changelog entries should be public-facing and focus only on changes that matter to plugin users.
