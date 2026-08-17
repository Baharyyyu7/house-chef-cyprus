<?php

namespace App\Notifications;

use App\Models\DigitalOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class DigitalOrderReady extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $orderId)
    {
        $this->afterCommit();
        $this->onQueue('mail');
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = DigitalOrder::query()->with('product')->findOrFail($this->orderId);
        $confirmationUrl = URL::signedRoute('shop.success', ['orderUuid' => $order->order_uuid]);

        return (new MailMessage)
            ->subject('Dijital ürününüz hazır — House Chef Cyprus')
            ->greeting('Merhaba '.$order->buyer_name.',')
            ->line($order->product->title.' için ödemeniz doğrulandı.')
            ->line('Sipariş kodunuz: '.$order->order_uuid)
            ->action('Ürünü güvenli şekilde indir', $confirmationUrl)
            ->line('Bu bağlantıyı paylaşmayın. İndirme hakkınız sipariş sayfasında gösterilir.');
    }
}
