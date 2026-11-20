# Vibecast by Compiled Rogue

Cast Vibecoded Content into WordPress.

Version 1.0.1 · WordPress 6.4+ · PHP 7.4+ · GPL-2.0-or-later

**Vibe-code clean, static HTML. Make it part of your WordPress site.**

Describe a section to your AI tool, refine it together, and bring the finished HTML into Vibecast. Preview it, publish it once, and reuse it across posts and pages. Update the saved section and every placement follows.

Vibecast's bundled agent skill gives your AI a shared format: clean HTML, inline styling, and four responsive heights. Each section keeps its own presentation, independent of your theme's styling. Use it for explainers, comparison tables, information panels, checklists, and reusable callouts.

## Get started

1. Open **Vibecasts → Agent skill** and copy the instructions or download `SKILL.md` for your AI tool.
2. Describe what you want. Ask for the HTML artifact and its four height values.
3. Add a Vibecast, paste the HTML, enter the heights, and save a draft to preview it.
4. Publish, then select it by name in the **Vibecast block**, or use `[cast-vibecoded-content id="123"]`.

For a ready-made introduction, choose **Create My First Vibecast** on the Agent skill screen. The example explains Vibecast while demonstrating it. It starts as a draft; opening it again preserves your edits.

## Responsive sections

Heights follow four familiar size ranges, based on the section's available width:

| Preset | Content width |
| --- | --- |
| base | Below 640px |
| sm | 640px to below 768px |
| md | 768px to below 1024px |
| lg | 1024px and above |

Each height accepts 100–10000 pixels and defaults to 600. The block uses the saved heights automatically. A narrow column gets the appropriate height even on a wide screen.

Only the surrounding page scrolls. Check each range with images loaded, disclosures expanded, and larger text: a height that is too short clips the embedded section. Published, unprotected sections include **Open full content**, which opens the complete section in its own scrollable document.

## Content and publishing

Use supported HTML with inline CSS; the saved preview shows the result after WordPress sanitization. Administrators manage the library, while content editors can place published sections. Drafts and private sections stay out of public output.

WordPress revisions include the HTML and heights, with comparisons available before restoring. Content and revisions are retained when the plugin is removed.

No AI account is connected to WordPress. Use the authoring skill with your preferred tool. The plugin sends no telemetry. Images included in your HTML load from their specified HTTP or HTTPS URLs.

Shortcodes also support slugs: `[cast-vibecoded-content slug="my-panel"]`. An explicit `height` attribute provides a fixed height when needed. Existing fixed-height blocks can switch to the saved responsive heights.

## Changelog

### 1.0.1

Updated plugin identity and contributor attribution; placement styles now load through WordPress asset hooks.

### 1.0.0

Initial release with an agent authoring skill, reusable HTML library, responsive heights, saved previews, revision comparisons, block and shortcode placement, and a built-in example.
