<?php

namespace App\Events;

use App\Models\Document;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel; // Pakai PresenceChannel untuk multi-user & live cursor
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Mengirim langsung tanpa queue
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $document;
    public $content;
    public $userId;

    // Ambil data dokumen, konten terbaru, dan siapa yang mengetik
    public function __construct(Document $document, $content, $userId)
    {
        $this->document = $document;
        $this->content = $content;
        $this->userId = $userId;
    }

    // Arahkan ke channel khusus untuk dokumen ini
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('document.' . $this->document->id),
        ];
    }

    // Data apa saja yang mau dilempar secara live ke HP/Laptop lain
    public function broadcastWith(): array
    {
        return [
            'content' => $this->content,
            'user_id' => $this->userId,
        ];
    }
}