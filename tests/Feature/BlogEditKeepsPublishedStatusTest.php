<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BlogEditKeepsPublishedStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_an_edit_of_a_published_post_keeps_it_published(): void
    {
        // 8 Ekim denetimi (kritik): düzenleme formunda "yayında" seçeneği yoktu;
        // kaydetmek yazıyı ilk seçenek olan "Fikir" durumuna düşürüyordu.
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $yazi = BlogPost::create(['title' => 'Yayındaki yazı', 'slug' => 'yayindaki-yazi', 'excerpt' => 'Özet', 'body' => $this->cesitliMetin('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed', 'status' => 'published', 'published_at' => now()]);

        $this->actingAs($admin)->get(route('admin.blog.edit', $yazi))
            ->assertOk()
            ->assertSee('value="published" selected', false);

        $this->actingAs($admin)->put(route('admin.blog.update', $yazi), [
            'title' => 'Yayındaki yazı', 'slug' => 'yayindaki-yazi', 'excerpt' => 'Özet güncellendi',
            'body' => $this->cesitliMetin('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40),
            'status' => 'published', 'fact_check_status' => 'passed', 'originality_status' => 'passed', 'editorial_status' => 'approved',
        ])->assertSessionHasNoErrors();

        $this->assertSame('published', $yazi->fresh()->status);
    }
}
