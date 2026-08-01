# SEO Schema Engine

Recipe and article pages emit JSON-LD only from their visible database fields. Recipes use `Recipe` with `HowToStep` instructions; articles use `Article`; both include a `BreadcrumbList`. Image schema is emitted only when public media has an allowed public URL. FAQ schema appears only when the stored post has real question/answer blocks.

The platform does not emit ratings, reviews, author expertise, video, medical claims or FAQ data unless those data are actually present and editor-approved.
