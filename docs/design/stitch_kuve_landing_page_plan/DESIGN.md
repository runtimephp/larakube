# Design System Specification: The Technical Couture

## 1. Overview & Creative North Star
**Creative North Star: "The Digital Architect"**

This design system is engineered for the high-performance developer. It moves beyond the generic "SaaS dashboard" by embracing a high-end, editorial aesthetic that mirrors the precision of clean code. We reject the "template" look characterized by heavy borders and flat grey boxes. Instead, we lean into **The Digital Architect**—a philosophy where depth is created through light and layering, and hierarchy is established through intentional asymmetry and bold typographic scales. 

The experience should feel like a premium IDE: dark, focused, and expensive. We achieve this by blending "Linear-style" structural discipline with "Vercel-style" atmospheric depth.

---

## 2. Colors & Atmospheric Depth
Our palette is rooted in the "Void"—a deep `#0c1324` that provides the ultimate canvas for our vibrant accents.

### The Palette
- **The Core (Neutral):** `surface` (#0c1324) and `surface_container_lowest` (#070d1f) form the foundation. 
- **The Pulse (Primary):** `primary` (#d0bcff) and `primary_container` (#a078ff). This is a vibrant, neon-adjacent purple that should be used sparingly to draw the eye to high-value actions.
- **The Depth (Secondary):** `secondary` (#c3c0ff) and `secondary_container` (#3626ce). These deep indigos provide a sophisticated "night-sky" feel for secondary elements.

### Rules of Engagement
*   **The "No-Line" Rule:** We do not use `1px solid` borders to define sections. Use background shifts instead. A `surface_container_low` section sitting on a `surface` background provides all the separation needed.
*   **Surface Hierarchy:** Always "nest" inward. If your page background is `surface`, your main content area should be `surface_container_low`, and cards within that area should be `surface_container`. This creates a natural "staircase" of light toward the user.
*   **The Glass & Gradient Rule:** For floating headers or sidebars, use Glassmorphism. Set the background to `surface` at 70% opacity with a `20px` backdrop blur. For primary CTAs, use a linear gradient from `primary` to `primary_container` at a 135-degree angle to give the button "soul."

---

## 3. Typography: Editorial Precision
We use a dual-font system to balance technical clarity with high-end personality.

*   **The Headline (Manrope):** Use Manrope for `display` and `headline` scales. Its geometric nature feels engineered and authoritative. 
    *   *Strategy:* Use `display-lg` for hero stats or section headers to create "intentional asymmetry"—large type against small, precise data points.
*   **The Workhorse (Inter):** Use Inter for `title`, `body`, and `label` scales. Inter is the industry standard for readability in dense, technical data.
*   **Hierarchy via Contrast:** Never use two fonts of the same weight next to each other. Pair a `headline-sm` (Bold) with `body-md` (Regular) to ensure the eye knows exactly where to land.

---

## 4. Elevation & Depth
In this system, elevation is a property of light, not physics.

*   **Tonal Layering:** Avoid shadows for static UI elements. Use the `surface-container` tiers. 
    *   *Lowest:* Backgrounds.
    *   *High/Highest:* Interactive cards or popovers.
*   **Ambient Shadows:** For "floating" elements like modals, use a shadow with a `48px` blur and `6%` opacity. The shadow color must be tinted with the `primary` token (#d0bcff) rather than black. This mimics the glow of a high-end monitor.
*   **The Ghost Border:** If high-density data requires containment, use a "Ghost Border." Use the `outline_variant` (#494454) at **15% opacity**. It should be felt, not seen.
*   **Minimal Roundness:** Stick strictly to the `md` (0.375rem / 6px) and `lg` (0.5rem / 8px) tokens. This keeps the aesthetic sharp and technical.

---

## 5. Components

### Buttons
*   **Primary:** Gradient of `primary` to `primary_container`. No border. White text (`on_primary`).
*   **Secondary:** Ghost style. Transparent background with a `Ghost Border`. Text is `primary_fixed_dim`.
*   **Tertiary:** Text-only. Use `label-md` with 1.5px letter spacing for a "pro-tool" feel.

### Cards & Data Lists
*   **The Card:** No borders. Background: `surface_container_low`. On hover, shift to `surface_container_high`.
*   **Lists:** Forbid divider lines. Use `spacing-4` (0.9rem) between items. Use a `surface_variant` background hover state to indicate selection. 

### Input Fields
*   **Style:** Background is `surface_container_lowest`. 
*   **Focus State:** Instead of a thick border, use a 1px `primary` border and a subtle `primary` outer glow (4px blur, 10% opacity).

### Status Chips
*   **Technical Feel:** Use `label-sm` in all-caps. Backgrounds should be 10% opacity of the status color (e.g., `error` at 10% for a "Critical" chip), ensuring the background color "bleeds" into the chip.

---

## 6. Do’s and Don’ts

### Do
*   **Do** use `display-lg` typography for single, impactful numbers in the developer portal (e.g., "99.9% Uptime").
*   **Do** use `spacing-16` or `spacing-24` between major sections. High-end design requires "breathing room."
*   **Do** use `primary_fixed_dim` for icons to give them a subtle, muted glow.

### Don't
*   **Don't** use 100% opaque borders. They clutter the UI and feel "cheap."
*   **Don't** use standard "Drop Shadows." Use tonal shifts or ambient glows.
*   **Don't** use pure white (#FFFFFF) for body text. Use `on_surface_variant` (#cbc3d7) to reduce eye strain in dark mode.
*   **Don't** use rounded corners larger than `xl` (0.75rem). We are building a portal for engineers, not a social app. Keep it architectural.