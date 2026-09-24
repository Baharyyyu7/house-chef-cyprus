/**
 * Hostinger'in cron servisi bu hesapta 2026-08-02'den beri hicbir isi
 * calistirmadi. Laravel zamanlayicisini disaridan tetiklemek icin bu Worker
 * dakikada bir /_zamanlayici/{token} adresini cagiriyor.
 *
 * Hedef adres HEDEF secret'inda; token kodda durmuyor.
 */
export default {
  async scheduled(event, env, ctx) {
    ctx.waitUntil(fetch(env.HEDEF, { method: 'GET' }));
  },

  async fetch(request, env) {
    return new Response('hc-zamanlayici', { status: 200 });
  },
};
