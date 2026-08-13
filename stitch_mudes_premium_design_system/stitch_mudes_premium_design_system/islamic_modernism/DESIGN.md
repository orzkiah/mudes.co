---
name: Islamic Modernism
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#404944'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#707974'
  outline-variant: '#bfc9c3'
  surface-tint: '#2b6954'
  primary: '#003527'
  on-primary: '#ffffff'
  primary-container: '#064e3b'
  on-primary-container: '#80bea6'
  inverse-primary: '#95d3ba'
  secondary: '#735c00'
  on-secondary: '#ffffff'
  secondary-container: '#fed65b'
  on-secondary-container: '#745c00'
  tertiary: '#4f1f19'
  on-tertiary: '#ffffff'
  tertiary-container: '#6b342d'
  on-tertiary-container: '#ea9e93'
  error: '#EF4444'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#b0f0d6'
  primary-fixed-dim: '#95d3ba'
  on-primary-fixed: '#002117'
  on-primary-fixed-variant: '#0b513d'
  secondary-fixed: '#ffe088'
  secondary-fixed-dim: '#e9c349'
  on-secondary-fixed: '#241a00'
  on-secondary-fixed-variant: '#574500'
  tertiary-fixed: '#ffdad5'
  tertiary-fixed-dim: '#ffb4a9'
  on-tertiary-fixed: '#380d08'
  on-tertiary-fixed-variant: '#6e372f'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
  emerald-900: '#064E3B'
  emerald-600: '#059669'
  gold-accent: '#D4AF37'
  gold-light: '#F9F1D7'
  surface-base: '#FFFFFF'
  surface-muted: '#F8FAFC'
  success: '#10B981'
  warning: '#F59E0B'
typography:
  display-lg:
    fontFamily: Hanken Grotesk
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Hanken Grotesk
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Hanken Grotesk
    fontSize: 30px
    fontWeight: '600'
    lineHeight: 38px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Hanken Grotesk
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.02em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  2xl: 48px
  3xl: 64px
  gutter: 24px
  container-max: 1280px
---

## Brand & Style

The design system is built on the pillars of **Spiritual Clarity, Modern SaaS Utility, and Community Warmth.** It targets a digitally-native Muslim audience that appreciates the "Quiet Luxury" found in high-end productivity tools like Notion and Linear, but seeks a sense of identity through subtle cultural cues.

The style is **Premium Minimalism**. It avoids traditional patterns in favor of "Islamic Elegance" expressed through a lush Emerald and Gold palette, generous whitespace (Tasbih-like rhythm), and a sophisticated use of depth. The interface feels light and breathable, prioritizing content and community connection over decorative clutter.

## Colors

The palette is anchored by **Deep Emerald**, representing life and growth, and **Refined Gold**, representing value and tradition. 

- **Primary:** The Emerald is used for core brand moments, primary actions, and key navigation states. It should feel rich and grounded.
- **Accent:** Gold is used sparingly for highlights, premium badges, and subtle border accents. It is never the dominant color, acting instead as a "light" within the interface.
- **Backgrounds:** The primary background is Pure White (`#FFFFFF`). Depth is created using `surface-muted` (`#F8FAFC`) to separate content blocks without the need for heavy borders.
- **Functional:** Standard semantic colors for success, warning, and error are adjusted to harmoniously coexist with the emerald primary.

## Typography

This design system utilizes a dual-font approach. **Hanken Grotesk** provides a sharp, contemporary "tech" feel for headlines, while **Inter** ensures maximum legibility and functional neutrality for body copy and UI labels.

Typography follows a strict hierarchical scale. Headlines use tighter letter-spacing and heavier weights to command attention, while body text uses a generous line-height to maintain a "breathable" feel. For Arabic script integration (if required), use **Noto Sans Arabic** with a 1.2x scale factor to match the visual weight of Inter.

## Layout & Spacing

The layout is based on a **12-column fluid grid** for web, transitioning to a **4-column grid** for mobile. 

- **The Grid:** Use 24px margins on mobile and up to 64px margins on desktop. Gutters are fixed at 24px to ensure whitespace is never cramped.
- **Rhythm:** Spacing follows a geometric 4px baseline. Components should generally use 16px (`md`) or 24px (`lg`) internal padding to reinforce the premium, "roomy" aesthetic.
- **Containers:** Content is centered in a max-width container of 1280px. For dashboard views, a fixed-width left sidebar (240px) is preferred, with a fluid content area.

## Elevation & Depth

Depth is achieved through **Tonal Layering** and **Ambient Shadows**, inspired by the Notion "borderless" look.

- **Level 0 (Base):** White (`#FFFFFF`) or Light Gray (`#F8FAFC`).
- **Level 1 (Cards/Surface):** White surface with a 1px border of `#E2E8F0` (or purely shadow-defined).
- **Shadows:** Use multi-layered, low-opacity shadows. A standard "Premium Shadow" consists of:
    - Layer 1: `0px 1px 2px rgba(0, 0, 0, 0.05)`
    - Layer 2: `0px 10px 15px -3px rgba(0, 0, 0, 0.03)`
- **Interaction:** On hover, cards should lift slightly (increasing shadow spread) rather than changing border color.

## Shapes

The shape language is characterized by **Generous Radii**. This softens the "tech" feel and makes the platform feel more welcoming and community-focused.

- **Standard UI (Inputs/Buttons):** Use `rounded-lg` (8px).
- **Cards & Sections:** Use `rounded-xl` (16px) or `rounded-2xl` (24px).
- **Selection Indicators:** Use pill shapes (full round) for tags and active state indicators.

## Components

- **Buttons:** Primary buttons are Solid Emerald with White text. Secondary buttons use a Subtle Gold background (`#F9F1D7`) with Gold text. All buttons feature 8px corners and a 0.2s transition.
- **Inputs & Textarea:** Use a light gray background (`#F1F5F9`) with no border until focused. On focus, a 1px Emerald border and a soft Emerald glow (shadow) appear.
- **Cards:** White background, 16px or 24px radius, with the "Premium Shadow" defined in the Elevation section. No borders unless the card sits on a white background.
- **Badges:** Use a "Soft Palette" approach—low-saturation background with high-saturation text (e.g., Light Gold background with Dark Gold text).
- **Tabs:** Use the "Underline" style with an Emerald accent for the active state, or the "Segmented" style with a White pill sliding over a gray track.
- **Modals/Drawers:** Feature a heavy backdrop blur (12px) to focus the user’s attention, with 24px rounded corners on the container.
- **Charts:** Use a palette of Emerald, Gold, and Teal. Maintain thin stroke widths (2px) and hide grid lines where possible for a cleaner "Stripe-like" look.
- **Empty States:** Use simplified, monochromatic Emerald illustrations with centered "Hanken Grotesk" headlines and clear call-to-action buttons.