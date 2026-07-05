# HybridCore Staff

[![HybridCore](https://img.shields.io/badge/HybridCore-%E2%89%A5%200.3.0-6366f1)](https://github.com/HybridMindLabs/HybridCore)
[![Version](https://img.shields.io/badge/version-1.0.0-22c55e)](CHANGELOG.md)
[![License](https://img.shields.io/badge/license-Proprietary-64748b)](LICENSE)

The official **Staff** extension for [HybridCore](https://github.com/HybridMindLabs/HybridCore) — show off your community team on a polished public page.

- 👥 **Curated team list** — add exactly who you want, in the order you want.
- 🗂️ **Groups** — organise members into sections (Management, Admins, Moderators…).
- 🔗 **Optional user link** — link a member to a site account to pull their avatar and profile link, or add a standalone entry with a custom avatar.
- 🎨 **Per-member accent colour**, role label, bio and Discord / Steam links.
- 🔎 Members are searchable in the global search, and a team-size widget appears on the admin dashboard.
- 🌍 English + Bulgarian translations.

## Requirements

- HybridCore **≥ 0.3.0**

## Installation

```bash
cd extensions/hybridcore
git clone https://github.com/HybridMindLabs/hybridcore-staff.git staff

php artisan hybridcore:extensions:sync
php artisan hybridcore:extensions:enable hybridcore/staff
```

Enabling runs the migration and seeds one example member (your first admin).

## Usage

- **Admin → Staff** — add, edit, reorder and remove team members.
- Public page lives at **`/staff`** (also linked in the site header).

## Development

```bash
php artisan hybridcore:extensions:test hybridcore/staff
php artisan hybridcore:extensions:zip hybridcore/staff
```

See [`BUILDING_EXTENSIONS.md`](https://github.com/HybridMindLabs/HybridCore/blob/main/extensions/BUILDING_EXTENSIONS.md) for the Extension SDK reference.

## License

Proprietary — © HybridMind Labs. See [LICENSE](LICENSE).
