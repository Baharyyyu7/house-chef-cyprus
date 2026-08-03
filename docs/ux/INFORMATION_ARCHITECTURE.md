# House Chef Cyprus Information Architecture

## Public hierarchy

```text
Home
├─ Discover recipes
│  ├─ Search results
│  ├─ Cuisines
│  ├─ Categories / meal moments
│  ├─ Cooking methods
│  ├─ Ingredients
│  ├─ Seasonal collections
│  └─ Dietary and allergen-aware discovery
├─ Guides
│  ├─ Cooking techniques
│  ├─ Ingredient notes
│  ├─ Seasonal planning
│  └─ Safety explainers
├─ How it works / trust
├─ Legal Center / contact
└─ Account (authenticated)
   ├─ Favourites and collections
   ├─ Pantry and matching
   ├─ Meal plans and shopping lists
   ├─ Cooking history
   └─ Settings, privacy, and preferences
```

## Admin hierarchy

```text
Operations
├─ Content: recipes, blog, quality, fact check, editorial review
├─ Media and SEO
├─ Customer: users and contact requests
├─ Product: personalization and analytics aggregates
└─ System: queue, scheduler, backup, health, failed jobs, audit
```

## Rules

- The header exposes only the highest-frequency paths: discover, pantry/match, guides, account.
- Deep taxonomies live on their own browsable pages or contextual links, not in an overloaded permanent mega-menu.
- A mega-menu is deferred until real taxonomy volume and usability testing justify it.
- Every route has a stated audience, canonical policy, accessibility label, and empty/error state.
