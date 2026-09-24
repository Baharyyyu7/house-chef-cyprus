<?php

declare(strict_types=1);

/*
 * English editions of the editorial blog catalogue, keyed by the Turkish post
 * slug (database/seeders/ProductionBlogCatalogueSeeder.php). Bodies use blank
 * lines between paragraphs and "## " for sub-headings.
 */
return [
    'kibris-mutfagina-baslangic-rehberi' => [
        'slug' => 'beginners-guide-to-cypriot-food',
        'title' => 'A Beginner’s Guide to Cypriot Food',
        'excerpt' => 'Get to know the island’s home cooking through its ingredients, its table and its cooking rhythm — a short, practical introduction.',
        'category' => 'Cypriot cuisine',
        'keywords' => ['cypriot food', 'cyprus cuisine', 'traditional cypriot dishes'],
        'meta_title' => 'Cypriot Food: A Beginner’s Guide to Cyprus Cuisine',
        'meta_description' => 'What is Cypriot food? Key ingredients, typical dishes and how to start cooking the island’s home food, from oven chicken to halloumi breakfasts.',
        'body' => <<<'TXT'
Cypriot cooking brings together the practicality of island life and a habit of sharing the table. Olive oil, lemon, tomatoes, fresh and dried herbs and cheese set the direction of most recipes, whether you are eating in a Greek Cypriot village or a Turkish Cypriot home.

## Start with ingredients, not rules

Rather than searching for a single “authentic” formula, think about what is in season and what will sit next to the dish on the table. Oven-baked chicken with potatoes, a halloumi breakfast pan or lemony roast potatoes are all interpretations of the same handful of ingredients, and they change from house to house.

## Dishes to try first

Good first recipes are the ones that teach you the island’s flavour balance: lemon and oregano with roasted meat and potatoes, halloumi with mint and tomato, pulses and grains cooked with olive oil, and simple salads dressed at the table. Grilled sheftalia, lentil dishes and syrupy desserts come next.

## How to use this guide

Read this guide alongside the recipes. The aim is not to reduce a cuisine to one correct version, but to give you a balanced starting point you can cook confidently at home — wherever your home is.
TXT,
        'faq' => [
            ['What is the national dish of Cyprus?', 'There is no single official dish, but halloumi cheese, souvla and sheftalia (grilled meats), kleftiko (slow-baked lamb) and meze spreads are among the best known foods of the island.'],
            ['Is Cypriot food spicy?', 'Generally not. Cypriot cooking relies on lemon, herbs such as oregano and mint, cinnamon and black pepper rather than chilli heat.'],
        ],
    ],
    'kibris-sofralarinda-temel-malzemeler' => [
        'slug' => 'essential-ingredients-of-cypriot-cooking',
        'title' => 'Essential Ingredients of the Cypriot Kitchen',
        'excerpt' => 'A closer look at how lemon, olive oil, tomatoes, herbs and cheese work together in everyday Cypriot cooking.',
        'category' => 'Cypriot cuisine',
        'keywords' => ['cypriot ingredients', 'halloumi', 'cyprus pantry'],
        'meta_title' => 'Essential Ingredients of Cypriot Cooking (Pantry Guide)',
        'meta_description' => 'Lemon, olive oil, tomatoes, herbs and halloumi: the pantry behind Cypriot home cooking, and how to season with salty cheeses.',
        'body' => <<<'TXT'
You do not need a long shopping list to cook a good Cyprus-inspired meal. Lemon, olive oil, tomatoes, onion, garlic and a seasonal herb create a common base for both hot and cold dishes.

## The core pantry

Keep good extra virgin olive oil for dressing and a more everyday one for cooking. Lemons are used generously, both juice and zest. Dried oregano and dried mint are the two herbs you will reach for most, with fresh parsley, dill and coriander when they are in season. Add cinnamon and black pepper for meat dishes, and tins of chickpeas and lentils for quick meals.

## Cooking with salty cheeses

When you use cheese, adjust the salt in the rest of the recipe. With saltier cheeses such as halloumi or feta, leave any extra salt until the very end and taste first — it gives you much more control over the final result.

## Building from here

Once your cupboard is stocked with these basics, it becomes easy to move between recipes, from a breakfast pan to a tray bake, without a special shop.
TXT,
        'faq' => [
            ['What can I use instead of halloumi?', 'Look for a firm grilling cheese such as Greek kefalograviera or a paneer-style cheese. They will not squeak like halloumi but they hold their shape in a hot pan.'],
        ],
    ],
    'ev-olculeri-rehberi' => [
        'slug' => 'turkish-cup-and-spoon-measures-explained',
        'title' => 'Turkish Cup and Spoon Measures Explained (Su Bardağı, Çay Bardağı)',
        'excerpt' => 'How to read household measures in Turkish and Cypriot recipes, with approximate millilitre equivalents and when to use grams instead.',
        'category' => 'Kitchen measures',
        'keywords' => ['su bardagi in ml', 'turkish cup measurement', 'cay bardagi', 'turkish recipe measurements'],
        'meta_title' => 'Turkish Measures: Su Bardağı, Çay Bardağı & Spoons in ml',
        'meta_description' => 'What is a su bardağı or çay bardağı in ml? A practical guide to Turkish household measures, with conversions and when to trust grams.',
        'body' => <<<'TXT'
Household measures make a recipe easy to read at a glance: a water glass, a spoon or “one onion” help you make quick decisions in the kitchen. But a glass of flour and a glass of rice do not weigh the same, so volume and weight should never be treated as interchangeable.

## Approximate equivalents

These are the common household measures in Turkish and Turkish Cypriot recipes. Glass sizes vary between homes, so treat them as guides:

- Su bardağı (water glass): about 200 ml
- Çay bardağı (tea glass): about 100 ml
- Kahve fincanı (coffee cup): about 70–80 ml
- Yemek kaşığı (tablespoon): about 15 ml
- Tatlı kaşığı (dessert spoon): about 7–10 ml
- Çay kaşığı (teaspoon): about 3–5 ml

A Turkish su bardağı is smaller than a US cup (240 ml), so recipes converted directly from cups can end up too wet.

## How House Chef writes measures

In our Turkish recipes the household measure is the main instruction, and the gram or millilitre figure is shown as secondary context only when the conversion for that specific ingredient is reliable. In our English recipes we lead with grams and millilitres, which are easier to follow outside Turkey and Cyprus.

## Where precision matters

In doughs and with salt, yeast and raising agents, small differences make a big change. For those recipes, follow the recipe’s own note instead of rounding measures at random.
TXT,
        'faq' => [
            ['How many ml is a Turkish su bardağı?', 'About 200 ml, although household glasses vary between roughly 180 and 250 ml.'],
            ['How many ml is a çay bardağı?', 'A Turkish tea glass holds about 100 ml when filled to the rim.'],
        ],
    ],
    'gram-yerine-ev-olcusu-kullanmak' => [
        'slug' => 'cooking-with-household-measures-instead-of-grams',
        'title' => 'Cooking with Household Measures Instead of Grams',
        'excerpt' => 'Tips for using spoons, glasses and “pieces” for convenience while handling their uncertainty honestly.',
        'category' => 'Kitchen measures',
        'keywords' => ['cups vs grams', 'household measures', 'recipe conversions'],
        'meta_title' => 'Cooking with Household Measures Instead of Grams',
        'meta_description' => 'When is it fine to cook with spoons and cups instead of grams? How ingredient density affects conversions, and how to scale eggs sensibly.',
        'body' => <<<'TXT'
Household measures are a great help when you know how dense the ingredient is. Spoons for oil, a glass for rice and “cloves” for garlic describe the ingredient in the form you actually see on the worktop.

## Why one cup is not one weight

Converting “one glass” to a single gram value for every dry ingredient is misleading. A glass of flour weighs far less than a glass of sugar or rice. That is why, when our conversion confidence for an ingredient is low, we do not show a gram figure as if it were exact.

## Practical rules

- Use weight for baking, doughs and anything with raising agents.
- Use household measures for forgiving dishes such as soups, stews, salads and pilafs.
- Level off spoons and glasses rather than heaping them, unless the recipe says otherwise.

## Scaling eggs and spices

When you change the number of servings, think about eggs, spices and raising agents in natural kitchen terms. Instead of “half an egg”, beat an egg and use part of it — it is clearer and more accurate.
TXT,
    ],
    'kuru-bakliyat-nasil-saklanir' => [
        'slug' => 'how-to-store-dried-beans-lentils-and-chickpeas',
        'title' => 'How to Store Dried Beans, Lentils and Chickpeas',
        'excerpt' => 'Basic notes for keeping chickpeas, lentils and beans dry, organised and easy to track.',
        'category' => 'Storage guides',
        'keywords' => ['how to store dried beans', 'storing lentils', 'pulse storage'],
        'meta_title' => 'How to Store Dried Beans, Lentils & Chickpeas',
        'meta_description' => 'Keep dried pulses fresh: airtight containers, a cool dry cupboard, first-in-first-out labelling, and why cooked beans need different care.',
        'body' => <<<'TXT'
Keeping dried pulses in closed containers in a cool, dry cupboard reduces moisture, odours and clutter. Once a pack is opened, writing the product name and the date you opened it on the container makes stock easy to track.

## First in, first out

Rather than tipping old and new packs into the same jar, use up the older product first. If anything looks or smells doubtful, it is safer to set it aside than to try to rescue it.

## Why age matters

Very old beans and chickpeas can take much longer to soften, however long you soak them. If a batch refuses to cook, it is usually age rather than your method.

## Cooked pulses are different

Cooked beans do not behave like dried ones. For cooling and storage times, follow the note in the recipe and trusted food-safety guidance — generally, refrigerate within two hours and use within a few days.
TXT,
    ],
    'taze-sebzeleri-buzdolabinda-saklamak' => [
        'slug' => 'how-to-keep-fresh-vegetables-longer-in-the-fridge',
        'title' => 'How to Keep Fresh Vegetables Longer in the Fridge',
        'excerpt' => 'Think about washing, drying and where things go according to what each vegetable needs.',
        'category' => 'Storage guides',
        'keywords' => ['store vegetables in fridge', 'keep vegetables fresh', 'reduce food waste'],
        'meta_title' => 'How to Keep Fresh Vegetables Longer in the Fridge',
        'meta_description' => 'Simple fridge habits that help vegetables last: wash only what you need, avoid trapped moisture, leave room for air and store cut veg covered.',
        'body' => <<<'TXT'
Not every vegetable likes the same storage conditions, but avoiding trapped moisture and bruising is a good starting point for most. Washing only what you are about to use helps the rest stay drier and last longer.

## Give them air

Leave room for air to circulate instead of packing the crisper drawer tightly. Keep cut vegetables in a closed container and check them regularly.

## Some things prefer the counter

Tomatoes keep their flavour better at room temperature until they are fully ripe, and whole onions, garlic and potatoes prefer a cool, dark, airy cupboard rather than the fridge.

## A note on quality and safety

This guide is about quality. If you suspect a vegetable has spoiled, do not use its smell or appearance as a test for whether it can be “saved”.
TXT,
    ],
    'sogan-ve-sarimsak-nasil-saklanmali' => [
        'slug' => 'how-to-store-onions-and-garlic',
        'title' => 'How to Store Onions and Garlic',
        'excerpt' => 'Keep onions and garlic within reach while reducing the risk of damp and sprouting.',
        'category' => 'Storage guides',
        'keywords' => ['how to store onions', 'how to store garlic', 'stop onions sprouting'],
        'meta_title' => 'How to Store Onions and Garlic (Stop Sprouting)',
        'meta_description' => 'Store whole onions and garlic in a dry, airy, dark spot; keep peeled or chopped ones in the fridge. Simple habits to prevent sprouting and waste.',
        'body' => <<<'TXT'
Onions and garlic stay in better condition in a dry, airy corner. Choose solutions that limit light and let air through, rather than fully sealed containers that trap moisture.

## Check regularly

Separate any pieces that have softened, show mould or smell unusual. Peeled or chopped onion and garlic cannot be stored like whole ones: they need a clean container and the fridge.

## Keep them separate from potatoes

Onions and potatoes stored together tend to spoil faster, so give them separate baskets if you can.

## A small basket helps

Using a small basket in your kitchen makes it easy to see what you have and stops you buying the same thing twice.
TXT,
    ],
    'corba-kivami-nasil-ayarlanir' => [
        'slug' => 'how-to-adjust-the-thickness-of-soup',
        'title' => 'How to Get the Right Soup Consistency',
        'excerpt' => 'Balance a soup with small additions and resting time rather than one big correction.',
        'category' => 'Cooking techniques',
        'keywords' => ['how to thicken soup', 'soup too thick', 'soup consistency'],
        'meta_title' => 'How to Thicken or Thin Soup (Get the Right Consistency)',
        'meta_description' => 'Soup too thick or too thin? Add hot liquid in small amounts, mash some of the vegetables, and season only once the texture has settled.',
        'body' => <<<'TXT'
A soup’s consistency is not settled in its first five minutes on the heat. Ingredients release starch, vegetables soften and liquid evaporates, so adding water or hot stock a little at a time gives you much more control.

## If it is too thick

Add hot liquid in small amounts and let the soup return to a simmer after each addition. Lentil and bean soups keep thickening as they stand, so leave them slightly looser than you want when serving.

## If it is too thin

Blend or mash part of the soup, or simmer it a little longer uncovered. Both usually work without any extra flour.

## Season last

Consider finishing touches such as salt, lemon and yoghurt after the consistency has settled. Reducing a soup concentrates its saltiness.
TXT,
    ],
    'pilavin-tane-tane-olmasi' => [
        'slug' => 'how-to-make-fluffy-rice-pilaf',
        'title' => 'How to Make Fluffy Rice Pilaf with Separate Grains',
        'excerpt' => 'Rinsing, resting and not lifting the lid can matter more than any single ingredient.',
        'category' => 'Cooking techniques',
        'keywords' => ['fluffy rice pilaf', 'turkish pilaf technique', 'how to cook rice pilaf'],
        'meta_title' => 'How to Make Fluffy Rice Pilaf with Separate Grains',
        'meta_description' => 'The Turkish technique for fluffy pilaf: rinse off surface starch, toast the grains in fat, keep the lid on and rest the pilaf under a cloth.',
        'body' => <<<'TXT'
The key to a pilaf with separate grains is not just the water ratio. Gently removing excess surface starch, briefly toasting the grains in fat without burning it, and not opening the lid unnecessarily during cooking all work together.

## The basic method

Rinse or soak the rice until the water runs clear. Toast it in butter or oil until the grains look glassy, add hot liquid, bring to the boil, then cover and cook on the lowest heat until the liquid is absorbed.

## Don’t skip the rest

Once the liquid has been absorbed, take the pan off the heat and let the pilaf rest, ideally with a clean tea towel under the lid. This lets the steam even out between the grains. Stirring at this stage breaks the grains.

## Take notes

The type of rice and the width of your pan change the result. Making small notes on your first attempt makes the next one far more predictable.
TXT,
    ],
    'makarna-sosu-secimi' => [
        'slug' => 'how-to-choose-the-right-pasta-sauce',
        'title' => 'How to Choose the Right Sauce for Your Pasta',
        'excerpt' => 'Think about how thick the sauce is, the shape of the pasta and when you will serve it.',
        'category' => 'Cooking techniques',
        'keywords' => ['pasta sauce pairing', 'pasta shapes and sauces', 'pasta water'],
        'meta_title' => 'How to Match Pasta Shapes and Sauces',
        'meta_description' => 'Which sauce suits which pasta? Match light oil and lemon sauces to long pasta, chunky sauces to shapes, and use pasta water to bind.',
        'body' => <<<'TXT'
When choosing a sauce, the first question should not be “which sauce is best?” but “which sauce will coat this pasta?”. Thin, long pasta works with light oil- or lemon-based sauces, while twisted and hollow shapes suit chunkier sauces that can get caught inside.

## Use the pasta water

Combining the pasta with the sauce before it dries out makes it easier to use the cooking water you set aside. That starchy water helps bind the sauce; add it in small splashes rather than all at once.

## Think about timing

If serving will be delayed, leave the sauce slightly looser. Pasta keeps absorbing liquid as it stands.
TXT,
    ],
    'firin-yemeklerinde-sicaklik-ve-sure' => [
        'slug' => 'oven-temperatures-and-cooking-times',
        'title' => 'Getting Oven Temperatures and Cooking Times Right',
        'excerpt' => 'Read a recipe’s timing alongside what you see and a safe internal temperature, not as an absolute number.',
        'category' => 'Cooking techniques',
        'keywords' => ['oven temperature guide', 'safe internal temperature chicken', 'fan vs conventional oven'],
        'meta_title' => 'Oven Temperatures & Cooking Times: A Practical Guide',
        'meta_description' => 'Why recipe times are only a guide, how to use a thermometer for chicken and meat, fan vs conventional ovens, and why crowded trays don’t brown.',
        'body' => <<<'TXT'
The temperature in an oven recipe only makes sense together with the material of your dish, the real heat of your oven and the starting temperature of the food. Rather than treating the number of minutes as a guarantee, check colour, texture and internal temperature together.

## Use a thermometer for meat

For recipes with meat or chicken, a reliable kitchen thermometer lets you measure instead of guessing. Chicken is safe at 74°C in the thickest part. For vegetables, browned edges and a knife sliding in easily are more useful signs.

## Fan or conventional?

Fan ovens cook faster. As a rule of thumb, reduce the temperature by about 20°C when a recipe is written for a conventional oven.

## Don’t overcrowd

An overfilled tray creates steam and reduces browning. Use two trays if necessary, or spread the food in a single layer.
TXT,
        'faq' => [
            ['What temperature should chicken be cooked to?', 'Cook chicken until the thickest part reaches at least 74°C (165°F) on a clean thermometer.'],
        ],
    ],
    'hamur-islerinde-sik-yapilan-hatalar' => [
        'slug' => 'common-mistakes-with-pastry-and-dough',
        'title' => 'Common Mistakes with Pastry and Dough',
        'excerpt' => 'Resting time, liquid balance and oven heat are three steps that work together.',
        'category' => 'Cooking techniques',
        'keywords' => ['dough mistakes', 'borek tips', 'baking troubleshooting'],
        'meta_title' => 'Common Pastry & Dough Mistakes (and How to Fix Them)',
        'meta_description' => 'Heavy börek, tough gözleme or flat bread? Why adding more flour rarely helps, why resting matters and how to learn your oven.',
        'body' => <<<'TXT'
With pastry and doughs, treating the amount of flour as the only fix usually makes the result heavier. Consider the texture of the dough, how absorbent your flour is and how long the dough has rested, all together.

## Rest the dough

Resting relaxes gluten, so thin doughs such as gözleme and lahmacun roll out without shrinking back. If a dough keeps springing back, cover it and wait ten minutes.

## Know your oven

Putting a cold tray into a very hot oven, or opening the oven door too early, can affect how things rise. Repeating the same recipe with small changes is the best way to learn how your oven behaves.

## Follow the order

Instead of increasing salt, yeast or baking powder at random, follow the recipe’s sequence. Raising agents in particular do not always scale in a straight line when you make a smaller batch.
TXT,
    ],
    'dolaptaki-malzemelerle-yemek-planlama' => [
        'slug' => 'meal-planning-with-what-you-already-have',
        'title' => 'Meal Planning with What’s Already in Your Kitchen',
        'excerpt' => 'Choose the ingredient closest to spoiling first, then shared preparation, and only then the recipe.',
        'category' => 'Kitchen organisation',
        'keywords' => ['meal planning', 'cook with what you have', 'reduce food waste'],
        'meta_title' => 'Meal Planning with What You Already Have',
        'meta_description' => 'A simple method to plan meals from your fridge and cupboards: spot what needs using, group the prep, then pick recipes. Less waste, less shopping.',
        'body' => <<<'TXT'
The first step in planning meals from your cupboards is not searching for a recipe but noticing which ingredients need using first. Make fresh produce, opened packs and use-by dates visible.

## Group the preparation

Next, think about shared preparation: vegetables that can be chopped on the same board or basic components that can go into the same pot save time across several meals. A tray of roasted vegetables, for example, can become a bowl, a pasta sauce and a soup.

## Use tools as a starting point

On the Turkish House Chef site, the pantry and “What can I cook?” tools suggest recipes from your saved ingredients. Treat their results as a starting point that depends on how up to date your records are, not as a promise.
TXT,
    ],
    'artan-yemekleri-saklama-ve-isitma' => [
        'slug' => 'storing-and-reheating-leftovers-safely',
        'title' => 'Storing and Reheating Leftovers Safely',
        'excerpt' => 'Cool leftovers quickly, divide them into small containers and check the temperature when reheating.',
        'category' => 'Storage guides',
        'keywords' => ['how to store leftovers', 'reheating leftovers safely', 'food safety at home'],
        'meta_title' => 'How to Store and Reheat Leftovers Safely',
        'meta_description' => 'Cool food fast in shallow containers, refrigerate within 2 hours, eat within a few days and reheat until piping hot all the way through.',
        'body' => <<<'TXT'
Dividing cooked food into wide, shallow containers helps it cool faster. How long it sits out, the temperature of the container and the real temperature of your fridge all matter; when conditions are doubtful, it is safer to throw food away than to eat it.

## General guidance

Most food-safety guidance recommends refrigerating leftovers within two hours of cooking, keeping them for only a short time (usually three to four days) and checking that every part reaches the same high temperature when you reheat them. When using a microwave, stirring or turning the food reduces cold spots.

## Rice needs extra care

Cooked rice should be cooled quickly and reheated only once, until steaming hot throughout.

## Not personal advice

This article is not personal health advice. Packaging instructions, your local authority’s guidance and any special dietary needs always come first.
TXT,
    ],
    'tariflerde-porsiyon-olcekleme' => [
        'slug' => 'how-to-scale-a-recipe-up-or-down',
        'title' => 'How to Scale a Recipe Up or Down',
        'excerpt' => 'When changing servings, don’t treat main ingredients and sensitive ones the same way.',
        'category' => 'Kitchen measures',
        'keywords' => ['how to scale a recipe', 'halving a recipe', 'doubling a recipe'],
        'meta_title' => 'How to Scale a Recipe Up or Down (Without Ruining It)',
        'meta_description' => 'Main ingredients scale easily; salt, spices, yeast and raising agents don’t. How to double or halve a recipe and handle awkward egg fractions.',
        'body' => <<<'TXT'
Scaling a recipe usually works directly for the main ingredients, but the same ratio does not always give good results for spices, salt, yeast and raising agents. Treat any suggested amounts as a starting point, then taste and check the consistency.

## Eggs and other whole items

For ingredients counted in pieces, such as eggs, fractional results need to become a realistic kitchen action. Using part of a beaten egg, or rounding the recipe to a more convenient number of servings, is clearer than “0.33 of an egg”.

## Pans and timing

A doubled recipe in a bigger pan may need more time; the same amount spread thinner may need less. Check for doneness rather than multiplying the cooking time.

## Honest uncertainty

House Chef does not invent false precision for measures that cannot be converted reliably; it prefers to show the uncertainty clearly.
TXT,
    ],
    'alerjen-etiketleri-nasil-okunur' => [
        'slug' => 'how-to-read-allergen-labels',
        'title' => 'How to Read Allergen Labels',
        'excerpt' => 'Learn why “contains”, “may contain” and cross-contamination warnings don’t mean the same thing.',
        'category' => 'Nutrition',
        'keywords' => ['allergen labels', 'may contain meaning', 'food allergy labels'],
        'meta_title' => 'How to Read Allergen Labels: “Contains” vs “May Contain”',
        'meta_description' => 'What “contains” and “may contain” mean on food labels, why recipes can’t replace packaging checks, and when to ask a health professional.',
        'body' => <<<'TXT'
“Contains” on a product or recipe means that the named allergen is present directly as an ingredient. “May contain traces” and cross-contamination warnings describe different kinds of risk and should not be read as definite ingredient information.

## Recipes and packaging

House Chef analyses are based on recipe data and how much we trust each source; they do not replace the current label on a packaged product. Brands, production lines and formulations can change, so always check the packaging again.

## The 14 major allergens

In the UK and EU, 14 allergens must be highlighted on labels: cereals containing gluten, crustaceans, eggs, fish, peanuts, soybeans, milk, nuts, celery, mustard, sesame, sulphites, lupin and molluscs.

## Medical needs come first

Anyone with an allergy or medical dietary need should follow the guidance of their own health professional and the manufacturer’s latest information.
TXT,
    ],
    'besin-degerlerinin-tahmini-olmasi' => [
        'slug' => 'why-recipe-nutrition-values-are-estimates',
        'title' => 'Why Recipe Nutrition Values Are Estimates',
        'excerpt' => 'When a recipe’s nutrition is not a lab result, the data source and measurement confidence shape the numbers.',
        'category' => 'Nutrition',
        'keywords' => ['recipe nutrition estimates', 'calorie calculation', 'nutrition data accuracy'],
        'meta_title' => 'Why Recipe Nutrition Values Are Only Estimates',
        'meta_description' => 'Recipe calories and macros depend on data sources, measure conversions, portion size and cooking losses. What that means for how you use them.',
        'body' => <<<'TXT'
Recipe nutrition values are calculated from the data source chosen for each ingredient, the measure conversion, the portion size and losses during cooking. That is why the result can change when the same dish is made with different brands or household measures.

## Different kinds of numbers

House Chef aims not to show calculated, editorial and verified values under the same label. When confidence is low, you should not draw firm health conclusions from the figures.

## How to use them

Use nutrition values to compare recipes and to get a general sense of a meal. This information is for general guidance only and does not replace diagnosis, treatment or a personal nutrition plan.
TXT,
    ],
    'kibris-mezeleriyle-pratik-sofra' => [
        'slug' => 'how-to-set-an-easy-cypriot-meze-table',
        'title' => 'How to Set an Easy Cypriot Meze Table',
        'excerpt' => 'Balance a table through temperature, texture and serving order rather than a huge number of plates.',
        'category' => 'Cypriot cuisine',
        'keywords' => ['cypriot meze', 'meze platter ideas', 'how to serve meze'],
        'meta_title' => 'How to Set an Easy Cypriot Meze Table at Home',
        'meta_description' => 'Plan a relaxed Cypriot-style meze: three textures, cold dishes made ahead, hot halloumi last, and plenty of bread, lemon and herbs.',
        'body' => <<<'TXT'
A small meze table can be complete with just three different textures: a cool yoghurt dish, a lemony pulse dip and a hot cheese or oven dish. That way each plate plays a different role.

## A simple starter menu

Try cacık or tzatziki, hummus, and pan-fried halloumi, with olives, tomatoes and cucumber on the side. Add a bean salad or courgette fritters if you are feeding more people.

## Think about serving order

Prepare the cold dishes in advance and bring out the hot dish last, and the table will be much easier to set. Bread, lemon wedges and fresh herbs tie the variety together.

## Make it your own

This is not a fixed traditional menu; it is a flexible way of setting the table that you can adapt to what you have at home and how many guests are coming.
TXT,
    ],
    'yaz-sicaklarinda-hafif-akdeniz-tarifleri' => [
        'slug' => 'light-mediterranean-recipes-for-hot-weather',
        'title' => 'Light Mediterranean Recipes for Hot Weather',
        'excerpt' => 'Simplify your summer menu with chilled pulses, fresh vegetables and lemony dressings.',
        'category' => 'Seasonal recipes',
        'keywords' => ['light summer recipes', 'mediterranean summer food', 'no cook meals'],
        'meta_title' => 'Light Mediterranean Recipes for Hot Summer Days',
        'meta_description' => 'Keep summer cooking light: chilled bean and chickpea bowls, crisp salads, lemon and olive oil dressings, and prep done in the cool of the day.',
        'body' => <<<'TXT'
To keep the menu light on hot days, instead of one big main course you can pair a cool bowl with pulses or protein with a fresh salad. Lemon, olive oil and fresh herbs complete the flavours without making them heavy.

## Plan the prep

Doing the preparation in the cooler part of the day reduces the work at serving time. Keeping dressings separate helps vegetables keep their texture.

## Ideas to try

A chickpea bowl with tahini dressing, shepherd’s salad, a white bean salad or chilled cacık all need little or no cooking.

## Stay hydrated

Fluid intake and personal dietary needs matter in summer; this article is not personalised health advice.
TXT,
    ],
    'cooking-mode-ile-adim-adim-ilerleme' => [
        'slug' => 'cooking-step-by-step-with-cooking-mode',
        'title' => 'Cooking Step by Step with Cooking Mode',
        'excerpt' => 'Use step tracking and multiple timers to match your own kitchen rhythm.',
        'category' => 'Kitchen organisation',
        'keywords' => ['cooking mode', 'recipe step tracker', 'kitchen timers'],
        'meta_title' => 'Cooking Step by Step with Cooking Mode',
        'meta_description' => 'How House Chef’s Cooking Mode helps you follow a recipe step by step with progress tracking and named timers — and why your senses still matter.',
        'body' => <<<'TXT'
Cooking Mode takes a recipe from the reading screen to a screen designed for cooking. Getting your ingredients ready before you start means your hands are less busy while you use the screen.

## Track your progress

When you complete a step, your progress is saved, and you can go back to re-read a note whenever you need to. If several timings run at once, naming each timer makes it easy to keep oven and hob tasks apart.

## Your senses still lead

This tool does not replace watching what is happening in the pan. Keep paying attention to smell, texture, colour and the safety notes as you follow the recipe. Cooking Mode is currently available to members on the Turkish edition of House Chef Cyprus.
TXT,
    ],
];
