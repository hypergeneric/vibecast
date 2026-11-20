=== Vibecast by Compiled Rogue ===
Contributors: michaelsablone
Tags: reusable content, html, shortcode, blocks
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Cast Vibecoded Content into WordPress.

== Description ==

Describe a section to your AI tool, refine it together, and bring the finished HTML into Vibecast. Preview it, publish it once, and reuse it across posts and pages. Update the saved section and every placement follows.

Vibecast's bundled agent skill gives your AI a shared format: clean HTML, inline styling, and four responsive heights. Each section keeps its own presentation, independent of your theme's styling. Use it for explainers, comparison tables, information panels, checklists, and reusable callouts.

* Copy or download an agent skill for your preferred AI tool.
* Save reusable HTML with supported inline styles.
* Set responsive heights for four familiar width ranges.
* Preview drafts and compare or restore WordPress revisions.
* Select published sections by name in the Vibecast block.
* Place content with a shortcode using its ID or slug.
* Create My First Vibecast for a ready-made example that explains and demonstrates the workflow.
* Open the complete content in a separate document when more reading space is needed.

No AI account is connected to WordPress. The plugin sends no telemetry. Images included in your HTML load from their specified HTTP or HTTPS URLs.

== Installation ==

1. Install and activate Vibecast.
2. Open Vibecasts > Agent skill. Copy the instructions or download SKILL.md for your AI tool. For a ready-made introduction, choose Create My First Vibecast.
3. Describe the section you want and ask for its HTML artifact and four height values.
4. Add a Vibecast, paste the HTML, enter the heights, and save a draft to preview it.
5. Publish, then select it by name in the Vibecast block, or use [cast-vibecoded-content id="123"].

== Frequently Asked Questions ==

= What can I create? =
Static sections such as explainers, comparison tables, information panels, checklists, and callouts. The agent skill describes the supported HTML and inline styling, includes an example, and explains how to prepare the height values. The saved preview shows the result after WordPress sanitization.

= How do responsive heights work? =
Save a height for base (below 640px), sm (640px to below 768px), md (768px to below 1024px), and lg (1024px and above). Each accepts 100–10000 pixels and defaults to 600. The section's available width selects the height, so narrow columns work independently of browser width. The block uses these saved values automatically. Browsers without container queries use the base height.

= How do I check that everything fits? =
Check each width range with images loaded, disclosures expanded, and larger text. Only the surrounding page scrolls; a height that is too short clips the embedded section. Published, non-password-protected sections include an Open full content link to the complete section in a separate, scrollable document.

= Who can manage content? =
Users with the manage_options capability can create, edit, and publish Vibecasts. Content editors can place published sections in their posts using the block or shortcode. Drafts and private sections are not rendered publicly.

= Can I use a slug or a fixed height? =
Yes: [cast-vibecoded-content slug="my-panel"]. An ID takes precedence if both are supplied. An explicit height attribute overrides the saved heights for that placement. Existing fixed-height blocks can switch to saved responsive heights.

= Can I restore an earlier version? =
Yes. WordPress revisions include the HTML and heights, with comparisons available before restoring. Revision retention follows your site's settings.

= What happens when I remove the plugin? =
Content, revisions, and saved configuration are retained. Blocks stop rendering while the plugin is inactive, and shortcodes may appear as text.

== Changelog ==

= 1.0.1 =
* Updated plugin identity and contributor attribution.
* Load placement styles through WordPress asset hooks.

= 1.0.0 =
Initial release with an agent authoring skill, reusable HTML library, responsive heights, saved previews, revision comparisons, block and shortcode placement, and a built-in example.
