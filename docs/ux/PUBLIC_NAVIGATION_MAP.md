# Public Navigation Map

| Audience | Primary navigation | Contextual navigation | Conversion without dark patterns |
|---|---|---|---|
| Guest | Tarifler, Ne Pişirebilirim?, Blog, Hakkımızda | Cuisine/category/ingredient links, related content | Save/cook actions explain sign-in benefit only when needed |
| Signed-in member | Tarifler, Ne Pişirebilirim?, Dolabım, Favoriler, account menu | Collections, meal plans, shopping lists, cooking session | Return to intended task after login |
| Search visitor | Canonical recipe/blog/category/guide page | Breadcrumbs, relevant recipe-guide links | Clear next useful action; no deceptive subscribe wall |

## Header rules

- Desktop: wordmark, four-to-five task-oriented links, search access, theme, account/menu.
- Tablet/mobile: accessible drawer, current-route state, focus trap, escape/close control, no horizontal overflow.
- Screen readers: landmark navigation, visible skip link, `aria-current` on active route.
- Do not place admin or unapproved feature navigation in public menus.
