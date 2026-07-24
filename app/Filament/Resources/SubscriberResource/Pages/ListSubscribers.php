<?php

namespace App\Filament\Resources\SubscriberResource\Pages;

use App\Filament\Resources\SubscriberResource;
use App\Models\Subscriber;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListSubscribers extends ListRecords
{
    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (): StreamedResponse => $this->exportCsv()),
        ];
    }

    /** Streamed + chunked so a large list never loads fully into memory. */
    protected function exportCsv(): StreamedResponse
    {
        $filename = 'subscribers-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            // Explicit $escape ('' = RFC 4180) — PHP 8.4 deprecates relying on the default.
            fputcsv($out, ['Email', 'Source page', 'Subscribed at', 'Unsubscribed at'], ',', '"', '');

            Subscriber::query()->orderBy('created_at')->chunk(500, function ($rows) use ($out): void {
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row->email,
                        $row->source_page,
                        $row->created_at?->toDateTimeString(),
                        $row->unsubscribed_at?->toDateTimeString(),
                    ], ',', '"', '');
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
