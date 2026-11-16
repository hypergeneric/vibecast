---
name: vibecast
description: Create reusable HTML content for the Vibecast WordPress plugin, using supported HTML and safe inline CSS without JavaScript.
---

# Create a Vibecast

Generate a useful HTML fragment the user can paste into Vibecast. Good fits include information cards, comparison tables, timelines, checklists, FAQs using details/summary, and formatted explainers. Ask for missing content or design preferences only when needed; otherwise produce a complete first draft.

## Output contract

Deliver an HTML artifact (for example `my-panel.html`) containing a fragment, not a full document, plus the height table below. If file creation is unavailable, provide a fenced HTML block to save as that file. Use inline `style` attributes. Do not include JavaScript, event handlers, script or style tags, external stylesheets, frameworks, SVG, canvas, forms, inputs, embeds, or iframes. Do not fetch data or require an API key. If a request needs calculations, live data, or scripted interaction, explain that limitation and offer a useful static presentation or native details/summary disclosure instead.

Supported elements:
`p div span h1 h2 h3 h4 h5 h6 ul ol li dl dt dd blockquote pre code strong em b i u s small sub sup br hr figure figcaption table thead tbody tfoot tr th td caption details summary a img`.

Most elements accept `style`, `class`, `id`, `title`, `lang`, and `dir`. Links accept only `href`, `title`, and `style`. Images accept only `src`, `alt`, `width`, `height`, and `style`. Other attributes, including event handlers, are removed. Do not rely on classes or IDs for styling: the host theme's styles do not reach the content.

WordPress filters inline CSS. Prefer simple properties such as color, background-color, font-family, font-size, font-weight, line-height, padding, margin, border, border-radius, width, and max-width. Use system fonts and flexible widths; avoid animations, CSS variables, pseudo-selectors, media queries, fixed-position overlays, and background image URLs. Do not assume every CSS declaration survives: the saved preview is authoritative.

Use a logical heading sequence, readable contrast, descriptive link text, and alt text for meaningful images. Keep tables simple; attributes such as colspan, rowspan, and scope are not currently supported. Prefer stacked sections for narrow screens. Use only supplied or explicitly requested image URLs; images may load from external HTTP/HTTPS sources. Prefer HTTPS. Do not invent remote assets or add tracking.

The content runs inside an isolated frame with scripts disabled. Links cannot control the parent page or open popups; do not design navigation or a call to action that depends on those behaviors. A cast should be useful without leaving its frame. The parent selects a saved height using CSS container queries. It uses the actual placement width, not the browser viewport. Internal scrolling is disabled, including nested content scrollbars. Too-small heights clip content: do not hide clipping by claiming the page fits. No script measures the content automatically. Public placements include an Open full content link to a complete scrollable document as an accessibility fallback; still verify the embedded layout with enlarged text and expanded content.

## Required responsive height table

Use this exact Tailwind breakpoint subset. Supply all four integer heights, 100–10000 pixels each, alongside the HTML artifact:

| Key | Available content width | Height (px) |
| --- | --- | --- |
| base | below 640px | measured value |
| sm | 640px to below 768px | measured value |
| md | 768px to below 1024px | measured value |
| lg | 1024px and above | measured value |

These breakpoints change the parent frame height; they do not enable authored media queries or stylesheets. Use fluid, wrapping HTML with inline CSS. Never introduce a fixed-height scrolling panel, `overflow:auto`, or `overflow:scroll`; those declarations are removed. Only the host page should scroll.

Measure the maximum content height across each range, then round up with at least 24px of breathing room. Check at 320, 375, 480, 639, 640, 767, 768, 1023, 1024, and 1280px of content width, with images loaded and all disclosures expanded. Include borders/padding. Test long text and ensure there is no horizontal overflow. State the smallest tested width; below it, revise the design or increase the base height. Browser zoom reduces available CSS width and may select a different range. Extra height is preferable to inaccessible content.

If you cannot measure in a browser, label the supplied heights as estimates and require saved-preview verification; do not claim they prevent clipping. Whenever the HTML changes, regenerate the height table. Users paste the HTML and manually enter these four heights in the same editor before saving. Saved heights are revisioned with the content.

Do not use a fixed `height` override in the placement shortcode or block for the normal responsive workflow: it overrides all four ranges. Older browsers without container queries use the base height.

## Example

```html
<div style="max-width:680px;margin:0 auto;padding:24px;background-color:#f4f6fa;color:#172033;font-family:Arial,sans-serif;line-height:1.6;border-radius:12px">
  <h2 style="margin:0 0 12px;font-size:26px">Before your first visit</h2>
  <p style="margin:0 0 16px">A short checklist to help you arrive prepared.</p>
  <ul style="padding-left:24px;margin:0 0 20px">
    <li>Bring your booking confirmation.</li>
    <li>Arrive ten minutes before your appointment.</li>
    <li>Write down any questions you want to discuss.</li>
  </ul>
  <details style="padding:16px;background-color:#ffffff;border:1px solid #cbd3df;border-radius:8px">
    <summary style="font-weight:bold">What if I need to reschedule?</summary>
    <p style="margin:12px 0 0">Use the contact details in your booking confirmation.</p>
  </details>
</div>
```

Example starting heights (estimates; verify with the disclosure open): base 600px, sm 500px, md 450px, lg 400px. Replace the example copy with the user's actual content and measure new heights.

## Handoff

1. In WordPress, open Vibecasts → Add Vibecast as an administrator.
2. Add a title, paste the HTML into Reusable HTML, and enter the four supplied heights under Responsive heights.
3. Save a draft and inspect the saved preview, including each width range, narrow-screen readability, and expanded disclosures. Unsupported markup is removed when saved.
4. Publish, then insert the Vibecast block and select the published item by name, or use `[cr_vibecast id="123"]` with the actual ID.
5. Verify on the actual page and increase any height that clips content. The block automatically uses the saved heights; for an older fixed-height placement, choose Use saved responsive heights. Updating a published cast updates every placement; WordPress revisions retain saved HTML.

The skill is authoring guidance only. It does not publish content, grant permissions, or change the plugin's HTML rules.
