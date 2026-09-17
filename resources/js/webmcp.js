/**
 * HouseChef Cyprus - WebMCP (Web Model Context Protocol) Client Runtime
 *
 * Implements browser-standard Agentic Tools interface via `navigator.modelContext`
 * and `window.webmcp` for autonomous AI agents, Chrome Agentic Browsing,
 * Claude Computer Use, ChatGPT Browser, and screen readers.
 */

(function () {
    if (typeof window === 'undefined') return;

    class WebMcpRegistry {
        constructor() {
            this.tools = new Map();
            this.declarativeForms = new Map();
        }

        registerTool(tool) {
            if (!tool || !tool.name) {
                console.warn('[WebMCP] Invalid tool definition:', tool);
                return;
            }
            this.tools.set(tool.name, tool);
        }

        getTools() {
            return Array.from(this.tools.values()).map(t => ({
                name: t.name,
                description: t.description,
                parameters: t.parameters || {},
                risk_level: t.risk_level || 'read_only',
                requires_human_confirmation: !!t.requires_human_confirmation
            }));
        }

        async callTool(name, params = {}) {
            const tool = this.tools.get(name);
            if (!tool) {
                throw new Error(`[WebMCP] Unknown tool: "${name}". Available tools: ${Array.from(this.tools.keys()).join(', ')}`);
            }
            if (typeof tool.execute !== 'function') {
                throw new Error(`[WebMCP] Tool "${name}" does not provide an execute function.`);
            }
            return await tool.execute(params);
        }

        scanDeclarativeForms() {
            const forms = document.querySelectorAll('form[toolname]');
            forms.forEach(form => {
                const name = form.getAttribute('toolname');
                const description = form.getAttribute('tooldescription') || form.getAttribute('aria-label') || 'Form action';
                if (!name) return;

                this.declarativeForms.set(name, form);

                // Register as declarative tool if not already registered imperatively
                if (!this.tools.has(name)) {
                    this.registerTool({
                        name: name,
                        description: `[Declarative Form] ${description}`,
                        declarative: true,
                        formAction: form.action,
                        formMethod: form.method || 'GET',
                        execute: async (params = {}) => {
                            const formData = new FormData(form);
                            for (const [k, v] of Object.entries(params)) {
                                formData.set(k, v);
                            }
                            if (form.method.toUpperCase() === 'GET') {
                                const url = new URL(form.action, window.location.origin);
                                for (const [k, v] of formData.entries()) {
                                    url.searchParams.set(k, v);
                                }
                                window.location.href = url.toString();
                                return { navigated: true, url: url.toString() };
                            } else {
                                form.submit();
                                return { submitted: true };
                            }
                        }
                    });
                }
            });
        }
    }

    const registry = new WebMcpRegistry();

    // Helper for JSON tool API requests
    async function apiRequest(endpoint, body = {}) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(body)
        });

        if (!response.ok) {
            const errData = await response.json().catch(() => ({}));
            throw new Error(errData.message || `HTTP ${response.status} error from ${endpoint}`);
        }

        return await response.json();
    }

    // Register 1: search_recipes
    registry.registerTool({
        name: 'search_recipes',
        description: 'Search HouseChef Cyprus recipes with multi-constraint filters (keyword, cuisine, max cooking time, dietary preferences, difficulty).',
        risk_level: 'read_only',
        parameters: {
            type: 'object',
            properties: {
                query: { type: 'string', description: 'Keyword to search in titles and excerpts.' },
                cuisine: { type: 'string', description: 'Cuisine slug (e.g. kibris-mutfagi, turk-mutfagi).' },
                category: { type: 'string', description: 'Category slug (e.g. ana-yemek, tatli, corba).' },
                max_minutes: { type: 'integer', description: 'Maximum total time (prep + cook) in minutes.' },
                difficulty: { type: 'string', enum: ['kolay', 'orta', 'zor'], description: 'Difficulty level.' },
                dietary_tag: { type: 'string', description: 'Dietary preference code.' }
            }
        },
        execute: async (params) => {
            return await apiRequest('/api/v1/agent/tools/search', params);
        }
    });

    // Register 2: find_recipes_by_ingredients
    registry.registerTool({
        name: 'find_recipes_by_ingredients',
        description: 'Match and rank recipes based on available kitchen/pantry ingredients. Answers "What can I cook with ingredient X, Y, Z?".',
        risk_level: 'read_only',
        parameters: {
            type: 'object',
            required: ['ingredients'],
            properties: {
                ingredients: { type: 'array', items: { type: 'string' }, description: 'List of ingredient names or IDs.' },
                excluded: { type: 'array', items: { type: 'string' }, description: 'Ingredients to exclude.' },
                max_missing: { type: 'integer', description: 'Maximum missing required ingredients allowed (default 2).' },
                max_minutes: { type: 'integer', description: 'Maximum total cooking time.' }
            }
        },
        execute: async (params) => {
            return await apiRequest('/api/v1/agent/tools/find-by-ingredients', params);
        }
    });

    // Register 3: get_recipe
    registry.registerTool({
        name: 'get_recipe',
        description: 'Retrieve complete structured information for a recipe: title, description, scaled ingredients, steps, timings, equipment, nutrition, and allergens.',
        risk_level: 'read_only',
        parameters: {
            type: 'object',
            required: ['slug'],
            properties: {
                slug: { type: 'string', description: 'Recipe slug or public ID.' },
                servings: { type: 'integer', description: 'Optional desired portion count.' }
            }
        },
        execute: async (params) => {
            return await apiRequest('/api/v1/agent/tools/recipe', params);
        }
    });

    // Register 4: adjust_servings
    registry.registerTool({
        name: 'adjust_servings',
        description: 'Calculate scaled ingredient amounts and nutrition values according to requested target serving count.',
        risk_level: 'read_only',
        parameters: {
            type: 'object',
            required: ['slug', 'servings'],
            properties: {
                slug: { type: 'string', description: 'Recipe slug or public ID.' },
                servings: { type: 'integer', minimum: 1, maximum: 99, description: 'Target portion count.' }
            }
        },
        execute: async (params) => {
            return await apiRequest('/api/v1/agent/tools/adjust-servings', params);
        }
    });

    // Register 5: get_cooking_step
    registry.registerTool({
        name: 'get_cooking_step',
        description: 'Get instruction, timing, duration, equipment, and chef tips for a specific cooking step.',
        risk_level: 'read_only',
        parameters: {
            type: 'object',
            required: ['slug', 'step'],
            properties: {
                slug: { type: 'string', description: 'Recipe slug.' },
                step: { type: 'integer', minimum: 1, description: 'Step number.' }
            }
        },
        execute: async (params) => {
            return await apiRequest('/api/v1/agent/tools/cooking-step', params);
        }
    });

    // Register 6: switch_language
    registry.registerTool({
        name: 'switch_language',
        description: 'Get the localized canonical destination URL to view content in Turkish (tr) or English (en).',
        risk_level: 'read_only',
        parameters: {
            type: 'object',
            required: ['locale'],
            properties: {
                locale: { type: 'string', enum: ['tr', 'en'], description: 'Target language.' },
                path: { type: 'string', description: 'Relative path to translate. Defaults to current path.' }
            }
        },
        execute: async (params) => {
            const path = params.path || window.location.pathname;
            return await apiRequest('/api/v1/agent/tools/switch-language', { locale: params.locale, path: path });
        }
    });

    // Register 7: save_recipe
    registry.registerTool({
        name: 'save_recipe',
        description: 'Save or remove a recipe from personal favorites. Requires authenticated session with explicit human confirmation.',
        risk_level: 'write',
        requires_human_confirmation: true,
        parameters: {
            type: 'object',
            required: ['slug'],
            properties: {
                slug: { type: 'string', description: 'Recipe slug.' }
            }
        },
        execute: async (params) => {
            return await apiRequest('/api/v1/agent/tools/save-recipe', params);
        }
    });

    // Attach to standard navigator.modelContext and window.webmcp
    if (!navigator.modelContext) {
        navigator.modelContext = registry;
    }
    window.webmcp = registry;

    // Scan declarative tools when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => registry.scanDeclarativeForms());
    } else {
        registry.scanDeclarativeForms();
    }

    window.dispatchEvent(new CustomEvent('webmcp:ready', { detail: registry }));
})();
