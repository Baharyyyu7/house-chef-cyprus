# AI Media Provider Matrix

This matrix is deliberately based on configuration and prior safe diagnostics, not on the presence of an SDK.

| Provider | State | Public-blog use | Automatic activation |
| --- | --- | --- | --- |
| Built-in OpenAI image generation | CONNECTED_VERIFIED for this editorial batch | Approved synthetic illustration | Used only through the controlled import pipeline; no key is stored in the application. |
| Gemini Imagen | FAILED | Not selected | A prior safe diagnostic recorded an unavailable model/endpoint; no automatic retry. |
| Pexels | CONFIGURED_NOT_TESTED | Stock candidates only | Never auto-published; requires identity and licence review. |
| Unsplash | CONFIGURED_NOT_TESTED | Stock candidates only | Never auto-published; official API and attribution required. |
| Eden AI / BFL / Stability | AVAILABLE_NO_CREDENTIAL or DISABLED | Private comparison only | Owner action and separate cost approval required. |

The release command `content:import-approved-synthetic-blog-media` imports only the twenty versioned, user-approved source illustrations. It does not call an external provider, expose a key, or create a new charge.
