# AI Provider Layer

`AiKitchenProviderInterface` defines `name`, safe `health`, and `respond`. `AiKitchenProviderRegistry` resolves a configured fallback chain. The local mock provider is bill-free and is the default production-safe baseline. Disabled remote providers return a safe unavailable code; they do not make network requests.

Before enabling a remote provider: obtain an approved budget, configure the key only in the deployment secret store, set a timeout, confirm data-processing terms, and test provider health without logging the key. Provider activation is deliberately not automatic.
