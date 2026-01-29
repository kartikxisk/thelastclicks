# Project Instructions

## Next.js Guidelines

- Never use `"use client"` directive in `page.tsx` or `layout.tsx` files. These should remain Server Components. Extract client-side logic into separate components instead.
- Always use Next.js `Link` component from `next/link` instead of HTML `<a>` tags for internal navigation. Use `<a>` tags only for external links (mailto:, tel:, external URLs).
- Always use Next.js `Image` component from `next/image` instead of HTML `<img>` tags for optimized image loading.

## TypeScript Guidelines

- Always mark component props as `readonly` to prevent accidental mutations.
