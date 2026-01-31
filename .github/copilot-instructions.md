# Project Instructions

## Next.js Guidelines

- Never use `"use client"` directive in `page.tsx` or `layout.tsx` files. These should remain Server Components. Extract client-side logic into separate components instead.
- Always use Next.js `Link` component from `next/link` instead of HTML `<a>` tags for internal navigation. Use `<a>` tags only for external links (mailto:, tel:, external URLs).
- Always use Next.js `Image` component from `next/image` instead of HTML `<img>` tags for optimized image loading.

## TypeScript Guidelines

- Always mark component props as `readonly` to prevent accidental mutations.

## Design & Styling Guidelines

### Color Rules
- **NEVER use pure black (`#000000`, `black`) or pure white (`#ffffff`, `white`)** in any component
- Always use the brand color palette or gray scale alternatives

### Brand Color Palette
```
Primary Colors:
- Deep Maroon: #280905
- Dark Red: #740A03
- Vibrant Red: #C3110C
- Orange Red: #E6501B

Neutral Colors:
- Light: zinc-50 (for light text/backgrounds where needed)
- Dark: zinc-950 (instead of black)
- Use zinc color scale (zinc-400, zinc-500, etc.) for all grayscale needs
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
