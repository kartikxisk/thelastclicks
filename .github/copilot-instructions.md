# Project Instructions

## Next.js Guidelines

- Never use `"use client"` directive in `page.tsx` or `layout.tsx` files. These should remain Server Components. Extract client-side logic into separate components instead.
- Always use Next.js `Link` component from `next/link` instead of HTML `<a>` tags for internal navigation. Use `<a>` tags only for external links (mailto:, tel:, external URLs).
- Always use Next.js `Image` component from `next/image` instead of HTML `<img>` tags for optimized image loading.

## TypeScript Guidelines

- Always mark component props as `readonly` to prevent accidental mutations.

## Icons Guidelines

- **NEVER use emojis** for icons in the codebase
- Always use **react-icons** library for all icon needs
- Import icons from appropriate icon packs (e.g., `react-icons/bi`, `react-icons/bs`, `react-icons/hi`, `react-icons/io5`, `react-icons/gi`, `react-icons/md`, `react-icons/tb`)
- Store icon names as strings in constants, then map them to actual icon components in UI components
- Common icon imports:
  ```typescript
  import { BiCameraMovie, BiRefresh, BiLock } from "react-icons/bi";
  import { BsLightning, BsBuilding, BsBox } from "react-icons/bs";
  import { HiSparkles, HiOutlineStar } from "react-icons/hi";
  import { IoColorPaletteOutline, IoDiamondOutline } from "react-icons/io5";
  import { MdCelebration, MdOutlineMovie } from "react-icons/md";
  ```

## Design & Styling Guidelines

### Color Rules

- **NEVER use pure black (`#000000`, `black`) or pure white (`#ffffff`, `white`)** in any component
- **NEVER use hardcoded hex colors** (e.g., `#280905`, `#740A03`, `#C3110C`, `#E6501B`)
- Always use Tailwind CSS utility classes with the configured brand colors

### Brand Color Palette (Use Tailwind Classes)

```
Primary Colors (use these Tailwind classes):
- Deep Maroon: bg-brand-deep, text-brand-deep, border-brand-deep
- Dark Red: bg-brand-dark, text-brand-dark, border-brand-dark
- Vibrant Red: bg-brand-primary, text-brand-primary, border-brand-primary
- Orange Red: bg-brand-accent, text-brand-accent, border-brand-accent

Gradients:
- from-brand-deep via-zinc-950 to-brand-dark
- from-brand-primary to-brand-accent

Neutral Colors:
- Light: zinc-50 (for light text/backgrounds where needed)
- Dark: zinc-950 (instead of black)
- Use zinc color scale (zinc-400, zinc-500, etc.) for all grayscale needs
```

### Color Usage Examples

```tsx
// ✅ CORRECT - Use Tailwind brand classes
<div className="bg-brand-deep text-brand-accent" />
<div className="bg-linear-to-r from-brand-primary to-brand-accent" />
<div className="border-brand-dark/30" />

// ❌ WRONG - Never use hardcoded hex values
<div className="bg-[#280905] text-[#E6501B]" />
<div className="from-[#C3110C] to-[#E6501B]" />
```

### Typography

- **Display/Heading Font**: Playwrite New Zealand Basic (`font-display` class)
- **Body Font**: Inter (default)
- Use `font-display` class for section headings and titles
- Use italic styling on display headings for elegance

### Component Architecture

- **Always create separate components** for reusable UI elements
- Extract sections into their own component files in `/components`
- Keep page files clean by importing components

### Background Style

- Use **dark backgrounds** as the default theme
- Prefer `gray-950` or brand dark colors over black
- Use gradients with brand colors for visual interest

### Animation Libraries

- Use **React Bits** for micro-interactions and utilities
- Use **Aceternity UI** for advanced animations and effects
- Implement smooth transitions and hover effects

### Media Considerations

- This is a photography/videography site - design for visual content
- Ensure components can accommodate photos and videos prominently
- Use proper aspect ratios for media containers
