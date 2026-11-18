<?php

namespace App\Filament\Resources\Materials\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                FileUpload::make('image-cover')
                    ->image()
                    ->disk('public')
                    ->directory('materials')
                    ->imageAspectRatio('4:3')
                    ->automaticallyOpenImageEditorForAspectRatio(),
                RichEditor::make('content')
                    ->json()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsVisibility('public')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
