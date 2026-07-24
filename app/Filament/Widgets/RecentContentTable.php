<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PostResource;
use App\Models\Post;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentContentTable extends TableWidget
{
    protected static ?string $heading = 'Latest journal posts';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->posts())
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No posts yet')
            ->emptyStateIcon('heroicon-o-document-text')
            ->recordUrl(fn (Post $record): string => PostResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('title')->searchable()->weight('bold')->limit(60),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'published' ? 'success' : 'warning'),
                TextColumn::make('published_at')->label('Published')->date()->placeholder('—')->sortable(),
                TextColumn::make('created_at')->label('Created')->since()->sortable(),
            ]);
    }

    /** @return Builder<Post> */
    protected function posts(): Builder
    {
        return Post::query();
    }
}
