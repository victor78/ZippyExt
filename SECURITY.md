# Security Policy

## Supported Versions

| Version | Supported          |
|---------|--------------------|
| 1.x     | ✅ Active support  |
| 0.x     | ⚠️ Critical fixes only |

## Reporting a Vulnerability

Please **do not** open a public GitHub issue for security vulnerabilities.

Report vulnerabilities by opening a [GitHub Security Advisory](https://github.com/victor78/ZippyExt/security/advisories/new)
or by contacting the maintainer directly via the profile on GitHub.

**Please include:**
- Description of the vulnerability
- Steps to reproduce
- Affected versions
- Potential impact

You will receive a response within 72 hours. If the vulnerability is confirmed,
a patch will be released as soon as possible.

## Known Issues

- `symfony/process < 5.4.51` (affects `0.x` line): CVE-2024-51736, CVE-2026-24739 —
  both Windows-only, not exploitable on Linux.
  Fixed in `1.x` with `symfony/process ^5.4.51`.

