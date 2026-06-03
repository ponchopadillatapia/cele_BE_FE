<?php

namespace App\Console\Commands;

use App\Events\NewNotification;
use App\Models\Notification;
use Illuminate\Console\Command;

class SendTestNotification extends Command
{
    protected $signature = 'notification:test {user_id} {type?}';
    protected $description = 'Enviar una notificación de prueba a un usuario';

    private array $samples = [
        'mensaje' => [
            'title' => 'Nuevo mensaje',
            'body' => 'María López te envió un mensaje en el chat de Tecnología.',
            'link' => '/chat',
        ],
        'multa' => [
            'title' => 'Multa registrada',
            'body' => 'Se ha registrado una multa por incumplimiento del reglamento interno. Revise los detalles.',
            'link' => '/multas/1',
        ],
        'asamblea' => [
            'title' => 'Asamblea programada',
            'body' => 'Se convoca a asamblea general ordinaria para el próximo sábado a las 10:00 AM.',
            'link' => '/asambleas/1',
        ],
        'pago_atrasado' => [
            'title' => 'Pago atrasado',
            'body' => 'Tiene un pago de mantenimiento pendiente del mes anterior. Evite recargos.',
            'link' => '/pagos/pendientes',
        ],
    ];

    public function handle(): void
    {
        $userId = $this->argument('user_id');
        $type = $this->argument('type') ?? array_rand($this->samples);

        if (!isset($this->samples[$type])) {
            $this->error("Tipo inválido. Opciones: mensaje, multa, asamblea, pago_atrasado");
            return;
        }

        $sample = $this->samples[$type];

        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $sample['title'],
            'body' => $sample['body'],
            'link' => $sample['link'],
        ]);

        broadcast(new NewNotification($notification));

        $this->info("Notificación de tipo '{$type}' enviada al usuario #{$userId}");
    }
}
