# Changelog

All notable changes to the HybridCore Staff extension are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed
- The Steam field is now validated as a URL — it was stored as a plain
  string but rendered as a direct link on the public page, so a non-URL
  value produced a broken link.

## [1.0.0] - 2026-07-05

### Added
- Curated public staff page at `/staff`, linked in the site header.
- Members grouped into sections, ordered by a sort value.
- Optional link to a site user (pulls avatar + profile link), or a standalone
  entry with a custom avatar.
- Per-member accent colour, role label, bio and Discord / Steam links.
- Admin CRUD to add, edit, reorder and remove members.
- Global-search provider for staff members and a team-size dashboard widget.
- English and Bulgarian translations.

[1.0.0]: https://github.com/HybridMindLabs/hybridcore-staff/releases/tag/v1.0.0
